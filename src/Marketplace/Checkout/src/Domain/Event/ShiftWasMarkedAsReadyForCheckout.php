<?php

declare(strict_types=1);

namespace Acme\Marketplace\Checkout\Domain\Event;

use Acme\Common\Domain\Event\DomainEvent;

class ShiftWasMarkedAsReadyForCheckout extends DomainEvent
{
    private string $shiftId;

    private string $matchId;

    private string $startedAt;

    private string $endsAt;

    private string $jobSeeker;

    private string $client;

    private float $hourlyRate;

    public function __construct(
        string $shiftId,
        string $matchId,
        string $startedAt,
        string $endsAt,
        string $jobSeeker,
        string $client,
        float $hourlyRate,
        string $eventId = null,
        string $occurredOn = null
    ) {
        parent::__construct($matchId, $eventId, $occurredOn);

        $this->shiftId = $shiftId;
        $this->matchId = $matchId;
        $this->startedAt = $startedAt;
        $this->endsAt = $endsAt;
        $this->jobSeeker = $jobSeeker;
        $this->client = $client;
        $this->hourlyRate = $hourlyRate;
    }

    public static function fromPayload(array $payload, string $occurredOn): self
    {
        return new self(
            $payload['shift_id'],
            $payload['match_id'],
            $payload['starts_at'],
            $payload['ends_at'],
            $payload['job_seeker_id'],
            $payload['client_id'],
            $payload['hourly_rate'],
            $payload['event_id'] ?? null,
            $occurredOn
        );
    }

    public function toArray(): array
    {
        return  [
            'shift_id' => $this->shiftId,
            'match_id' => $this->matchId,
            'starts_at' => $this->startedAt,
            'ends_at' => $this->endsAt,
            'job_seeker_id' => $this->jobSeeker,
            'client_id' => $this->client,
            'hourly_rate' => $this->hourlyRate,
        ];
    }

    public function shift(): string
    {
        return $this->shiftId;
    }

    public function match(): string
    {
        return $this->matchId;
    }

    public function startedAt(): string
    {
        return $this->startedAt;
    }

    public function endsAt(): string
    {
        return $this->endsAt;
    }

    public function jobSeekerId(): string
    {
        return $this->jobSeeker;
    }

    public function clientId(): string
    {
        return $this->client;
    }

    public function hourlyRate(): float
    {
        return $this->hourlyRate;
    }
}
