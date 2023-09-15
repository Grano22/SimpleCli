<?php

declare(strict_types=1);

namespace Grano22\SimpleCli\Core;

use Closure;
use RuntimeException;
use SplFixedArray;

class InvokerMap
{
    /** @var array<string, Closure[]|SplFixedArray<Closure>> */
    private array $map = [];
    /** @var array<string, int> $mapIterator */
    private array $mapIterator = [];
    /** @var array<string, InvokerMap> */
    private array $subsets = [];

    public function __construct() { }

    public function addNewSubset(string $subsetName): void
    {
        $this->subsets[$subsetName] = new InvokerMap();
    }

    public function addSubset(InvokerMap $subset, string $subsetName): void
    {
        $this->subsets[$subsetName] = $subset;
    }

    public function predefineMapCollection(string $name, int $maxCapacity = 0): void
    {
        $this->map[$name] = $maxCapacity ? new SplFixedArray($maxCapacity) : [];
        $this->mapIterator[$name] = 0;
    }

    public function addInvocable(callable $fn, string $name): void
    {
        if (!array_key_exists($name, $this->map)) {
            $this->map[$name] = [];
        }

        if ($this->map[$name] instanceof SplFixedArray) {
            $this->map[$name][$this->mapIterator[$name]++] = Closure::fromCallable($fn);

            return;
        }

        $this->map[$name][] = Closure::fromCallable($fn);
    }

    public function getInvocable(string $name, int $position = 0): ?Closure
    {
        return $this->map[$name][$position] ?? null;
    }

    /** @return Closure[]|SplFixedArray<Closure> */
    public function getInvocables(string $name): array|SplFixedArray
    {
        return $this->map[$name] ?? [];
    }

    public function hasSubset(string $subsetName): bool
    {
        return array_key_exists($subsetName, $this->subsets);
    }

    public function getSubset(string $subsetName): InvokerMap
    {
        if (!$this->hasSubset($subsetName)) {
            throw new RuntimeException("Subset $subsetName does not exists");
        }

        return $this->subsets[$subsetName];
    }

    public function toMap(): array
    {
        return $this->map;
    }
}