<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use BehatExpectException\ExpectException;
use Acme\Marketplace\Checkout\Domain\Exception\CannotMarkNoShow;

final class NoShowContext implements Context
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
     * @When The system marks the Checkout as NoShow
     */
    public function theSystemMarksTheCheckoutAsNoshow()
    {
        $this->mayFail(fn() => $this->checkoutContext->checkout()->markAsNoShow());
    }

    /**
     * @Then NoShow should fail
     */
    public function itShouldFailDueToCheckoutBeingAlreadyAgreed()
    {
        $this->assertCaughtExceptionMatches(CannotMarkNoShow::class);
    }
}
