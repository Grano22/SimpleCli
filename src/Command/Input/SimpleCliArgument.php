<?php

declare(strict_types=1);

namespace Grano22\SimpleCli\Command\Input;

use Grano22\SimpleCli\Command\Input\Part\CommandPart;
use RuntimeException;

class SimpleCliArgument extends CommandPart {
    public const PIPED = 0b1000;

    private mixed $value;

    public function __construct(
        private string $name,
        int $options = 0
    ) {
        parent::__construct($options);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getOptions(): int
    {
        return $this->options;
    }

    public function bindValue(mixed $newValue): self
    {
        $this->value = $newValue;

        return $this;
    }

    public function getValue(): mixed
    {
        if (!isset($this->value) && $this->value !== null) {
            throw new RuntimeException("Argument of name {$this->name} is not bound");
        }

        return $this->value;
    }
}
