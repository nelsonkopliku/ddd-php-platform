<?php

declare(strict_types=1);

namespace Acme\Marketplace\Checkout\Application\Proposal;

use DateTimeImmutable;
use Acme\Common\Application\Bus\Command\CommandHandler;
use Acme\Marketplace\Checkout\Domain\Command\SubmitClientProposal;
use Acme\Marketplace\Checkout\Domain\Proposal\ClientProposal;
use Acme\Marketplace\Checkout\Domain\Proposal\ProposalContent;
use Acme\Marketplace\Checkout\Domain\Repository\CheckoutRepository;
use Acme\Marketplace\Checkout\Domain\ValueObject\CheckoutId;

final class SubmitClientProposalHandler implements CommandHandler
{
    private CheckoutRepository $checkoutRepository;

    public function __construct(CheckoutRepository $checkoutRepository)
    {
        $this->checkoutRepository = $checkoutRepository;
    }

    public function __invoke(SubmitClientProposal $command): void
    {
        $checkout = $this->checkoutRepository->search(CheckoutId::fromString($command->checkoutId()));

        $clientProposal = ClientProposal::fromClientAndContent(
            $command->clientId(),
            ProposalContent::fromValues(
                new DateTimeImmutable($command->workedFrom()),
                new DateTimeImmutable($command->workedUntil()),
                $command->minutesBreak(),
                $command->compensation()
            )
        );

        $checkout->submitClientProposal($clientProposal);

        $this->checkoutRepository->save($checkout);
    }
}
