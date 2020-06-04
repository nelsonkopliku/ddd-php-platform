<?php

declare(strict_types=1);

namespace Acme\Marketplace\Checkout\Domain\Event;

use DateTimeImmutable;
use Acme\Common\Domain\Event\DomainEvent;
use Acme\Marketplace\Checkout\Domain\Proposal\ProposalContent;
use Acme\Marketplace\Checkout\Domain\ValueObject\CheckoutId;

abstract class AgreementWasSubmitted extends DomainEvent
{
    protected string $submitter;

    protected CheckoutId $checkout;

    protected ProposalContent $proposalContent;

    final protected function __construct(
        CheckoutId $checkout,
        ProposalContent $proposal,
        string $submitter,
        string $eventId = null,
        string $occurredOn = null
    ) {
        $this->checkout = $checkout;
        $this->submitter = $submitter;
        $this->proposalContent = $proposal;

        parent::__construct($this->checkout->value(), $eventId, $occurredOn);
    }

    /**
     * @param array<string,mixed> $payload
     */
    public static function fromPayload(array $payload, string $occurredOn): DomainEvent
    {
        $proposal = $payload['proposal'];

        return new static(
            CheckoutId::fromString($payload['checkout']),
            ProposalContent::fromValues(
                new DateTimeImmutable($proposal['worked_from']),
                new DateTimeImmutable($proposal['worked_until']),
                $proposal['minutes_break'],
                $proposal['compensation']
            ),
            $payload['submitted_by'],
            $payload['event_id'] ?? null,
            $occurredOn
        );
    }

    public function toArray(): array
    {
        return [
            'submitted_by' => $this->submitter,
            'checkout' => $this->checkout->value(),
            'submitted_at' => $this->occurredOn()->format(DATE_ATOM),
            'proposal' => [
                'worked_from' => $this->proposalContent->hoursWorked()->from()->format(DATE_ATOM),
                'worked_until' => $this->proposalContent->hoursWorked()->until()->format(DATE_ATOM),
                'minutes_break' => $this->proposalContent->hoursWorked()->minutesBreak(),
                'compensation' => $this->proposalContent->compensation(),
            ],
        ];
    }
}
