<?php

declare(strict_types=1);

namespace Grano22\SimpleCli\Tests\Unit\Input;

use Grano22\SimpleCli\Command\Input\CommandPartsBuilder;
use Grano22\SimpleCli\Command\Input\SimpleCliArgumentsCollection;
use Grano22\SimpleCli\Command\Input\SimpleCliOption;
use Grano22\SimpleCli\Command\Input\SimpleCliOptionsCollection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(CommandPartsBuilder::class)]
class CommandPartsBuilderTest extends TestCase
{
    public static function provideArgs(): iterable
    {
        yield 'All type of args' => [
            'argv' => [
                'Run',
                'starship',
                'normal',
                '-v',
                '-cab',
                '-nrvi',
                '--watch',
                '--level=3'
            ],
            'result' => [
                'usagesMap' => [
                    0 => false,
                    1 => false,
                    'v' => false,
                    'c' => false,
                    'n' => false,
                    'watch' => false,
                    'level' => false,
                    'a' => false,
                    'b' => false
                ],
                'usagesMapValues' => [
                    'n' => [
                        'rvi' => false
                    ]
                ],
                'parts' => [
                    'argument' => [
                        0 => 'starship',
                        1 => 'normal'
                    ],
                    'short_option' => [
                        0 => 'v',
                        1 => 'c',
                        2 => 'n',
                        3 => 'a',
                        4 => 'b',
                    ],
                    'long_option' => [
                        0 => 'watch',
                        1 => 'level'
                    ],
                ],
                'values' => [
                    0 => 'starship',
                    1 => 'normal',
                    'level' => '3',
                    'n' => 'rvi',
                ],
                'independent' => [],
            ],
            'options' => [
                new SimpleCliOption(
                    'c',
                    SimpleCliOption::NEGABLE | SimpleCliOption::OPTIONAL
                ),
                new SimpleCliOption(
                    'a',
                    SimpleCliOption::NEGABLE
                ),
                new SimpleCliOption(
                    'b',
                    SimpleCliOption::NEGABLE
                ),
                new SimpleCliOption(
                    'n',
                    SimpleCliOption::OPTIONAL
                ),
                new SimpleCliOption(
                    'watch',
                    SimpleCliOption::NEGABLE
                ),
                new SimpleCliOption(
                    'level',
                    SimpleCliOption::REQUIRED
                )
            ]
        ];
    }

    #[DataProvider('provideArgs')]
    public function testCommandPartsBuiltAsExpected(array $argv, array $result, array $options): void
    {
        // Arrange
        $argc = count($argv);
        $definedArguments = new SimpleCliArgumentsCollection();
        $definedOptions = new SimpleCliOptionsCollection(...$options);
        $commandPartBuilder = new CommandPartsBuilder();

        // Act
        $commandParts = $commandPartBuilder->build($argv, $argc);
        $finalizedCommandParts = $commandPartBuilder->rebuildWithSeparatedArgs($commandParts, $definedArguments, $definedOptions);

        // Assert
        self::assertEquals($result, $finalizedCommandParts);
    }
}
