<?php

declare(strict_types=1);

namespace Grano22\SimpleCli\Command\Input;

class SimpleCliOptionsCollection {
    /** @var array<string, SimpleCliOption> $elements */
    private array $elements;

    public function __construct(SimpleCliOption ...$cliArguments) {
        $this->elements = array_combine(
            array_map(static fn (SimpleCliOption $cliArgument) => $cliArgument->getName(), $cliArguments),
            $cliArguments
        );
    }

    public function getByName(string $name): ?SimpleCliOption
    {
        return
            array_key_exists($name, $this->elements) ?
                $this->elements[$name] :
                null;
    }

    /** @return SimpleCliOption[] */
    public function toArray(): array
    {
        return array_values($this->elements);
    }
}
