<?php

declare(strict_types=1);

namespace Grano22\SimpleCli\Command\Input;

use Grano22\SimpleCli\Data\CollectionStructure;

/** @implements CollectionStructure<SimpleCliArgument> */
class SimpleCliArgumentsCollection extends CollectionStructure {
    public function __construct(SimpleCliArgument ...$cliArguments) {
        $this->elements = array_combine(
            array_map(static fn (SimpleCliArgument $cliArgument) => $cliArgument->getName(), $cliArguments),
            $cliArguments
        );
    }

    public function getPiped(): ?SimpleCliArgument
    {
        return current(array_filter(
            array_values($this->elements),
            static fn (SimpleCliArgument $argument) => $argument->getOptions() & SimpleCliArgument::PIPED
        )) ?? null;
    }

    /** @return SimpleCliArgument[] */
    public function toArray(): array
    {
        return array_values($this->elements);
    }
}
