<?php

declare(strict_types=1);

namespace Acme\Marketplace\Checkout\Domain\Command;

use Acme\Common\Domain\Command\Command;

class OpenCheckout implements Command
{
    private string $id;

    private string $shiftId;

    private string $startedAt;

    private string $endsAt;

    private string $jobSeekerId;

    private string $clientId;

    private float $hourlyRate;

    public function __construct(
        string $id,
        string $shiftId,
        string $startedAt,
        string $endsAt,
        string $jobSeekerId,
        string $clientId,
        float $hourlyRate
    ) {
        $this->id = $id;
        $this->shiftId = $shiftId;
        $this->startedAt = $startedAt;
        $this->endsAt = $endsAt;
        $this->jobSeekerId = $jobSeekerId;
        $this->clientId = $clientId;
        $this->hourlyRate = $hourlyRate;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function shiftId(): string
    {
        return $this->shiftId;
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
        return $this->jobSeekerId;
    }

    public function clientId(): string
    {
        return $this->clientId;
    }

    public function hourlyRate(): float
    {
        return $this->hourlyRate;
    }
}
