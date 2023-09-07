<?php

declare(strict_types=1);

namespace Grano22\SimpleCli\Command\Input;

use RuntimeException;

class CommandPartsBuilder
{
    public const ARGUMENT = 'argument';
    public const SHORT_OPTION = 'short_option';
    public const LONG_OPTION = 'long_option';

    public const POSIX_SHORT_ARGUMENT = '-%s';

    public function build(array $argv, int $argc): array {
        $commandParts = [
            'usagesMap' => [],
            'usagesMapValues' => [],
            'parts' => [
                self::ARGUMENT => [],
                self::SHORT_OPTION => [],
                self::LONG_OPTION => []
            ],
            'values' => [],
            'correlation' => []
        ];

        $argumentLastPosition = 0;

        for ($argIndex = 1; $argIndex < $argc; $argIndex++) {
            $partType = self::ARGUMENT;
            $partDef = $argv[$argIndex];
            $partName = $partDef;

            if (str_starts_with($partDef, '--')) {
                $partType = self::LONG_OPTION;
                $partName = substr($partDef, 2);

                if (str_contains($partName, '=')) {
                    [$longOptionName, $longOptionValue] = explode('=', $partDef);
                    $partName = $longOptionName;

                    $commandParts['values'][$longOptionName] = $longOptionValue;
                }

                if (in_array($partName, $commandParts['parts'][$partType])) {
                    throw new RuntimeException("Duplicated option: {$partDef}");
                }

                if (isset($argv[$argIndex + 1]) && !str_starts_with($argv[$argIndex + 1], '-')) {
                    $commandParts['values'][$partName] = $argv[$argIndex + 1];

                    if (array_key_exists($partName, $commandParts['usagesMapValues'])) {
                        $commandParts['usagesMapValues'][$partName] = [];
                    }

                    $commandParts['usagesMapValues'][$partName][$argv[$argIndex + 1]] = false;
                    $argIndex++;
                }
            } elseif (str_starts_with($partDef, '-')) {
                $partType = self::SHORT_OPTION;
                $optionName = substr($partDef, 1);

                if (strlen($partDef) > 2) {
                    $optionValue = substr($partDef, 2);

                    $commandParts['correlation'][$optionName] = $optionValue;
                }

                if (in_array($optionName, $commandParts['parts'][$partType])) {
                    throw new RuntimeException("Duplicated option: {$partDef}");
                }
            }

            $commandParts['parts'][$partType][] = $partName;

            if ($partType === self::ARGUMENT) {
                $partName = $argumentLastPosition++;
                $commandParts['values'][$partName] = $partDef;
            }

            $commandParts['usagesMap'][$partName] = false;
        }

        //var_dump($commandParts);

        return $commandParts;
    }

    public function rebuildWithSeparatedArgs(
        array $baseCommandParts,
        SimpleCliArgumentsCollection $definedArguments,
        SimpleCliOptionsCollection $definedOptions,
    ): array {
        $commandParts = [
            'usagesMap' => $baseCommandParts['usagesMap'],
            'usagesMapValues' => $baseCommandParts['usagesMapValues'],
            'parts' => [
                self::ARGUMENT => $baseCommandParts['parts'][self::ARGUMENT],
                self::SHORT_OPTION => $baseCommandParts['parts'][self::SHORT_OPTION],
                self::LONG_OPTION => $baseCommandParts['parts'][self::LONG_OPTION]
            ],
            'values' => $baseCommandParts['values']
        ];

        $foundShortOptionsWithoutValues = [];

        /** @var SimpleCliOption $definedOption */
        foreach ($definedOptions as $definedOption) {
            $shortNames = $definedOption->getShortNames();

            foreach ($shortNames as $shortName) {
                if (
                    ($definedOption->getOptions() & SimpleCliOption::NEGABLE) &&
                    isset($baseCommandParts['correlation'][$shortName])
                ) {
                    $shortOptions = str_split($baseCommandParts['correlation'][$shortName]);

                    for ($index = 0; $index < $shortOptions; $index++) {
                        if (in_array($shortOptions[$index], $baseCommandParts['parts'][self::SHORT_OPTION])) {
                            throw new RuntimeException("Duplicated option: {$shortOptions[$index]}");
                        }

                        $commandParts['usagesMap'][$shortOptions[$index]] = false;
                        $commandParts['parts'][self::SHORT_OPTION][] = $shortOptions[$index];
                    }
                }
            }
        }

        //var_dump($commandParts);

        //array_filter($baseCommandParts['parts'][self::SHORT_OPTION], static fn (string $part) => in_array($part, $found))

        return $commandParts;
    }
}
