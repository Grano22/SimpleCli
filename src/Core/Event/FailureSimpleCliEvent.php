<?php

declare(strict_types=1);

namespace Grano22\SimpleCli\Core\Event;

use DateTimeImmutable;
use Exception;
use LogicException;

class FailureSimpleCliEvent implements SimpleCliEvent
{
    protected const SUPPORTED_EXCEPTIONS = [Exception::class];

    protected DateTimeImmutable $occurredAt;

    public static function createFromException(Exception $exception): self
    {
        $thisClass = get_called_class();

        if (!in_array($exception::class, $thisClass::SUPPORTED_EXCEPTIONS, true)) {
            throw new LogicException("Cannot create event " . $thisClass . ' from unsupported exception ' . $exception::class);
        }

        return new $thisClass(
            $exception->getMessage(),
            $exception->getCode(),
            $exception::class
        );
    }

    private function __construct(
        protected string $reason,
        protected int $errorCode,
        protected string $category
    ) {
        $this->occurredAt = new DateTimeImmutable();
    }

    public function getReason(): string
    {
        return $this->reason;
    }

    public function getOccurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }

    public function getLabels(): array
    {
        return [
            'category' => $this->category
        ];
    }
}