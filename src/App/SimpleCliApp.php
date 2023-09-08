<?php

declare(strict_types=1);

namespace Grano22\SimpleCli\App;

use Grano22\SimpleCli\App\Shared\DI\AppContainer;
use Grano22\SimpleCli\App\Shared\Lib\Psr\Container\ContainerInterface;
use Grano22\SimpleCli\Command\Input\SimpleCliCommandsStack;
use Grano22\SimpleCli\Command\InputValidator;
use Grano22\SimpleCli\Command\SimpleCliCommand;
use Grano22\SimpleCli\SimpleCliOperator;
use JetBrains\PhpStorm\NoReturn;

class SimpleCliApp {
    private SimpleCliCommandsStack $definedCommands;
    private ContainerInterface $container;

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

        $this->prepareAppComponents();
    }

    public function getConfig(): SimpleCliAppConfig
    {
        return $this->config;
    }

    /** @return never-return */
    #[NoReturn]
    public function autoExecuteCommand()
    {
        /** @var SimpleCliOperator $cliOperator */
        $cliOperator = $this->container->get(SimpleCliOperator::class);
        $commandToUse = $cliOperator->prepareActualRequestedCommand($this->definedCommands);

        exit(
            $commandToUse->execute(
                $cliOperator->readPipeData(),
                new SimpleCliAppExecutionContext(
                    $this->config
                )
            )
        );
    }

    private function prepareAppComponents(): void
    {
        $inputValidator = new InputValidator(true);

        $this->container = AppContainer::create([
            SimpleCliOperator::class => new SimpleCliOperator($inputValidator),
            InputValidator::class => $inputValidator
        ]);
    }
}
