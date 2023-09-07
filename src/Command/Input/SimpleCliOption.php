<?php

declare(strict_types=1);

namespace Grano22\SimpleCli\Command\Input;

use RuntimeException;

class SimpleCliOption {
    public const OPTIONAL = 0b010;
    public const REQUIRED = 0b001;
    public const NEGABLE = 0b100;

    private mixed $value;

    public function __construct(
        private string $name,
        private int $options = self::NEGABLE,
        /** @var string[] $aliases */
        private array $aliases = []
    ) {}

    public function getName(): string
    {
        return $this->name;
    }

    public function getShortNames(): array
    {
        return array_filter([$this->name, ...$this->aliases], static fn(string $name) => strlen($name) === 1);
    }

    public function getLongNames(): array
    {
        return array_filter([$this->name, ...$this->aliases], static fn(string $name) => strlen($name) > 1);
    }

    public function getAliases(): array
    {
        return $this->aliases;
    }

    public function getOptions(): int
    {
        return $this->options;
    }

    public function isNegable(): bool
    {
        return !!($this->options & SimpleCliOption::NEGABLE);
    }

    public function bindValue(mixed $newValue): self
    {
        $this->value = $newValue;

        return $this;
    }

    public function getValue(): mixed
    {
        if (!isset($this->value) && $this->value !== null) {
            throw new RuntimeException("Option of name {$this->name} is not bound");
        }

        return $this->value;
    }
}
