<?php

declare(strict_types=1);

namespace Grano22\SimpleCli\Core\Event;

use Grano22\SimpleCli\Command\Input\Exception\CommandNotFound;

class CommandNotFoundEvent extends FailureSimpleCliEvent
{
    protected const SUPPORTED_EXCEPTIONS = [CommandNotFound::class];
}
