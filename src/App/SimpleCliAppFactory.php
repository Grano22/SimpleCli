<?php

declare(strict_types=1);

namespace Grano22\SimpleCli\App;

use Closure;
use Grano22\SimpleCli\App\Factory\SimpleCliFilterConstraint;
use Grano22\SimpleCli\Command\Input\SimpleCliArgument;
use Grano22\SimpleCli\Command\Input\SimpleCliArgumentsCollection;
use Grano22\SimpleCli\Command\Input\SimpleCliCommandInput;
use Grano22\SimpleCli\Command\Input\SimpleCliCommandsStack;
use Grano22\SimpleCli\Command\Input\SimpleCliOption;
use Grano22\SimpleCli\Command\Input\SimpleCliOptionsCollection;
use Grano22\SimpleCli\Command\SimpleCliCommand;
use Grano22\SimpleCli\Core\InvokerMap;

class SimpleCliAppFactory {
    /** @var array<string, array> $definedCommands */
    private array $definedCommands = [];

    /** @var array<string, SimpleCliArgumentsCollection> $definedArgumentsForCommand */
    private array $definedArgumentsForCommand = [];

    /** @var array<string, SimpleCliOptionsCollection> $definedOptionsForCommand */
    private array $definedOptionsForCommand = [];

    private array $lateBoundOptions = [];

    private array $lateBoundArguments = [];

    private SimpleCliAppConfig $appConfig;
    private InvokerMap $handlersMap;
    private InvokerMap $listenersMap;

    public static function create(): self
    {
        return new self();
    }

    private function __construct()
    {
        $this->appConfig = new SimpleCliAppConfig(
            debug: false
        );
        $this->handlersMap = new InvokerMap();
        $this->listenersMap = new InvokerMap();
    }

    public function withCliAppConfiguration(
        ?bool $debug = null
    ): self {
        $this->appConfig = new SimpleCliAppConfig(
            debug: $debug ?? $this->appConfig->isDebugEnabled()
        );

        return $this;
    }

    public function withCommandArgument(
        string $commandName,
        string $name,
        int $options
    ): self {
        $previouslyDefinedArguments = isset($this->definedArgumentsForCommand[$commandName])
            ? $this->definedArgumentsForCommand[$commandName]->toArray()
            : [];

        $previouslyDefinedArguments[] = new SimpleCliArgument(
            $name,
            $options
        );

        $this->definedArgumentsForCommand[$commandName] = new SimpleCliArgumentsCollection(...$previouslyDefinedArguments);

        return $this;
    }

    public function withCommandsArgument(
        /** @param string[]|SimpleCliFilterConstraint $commandNames */
        array|SimpleCliFilterConstraint $commandNames,
        string $name,
        int $options
    ): self {
        if ($commandNames instanceof SimpleCliFilterConstraint) {
            $this->lateBoundArguments[$name] = [
                'wildcard' => $commandNames,
                'name' => $name,
                'options' => $options
            ];

            return $this;
        }

        foreach ($commandNames as $commandName) {
            $this->withCommandArgument($commandName, $name, $options);
        }

        return $this;
    }

    public function withCommandsArguments(
        /** @param string[] $commandNames */
        array $commandNames,
        array $names,
        int $options
    ): self {
        foreach ($names as $name) {
            $this->withCommandsArgument($commandNames, $name, $options);
        }

        return $this;
    }

    public function withCommandOption(
        string $commandName,
        string $name,
        int $options
    ): self {
        $previouslyDefinedOptions = isset($this->definedOptionsForCommand[$commandName])
            ? $this->definedOptionsForCommand[$commandName]->toArray()
            : [];

        $previouslyDefinedOptions[] = new SimpleCliOption(
            $name,
            $options
        );

        $this->definedOptionsForCommand[$commandName] = new SimpleCliOptionsCollection(...$previouslyDefinedOptions);

        return $this;
    }

    public function withCommandsOption(
        /** @param string[]|SimpleCliFilterConstraint $commandNames */
        array|SimpleCliFilterConstraint $commandNames,
        string $name,
        int $options
    ): self {
        if ($commandNames instanceof SimpleCliFilterConstraint) {
            $this->lateBoundOptions[$name] = [
                'wildcard' => $commandNames,
                'name' => $name,
                'options' => $options
            ];

            return $this;
        }

        foreach ($commandNames as $commandName) {
            $this->withCommandOption($commandName, $name, $options);
        }

        return $this;
    }

    public function withCommandsOptions(
        /** @param string[]|SimpleCliFilterConstraint $commandNames */
        array|SimpleCliFilterConstraint $commandNames,
        /** @param string[] $names */
        array $names,
        int $options
    ): self {
        foreach ($names as $name) {
            $this->withCommandsOption($commandNames, $name, $options);
        }

        return $this;
    }

    /** @param callable(SimpleCliCommandInput, SimpleCliAppExecutionContext): int $executionLogic */
    public function withCommand(
        string $name,
        callable $executionLogic
    ): self {
        if (!array_key_exists($name, $this->definedArgumentsForCommand)) {
            $this->definedArgumentsForCommand[$name] = new SimpleCliArgumentsCollection();
        }

        if (!array_key_exists($name, $this->definedOptionsForCommand)) {
            $this->definedOptionsForCommand[$name] = new SimpleCliOptionsCollection();
        }

        $this->definedCommands[$name] = [
            'name' => $name,
            'executionLogic' => Closure::fromCallable($executionLogic)
        ];

        return $this;
    }

    public function setUnexpectedErrorHandler(callable $fn): self
    {
        $this->handlersMap->addInvocable($fn, SimpleCliApp::HANDLER_UNEXPECTED_ERRORS);

        return $this;
    }

    public function addEventsListener(callable $fn, array $targetEvents): self
    {
        foreach ($targetEvents as $targetEvent) {
            $this->listenersMap->addInvocable($fn, $targetEvent);
        }

        return $this;
    }

    public function build(): SimpleCliApp
    {
        $createdCommands = [];

        foreach ($this->lateBoundArguments as $lateBoundArgument) {
            $commandNames = array_map(
                static fn(array $commandProto) => $commandProto['name'],
                $lateBoundArgument['wildcard']->resolveArray($this->definedCommands)
            );

            foreach ($commandNames as $commandName) {
                $this->withCommandOption($commandName, $lateBoundArgument['name'], $lateBoundArgument['options']);
            }
        }

        foreach ($this->lateBoundOptions as $lateBoundOption) {
            $commandNames = array_map(
                static fn(array $commandProto) => $commandProto['name'],
                $lateBoundOption['wildcard']->resolveArray($this->definedCommands)
            );

            foreach ($commandNames as $commandName) {
                $this->withCommandOption($commandName,$lateBoundOption['name'], $lateBoundOption['options']);
            }
        }

        foreach ($this->definedCommands as $definedCommand) {
            $createdCommands[$definedCommand['name']] = new SimpleCliCommand(
                $definedCommand['name'],
                Closure::fromCallable($definedCommand['executionLogic']),
                $this->definedArgumentsForCommand[$definedCommand['name']],
                $this->definedOptionsForCommand[$definedCommand['name']]
            );
        }

        $this->handlersMap->addSubset($this->listenersMap, 'listeners');

        return SimpleCliApp::createWithConfigAndCommands(
            $this->appConfig,
            $createdCommands,
            $this->handlersMap
        );
    }
}
