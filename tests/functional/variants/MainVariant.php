<?php

use Grano22\SimpleCli\App\SimpleCliAppFactory;
use Grano22\SimpleCli\Command\Input\SimpleCliArgument;
use Grano22\SimpleCli\Command\Input\SimpleCliCommandInput;
use Grano22\SimpleCli\Command\Input\SimpleCliOption;
use PHPUnit\Framework\Assert;

$cliApp = SimpleCliAppFactory::create()
    ->withCommandsArguments(
        commandNames: ['test:all'],
        name: 'arg1',
        options: SimpleCliArgument::REQUIRED
    )
    ->withCommandOption(
        commandName: 'test:all',
        name: 'optNegable',
        options: SimpleCliOption::NEGABLE | SimpleCliOption::REQUIRED
    )
    ->withCommand(
        name: 'test:all',
        executionLogic: static function(SimpleCliCommandInput $input) {
            $argument_1 = $input->getArguments()->getByName('arg1')->getValue();
            $option_1 = $input->getOptions()->getByName('optNegable')->getValue();

            if ($argument_1) {
                echo "arg1 is $argument_1" . PHP_EOL;
            }

            if ($option_1) {
                echo "optNegable is set" . PHP_EOL;
            }

            return 0;
        }
    )
    ->build();

return $cliApp;