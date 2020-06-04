<?php

declare(strict_types=1);

namespace Acme\Common\Domain\Event;

final class EventRecorder
{
    private array $recordedEvents = [];

    public function releaseEvents(): array
    {
        $events = $this->recordedEvents;

        $this->eraseEvents();

        return $events;
    }

    public function eraseEvents(): void
    {
        $this->recordedEvents = [];
    }

    public function record(DomainEvent $event): void
    {
        $this->recordedEvents[] = $event;
    }
}
