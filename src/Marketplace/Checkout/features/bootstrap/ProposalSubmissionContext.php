<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;
use BehatExpectException\ExpectException;
use PHPUnit\Framework\Assert;
use Acme\Marketplace\Checkout\Domain\Exception\CannotSubmitProposal;
use Acme\Marketplace\Checkout\Tests\Unit\Domain\Dummy\ProposalDummy;

final class ProposalSubmissionContext implements Context
{
    use ExpectException;

    private CheckoutContext $checkoutContext;

    /** @BeforeScenario */
    public function gatherContexts(\Behat\Behat\Hook\Scope\BeforeScenarioScope $scope)
    {
        $environment = $scope->getEnvironment();

        $this->checkoutContext = $environment->getContext(CheckoutContext::class);
    }

    /**
     * @When A job seeker that is not known in the Parties submits a proposal
     */
    public function aJobSeekerThatIsNotKnownInThePartiesSubmitsAProposal(): void
    {
        $this->mayFail(
            fn () => $this->checkoutContext->checkout()->submitJobSeekerProposal(
                ProposalDummy::jobSeekerProposal('notAllowed')
            )
        );
    }

    /**
     * @Then The submitting job seeker should not be allowed to submit proposal
     */
    public function theSubmittingJobSeekerShouldNotBeAllowedToSubmitProposal(): void
    {
        $this->assertCaughtExceptionMatches(
            CannotSubmitProposal::class,
            'JobSeeker "notAllowed" not allowed to submit proposal'
        );
    }

    /**
     * @When A client that is not known in the Parties submits a proposal
     */
    public function aClientThatIsNotKnownInThePartiesSubmitsAProposal(): void
    {
        $this->mayFail(
            fn () => $this->checkoutContext->checkout()->submitClientProposal(
                ProposalDummy::clientProposal('notAllowed')
            )
        );
    }

    /**
     * @Then The submitting client should not be allowed to submit proposal
     */
    public function theSubmittingClientShouldNotBeAllowedToSubmitProposal(): void
    {
        $this->assertCaughtExceptionMatches(
            CannotSubmitProposal::class,
            'Client "notAllowed" not allowed to submit proposal'
        );
    }

    /**
     * @Then The submission should fail due to Checkout being already agreed
     */
    public function theSubmissionShouldFailDueToCheckoutBeingAlreadyAgreed(): void
    {
        $this->assertCaughtExceptionMatches(
            CannotSubmitProposal::class,
            'Proposal cannot be submitted to Checkout "someCheckoutId". Checkout has already been agreed'
        );
    }

    /**
     * @Then The submission should fail due to Job seeker submitting multiple times in a row
     */
    public function theSubmissionShouldFailDueToJobSeekerSubmittingMultipleTimesInARow(): void
    {
        $this->assertCaughtExceptionMatches(
            CannotSubmitProposal::class,
            'JobSeeker "jobSeekerId" already submitted a proposal for Checkout'
        );
    }

    /**
     * @Then The submission should fail due to Client submitting multiple times in a row
     */
    public function theSubmissionShouldFailDueToClientSubmittingMultipleTimesInARow(): void
    {
        $this->assertCaughtExceptionMatches(
            CannotSubmitProposal::class,
            'Client "clientId" already submitted a proposal for Checkout'
        );
    }

    /**
     * @When The job seeker submits a proposal
     */
    public function theJobSeekerSubmitsAProposal(): void
    {
        $this->mayFail(fn () => $this->checkoutContext->theJobSeekerSubmitsAProposal());
    }

    /**
     * @When The client submits a proposal
     */
    public function theClientSubmitsAProposal(): void
    {
        $this->mayFail(fn () => $this->checkoutContext->theClientSubmitsAProposal());
    }

    /**
     * @Given A Checkout pending for Client Agreement
     */
    public function aCheckoutPendingForClientAgreement(): void
    {
        $this->checkoutContext->buildCheckout(false, ProposalDummy::jobSeekerProposal());
    }

    /**
     * @Given A Checkout pending for JobSeeker Agreement
     */
    public function aCheckoutPendingForJobSeekerAgreement(): void
    {
        $this->checkoutContext->buildCheckout(false, ProposalDummy::clientProposal());
    }

    /**
     * @Then Submission should be successful
     */
    public function submissionShouldBeSuccessful(): void
    {
        Assert::assertTrue($this->checkoutContext->checkout()->hasProposal());
    }
}
