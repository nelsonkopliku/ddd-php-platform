<?php

declare(strict_types=1);

namespace Acme\Marketplace\Checkout\Application\Proposal;

use DateTimeImmutable;
use Acme\Common\Application\Bus\Command\CommandHandler;
use Acme\Marketplace\Checkout\Domain\Command\SubmitJobSeekerProposal;
use Acme\Marketplace\Checkout\Domain\Proposal\JobSeekerProposal;
use Acme\Marketplace\Checkout\Domain\Proposal\ProposalContent;
use Acme\Marketplace\Checkout\Domain\Repository\CheckoutRepository;
use Acme\Marketplace\Checkout\Domain\ValueObject\CheckoutId;

final class SubmitJobSeekerProposalHandler implements CommandHandler
{
    private CheckoutRepository $checkoutRepository;

    public function __construct(CheckoutRepository $checkoutRepository)
    {
        $this->checkoutRepository = $checkoutRepository;
    }

    public function __invoke(SubmitJobSeekerProposal $command): void
    {
        $checkout = $this->checkoutRepository->search(CheckoutId::fromString($command->checkoutId()));

        $jobSeekerProposal = JobSeekerProposal::fromJobSeekerAndContent(
            $command->jobSeekerId(),
            ProposalContent::fromValues(
                new DateTimeImmutable($command->workedFrom()),
                new DateTimeImmutable($command->workedUntil()),
                $command->minutesBreak(),
                $command->compensation()
            )
        );

        $checkout->submitJobSeekerProposal($jobSeekerProposal);

        $this->checkoutRepository->save($checkout);
    }
}
