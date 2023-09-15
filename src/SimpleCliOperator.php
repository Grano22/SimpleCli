<?php

declare(strict_types=1);

namespace Grano22\SimpleCli;

use Generator;
use Grano22\SimpleCli\Command\Input\CommandPartsBuilder;
use Grano22\SimpleCli\Command\Input\Exception\ArgumentIsMissing;
use Grano22\SimpleCli\Command\Input\Exception\CommandNotFound;
use Grano22\SimpleCli\Command\Input\Exception\CommandOptionIsMissing;
use Grano22\SimpleCli\Command\Input\Exception\CommandOptionOccurredAtLeastTwice;
use Grano22\SimpleCli\Command\Input\Exception\UnusedCommandParts;
use Grano22\SimpleCli\Command\Input\SimpleCliArgument;
use Grano22\SimpleCli\Command\Input\SimpleCliCommandsStack;
use Grano22\SimpleCli\Command\Input\SimpleCliOption;
use Grano22\SimpleCli\Command\InputValidator;
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

    public function __construct(
        private InputValidator $inputValidator
    ) {
    }

    /**
     * @throws CommandNotFound
     * @throws UnusedCommandParts
     * @throws CommandOptionIsMissing
     * @throws ArgumentIsMissing
     * @throws CommandOptionOccurredAtLeastTwice
     */
    public function prepareActualRequestedCommand(SimpleCliCommandsStack $definedCommands, array $argv, int $argc): ?SimpleCliCommand
    {
        $commandPartBuilder = new CommandPartsBuilder();
        $commandParts = $commandPartBuilder->build($argv, $argc);

        $requestedCommand = $argv[1] ?? '';
        $commandToUse = $definedCommands->getByName($requestedCommand);

        if (!$commandToUse) {
            throw new CommandNotFound($requestedCommand, 0);
        }

        $commandParts = $commandPartBuilder->rebuildWithSeparatedArgs(
            $commandParts,
            $commandToUse->getDefinedArguments(),
            $commandToUse->getDefinedOptions()
        );

        $this->invokedCommand = SimpleCliInvokedCommand::build(basename($argv[0]), $commandParts);
        $this->invokedCommand->markArgumentAsUsed(0);

        if ($commandParts['independent'] !== []) {
            $this->inputValidator->addException(
                InputValidator::OMIT_REQUIRED_OPTIONS | InputValidator::OMIT_REQUIRED_ARGUMENTS
            );
        }

        $this->readPipeData();
        $this->readOptions($commandToUse);
        $this->readArguments($commandToUse);

        $unusedParts = $this->invokedCommand->getUnusedParts();

        if ($unusedParts !== []) {
            throw UnusedCommandParts::createWithParsedParts($unusedParts);
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

    /**
     * @throws ArgumentIsMissing
     */
    private function readArguments(SimpleCliCommand $command): void
    {
        $allParts = $this->invokedCommand->getAllParts();
        $index = 1;

        foreach ($command->getDefinedArguments()->toArray() as $argument) {
            if (($argument->getOptions() & SimpleCliArgument::PIPED) && $this->hasPipedData()) {
                $argument->bindValue($this->readPipeData());

                continue;
            }

            $this->inputValidator->verifyIsArgumentMissing($argument, $index, $allParts);

            if (!isset($allParts[CommandPartsBuilder::ARGUMENT][$index])) {
                $argument->bindValue(null);

                continue;
            }

            $argument->bindValue($allParts[CommandPartsBuilder::ARGUMENT][$index]);
            $this->invokedCommand->markArgumentAsUsed($index);

            $index++;
        }
    }

    /**
     * @throws CommandOptionIsMissing
     * @throws CommandOptionOccurredAtLeastTwice
     */
    private function readOptions(SimpleCliCommand $command): void
    {
        foreach ($command->getDefinedOptions()->toArray() as $option) {
            $occurredTimes = 0;
            $optionValue = null;

            foreach ($option->getAllNames() as $alias) {
                $optionValue = $this->invokedCommand->getValueAssociatedToOption(
                    $alias,
                    strlen($alias) === 1,
                    !$option->isNegable()
                );
                $occurredTimes += (int)isset($optionValue);

                if ($occurredTimes > 1) {
                    throw CommandOptionOccurredAtLeastTwice::createForOptionAlias($option->getName(), $alias);
                }
            }

            if ($occurredTimes) {
                $option->bindValue($optionValue, true);

                continue;
            }

            $this->inputValidator->verifyIsOptionMissing($occurredTimes, $option);

            $option->bindValue($option->getDefaultValue());
        }
    }
}
