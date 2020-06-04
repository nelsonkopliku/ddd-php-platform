<?php

declare(strict_types=1);

namespace Acme\Common\Infrastructure\Bus\Event;

use Enqueue\Client\ProducerInterface;
use Acme\Common\Application\Bus\Event\EventBus;
use Acme\Common\Domain\Event\DomainEvent;

final class MessageProducerEventBus implements EventBus
{
    private ProducerInterface $producer;

    public function __construct(ProducerInterface $producer)
    {
        $this->producer = $producer;
    }

    public function publish(DomainEvent ...$domainEvents): void
    {
        foreach ($domainEvents as $domainEvent) {
            $this->producer->sendEvent($domainEvent::name(), $domainEvent->toArray());
        }
    }
}
