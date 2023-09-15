<?php

namespace Grano22\SimpleCli\Command\Input\Exception;

use Exception;

class CommandNotFound extends Exception
{
    public function __construct(
        public string $givenName,
        public int $level
    ) {
        parent::__construct("$this->givenName command not found");
    }
}
