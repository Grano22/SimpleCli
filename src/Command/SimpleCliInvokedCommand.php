<?php

declare(strict_types=1);

namespace Grano22\SimpleCli\Command;

use Grano22\SimpleCli\Command\Input\CommandPartsBuilder;
use Grano22\SimpleCli\Command\Input\SimpleCliOption;
use RuntimeException;

class SimpleCliInvokedCommand
{
    public static function build(string $scriptPath, array $commandParts): self
    {
        return new self(
            $scriptPath,
            $commandParts
        );
    }

    public function __construct(
        private string $scriptPath,
        private array $commandParts
    ) {
    }

    public function markArgumentAsUsed(int $argumentIndex): void
    {
        $this->commandParts['usagesMap'][$argumentIndex] = true;
    }

    public function getValueAssociatedToOption(string $optionName, bool $short, bool $shouldHaveValue): string|null|bool
    {
        //var_dump($this->commandParts['usagesMap']);

        if (!isset($this->commandParts['usagesMap'][$optionName])) {
            return null;
        }

        if ($this->commandParts['usagesMap'][$optionName]) {
            throw new RuntimeException("Option $optionName is used twice");
        }

        $this->commandParts['usagesMap'][$optionName] = true;

        if (!$shouldHaveValue) {
            return true;
        }

        $optionValue = $this->commandParts['values'][$optionName] ?? null;

        if (!$short && $optionValue) {
            $this->commandParts['usagesMapValues'][$optionName][$optionValue] = true;
        }

        return $optionValue;
    }

    public function getAllParts(): array
    {
        return $this->commandParts['parts'];
    }

    public function getUnusedParts(): array
    {
        $usesMapValuesKeys = [];

        foreach ($this->commandParts['usagesMapValues'] as $typeName => $usagesMapValue) {
            foreach ($usagesMapValue as $valueKey => $used) {
                if (!$used) {
                    $usesMapValuesKeys[] = [$typeName, $valueKey];
                }
            }
        }

        foreach ($this->commandParts['usagesMap'] as $typeName => $used) {
            if (!$used) {
                $usesMapValuesKeys[] = [$typeName, $this->commandParts['values'][$typeName] ?? 'true'];
            }
        }

        return $usesMapValuesKeys;
    }
}
