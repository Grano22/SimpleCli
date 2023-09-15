<?php

declare(strict_types=1);

namespace Grano22\SimpleCli\App;

use Exception;
use Grano22\SimpleCli\App\Shared\DI\AppContainer;
use Grano22\SimpleCli\App\Shared\Lib\Psr\Container\ContainerInterface;
use Grano22\SimpleCli\Command\Input\Exception\ArgumentIsMissing;
use Grano22\SimpleCli\Command\Input\Exception\CommandHandlerHasMissingReturnCode;
use Grano22\SimpleCli\Command\Input\Exception\CommandNotFound;
use Grano22\SimpleCli\Command\Input\Exception\CommandOptionIsMissing;
use Grano22\SimpleCli\Command\Input\Exception\CommandOptionOccurredAtLeastTwice;
use Grano22\SimpleCli\Command\Input\Exception\UnusedCommandParts;
use Grano22\SimpleCli\Command\Input\SimpleCliCommandsStack;
use Grano22\SimpleCli\Command\InputValidator;
use Grano22\SimpleCli\Command\SimpleCliCommand;
use Grano22\SimpleCli\Core\Event\CommandNotFoundEvent;
use Grano22\SimpleCli\Core\Event\FailureSimpleCliEvent;
use Grano22\SimpleCli\Core\Event\MissingArgumentEvent;
use Grano22\SimpleCli\Core\Event\MissingOptionEvent;
use Grano22\SimpleCli\Core\Event\MissingReturnCodeEvent;
use Grano22\SimpleCli\Core\Event\NotUsedCommandPartsEvent;
use Grano22\SimpleCli\Core\Event\OptionOccurredAtLeastTwiceEvent;
use Grano22\SimpleCli\Core\Event\SimpleCliEvent;
use Grano22\SimpleCli\Core\Event\SimpleCliEventsManager;
use Grano22\SimpleCli\Core\InvokerMap;
use Grano22\SimpleCli\SimpleCliOperator;
use JetBrains\PhpStorm\NoReturn;

class SimpleCliApp {
    public const HANDLER_UNEXPECTED_ERRORS = 'unexpected_errors';
    private SimpleCliCommandsStack $definedCommands;
    private ContainerInterface $container;
    private SimpleCliEventsManager $eventsManager;
    private InvokerMap $definedReactants;

    public static function createWithConfigAndCommands(
        SimpleCliAppConfig $config,
        array $commands,
        ?InvokerMap $definedReactants = null
    ): self {
        return new self($config, $commands, $definedReactants);
    }

    private function __construct(
        private SimpleCliAppConfig $config,
        /** @var SimpleCliCommand[] $commands */
        array $commands = [],
        ?InvokerMap $definedReactants = null
    ) {
        $this->definedCommands = new SimpleCliCommandsStack(...$commands);

        $this->prepareAppComponents($definedReactants);
    }

    public function getConfig(): SimpleCliAppConfig
    {
        return $this->config;
    }

    public function executeCommandWithSpecifiedArgs(array $customArgv, int $customArgc): void
    {
        $this->executeCommand($customArgv, $customArgc);
    }

    #[NoReturn]
    public function autoExecuteCommand(): void
    {
        global $argv, $argc;

        $this->executeCommand($argv, $argc);
    }

    private function executeCommand(array $argv, int $argc): void
    {
        try {
            /** @var SimpleCliOperator $cliOperator */
            $cliOperator = $this->container->get(SimpleCliOperator::class);
            $commandToUse = $cliOperator->prepareActualRequestedCommand($this->definedCommands, $argv, $argc);

            exit(
                $commandToUse->execute(
                    $cliOperator->readPipeData(),
                    new SimpleCliAppExecutionContext(
                        $this->config
                    )
                )
            );
        } catch (
            CommandNotFound |
            ArgumentIsMissing |
            CommandOptionIsMissing |
            UnusedCommandParts |
            CommandHandlerHasMissingReturnCode |
            CommandOptionOccurredAtLeastTwice
            $knownException
        ) {
//            $this->eventsManager->processEventsWithNew(FailureSimpleCliEvent::createFromException($knownException));
            $exceptionEvent = $this->createEventByExceptionType($knownException);
            $this->eventsManager->processEventsWithNew($exceptionEvent);
        } catch(Exception $unknownException) {
            foreach ($this->definedReactants->getInvocables(self::HANDLER_UNEXPECTED_ERRORS) as $invocable) {
                $invocable($unknownException);
            }
        }
    }

    private function prepareAppComponents(?InvokerMap &$definedReactants): void
    {
        $inputValidator = new InputValidator(true);
        $this->definedReactants = new InvokerMap();
        $this->eventsManager = new SimpleCliEventsManager();

        $this->definedReactants->predefineMapCollection(self::HANDLER_UNEXPECTED_ERRORS, 1);

        $finalHandlers = [
            self::HANDLER_UNEXPECTED_ERRORS => $definedReactants->getInvocable(self::HANDLER_UNEXPECTED_ERRORS) ??
            static function (Exception $unexpectedException) {
                echo 'Unexpected error occurred: ' . $unexpectedException->getMessage();
                exit(1);
            },
            'listeners' => [
                MissingArgumentEvent::class => [],
                MissingOptionEvent::class => [],
                NotUsedCommandPartsEvent::class => [],
                MissingReturnCodeEvent::class => [],
                CommandNotFoundEvent::class => [],
                OptionOccurredAtLeastTwiceEvent::class => []
            ]
        ];

        if ($definedReactants->hasSubset('listeners')) {
            $finalHandlers['listeners'] = array_merge(
                $finalHandlers['listeners'],
                $definedReactants->getSubset('listeners')->toMap()
            );
        }

        $this->definedReactants->addInvocable($finalHandlers[self::HANDLER_UNEXPECTED_ERRORS], self::HANDLER_UNEXPECTED_ERRORS);

        foreach ($finalHandlers['listeners'] as $definedEvent => $definedListeners) {
            if ($definedListeners === []) {
                $definedListeners[] = static function(FailureSimpleCliEvent $event) {
                    echo $event->getReason();
                    exit(1);
                };
            }

            foreach ($definedListeners as $definedListener) {
                $this->eventsManager->addEventListener($definedListener, [$definedEvent]);
            }
        }

        $definedReactants = null;

        $this->container = AppContainer::create([
            SimpleCliOperator::class => new SimpleCliOperator($inputValidator),
            InputValidator::class => $inputValidator,
            SimpleCliEventsManager::class => $this->eventsManager
        ]);
    }

    private function createEventByExceptionType(Exception $knownException): SimpleCliEvent
    {
        return match($knownException::class) {
            ArgumentIsMissing::class => MissingArgumentEvent::createFromException($knownException),
            CommandOptionIsMissing::class => MissingOptionEvent::createFromException($knownException),
            UnusedCommandParts::class => NotUsedCommandPartsEvent::createFromException($knownException),
            CommandHandlerHasMissingReturnCode::class => MissingReturnCodeEvent::createFromException($knownException),
            CommandNotFound::class => CommandNotFoundEvent::createFromException($knownException),
            CommandOptionOccurredAtLeastTwice::class => OptionOccurredAtLeastTwiceEvent::createFromException($knownException),
            default => FailureSimpleCliEvent::createFromException($knownException)
        };
    }
}
