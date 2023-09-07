<?php

declare(strict_types=1);

namespace Grano22\SimpleCli\App;

use Closure;
use Grano22\SimpleCli\Command\Input\SimpleCliArgument;
use Grano22\SimpleCli\Command\Input\SimpleCliArgumentsCollection;
use Grano22\SimpleCli\Command\Input\SimpleCliCommandInput;
use Grano22\SimpleCli\Command\Input\SimpleCliOption;
use Grano22\SimpleCli\Command\Input\SimpleCliOptionsCollection;
use Grano22\SimpleCli\Command\SimpleCliCommand;

class SimpleCliAppFactory {
    /** @var array<string, SimpleCliCommand> $definedCommands */
    private array $definedCommands = [];

    /** @var array<string, SimpleCliArgumentsCollection> $definedArgumentsForCommand */
    private array $definedArgumentsForCommand = [];

    /** @var array<string, SimpleCliOptionsCollection> $definedOptionsForCommand */
    private array $definedOptionsForCommand = [];

    private SimpleCliAppConfig $appConfig;

    public static function create(): self
    {
        return new self();
    }

    private function __construct()
    {
        $this->appConfig = new SimpleCliAppConfig(
            debug: false
        );
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

    public function withCommandsArguments(
        /** @param string[] $commandNames */
        array $commandNames,
        string $name,
        int $options
    ): self {
        foreach ($commandNames as $commandName) {
            $this->withCommandArgument($commandName, $name, $options);
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

    public function withCommandsOptions(
        /** @param string[] $commandNames */
        array $commandNames,
        string $name,
        int $options
    ): self {
        foreach ($commandNames as $commandName) {
            $this->withCommandOption($commandName, $name, $options);
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

        $this->definedCommands[$name] = new SimpleCliCommand(
            $name,
            Closure::fromCallable($executionLogic),
            $this->definedArgumentsForCommand[$name],
            $this->definedOptionsForCommand[$name]
        );

        return $this;
    }

    public function build(): SimpleCliApp
    {
        return SimpleCliApp::createWithConfigAndCommands($this->appConfig, $this->definedCommands);
    }
}
