<?php

namespace Grano22\SimpleCli\Data;

/** @template T */
abstract class CollectionStructure {
    /** @var array<string, T> $elements */
    protected array $elements;

    /** @return T|null */
    public function getByName(string $name): ?object
    {
        return
            array_key_exists($name, $this->elements) ?
                $this->elements[$name] :
                null;
    }
}
