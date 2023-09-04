<?php

declare(strict_types=1);

namespace Grano22\SimpleCli\Command;

use RuntimeException;

class SimpleCliInvokedCommand
{
    public static function build(): self
    {
        global $argv, $argc;

        $commandParts = [];
        for ($argi = 1; $argi < $argc; $argi++) {
            if (
                str_starts_with('--', $argv[$argi]) &&
                in_array($argv[$argi], $commandParts)
            ) {
                throw new RuntimeException("Duplicated option: {$argv[$argi]}");
            }

            $commandParts[] = ['content' => $argv[$argi], 'used' => false];
        }

        return new self(
            basename($argv[0]),
            $commandParts
        );
    }

    public function __construct(
        private string $scriptPath,
        private array  $commandParts
    )
    {
    }

    public function markArgumentAsUsed(int $argumentIndex): void
    {
        $this->commandParts[$argumentIndex]['used'] = true;
    }

    public function getValueAssociatedToOption(string $optionName, bool $short, bool $shouldHaveValue): string|null|bool
    {
        if ($short) {
            $foundShortOption = current(
                array_filter(
                    $this->commandParts,
                    static fn(array $part) => !$part['used'] && str_starts_with($part['content'], '-' . $optionName)
                )
            );

            if (!$foundShortOption) {
                return null;
            }

            $partIndex = array_search(['content' => '-' . $optionName, 'used' => false], $this->commandParts);
            $this->commandParts[$partIndex]['used'] = true;

            if (!$shouldHaveValue) {
                return true;
            }

            return str_replace('-' . $optionName, '', $foundShortOption);
        }

        $partIndex = array_search(['content' => '--' . $optionName, 'used' => false], $this->commandParts);

        if ($partIndex === false) {
            return null;
        }

        $this->commandParts[$partIndex]['used'] = true;

        if (!$shouldHaveValue) {
            return true;
        }

        if (!isset($this->commandParts[$partIndex + 1])) {
            throw new RuntimeException("Missing value for long option $optionName");
        }

        $optionValue = $this->commandParts[$partIndex + 1]['content'];

//        if (str_replace(' ', '', $optionValue) === $optionValue && !ctype_alnum($optionValue)) {
//            throw new RuntimeException("Invalid long option value");
//        }

        $this->commandParts[$partIndex + 1]['used'] = true;

        return $optionValue;
    }

    public function getAllParts(): array
    {
        return $this->commandParts;
    }

    public function getUnusedParts(): array
    {
        return array_filter($this->commandParts, static fn(array $part) => !$part['used']);
    }
}
