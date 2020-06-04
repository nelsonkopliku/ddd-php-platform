<?php

declare(strict_types=1);

namespace Acme\Marketplace\Checkout\Domain\Event;

use DateTimeImmutable;
use Acme\Common\Domain\Event\DomainEvent;
use Acme\Marketplace\Checkout\Domain\Proposal\JobSeekerProposal;
use Acme\Marketplace\Checkout\Domain\Proposal\ProposalContent;
use Acme\Marketplace\Checkout\Domain\ValueObject\CheckoutId;

final class ProposalWasSubmittedByJobSeeker extends DomainEvent
{
    private string $proposer;

    private CheckoutId $checkout;

    private ProposalContent $content;

    private function __construct(
        CheckoutId $checkout,
        string $jobSeekerId,
        ProposalContent $content,
        string $eventId = null,
        string $occurredOn = null
    ) {
        $this->checkout = $checkout;
        $this->proposer = $jobSeekerId;
        $this->content = $content;

        parent::__construct($this->checkout->value(), $eventId, $occurredOn);
    }

    public static function fromPayload(array $payload, string $occurredOn): self
    {
        $proposal = $payload['proposal'];

        return new self(
            CheckoutId::fromString($payload['checkout']),
            $payload['proposer'],
            ProposalContent::fromValues(
                new DateTimeImmutable($proposal['worked_from']),
                new DateTimeImmutable($proposal['worked_until']),
                $proposal['minutes_break'],
                $proposal['compensation']
            ),
            $payload['event_id'] ?? null,
            $occurredOn
        );
    }

    public function toArray(): array
    {
        return [
            'checkout' => $this->checkout->value(),
            'proposer' => $this->proposer,
            'proposal' => [
                'worked_from' => $this->content->hoursWorked()->from()->format(DATE_ATOM),
                'worked_until' => $this->content->hoursWorked()->until()->format(DATE_ATOM),
                'minutes_break' => $this->content->hoursWorked()->minutesBreak(),
                'compensation' => $this->content->compensation(),
            ],
        ];
    }

    public static function forCheckout(JobSeekerProposal $proposal, CheckoutId $checkout): self
    {
        return new self($checkout, $proposal->proposedBy(), $proposal->content());
    }
}
