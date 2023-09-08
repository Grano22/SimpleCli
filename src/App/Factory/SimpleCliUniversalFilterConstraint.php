<?php

declare(strict_types=1);

namespace Grano22\SimpleCli\App\Factory;

use Grano22\SimpleCli\Data\CollectionStructure;
use LogicException;
use RuntimeException;

class SimpleCliUniversalFilterConstraint implements SimpleCliFilterConstraint
{
    public static function wildcard(string $pattern): SimpleCliFilterConstraint
    {
        return new self($pattern);
    }

    private function __construct(private string $pattern) {
        if (!$this->isTokenSupported($this->pattern)) {
            throw new LogicException("Unsupported constraint token $this->pattern in " . get_class($this));
        }
    }

    public function resolve(CollectionStructure $collection): object
    {
        if ($this->pattern === '*') {
            return $collection;
        }

        throw new RuntimeException("Unknown wildcard");
    }

    public function resolveArray(array $data): object|array
    {
        if ($this->pattern === '*') {
            return $data;
        }

        throw new RuntimeException("Unknown wildcard");
    }

    private function isTokenSupported(string $token): bool
    {
        return in_array($token, ['*'], true);
    }
}
