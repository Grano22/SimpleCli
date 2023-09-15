<?php

declare(strict_types=1);

namespace Grano22\SimpleCli\Command\Input\Exception;

use Exception;

class UnusedCommandParts extends Exception
{
    public static function createWithParsedParts(array $unusedParts): self
    {
        return new self($unusedParts);
    }

    private function __construct(private array $unusedParts) {
        parent::__construct(
            "Unused parts: " .
            implode(
                ',',
                array_map(static fn(array $unusedPart) => $unusedPart[0] . ' - ' . $unusedPart[1], $this->unusedParts)
            )
        );
    }
}
