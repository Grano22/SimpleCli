<?php

declare(strict_types=1);

namespace Grano22\SimpleCli\Command\Input;

use RuntimeException;

class CommandPartsBuilder
{
    public const UNKNOWN = 'unknown';
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
            $partType = $this->determineCommandPartType($argv[$argIndex]);


            if ($partType === self::SHORT_OPTION) {
                $this->prepareShortOption($commandParts, $argv, $argIndex);

                continue;
            }

            if ($partType === self::LONG_OPTION) {
                $this->prepareLongOption($commandParts, $argv, $argIndex);

                continue;
            }

            if ($partType === self::ARGUMENT) {
                $this->prepareArgument($commandParts,  $argv, $argIndex, $argumentLastPosition);

                continue;
            }

            throw new RuntimeException("Unknown part {$argv[$argIndex]}");
        }

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
            'values' => $baseCommandParts['values'],
            'independent' => []
        ];

        /** @var SimpleCliOption $definedOption */
        foreach ($definedOptions as $definedOption) {
            $names = $definedOption->getAllNames();

            foreach ($names as $name) {
                if (strlen($name) === 1 && $definedOption->isNegable() && isset($baseCommandParts['correlation'][$name])) {
                    $shortOptions = str_split($baseCommandParts['correlation'][$name]);

                    for ($index = 0; $index < count($shortOptions); $index++) {
                        if (in_array($shortOptions[$index], $baseCommandParts['parts'][self::SHORT_OPTION])) {
                            throw new RuntimeException("Duplicated option: {$shortOptions[$index]}");
                        }

                        $commandParts['usagesMap'][$shortOptions[$index]] = false;
                        $commandParts['parts'][self::SHORT_OPTION][] = $shortOptions[$index];
                    }
                }

                if (!$definedOption->isNegable() && isset($baseCommandParts['correlation'][$name])) {
                    $commandParts['values'][$name] = $baseCommandParts['correlation'][$name];

                    if (!array_key_exists($name, $commandParts['usagesMapValues'])) {
                        $commandParts['usagesMapValues'][$name] = [];
                    }

                    $commandParts['usagesMapValues'][$name][$baseCommandParts['correlation'][$name]] = false;
                }

                if (
                    ($definedOption->getOptions() & SimpleCliOption::IGNORE_REST_REQUIRED) &&
                    (
                        in_array($name, $baseCommandParts['parts'][self::SHORT_OPTION], true) ||
                        in_array($name, $baseCommandParts['parts'][self::LONG_OPTION], true)
                    )
                ) {
                    $commandParts['independent'][] = $name;
                }
            }
        }

        return $commandParts;
    }

    private function prepareArgument(array &$commandParts, array $args, int $argIndex, int &$argumentLastPosition): void
    {
        if ($argIndex !== 0 && in_array($this->determineCommandPartType($args[$argIndex - 1]), [self::SHORT_OPTION, self::LONG_OPTION])) {
            $optionName = ltrim($args[$argIndex - 1], '-');
            $commandParts['correlation'][$optionName] = $args[$argIndex];

            return;
        }

        $partName = $argumentLastPosition++;
        $commandParts['values'][$partName] = $args[$argIndex];
        $commandParts['parts'][self::ARGUMENT][] = $args[$argIndex];
        $commandParts['usagesMap'][$partName] = false;
    }

    private function prepareLongOption(array &$commandParts, array $args, int &$argIndex): void
    {
        $partDef = $args[$argIndex];
        $optionName = substr($partDef, 2);

        if (str_contains($optionName, '=')) {
            [$longOptionName, $longOptionValue] = explode('=', $optionName);
            $optionName = $longOptionName;

            $commandParts['values'][$longOptionName] = $longOptionValue;
        }

        if (in_array($optionName, $commandParts['parts'][self::LONG_OPTION])) {
            throw new RuntimeException("Duplicated option: {$partDef}");
        }

        $commandParts['parts'][self::LONG_OPTION][] = $optionName;
        $commandParts['usagesMap'][$optionName] = false;
    }

    private function prepareShortOption(array &$commandParts, array $args, int $argIndex): void
    {
        $partDef = $args[$argIndex];
        $optionName = substr($partDef, 1, 1);

        if (strlen($partDef) > 2) {
            $optionValue = substr($partDef, 2);

            $commandParts['correlation'][$optionName] = $optionValue;
        }

        if (in_array($optionName, $commandParts['parts'][self::SHORT_OPTION])) {
            throw new RuntimeException("Duplicated option: {$partDef}");
        }

        $commandParts['parts'][self::SHORT_OPTION][] = $optionName;
        $commandParts['usagesMap'][$optionName] = false;
    }

    private function determineCommandPartType(string $pattern): string
    {
        $dashesAtStartCount = 0;

        $splitedPattern = str_split($pattern);

        foreach ($splitedPattern as $subpattern) {
            if ($subpattern !== '-') {
                break;
            }

            $dashesAtStartCount++;
        }

        if ($dashesAtStartCount > 2) {
            return self::UNKNOWN;
        }

        if ($dashesAtStartCount === 2) {
            return self::LONG_OPTION;
        }

        if ($dashesAtStartCount === 1) {
            return self::SHORT_OPTION;
        }

        return self::ARGUMENT;
    }
}
