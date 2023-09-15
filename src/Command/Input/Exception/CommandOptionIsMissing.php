<?php

declare(strict_types=1);

namespace Grano22\SimpleCli\Command\Input\Exception;

use Exception;

class CommandOptionIsMissing extends Exception
{
    public static function create(string $optionName): self
    {
        return new self($optionName);
    }

    private function __construct(private string $optionName) {
        parent::__construct(
            "Option $this->optionName is missing"
        );
    }
}