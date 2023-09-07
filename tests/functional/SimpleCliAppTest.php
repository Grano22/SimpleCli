<?php

declare(strict_types=1);

namespace Grano22\SimpleCli\Tests\Functional;

use Grano22\SimpleCli\Command\Input\SimpleCliOption;
use Grano22\SimpleCli\Tests\Functional\Utils\CommandTester;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class SimpleCliAppTest extends TestCase
{
    public function testCreatedAppWorkAsExpected(): void
    {
        // Arrange
        $argument_1 = 'Adamek';
        $short_option_value = 'ShortValue';
        $long_option_value = 'Long Value';
        $piped_data = 'piped data';
        $short_option_with_spaced_value = 'spaced_val';
        $short_option_with_trimed_value = 'trimed_val';
        $simplified_option_value = 'simple';
        $complex_option_value = 'Complex Value';

        // Act
        $commandTester = CommandTester::run(
            sprintf(
                'echo "%s" | php ' . __DIR__ . '/Utils/ctestrunner.php test:all' . str_repeat(' %s', 9),
                $piped_data,
                $argument_1,
                '--optNegable',
                "--optWithValue $short_option_value",
                "--optWithLongValue '$long_option_value'",
                "-o $short_option_with_spaced_value",
                "-p$short_option_with_trimed_value",
                "--optWithSimplifiedValue=$simplified_option_value",
                "--optWithComplexValue='$complex_option_value'",
                "-abc"
            ),
            __DIR__ . '/variants/MainVariant.php'
        );

        // Assert
        $commandTester->assertOutputEquals(
            <<<EXPECTED
            arg1 is $argument_1
            optNegable is set
            optWithValue is $short_option_value
            optWithLongValue is $long_option_value
            Piped argument directly: $piped_data
            Piped argument indirectly: $piped_data
            Short option with spaced value: $short_option_with_spaced_value
            Short option with trimed value: $short_option_with_trimed_value
            a is set
            b is set
            c is set
            Data from stdin: $piped_data
            EXPECTED
        );
        $commandTester->assertReturnCodeEquals(0);
    }

    #[DataProvider('provideWrongInputAndExpectedOutput')]
    public function testUnsupportedInputTypesFailsApp(array $givenInput, string $expectedOutput, array $extendCommand): void
    {
        // Arrange

        // Act
        $commandTester = CommandTester::run(
            sprintf(
                'echo "piped data" | php ' . __DIR__ . '/Utils/ctestrunner.php' .
                str_repeat(' %s', count($givenInput)),
                ...array_values($givenInput)
            ),
            __DIR__ . '/variants/MainVariant.php',
            $this->defineForCommand($extendCommand)
        );

        // Assert
        $commandTester->assertReturnCodeEquals(255);
        //$commandTester->assertOutputEquals($expectedOutput);
    }

    public static function provideWrongInputAndExpectedOutput(): iterable
    {
        $commandName = 'test:not-supported';

        yield [
            'givenInput' => [$commandName, '--optWithValue=ShortValue'],
            'expectedOutput' => '',
            'extendCommand' => [
                 [
                     'action' => 'createOption',
                    'commandsNames' => [$commandName],
                    'name' => 'optWithValue',
                    'options' => SimpleCliOption::REQUIRED
                ]
            ]
        ];

        yield [
            'givenInput' => [$commandName, "--optWithLongValue='Long Value'"],
            'expectedOutput' => '',
            'extendCommand' => [
                [
                    'action' => 'createOption',
                    'commandsNames' => [$commandName],
                    'name' => 'optWithLongValue',
                    'options' => SimpleCliOption::REQUIRED
                ]
            ]
        ];

        yield [
            'givenInput' => [$commandName, '-o value'],
            'expectedOutput' => '',
            'extendCommand' => [
                [
                    'action' => 'createOption',
                    'commandsNames' => [$commandName],
                    'name' => 'o',
                    'options' => SimpleCliOption::REQUIRED
                ]
            ]
        ];

        yield [
            'givenInput' => [$commandName, '-ovalue'],
            'expectedOutput' => '',
            'extendCommand' => [
                [
                    'action' => 'createOption',
                    'commandsNames' => [$commandName],
                    'name' => 'o',
                    'options' => SimpleCliOption::REQUIRED
                ]
            ]
        ];
    }

    private function defineForCommand(array $inputArgs): array
    {
        return $inputArgs;
    }

    private function defineCommandOption(array $inputOptions): array
    {
        return $inputOptions;
    }
}