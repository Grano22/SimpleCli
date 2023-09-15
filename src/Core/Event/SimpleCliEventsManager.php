<?php

declare(strict_types=1);

namespace Grano22\SimpleCli\Core\Event;

use Closure;
use Grano22\SimpleCli\Core\InvokerMap;
use SplObjectStorage;

class SimpleCliEventsManager
{
    private SplObjectStorage $events;
    private InvokerMap $listeners;

    public function __construct(?InvokerMap $initialListeners = null)
    {
        $this->listeners = new InvokerMap();

        if ($initialListeners) {
            $this->listeners = $initialListeners;
        }

        $this->events = new SplObjectStorage();
    }

    /** @param string[] $eventTypes */
    public function addEventListener(callable $fn, array $eventTypes): void
    {
        foreach ($eventTypes as $eventType) {
            $this->listeners->addInvocable($fn, $eventType);
        }
    }

    public function processEventsWithNew(SimpleCliEvent $newEvent): void
    {
        $this->addEvent($newEvent);
        $this->processEventsOfType($newEvent::class);
    }

    public function processEventsOfType(string $eventType): void
    {
        for ($this->events->rewind(); $this->events->valid(); $this->events->next()) {
            /** @var SimpleCliEvent $currentEvent */
            $currentEvent = $this->events->current();
            $currentEventType = $currentEvent::class;

            if ($currentEventType === $eventType) {
                foreach ($this->listeners->getInvocables($currentEventType) as $eventListener) {
                    $eventListener->__invoke($currentEvent);
                }

                $this->events->detach($currentEvent);
            }
        }
    }

    public function addEvent(SimpleCliEvent $event): void
    {
        $this->events->attach($event);
    }
}