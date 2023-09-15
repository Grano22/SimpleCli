<?php

declare(strict_types=1);

namespace Grano22\SimpleCli\Command\Input\Exception;

use Exception;

class CommandOptionOccurredAtLeastTwice extends Exception
{
    public static function createForOptionAlias(string $optionName, string $optionAlias): self
    {
        return new self($optionName, $optionAlias);
    }

    private function __construct(private string $optionName, private string $optionAlias) {
        parent::__construct(
            "Option $this->optionName as alias $this->optionAlias can be only specified one time"
        );
    }
}
