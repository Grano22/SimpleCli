<?php

declare(strict_types=1);

namespace Grano22\SimpleCli\Command;

use Grano22\SimpleCli\Command\Input\CommandPartsBuilder;
use Grano22\SimpleCli\Command\Input\Exception\ArgumentIsMissing;
use Grano22\SimpleCli\Command\Input\Exception\CommandOptionIsMissing;
use Grano22\SimpleCli\Command\Input\SimpleCliArgument;
use Grano22\SimpleCli\Command\Input\SimpleCliOption;

class InputValidator
{
    public const OMIT_REQUIRED_OPTIONS = 0b001;
    public const OMIT_REQUIRED_ARGUMENTS = 0b010;

    private int $exceptions = 0;

    public function __construct(private bool $active) {}

    public function isActive(): bool
    {
        return $this->active;
    }

    public function addException(int $newException): void
    {
        $this->exceptions |= $newException;
    }

    /**
     * @throws ArgumentIsMissing
     */
    public function verifyIsArgumentMissing(SimpleCliArgument $argument, int $index, array $allParts): void
    {
        if (
            $this->active &&
            !($this->exceptions & self::OMIT_REQUIRED_ARGUMENTS) &&
            $argument->isRequired() &&
            !isset($allParts[CommandPartsBuilder::ARGUMENT][$index])
        ) {
            throw ArgumentIsMissing::create($index + 1, $argument->getName());
        }
    }

    /**
     * @throws CommandOptionIsMissing
     */
    public function verifyIsOptionMissing(int $occurredTimes, SimpleCliOption $option): void
    {
        if ($this->active && !($this->exceptions & self::OMIT_REQUIRED_OPTIONS) && !$occurredTimes && $option->isRequired()) {
            throw CommandOptionIsMissing::create($option->getName());
        }
    }
}
