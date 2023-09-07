<?php

declare(strict_types=1);

namespace Grano22\SimpleCli\App\Definition;

use Grano22\SimpleCli\App\SimpleCliAppConfig;
use Grano22\SimpleCli\Command\Input\SimpleCliCommandsStack;
use Grano22\SimpleCli\Command\SimpleCliCommand;
use Grano22\SimpleCli\SimpleCliOperator;

class SimpleCliAppDefinition
{
    private SimpleCliCommandsStack $definedCommands;
    private SimpleCliOperator $cliOperator;

    public static function createWithConfigAndCommands(SimpleCliAppConfig $config, array $commands): self
    {
        return new self($config, $commands);
    }

    private function __construct(
        private SimpleCliAppConfig $config,
        /** @var SimpleCliCommand[] $commands */
        array $commands = []
    ) {
        $this->definedCommands = new SimpleCliCommandsStack(...$commands);
        $this->cliOperator = new SimpleCliOperator();
    }

    public function getConfig(): SimpleCliAppConfig
    {
        return $this->config;
    }

    public function getCommandByName(string $name): SimpleCliCommand
    {
        return $this->definedCommands->getByName($name);
    }
}
