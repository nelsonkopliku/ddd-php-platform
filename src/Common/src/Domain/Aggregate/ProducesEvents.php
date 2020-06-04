<?php

declare(strict_types=1);

namespace Acme\Common\Domain\Aggregate;

use Acme\Common\Domain\Event\DomainEvent;

trait ProducesEvents
{
    /**
     * @var DomainEvent[]
     */
    protected iterable $recordedEvents = [];

    /**
     * @return DomainEvent[]
     */
    public function getRecordedEvents(): array
    {
        $pendingEvents = $this->recordedEvents;

        $this->recordedEvents = [];

        return $pendingEvents;
    }

    protected function recordThat(DomainEvent $event): void
    {
        $this->recordedEvents[] = $event;
    }
}
