<?php

namespace Grano22\SimpleCli\Data;

use ArrayIterator;
use Iterator;
use IteratorAggregate;

/** @template T */
abstract class CollectionStructure implements IteratorAggregate {
    /** @var array<string, T> $elements */
    protected array $elements;

    public function getIterator(): Iterator
    {
        return new ArrayIterator($this->elements);
    }

    /** @return T|null */
    public function getByName(string $name): ?object
    {
        return
            array_key_exists($name, $this->elements) ?
                $this->elements[$name] :
                null;
    }

    /** @return T[] */
    public function toArray(): array
    {
        return array_values($this->elements);
    }
}
