<?php

declare(strict_types=1);

namespace Grano22\SimpleCli\Command\Input\Exception;

use Exception;

class CommandHandlerHasMissingReturnCode extends Exception
{
    public static function createWithHandlerDetails(string $commandName): self
    {
        return new self($commandName);
    }

    private function __construct(string $commandName) {
        parent::__construct(
            "Command $commandName has missing return code on handler"
        );
    }
}
