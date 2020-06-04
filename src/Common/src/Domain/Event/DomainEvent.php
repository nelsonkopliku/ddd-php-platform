<?php

declare(strict_types=1);

namespace Acme\Common\Domain\Event;

use DateTimeImmutable;
use Ramsey\Uuid\Uuid;
use ReflectionClass;

abstract class DomainEvent
{
    private string $aggregateId;
    private string $eventId;
    private DateTimeImmutable $occurredOn;

    protected function __construct(string $aggregateId, string $eventId = null, string $occurredOn = null)
    {
        $this->aggregateId = $aggregateId;
        $this->eventId = $eventId ?: Uuid::uuid4()->toString();

        $this->occurredOn = $occurredOn ? (new DateTimeImmutable())->setTimestamp((int) $occurredOn) : new DateTimeImmutable();
    }

    /**
     * @param array<string,mixed> $payload
     */
    abstract public static function fromPayload(array $payload, string $occurredOn): DomainEvent;

    /**
     * @return array<string,mixed>
     */
    abstract public function toArray(): array;

    public static function name(): string
    {
        return strtolower(
            preg_replace(
                '/([^A-Z\s])([A-Z])/', "$1_$2",
                (new ReflectionClass(static::class))->getShortName()
            )
        );
    }

    public function aggregateId(): string
    {
        return $this->aggregateId;
    }

    public function eventId(): string
    {
        return $this->eventId;
    }

    public function occurredOn(): DateTimeImmutable
    {
        return $this->occurredOn;
    }
}
