<?php

declare(strict_types=1);

namespace Acme\Marketplace\Checkout\Application\Agreement;

use Acme\Common\Application\Bus\Command\CommandHandler;
use Acme\Marketplace\Checkout\Domain\Command\SubmitClientAgreement;
use Acme\Marketplace\Checkout\Domain\Repository\CheckoutRepository;
use Acme\Marketplace\Checkout\Domain\ValueObject\CheckoutId;

final class SubmitClientAgreementHandler implements CommandHandler
{
    private CheckoutRepository $checkoutRepository;

    public function __construct(CheckoutRepository $checkoutRepository)
    {
        $this->checkoutRepository = $checkoutRepository;
    }

    public function __invoke(SubmitClientAgreement $command): void
    {
        $checkout = $this->checkoutRepository->search(CheckoutId::fromString($command->checkoutId()));

        $checkout->submitClientAgreement($command->clientId());

        $this->checkoutRepository->save($checkout);
    }
}
