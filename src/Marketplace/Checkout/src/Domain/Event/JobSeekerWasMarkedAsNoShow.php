<?php

declare(strict_types=1);

namespace Acme\Marketplace\Checkout\Domain\Event;

use Acme\Common\Domain\Event\DomainEvent;
use Acme\Marketplace\Checkout\Domain\ValueObject\CheckoutId;

final class JobSeekerWasMarkedAsNoShow extends DomainEvent
{
    private CheckoutId $checkoutId;

    private string $jobSeeker;

    private function __construct(CheckoutId $checkoutId, string $jobSeeker)
    {
        $this->checkoutId = $checkoutId;
        $this->jobSeeker = $jobSeeker;

        parent::__construct($checkoutId->value());
    }

    public static function forCheckout(string $jobSeeker, CheckoutId $checkout): self
    {
        return new self($checkout, $jobSeeker);
    }

    public static function fromPayload(array $payload, string $occurredOn): DomainEvent
    {
        return new self(
            CheckoutId::fromString($payload['checkout_id']),
            $payload['job_seeker']
        );
    }

    public function toArray(): array
    {
        return [
            'checkout_id' => $this->checkoutId->value(),
            'job_seeker' => $this->jobSeeker
        ];
    }
}
