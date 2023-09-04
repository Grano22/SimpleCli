<?php

declare(strict_types=1);

namespace Grano22\SimpleCli\App;

class SimpleCliAppExecutionContext {
    public function __construct(
        private SimpleCliAppConfig $appConfig,
    ) {}

    public function getAppConfig(): SimpleCliAppConfig
    {
        return $this->appConfig;
    }
}
