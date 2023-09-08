SimpleCli
===========

### Project Motto
'Create CLI Apps and easy and modular way :)'

## Features

* Parse input args into top level command, subcommands, arguments and options
* Event-driven way
* Modular - App addons

## Examples

```php
use Grano22\SimpleCli\App\Factory\SimpleCliUniversalFilterConstraint;
use Grano22\SimpleCli\App\SimpleCliAppFactory;
use Grano22\SimpleCli\Command\Input\SimpleCliArgument;
use Grano22\SimpleCli\Command\Input\SimpleCliCommandInput;
use Grano22\SimpleCli\Command\Input\SimpleCliOption;

$myAwesomeCliApp = SimpleCliAppFactory::create()
    ->withCommandsOption(
        commandNames: SimpleCliUniversalFilterConstraint::wildcard('*'),
        name: 'help',
        options: SimpleCliOption::OPTIONAL | SimpleCliOption::IGNORE_REST_REQUIRED | SimpleCliOption::NEGABLE
    )
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