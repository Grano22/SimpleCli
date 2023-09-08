<?php

declare(strict_types=1);

namespace Grano22\SimpleCli\App\Factory;

use Grano22\SimpleCli\Data\CollectionStructure;

interface SimpleCliFilterConstraint
{
    public static function wildcard(string $pattern): self;
    public function resolve(CollectionStructure $collection): object;
}
