<?php

declare(strict_types=1);

namespace Grano22\SimpleCli\Command\Input\Exception;

use Exception;

class ArgumentIsMissing extends Exception
{
    public static function create(int $argumentExpectedPosition, string $argumentName): self
    {
        return new self($argumentExpectedPosition, $argumentName);
    }

    private function __construct(private int $argumentExpectedPosition, private string $argumentName) {
        parent::__construct(
            "Argument at $this->argumentExpectedPosition position named $this->argumentName must be specified"
        );
    }
}
