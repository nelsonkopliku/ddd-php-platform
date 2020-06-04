<?php

declare(strict_types=1);

namespace Acme\Marketplace\Checkout\Infrastructure\Persistence\Doctrine\Entity;

use DateTimeImmutable;
use Ramsey\Uuid\UuidInterface;
use Acme\Marketplace\Checkout\Domain\Proposal\ClientProposal;
use Acme\Marketplace\Checkout\Domain\Proposal\JobSeekerProposal;
use Acme\Marketplace\Checkout\Domain\Proposal\Proposal as DomainProposal;
use Acme\Marketplace\Checkout\Domain\Proposal\ProposalContent;
use Webmozart\Assert\Assert;

class Proposal
{
    private const PROPOSAL_CLASSMAP = [
        'jobseeker' => JobSeekerProposal::class,
        'client' => ClientProposal::class,
    ];

    public UuidInterface $id;

    public DateTimeImmutable $workedFrom;

    public DateTimeImmutable $workedUntil;

    public int $minutesBreak;

    public string $compensation;

    public string $proposedBy;

    public DateTimeImmutable $proposedAt;

    public Checkout $checkout;

    public function __construct()
    {
        $this->proposedAt = new DateTimeImmutable();
    }

    public static function fromDomainProposal(DomainProposal $proposal): self
    {
        Assert::oneOf($proposalClass = get_class($proposal), self::PROPOSAL_CLASSMAP);

        $self = new self();

        $self->workedFrom = $proposal->content()->hoursWorked()->from();
        $self->workedUntil = $proposal->content()->hoursWorked()->until();
        $self->minutesBreak = $proposal->content()->hoursWorked()->minutesBreak();
        $self->compensation = $proposal->content()->compensation();
        /** @var string $proposedBy */
        $proposedBy = array_search($proposalClass, self::PROPOSAL_CLASSMAP, true);
        $self->proposedBy = $proposedBy;
        $self->proposedAt = $proposal->proposedAt();

        return $self;
    }

    public function toDomainProposal(): DomainProposal
    {
        Assert::oneOf($this->proposedBy, array_keys(self::PROPOSAL_CLASSMAP));

        $proposalClass = self::PROPOSAL_CLASSMAP[$this->proposedBy];

        $args = [
            ProposalContent::fromValues(
                $this->workedFrom,
                $this->workedUntil,
                $this->minutesBreak,
                $this->compensation
            ),
            $this->proposerId($proposalClass),
            $this->proposedAt,
        ];

        return $proposalClass::fromValues(...$args);
    }

    private function proposerId(string $proposalClass): string
    {
        if (JobSeekerProposal::class === $proposalClass) {
            return $this->checkout->jobSeekerId;
        }

        return $this->checkout->clientId;
    }
}
