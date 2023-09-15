<?php

namespace Grano22\SimpleCli\Core\Event;

use Grano22\SimpleCli\Command\Input\Exception\ArgumentIsMissing;

class MissingArgumentEvent extends FailureSimpleCliEvent
{
    protected const SUPPORTED_EXCEPTIONS = [ArgumentIsMissing::class];
}