<?php

declare(strict_types=1);

namespace App;

use Exception;
use Grano22\SimpleCli\App\SimpleCliAppFactory;
use PHPUnit\Framework\TestCase;

class SimpleCliAppTest extends TestCase
{
    public function testErrorsAreHandledProperly(): void
    {
        // Arrange
        $unknownException = new Exception('Unknown error');
        $actualException = null;
        $app = $this->createBaseAppProto()
            ->withCommand(
                name: 'main',
                executionLogic: static function() use (&$unknownException) {
                    throw $unknownException;
                }
            )
            ->setUnexpectedErrorHandler(static function(Exception $exception) use (&$actualException) {
                $actualException = $exception;
            })
            ->build();

        // Act
        $app->executeCommandWithSpecifiedArgs([
            'test.php',
            'main'
        ], 1);

        // Assert
        self::assertEquals($unknownException, $actualException);
    }

    private function createBaseAppProto(): SimpleCliAppFactory
    {
        return SimpleCliAppFactory::create()
            ->withCliAppConfiguration(
                debug: false
            );
    }
}
