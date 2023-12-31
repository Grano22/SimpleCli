<?php

declare(strict_types=1);

namespace Grano22\SimpleCli\Command\Input;

use Grano22\SimpleCli\Command\Input\Part\CommandPart;
use RuntimeException;

class SimpleCliOption extends CommandPart {
    public const NEGABLE = 0b0100;
    public const IGNORE_REST_REQUIRED = 0b1000;

    private mixed $value;
    private bool $defined = false;

    public function __construct(
        private string $name,
        int $options = self::NEGABLE,
        /** @var string[] $aliases */
        private array $aliases = [],
        private mixed $defaultValue = null
    ) {
        parent::__construct($options);
    }

    public function isDefined(): bool
    {
        return $this->defined;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getAllNames(): array
    {
        return [$this->name, ...$this->aliases];
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

    public function getDefaultValue(): mixed
    {
        return $this->defaultValue;
    }

    public function getOptions(): int
    {
        return $this->options;
    }

    public function isNegable(): bool
    {
        return !!($this->options & SimpleCliOption::NEGABLE);
    }

    public function bindValue(mixed $newValue, bool $valueIsDefined = false): self
    {
        $this->value = $newValue;
        $this->defined = $valueIsDefined;

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
