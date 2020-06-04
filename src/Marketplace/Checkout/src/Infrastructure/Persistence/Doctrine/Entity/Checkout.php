<?php

declare(strict_types=1);

namespace Acme\Marketplace\Checkout\Infrastructure\Persistence\Doctrine\Entity;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Selectable;
use Acme\Marketplace\Checkout\Domain\Checkout as DomainCheckout;
use Acme\Marketplace\Checkout\Domain\ValueObject\CheckoutId;
use Acme\Marketplace\Checkout\Domain\ValueObject\Parties;
use Acme\Marketplace\Checkout\Domain\ValueObject\StartedShift;

class Checkout
{
    public string $id;

    public string $shiftId;

    public DateTimeImmutable $startedAt;

    public DateTimeImmutable $end;

    public string $jobSeekerId;

    public string $clientId;

    /**
     * The domain model has Checkout->Proposal relation (one to one in ORM words)
     * while this underlying persistence layer stores them as
     * CheckoutEntity->ProposalEntity[] (one to many)
     * Why?
     *      The main reason is that we can have the history of all the submitted proposals for a checkout.
     *      Without even sourcing
     *      For the sake of simplicity
     *
     * is this needed?
     *      it is not strictly necessary
     *
     * couldn't we just have a simple one to one at an entity level also?
     *      Yes sure, but consider that in order to keep history of the submitted proposals
     *      we would be required to either:
     *          - implement Checkout AR as an event sourced AR
     *          - use stored domain events (which are not yet stored in this PoC) as debugging tool
     *
     * So, yes, whatever it fits better.
     *
     * @var ArrayCollection<int, Proposal>|Collection<int, Proposal>
     */
    public Collection $proposals;

    public bool $agreed;

    public bool $noShow;

    public function __construct()
    {
        $this->proposals = new ArrayCollection();
        $this->agreed = false;
        $this->noShow = false;
    }

    public static function fromDomainCheckout(DomainCheckout $checkout): self
    {
        $self = new self();

        $self->id = $checkout->id()->value();
        $self->shiftId = $checkout->shift()->id();
        $self->startedAt = $checkout->shift()->start();
        $self->end = $checkout->shift()->end();
        $self->clientId = $checkout->parties()->client();
        $self->jobSeekerId = $checkout->parties()->jobSeeker();
        $self->agreed = $checkout->isAgreed();
        $self->noShow = $checkout->isNoShow();

        if ($checkout->hasProposal()) {
            $self->addProposal(Proposal::fromDomainProposal($checkout->currentProposal()));
        }

        return $self;
    }

    public function addProposal(Proposal $proposal): void
    {
        $proposal->checkout = $this;
        $this->proposals->add($proposal);
    }

    public function refresh(DomainCheckout $domainCheckout): void
    {
        $this->agreed = $domainCheckout->isAgreed();
        $this->noShow = $domainCheckout->isNoShow();

        if ($domainCheckout->hasProposal()) {
            $this->addProposal(
                Proposal::fromDomainProposal($domainCheckout->currentProposal())
            );
        }
    }

    /**
     * This method will be called by the ORM after this entity has been loaded from the DB
     *
     * If the ORM maps directly to the domain model, then these ORM Lifecycle Methods would pollute the domain model
     * itself if kept within the same class, therefore a listener would be a better place for these methods
     *
     * If, like in this example, there has been complete separation between domain model an orm entities
     * then such methods, when simple enough, can live in the same entity class definition.
     * If this becomes to much, it is still possible to move this out into its own listener class(es)
     *
     * pros, cons, options
     */
    public function postLoaded(): void
    {
        /** @var Selectable<int, Proposal> $currentProposals */
        $currentProposals = $this->proposals;

        $this->proposals = $currentProposals->matching(Criteria::create()->orderBy(['proposedAt' => Criteria::ASC]));
    }

    public function toDomainCheckout(): DomainCheckout
    {
        return DomainCheckout::fromValues(
            CheckoutId::fromString($this->id),
            StartedShift::fromValues(
                $this->shiftId,
                $this->startedAt,
                $this->end
            ),
            Parties::fromClientAndJobSeeker(
                $this->clientId,
                $this->jobSeekerId
            ),
            ($lastProposal = $this->proposals->last()) ? $lastProposal->toDomainProposal() : null,
            $this->agreed,
            $this->noShow
        );
    }
}
