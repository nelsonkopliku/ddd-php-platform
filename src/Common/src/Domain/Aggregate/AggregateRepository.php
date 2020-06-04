<?php

declare(strict_types=1);

namespace Acme\Common\Domain\Aggregate;

use Acme\Common\Domain\Event\EventRecorder;

abstract class AggregateRepository
{
    protected EventRecorder $eventRecorder;

    public function __construct(EventRecorder $eventRecorder)
    {
        $this->eventRecorder = $eventRecorder;
    }

    public function saveAggregateRoot(AggregateRoot $aggregateRoot): void
    {
        foreach ($aggregateRoot->getRecordedEvents() as $recordedEvent) {
            $this->eventRecorder->record($recordedEvent);
        }
    }
}
