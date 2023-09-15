SimpleCli
===========

### Project Motto

'Create CLI Apps with easy and modular way :)'

## Description

This project is created to make much easier to create awesome CLI app with additional modules (addons) to
handle various use cases for example: OutputWriter is standalone component, also TUI and more! :)

## Features

* Parse input args into top level command, subcommands, arguments and options,
* Multi levels commands (coming soon),
* Event-driven way - handle any kind of errors and other actions,
* Modular - App addons (coming soon),

## Examples

__General example__:

```php
use Grano22\SimpleCli\App\Factory\SimpleCliUniversalFilterConstraint;
use Grano22\SimpleCli\App\SimpleCliAppFactory;
use Grano22\SimpleCli\Command\Input\SimpleCliArgument;
use Grano22\SimpleCli\Command\Input\SimpleCliCommandInput;
use Grano22\SimpleCli\Command\Input\SimpleCliOption;

$myAwesomeCliApp = SimpleCliAppFactory::create()
    // Define help independent option for all commands
    ->withCommandsOption(
        commandNames: SimpleCliUniversalFilterConstraint::wildcard('*'),
        name: 'help',
        options: SimpleCliOption::OPTIONAL | SimpleCliOption::IGNORE_REST_REQUIRED | SimpleCliOption::NEGABLE
    )
    // Add your custom unknown error handler
    ->setUnexpectedErrorHandler(static function(Exception $exception) {
        // Do something with your custom exception
        
        echo $exception->getMessage();
        
        exit(1);
    })
    ->withCommandsArguments(
        commandNames: ['greetings'],
        name: 'tone',
        options: SimpleCliArgument::REQUIRED
    )
    ->withCommandOption(
        commandName: ['greetings', 'info'],
        name: 'verbose',
        options: SimpleCliOption::OPTIONAL
    )
    // Define your command logic :)
    ->withCommand(
        name: 'greetings',
        executionLogic: static function(SimpleCliCommandInput $input): int {
            $message = "Hello world";
                    
            $tone = $input->getArguments()->getByName('tone')->getValue();
            
            if ($tone = 'high') {
                $message .= '!';
            }
            
            echo $message;
        
            return 0;
        }
    )
    ->build()
;

$myAwesomeCliApp->autoExecuteCommand();
```

---

__Adding event listeners__:

```php
$myAwesomeCliApp = SimpleCliAppFactory::create()
    // Define your event listener for one or multiple events
    ->addEventsListener(static function(CommandNotFoundEvent|MissingArgumentEvent $event) {
        echo "Missing argument or command";
        
        exit(1);
    }, [CommandNotFoundEvent::class, MissingArgumentEvent::class])
;
```

---

__Adding options with aliases and default values__

```php
$myAwesomeCliApp = SimpleCliAppFactory::create()
    ->withCommandOption(
        commandName: 'cmd1',
        name: 'optWithAliases',
        options: SimpleCliOption::REQUIRED,
        aliases: ['l', 'q']
    )
    ->withCommandOption(
        commandName: 'cmd1',
        name: 'optWithDefaultValue',
        options: SimpleCliOption::OPTIONAL,
        defaultValue: 'DEFAULT_VALUE'
    )
```