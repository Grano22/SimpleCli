<?php

use Grano22\SimpleCli\App\SimpleCliAppFactory;
use Grano22\SimpleCli\Command\Input\SimpleCliArgument;
use Grano22\SimpleCli\Command\Input\SimpleCliCommandInput;
use Grano22\SimpleCli\Command\Input\SimpleCliOption;

$cliAppProto = SimpleCliAppFactory::create()
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
    ->withCommandOption(
        commandName: 'test:all',
        name: 'optWithValue',
        options: SimpleCliOption::REQUIRED
    )
    ->withCommandsOptions(
        commandNames: ['test:all'],
        name: 'optWithLongValue',
        options: SimpleCliOption::REQUIRED
    )
    ->withCommandOption(
        commandName: 'test:all',
        name: 'optOptional',
        options: SimpleCliOption::OPTIONAL
    )
    ->withCommandOption(
        commandName: 'test:no-supported',
        name: 'o',
        options: SimpleCliOption::REQUIRED
    )
    ->withCommandArgument(
        commandName: 'test:all',
        name: 'argPiped',
        options: SimpleCliArgument::REQUIRED | SimpleCliArgument::PIPED
    )
    ->withCommand(
        name: 'test:all',
        executionLogic: static function(SimpleCliCommandInput $input) {
            $argument_1 = $input->getArguments()->getByName('arg1')->getValue();
            $option_1 = $input->getOptions()->getByName('optNegable')->getValue();
            $option_2 = $input->getOptions()->getByName('optWithValue')->getValue();
            $option_3 = $input->getOptions()->getByName('optWithLongValue')->getValue();
            $piped_option_directly = $input->getArguments()->getByName('argPiped')->getValue();
            $piped_data = $input->getPipedData(false);
            $piped_option_indirectly = $input->getPipedData(true);

            if ($argument_1) {
                echo "arg1 is $argument_1" . PHP_EOL;
            }

            if ($option_1) {
                echo "optNegable is set" . PHP_EOL;
            }

            if ($option_2) {
                echo "optWithValue is $option_2" . PHP_EOL;
            }

            if ($option_3) {
                echo "optWithLongValue is $option_3" . PHP_EOL;
            }

            if ($piped_option_directly) {
                echo "Piped argument directly: $piped_option_directly";
            }

            if ($piped_option_indirectly) {
                echo "Piped argument indirectly: $piped_option_indirectly";
            }

            echo "Data from stdin: $piped_data";

            return 0;
        }
    )
    ->withCommand(
        name: 'test:not-supported',
        executionLogic: static function(SimpleCliCommandInput $input): int {
            return 0;
        }
    )
;

return $cliAppProto;