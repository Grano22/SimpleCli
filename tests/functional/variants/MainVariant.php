<?php

use Grano22\SimpleCli\App\Factory\SimpleCliUniversalFilterConstraint;
use Grano22\SimpleCli\App\SimpleCliAppFactory;
use Grano22\SimpleCli\Command\Input\SimpleCliArgument;
use Grano22\SimpleCli\Command\Input\SimpleCliCommandInput;
use Grano22\SimpleCli\Command\Input\SimpleCliOption;

$cliAppProto = SimpleCliAppFactory::create()
    ->withCommandsOption(
        commandNames: SimpleCliUniversalFilterConstraint::wildcard('*'),
        name: 'help',
        options: SimpleCliOption::OPTIONAL | SimpleCliOption::IGNORE_REST_REQUIRED | SimpleCliOption::NEGABLE
    )
    ->withCommandsArgument(
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
    ->withCommandsOption(
        commandNames: ['test:all'],
        name: 'optWithLongValue',
        options: SimpleCliOption::REQUIRED
    )
    ->withCommandOption(
        commandName: 'test:all',
        name: 'optOptional',
        options: SimpleCliOption::OPTIONAL
    )
    ->withCommandArgument(
        commandName: 'test:all',
        name: 'argPiped',
        options: SimpleCliArgument::REQUIRED | SimpleCliArgument::PIPED
    )
    ->withCommandOption(
        commandName: 'test:all',
        name: 'o',
        options: SimpleCliOption::REQUIRED
    )
    ->withCommandOption(
        commandName: 'test:all',
        name: 'p',
        options: SimpleCliOption::REQUIRED
    )
    ->withCommandOption(
        commandName: 'test:all',
        name: 'optWithSimplifiedValue',
        options: SimpleCliOption::REQUIRED
    )
    ->withCommandOption(
        commandName: 'test:all',
        name: 'optWithComplexValue',
        options: SimpleCliOption::REQUIRED
    )
    ->withCommandOption(
        commandName: 'test:all',
        name: 'a',
        options: SimpleCliOption::REQUIRED | SimpleCliOption::NEGABLE
    )
    ->withCommandOption(
        commandName: 'test:all',
        name: 'b',
        options: SimpleCliOption::REQUIRED | SimpleCliOption::NEGABLE
    )
    ->withCommandOption(
        commandName: 'test:all',
        name: 'c',
        options: SimpleCliOption::REQUIRED | SimpleCliOption::NEGABLE
    )
//    ->withCommandOption(
//        commandName: 'test:no-supported',
//        name: 'o',
//        options: SimpleCliOption::REQUIRED
//    )
    ->withCommand(
        name: 'test:all',
        executionLogic: static function(SimpleCliCommandInput $input) {
            if ($input->getOptions()->getByName('help')->getValue()) {
                echo "Help page";

                return 0;
            }

            $argument_1 = $input->getArguments()->getByName('arg1')->getValue();
            $option_1 = $input->getOptions()->getByName('optNegable')->getValue();
            $option_2 = $input->getOptions()->getByName('optWithValue')->getValue();
            $option_3 = $input->getOptions()->getByName('optWithLongValue')->getValue();
            $piped_option_directly = $input->getArguments()->getByName('argPiped')->getValue();
            $piped_data = $input->getPipedData(false);
            $piped_option_indirectly = $input->getPipedData(true);
            $shortOptionWithSpacedValue = $input->getOptions()->getByName('o')->getValue();
            $shortOptionWithTrimedValue = $input->getOptions()->getByName('p')->getValue();
            $splited_negable_option_a = $input->getOptions()->getByName('a')->getValue();
            $splited_negable_option_b = $input->getOptions()->getByName('b')->getValue();
            $splited_negable_option_c = $input->getOptions()->getByName('c')->getValue();

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

            if ($shortOptionWithSpacedValue) {
                echo "Short option with spaced value: $shortOptionWithSpacedValue" . PHP_EOL;
            }

            if ($shortOptionWithTrimedValue) {
                echo "Short option with trimed value: $shortOptionWithTrimedValue" . PHP_EOL;
            }

            if ($splited_negable_option_a) {
                echo "a is set" . PHP_EOL;
            }

            if ($splited_negable_option_b) {
                echo "b is set" . PHP_EOL;
            }

            if ($splited_negable_option_c) {
                echo "c is set" . PHP_EOL;
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