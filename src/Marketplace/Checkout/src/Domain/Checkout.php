<?php

declare(strict_types=1);

namespace Acme\Marketplace\Checkout\Domain;

use Acme\Common\Domain\Aggregate\AggregateRoot;
use Acme\Marketplace\Checkout\Domain\Event\AgreementWasSubmittedByClient;
use Acme\Marketplace\Checkout\Domain\Event\AgreementWasSubmittedByJobSeeker;
use Acme\Marketplace\Checkout\Domain\Event\JobSeekerWasMarkedAsNoShow;
use Acme\Marketplace\Checkout\Domain\Event\ProposalWasSubmittedByClient;
use Acme\Marketplace\Checkout\Domain\Event\ProposalWasSubmittedByJobSeeker;
use Acme\Marketplace\Checkout\Domain\Exception\CannotMarkNoShow;
use Acme\Marketplace\Checkout\Domain\Exception\CannotSubmitAgreement;
use Acme\Marketplace\Checkout\Domain\Exception\CannotSubmitProposal;
use Acme\Marketplace\Checkout\Domain\Exception\CheckoutAlreadyAgreed;
use Acme\Marketplace\Checkout\Domain\Exception\NoProposalAvailable;
use Acme\Marketplace\Checkout\Domain\Proposal\ClientProposal;
use Acme\Marketplace\Checkout\Domain\Proposal\JobSeekerProposal;
use Acme\Marketplace\Checkout\Domain\Proposal\Proposal;
use Acme\Marketplace\Checkout\Domain\ValueObject\CheckoutId;
use Acme\Marketplace\Checkout\Domain\ValueObject\Parties;
use Acme\Marketplace\Checkout\Domain\ValueObject\StartedShift;
use Webmozart\Assert\Assert;

final class Checkout extends AggregateRoot
{
    public const NO_SHOW_DAYS_LIMIT = 7;

    private CheckoutId $id;

    private StartedShift $startedShift;

    private Parties $parties;

    private ?Proposal $currentProposal;

    private bool $agreed;

    private bool $noShow;

    private function __construct(
        CheckoutId $id,
        StartedShift $startedShift,
        Parties $parties,
        ?Proposal $currentProposal,
        bool $agreed,
        bool $noShow
    ) {
        $agreed           && Assert::false($noShow, 'Cannot create an agreed Checkout which is also marked as NoShow');
        !$currentProposal && Assert::false($agreed, 'Cannot create an agreed Checkout without a proposal');
        $currentProposal  && Assert::false($noShow, 'Cannot create a NoShow Checkout with pending proposals');

        $this->id = $id;
        $this->startedShift = $startedShift;
        $this->parties = $parties;
        $this->currentProposal = $currentProposal;
        $this->agreed = $agreed;
        $this->noShow = $noShow;
    }

    public static function open(CheckoutId $id, StartedShift $startedShift, Parties $parties): self
    {
        return new self($id, $startedShift, $parties, null, false, false);
    }

    public static function fromValues(
        CheckoutId $id,
        StartedShift $startedShift,
        Parties $parties,
        ?Proposal $currentProposal,
        bool $agreed,
        bool $noShow = false
    ): self {
        return new self($id, $startedShift, $parties, $currentProposal, $agreed, $noShow);
    }

    public function submitJobSeekerProposal(JobSeekerProposal $jobSeekerProposal): void
    {
        $this->ensureProposalCanBeSubmittedToCheckout();

        if (!$this->parties->isJobSeekerAllowed($proposingJobSeeker = $jobSeekerProposal->proposedBy())) {
            throw CannotSubmitProposal::jobSeekerIsNotAllowed($proposingJobSeeker, $this->id);
        }

        if ($this->isJobSeekerLatestProposal()) {
            throw CannotSubmitProposal::jobSeekerAlreadySubmitted($proposingJobSeeker, $this->id);
        }

        $this->currentProposal = $jobSeekerProposal;

        $this->recordThat(ProposalWasSubmittedByJobSeeker::forCheckout($jobSeekerProposal, $this->id));
    }

    public function submitClientProposal(ClientProposal $clientProposal): void
    {
        $this->ensureProposalCanBeSubmittedToCheckout();

        if (!$this->parties->isClientAllowed($proposingClient = $clientProposal->proposedBy())) {
            throw CannotSubmitProposal::clientIsNotAllowed($proposingClient, $this->id);
        }

        if ($this->isClientLatestProposal()) {
            throw CannotSubmitProposal::clientAlreadySubmitted($proposingClient, $this->id);
        }

        $this->currentProposal = $clientProposal;

        $this->recordThat(ProposalWasSubmittedByClient::forCheckout($clientProposal, $this->id));
    }

