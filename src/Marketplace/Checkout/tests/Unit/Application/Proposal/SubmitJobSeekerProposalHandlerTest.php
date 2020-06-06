<?php

declare(strict_types=1);

namespace Acme\Marketplace\Checkout\Tests\Unit\Application\Proposal;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Acme\Marketplace\Checkout\Application\Proposal\SubmitJobSeekerProposalHandler;
use Acme\Marketplace\Checkout\Domain\Checkout;
use Acme\Marketplace\Checkout\Domain\Repository\CheckoutRepository;
use Acme\Marketplace\Checkout\Domain\Command\SubmitJobSeekerProposal;
use Acme\Marketplace\Checkout\Tests\Unit\Domain\Dummy\CheckoutDummy;
use Acme\Marketplace\Checkout\Tests\Unit\Domain\Dummy\ProposalDummy;
use Prophecy\PhpUnit\ProphecyTrait;

final class SubmitJobSeekerProposalHandlerTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @test
     */
    public function it_should_handle_job_seeker_proposal_submission(): void
    {
        $repository = $this->prophesize(CheckoutRepository::class);

        $checkout = CheckoutDummy::aCheckoutReadyForJobSeekerProposal();
        $futureCheckout = CheckoutDummy::aCheckoutReadyForJobSeekerProposal();

        $jobSeekerProposal = ProposalDummy::jobSeekerProposal();
        $futureCheckout->submitJobSeekerProposal($jobSeekerProposal);

        $repository
            ->search(Argument::exact($checkout->id()))
            ->willReturn($checkout)
            ->shouldBeCalled()
        ;

        $repository
            ->save(Argument::that(
                fn (Checkout $futureCheckout) => $futureCheckout->currentProposal() === $checkout->currentProposal() &&
                    $futureCheckout->isJobSeekerLatestProposal()
            ))
            ->shouldBeCalled()
        ;

        $handler = new SubmitJobSeekerProposalHandler($repository->reveal());

        $handler(new SubmitJobSeekerProposal(
            CheckoutDummy::JOBSEEKER_ID,
            $checkout->id()->value(),
            $jobSeekerProposal->content()->hoursWorked()->from()->format(DATE_ATOM),
            $jobSeekerProposal->content()->hoursWorked()->until()->format(DATE_ATOM),
            $jobSeekerProposal->content()->hoursWorked()->minutesBreak(),
            $jobSeekerProposal->content()->compensation()
        ));
    }
}
