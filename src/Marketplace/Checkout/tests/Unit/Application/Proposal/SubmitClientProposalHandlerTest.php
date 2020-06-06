<?php

declare(strict_types=1);

namespace Acme\Marketplace\Checkout\Tests\Unit\Application\Proposal;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Acme\Marketplace\Checkout\Application\Proposal\SubmitClientProposalHandler;
use Acme\Marketplace\Checkout\Domain\Checkout;
use Acme\Marketplace\Checkout\Domain\Repository\CheckoutRepository;
use Acme\Marketplace\Checkout\Domain\Command\SubmitClientProposal;
use Acme\Marketplace\Checkout\Domain\Event\ProposalWasSubmittedByClient;
use Acme\Marketplace\Checkout\Tests\Unit\Domain\Dummy\CheckoutDummy;
use Acme\Marketplace\Checkout\Tests\Unit\Domain\Dummy\ProposalDummy;
use Prophecy\PhpUnit\ProphecyTrait;

final class SubmitClientProposalHandlerTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @test
     */
    public function it_should_handle_client_proposal_submission(): void
    {
        $repository = $this->prophesize(CheckoutRepository::class);

        $checkout = CheckoutDummy::aCheckoutReadyForClientProposal();
        $clientProposal = ProposalDummy::clientProposal();

        $repository
            ->search(Argument::exact($checkout->id()))
            ->willReturn($checkout)
            ->shouldBeCalled()
        ;

        $repository
            ->save(Argument::that(
                fn (Checkout $modifiedCheckout) =>
                    $modifiedCheckout->isClientLatestProposal() &&
                    $modifiedCheckout->currentProposal()->equalsTo($checkout->currentProposal())
            ))
            ->shouldBeCalled()
        ;

        $handler = new SubmitClientProposalHandler($repository->reveal());

        $handler(new SubmitClientProposal(
            CheckoutDummy::CLIENT_ID,
            $checkout->id()->value(),
            $clientProposal->content()->hoursWorked()->from()->format(DATE_ATOM),
            $clientProposal->content()->hoursWorked()->until()->format(DATE_ATOM),
            $clientProposal->content()->hoursWorked()->minutesBreak(),
            $clientProposal->content()->compensation()
        ));

        $recordedEvents = $checkout->getRecordedEvents();

        $this->assertInstanceOf(ProposalWasSubmittedByClient::class, current($recordedEvents));
    }
}
