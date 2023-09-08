<?php

declare(strict_types=1);

namespace Grano22\SimpleCli\Command\Input;

use Grano22\SimpleCli\Data\CollectionStructure;

/** @implements CollectionStructure<SimpleCliOption> */
class SimpleCliOptionsCollection extends CollectionStructure {
    public function __construct(SimpleCliOption ...$cliArguments) {
        $this->elements = array_combine(
            array_map(static fn (SimpleCliOption $cliArgument) => $cliArgument->getName(), $cliArguments),
            $cliArguments
        );
    }
}
