<?php

declare(strict_types=1);

namespace Grano22\SimpleCli\App;

class SimpleCliAppConfig {
    public function __construct(
        private bool $debug
    ) {
    }

    public function isDebugEnabled(): bool
    {
        return $this->debug;
    }
}
