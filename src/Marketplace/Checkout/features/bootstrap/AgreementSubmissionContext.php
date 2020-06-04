<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use BehatExpectException\ExpectException;
use PHPUnit\Framework\Assert;
use Acme\Marketplace\Checkout\Domain\Exception\CannotSubmitAgreement;
use Acme\Marketplace\Checkout\Domain\Exception\CheckoutAlreadyAgreed;

final class AgreementSubmissionContext implements Context
{
    use ExpectException;

    private CheckoutContext $checkoutContext;

    /** @BeforeScenario */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $environment = $scope->getEnvironment();

        $this->checkoutContext = $environment->getContext(CheckoutContext::class);
    }

    /**
     * @When A job seeker that is not known in the Parties submits agreement
     */
    public function aJobSeekerThatIsNotKnownInThePartiesSubmitsAgreement()
    {
        $this->mayFail(fn () => $this->checkoutContext->checkout()->submitJobSeekerAgreement('notAllowed'));
    }

    /**
     * @When A client that is not known in the Parties submits agreement
     */
    public function aClientThatIsNotKnownInThePartiesSubmitsAgreement()
    {
        $this->mayFail(fn () => $this->checkoutContext->checkout()->submitClientAgreement('notAllowed'));
    }

    /**
     * @Then The job seeker should not be allowed to submit agreement
     */
    public function theJobSeekerShouldNotBeAllowedToSubmitAgreement()
    {
        $this->assertCaughtExceptionMatches(
            CannotSubmitAgreement::class,
            'JobSeeker "notAllowed" not allowed to submit Agreement for Checkout'
        );
    }

    /**
     * @Then The client should not be allowed to submit agreement
     */
    public function theClientShouldNotBeAllowedToSubmitAgreement()
    {
        $this->assertCaughtExceptionMatches(
            CannotSubmitAgreement::class,
            'Client "notAllowed" not allowed to submit Agreement for Checkout'
        );
    }

    /**
     * @Then The agreement submission should fail due to Checkout being already agreed
     */
    public function theAgreementSubmissionShouldFailDueToCheckoutBeingAlreadyAgreed()
    {
        $this->assertCaughtExceptionMatches(
            CheckoutAlreadyAgreed::class,
            'Checkout "someCheckoutId" has already been agreed. Cannot agree again.'
        );
    }

    /**
     * @Then The agreement submission should fail due to Checkout not having proposals
     */
    public function theAgreementSubmissionShouldFailDueToCheckoutNotHavingProposals()
    {
        $this->assertCaughtExceptionMatches(
            CannotSubmitAgreement::class,
            'Unable to submit Agreement for Checkout "someCheckoutId". No proposals available'
        );
    }

    /**
     * @Then The submission should fail due to JobSeeker submitting agrement on own proposal
     */
    public function theSubmissionShouldFailDueToJobSeekerSubmittingAgrementOnOwnProposal()
    {
        $this->assertCaughtExceptionMatches(
            CannotSubmitAgreement::class,
            'JobSeeker "jobSeekerId" not allowed to submit Agreement on own proposal for Checkout "someCheckoutId"'
        );
    }

    /**
     * @Then The submission should fail due to Client submitting agrement on own proposal
     */
    public function theSubmissionShouldFailDueToClientSubmittingAgrementOnOwnProposal()
    {
        $this->assertCaughtExceptionMatches(
            CannotSubmitAgreement::class,
            'Client "clientId" not allowed to submit Agreement on own proposal for Checkout "someCheckoutId"'
        );
    }

    /**
     * @When The job seeker submits the agreement
     */
    public function theJobSeekerSubmitsTheAgreement()
    {
        $this->mayFail(fn () => $this->checkoutContext->theJobSeekerSubmitsAgreement());
    }

    /**
     * @When The client submits the agreement
     */
    public function theClientSubmitsTheAgreement()
    {
        $this->mayFail(fn () => $this->checkoutContext->theClientSubmitsAgreement());
    }

    /**
     * @Then Agreement submission should be successful
     */
    public function agreementSubmissionShouldBeSuccessful()
    {
        Assert::assertTrue($this->checkoutContext->checkout()->isAgreed());
    }
}
