<?php

declare(strict_types=1);

namespace Grano22\SimpleCli\Command\Input\Part;

abstract class CommandPart
{
    public function __construct(protected int $options) {}

    public const REQUIRED = 0b0001;
    public const OPTIONAL = 0b0010;

    public function isRequired(): bool
    {
        return !!($this->options & self::REQUIRED);
    }

    public function isOptional(): bool
    {
        return !!($this->options & self::OPTIONAL);
    }
}
