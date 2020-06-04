<?php

declare(strict_types=1);

namespace Acme\Marketplace\Checkout\Domain\ValueObject;

final class Parties
{
    private string $jobSeekerId;

    private string $clientId;

    private function __construct(string $clientId, string $jobSeekerId)
    {
        $this->clientId = $clientId;
        $this->jobSeekerId = $jobSeekerId;
    }

    public static function fromClientAndJobSeeker(string $clientId, string $jobSeekerId): self
    {
        return new self($clientId, $jobSeekerId);
    }

    public function isClientAllowed(string $clientId): bool
    {
        return $clientId === $this->clientId;
    }

    public function isJobSeekerAllowed(string $jobSeekerId): bool
    {
        return $jobSeekerId === $this->jobSeekerId;
    }

    public function jobSeeker(): string
    {
        return $this->jobSeekerId;
    }

    public function client(): string
    {
        return $this->clientId;
    }
}
