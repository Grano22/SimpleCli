<?php

declare(strict_types=1);

namespace Grano22\SimpleCli;

use Generator;
use Grano22\SimpleCli\Command\Input\SimpleCliArgument;
use Grano22\SimpleCli\Command\Input\SimpleCliCommandsStack;
use Grano22\SimpleCli\Command\Input\SimpleCliOption;
use Grano22\SimpleCli\Command\SimpleCliCommand;
use Grano22\SimpleCli\Command\SimpleCliInvokedCommand;
use RuntimeException;

function non_block_read($fd): ?Generator {
    $read = [$fd];
    $write = null;
    $except = null;
    $result = stream_select($read, $write, $except, 0);
    if($result === false || $result === 0) return null;

    while ($nextStreamLine = stream_get_line($fd, 1))
        yield $nextStreamLine;
}

class SimpleCliOperator {
    private SimpleCliInvokedCommand $invokedCommand;
    private string $pipedData;

    public function __construct() {
        $this->invokedCommand = SimpleCliInvokedCommand::build();
    }

    public function prepareActualRequestedCommand(SimpleCliCommandsStack $definedCommands): ?SimpleCliCommand
    {
        global $argv;

        $requestedCommand = $argv[1] ?? '';
        $commandToUse = $definedCommands->getByName($requestedCommand);

        if (!$commandToUse) {
            echo "Unknown command $requestedCommand";
            exit(1);
        }

        $this->invokedCommand->markArgumentAsUsed(0);

        $this->readPipeData();
        $this->readOptions($commandToUse);
        $this->readArguments($commandToUse);

        $unusedParts = $this->invokedCommand->getUnusedParts();

        if ($unusedParts !== []) {
            throw new RuntimeException(
                "Unused parts: " .
                implode(',', array_map(static fn(array $unusedPart) => $unusedPart['content'], $unusedParts))
            );
        }

        return $commandToUse;
    }

    private function hasPipedData(): bool
    {
        return !!$this->pipedData;
    }

    public function readPipeData(): string
    {
        if (!isset($this->pipedData)) {
            $this->pipedData = '';

            foreach (non_block_read(STDIN) as $fragmentOfPipedData) {
                $this->pipedData .= $fragmentOfPipedData;
            }
        }

        return $this->pipedData;
    }

    private function readArguments(SimpleCliCommand $command): void
    {
        $allParts = $this->invokedCommand->getAllParts();
        $index = 1;

        foreach ($command->getDefinedArguments()->toArray() as $argument) {
            if (($argument->getOptions() & SimpleCliArgument::PIPED) && $this->hasPipedData()) {
                $argument->bindValue($this->readPipeData());

                continue;
            }

            if (($argument->getOptions() & SimpleCliArgument::REQUIRED) && !isset($allParts[$index])) {
                echo "Argument " . $index + 1 . " named {$argument->getName()} must be specified";
                exit(1);
            }

            if (($argument->getOptions() & SimpleCliArgument::OPTIONAL) && !isset($allParts[$index])) {
                $argument->bindValue(null);

                continue;
            }

            $argument->bindValue($allParts[$index]['content']);
            $this->invokedCommand->markArgumentAsUsed($index);

            $index++;
        }
    }

    private function readOptions(SimpleCliCommand $command): void
    {
        foreach ($command->getDefinedOptions()->toArray() as $option) {
            $occuredTimes = 0;

            foreach ([$option->getName(), ...$option->getAliases()] as $alias) {
                $optionValue = $this->invokedCommand->getValueAssociatedToOption(
                    $alias,
                    $alias !== $option->getName(),
                    !($option->getOptions() & SimpleCliOption::NEGABLE)
                );
                $occuredTimes += (int)isset($optionValue);

                if ($occuredTimes > 1) {
                    echo "Option {$option->getName()} as alias $alias can be only specified one time";

                    exit(1);
                }

                if ($occuredTimes) {
                    $option->bindValue($optionValue);

                    continue 2;
                }
            }

            if (!$occuredTimes && ($option->getOptions() & SimpleCliOption::REQUIRED)) {
                echo "Option {$option->getName()} is missing";

                exit(1);
            }

            $option->bindValue(null);
        }
    }
}
