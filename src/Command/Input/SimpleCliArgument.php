<?php

declare(strict_types=1);

namespace Grano22\SimpleCli\Command\Input;

class SimpleCliArgument {
    public const OPTIONAL = 0b010;
    public const REQUIRED = 0b001;
    public const PIPED = 0b100;

    private mixed $value;

    public function __construct(
        private string $name,
        private int $options = 0
    ) {}

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
