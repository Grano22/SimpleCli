<?php

declare(strict_types=1);

namespace Grano22\SimpleCli\Command\Input;

use Grano22\SimpleCli\Command\SimpleCliCommand;
use Grano22\SimpleCli\Data\CollectionStructure;


/** @implements CollectionStructure<SimpleCliCommand> */
class SimpleCliCommandsStack extends CollectionStructure {
    public function __construct(SimpleCliCommand ...$cliCommands) {
        $this->elements = array_combine(
            array_map(static fn (SimpleCliCommand $cliArgument) => $cliArgument->getName(), $cliCommands),
            $cliCommands
        );
    }

    /** @return string[] */
    public function getNames(): array
    {
        return array_map(
            static fn(SimpleCliCommand $command) => $command->getName(),
            $this->elements
        );
    }
}
