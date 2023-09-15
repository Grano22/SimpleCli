<?php

declare(strict_types=1);

namespace Grano22\SimpleCli\Core\Event;

use DateTimeImmutable;

interface SimpleCliEvent
{
    public function getOccurredAt(): DateTimeImmutable;
    /** @return array<string, mixed> */
    public function getLabels(): array;
}