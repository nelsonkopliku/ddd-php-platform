<?php

declare(strict_types=1);

namespace Acme\Marketplace\Checkout\Application\Agreement;

use Acme\Common\Application\Bus\Command\CommandHandler;
use Acme\Marketplace\Checkout\Domain\Command\SubmitJobSeekerAgreement;
use Acme\Marketplace\Checkout\Domain\Repository\CheckoutRepository;
use Acme\Marketplace\Checkout\Domain\ValueObject\CheckoutId;

final class SubmitJobSeekerAgreementHandler implements CommandHandler
{
    private CheckoutRepository $checkoutRepository;

    public function __construct(CheckoutRepository $checkoutRepository)
    {
        $this->checkoutRepository = $checkoutRepository;
    }

    public function __invoke(SubmitJobSeekerAgreement $command): void
    {
        $checkout = $this->checkoutRepository->search(CheckoutId::fromString($command->checkoutId()));

        $checkout->submitJobSeekerAgreement($command->jobSeeker());

        $this->checkoutRepository->save($checkout);
    }
}
