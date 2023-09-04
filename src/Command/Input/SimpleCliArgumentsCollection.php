<?php

declare(strict_types=1);

namespace Grano22\SimpleCli\Command\Input;

class SimpleCliArgumentsCollection {
    /** @var array<string, SimpleCliArgument> $elements */
    private array $elements;

    public function __construct(SimpleCliArgument ...$cliArguments) {
        $this->elements = array_combine(
            array_map(static fn (SimpleCliArgument $cliArgument) => $cliArgument->getName(), $cliArguments),
            $cliArguments
        );
    }

    public function getPiped(): ?SimpleCliArgument
    {
        return array_filter(
            array_values($this->elements),
            static fn (SimpleCliArgument $argument) => $argument->getOptions() & SimpleCliArgument::PIPED
        )[0] ?? null;
    }

    public function getByName(string $name): ?SimpleCliArgument
    {
        return
            array_key_exists($name, $this->elements) ?
                $this->elements[$name] :
                null;
    }

    /** @return SimpleCliArgument[] */
    public function toArray(): array
    {
        return array_values($this->elements);
    }
}
