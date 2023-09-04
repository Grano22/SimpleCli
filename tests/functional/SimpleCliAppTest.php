<?php

declare(strict_types=1);

namespace Grano22\SimpleCli\Tests\Functional;

use Grano22\SimpleCli\Tests\Functional\Utils\CommandTester;
use PHPUnit\Framework\TestCase;

class SimpleCliAppTest extends TestCase
{
    public function testCreatedAppWorkAsExpected(): void
    {
        // Arrange
        $argument_1 = 'Adamek';

        // Act
        $commandTester = CommandTester::run(
            sprintf(
                'php ' . __DIR__ . '/Utils/ctestrunner.php test:all %s %s',
                $argument_1,
                '--optNegable'
            ),
            __DIR__ . '/variants/MainVariant.php'
        );

        // Assert
        $commandTester->assertOutputEquals(
            <<<EXPECTED
            arg1 is $argument_1
            optNegable is set
            EXPECTED
        );
        $commandTester->assertReturnCodeEquals(0);
    }

    private function mockAppExit(): void
    {

    }
}