    public function submitClientAgreement(string $client): void
    {
        $this->ensureAgreementCanBeSubmittedToCheckout();

        if (!$this->parties->isClientAllowed($client)) {
            throw CannotSubmitAgreement::clientIsNotAllowed($client, $this->id);
        }

        if ($this->isClientLatestProposal()) {
            throw CannotSubmitAgreement::clientTryingToAgreeOnOwnProposal($client, $this->id);
        }

        $this->agreed = true;

        $this->recordThat(
            AgreementWasSubmittedByClient::forCheckoutWithContent($client, $this->id, $this->currentProposal()->content())
        );
    }

    public function submitJobSeekerAgreement(string $jobSeeker): void
    {
        $this->ensureAgreementCanBeSubmittedToCheckout();

        if (!$this->parties->isJobSeekerAllowed($jobSeeker)) {
            throw CannotSubmitAgreement::jobSeekerIsNotAllowed($jobSeeker, $this->id);
        }

        if ($this->isJobSeekerLatestProposal()) {
            throw CannotSubmitAgreement::jobSeekerTryingToAgreeOnOwnProposal($jobSeeker, $this->id);
        }

        $this->agreed = true;

        $this->recordThat(
            AgreementWasSubmittedByJobSeeker::forCheckoutWithContent(
                $jobSeeker,
                $this->id,
                $this->currentProposal()->content()
            )
        );
    }

    public function markAsNoShow(/** string $bySomeone */): void
    {
        $this->ensureCanBeMarkedAsNoShow();

        $this->noShow = true;

        // Should we also pass client id here?
        $this->recordThat(JobSeekerWasMarkedAsNoShow::forCheckout($this->parties->jobSeeker(), $this->id));
    }

    private function ensureCanBeMarkedAsNoShow(): void
    {
        /**
         * A checkout cannot be Marked as NoShow when:
         *     - it has been agreed
         *     - Shift cannot be Checked out yet (aka too early)
         *     - A proposal has been submitted
         *     - NO_SHOW_DAYS_LIMIT has not been exceeded
         *
         * Not really sure about the third point:
         * "A proposal has been submitted" means: either a Client or a FF proposal is there
         * So, does it make sense to allow a Client to send a NoShow on a Checkout where the Client
         * itself is waiting for FF agreement (aka $this->isClientLatestProposal() === true)
         * What should be the behavior?
         */

        // should we move this to StartedShift as a method?
        $hasExceededNoShowLimit =
            fn(StartedShift $shift) =>
                $shift
                    ->start()
                    ->modify(sprintf('+%d days', self::NO_SHOW_DAYS_LIMIT)) < new \DateTimeImmutable()
        ;

        $reasonToFail = $this->agreed ? 'Checkout already Agreed' : null;
        $reasonToFail ??= !$this->startedShift->canBeCheckedOut() ? 'Shift not yet ready for Checkout' : null;
        $reasonToFail ??= $this->hasProposal() ? 'Pending Proposals on Checkout' : null;
        $reasonToFail ??= !$hasExceededNoShowLimit($this->startedShift) ? 'Too early to Mark as No Show' : null;

        if ($reasonToFail) {
            throw CannotMarkNoShow::forCheckout($this, $reasonToFail);
        }
    }

    private function ensureAgreementCanBeSubmittedToCheckout(): void
    {
        if ($this->agreed) {
            throw CheckoutAlreadyAgreed::triedToAgreeAgain($this->id);
        }

        if ($this->noShow) {
            throw CannotSubmitAgreement::becauseCheckoutWasMarkedAsNoShow($this->id);
        }

        if (!$this->hasProposal()) {
            throw CannotSubmitAgreement::noAvailableProposalsForCheckout($this->id);
        }
    }

    private function ensureProposalCanBeSubmittedToCheckout(): void
    {
        if (!$this->startedShift->canBeCheckedOut()) {
            throw CannotSubmitProposal::becauseShiftIsNotYetChecokutable($this->id);
        }

        if ($this->agreed) {
            throw CannotSubmitProposal::thereIsAlreadyAnAgreementOnCheckout($this->id);
        }

        if ($this->noShow) {
            throw CannotSubmitProposal::becauseCheckoutWasMarkedAsNoShow($this->id);
        }
    }

    public function id(): CheckoutId
    {
        return $this->id;
    }

    public function isClientLatestProposal(): bool
    {
        return $this->currentProposal instanceof ClientProposal;
    }

    public function isJobSeekerLatestProposal(): bool
    {
        return $this->currentProposal instanceof JobSeekerProposal;
    }

    public function hasProposal(): bool
    {
        return $this->currentProposal instanceof Proposal;
    }

    public function currentProposal(): Proposal
    {
        $proposal = $this->currentProposal;

        if (!$this->hasProposal()) {
            throw NoProposalAvailable::forCheckout($this);
        }

        /** @var Proposal $proposal */
        return $proposal;
    }

    public function shift(): StartedShift
    {
        return $this->startedShift;
    }

    public function parties(): Parties
    {
        return $this->parties;
    }

    public function isAgreed(): bool
    {
        return $this->agreed;
    }

    public function isNoShow(): bool
    {
        return $this->noShow;
    }
}
