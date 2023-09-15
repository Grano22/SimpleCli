<?php

declare(strict_types=1);

namespace Grano22\SimpleCli\Core\Event;

use Grano22\SimpleCli\Command\Input\Exception\CommandHandlerHasMissingReturnCode;

class MissingReturnCodeEvent extends FailureSimpleCliEvent
{
    protected const SUPPORTED_EXCEPTIONS = [CommandHandlerHasMissingReturnCode::class];
}
