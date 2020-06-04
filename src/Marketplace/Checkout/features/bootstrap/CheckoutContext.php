<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;
use BehatExpectException\ExpectException;
use PHPUnit\Framework\Assert;
use Acme\Marketplace\Checkout\Domain\Checkout;
use Acme\Marketplace\Checkout\Domain\Proposal\Proposal;
use Acme\Marketplace\Checkout\Domain\ValueObject\CheckoutId;
use Acme\Marketplace\Checkout\Domain\ValueObject\Parties;
use Acme\Marketplace\Checkout\Domain\ValueObject\StartedShift;
use Acme\Marketplace\Checkout\Tests\Unit\Domain\Dummy\ProposalDummy;
use Acme\Marketplace\Checkout\Tests\Unit\Domain\Dummy\ShiftDummy;

final class CheckoutContext implements Context
{
    use ExpectException;

    protected Checkout $checkout;

    public function buildCheckout(bool $agreed = false, Proposal $proposal = null): void
    {
        $from = new DateTimeImmutable('2020-01-01 12:00:00');
        $to = $from->modify('+8 hours');
        $checkoutId = CheckoutId::fromString('someCheckoutId');

        $this->checkout = Checkout::fromValues(
            $checkoutId,
            StartedShift::fromValues(
                'someShiftId',
                $from,
                $to
            ),
            Parties::fromClientAndJobSeeker(
                'clientId',
                'jobSeekerId'
            ),
            $proposal,
            $agreed
        );
    }

    public function checkout(): Checkout
    {
        return $this->checkout;
    }

    /**
     * @Given An opened Checkout
     */
    public function anOpenedCheckout(): void
    {
        $this->buildCheckout(false, ProposalDummy::clientProposal());
    }

    /**
     * @Given A Checkout with no proposals
     */
    public function aCheckoutWithNoProposals()
    {
        $this->buildCheckout(false);
    }

    /**
     * @Given An already agreed Checkout
     */
    public function anAlreadyAgreedCheckout(): void
    {
        $this->buildCheckout(true, ProposalDummy::clientProposal());
    }

    /**
     * @Given A checkout not ready yet to be modified
     */
    public function aCheckoutNotReadyYetToBeModified()
    {
        $aShiftStartedLessThanOneHourAgo = ShiftDummy::aShiftStartedLessThanOneHourAgo();

        $this->checkout = Checkout::fromValues(
            CheckoutId::fromString('some'),
            $aShiftStartedLessThanOneHourAgo,
            Parties::fromClientAndJobSeeker(
                'jobSeeker',
                'client'
            ),
            null,
            false
        );
    }

    /**
     * @Given A Checkout for a Shift started less than 7 days ago
     */
    public function aCheckoutForAShiftStartedLessThanDaysAgo()
    {
        $shiftStartedLessThanOneWeekAgo = ShiftDummy::aShiftStartedLessThanOneWeekAgo();

        $this->checkout = Checkout::fromValues(
            CheckoutId::fromString('some'),
            $shiftStartedLessThanOneWeekAgo,
            Parties::fromClientAndJobSeeker(
                'jobSeeker',
                'client'
            ),
            null,
            false
        );
    }

    /**
     * @Given A Checkout inactive for more than 7 days
     */
    public function aCheckoutInactiveForMoreThanDays()
    {
        $aShiftStartedMoreThanOneWeekAgo = ShiftDummy::aShiftStartedMoreThanOneWeekAgo();

        $this->checkout = Checkout::fromValues(
            CheckoutId::fromString('some'),
            $aShiftStartedMoreThanOneWeekAgo,
            Parties::fromClientAndJobSeeker(
                'jobSeeker',
                'client'
            ),
            null,
            false
        );
    }

    public function theJobSeekerSubmitsAProposal(): void
    {
        $this->checkout->submitJobSeekerProposal(ProposalDummy::jobSeekerProposal());
    }

    public function theClientSubmitsAProposal(): void
    {
        $this->checkout->submitClientProposal(ProposalDummy::clientProposal());
    }

    public function theJobSeekerSubmitsAgreement(): void
    {
        $this->checkout->submitJobSeekerAgreement('jobSeekerId');
    }

    public function theClientSubmitsAgreement(): void
    {
        $this->checkout->submitClientAgreement('clientId');
    }

    /**
     * @Then The :domainEventName Domain Event should be raised
     */
    public function theDomainEventShouldBeRaised(string $domainEventName): void
    {
        $events = $this->checkout->getRecordedEvents();

        Assert::assertSame($domainEventName, $events[0]::name());
    }
}
