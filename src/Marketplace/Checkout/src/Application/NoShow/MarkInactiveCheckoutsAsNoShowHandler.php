<?php

declare(strict_types=1);

namespace Acme\Marketplace\Checkout\Application\NoShow;

use Acme\Common\Application\Bus\Command\CommandHandler;
use Acme\Marketplace\Checkout\Domain\Command\MarkJobSeekerAsNoShow;
use Acme\Marketplace\Checkout\Domain\Repository\CheckoutRepository;
use Acme\Marketplace\Checkout\Domain\ValueObject\CheckoutId;

final class MarkInactiveCheckoutsAsNoShowHandler implements CommandHandler
{
    private CheckoutRepository $repository;

    public function __construct(CheckoutRepository $repository)
    {
        $this->repository = $repository;
    }

    public function __invoke(MarkJobSeekerAsNoShow $command): void
    {
        $checkout = $this->repository->search(CheckoutId::fromString($command->checkoutId()));

        $checkout->markAsNoShow();

        $this->repository->save($checkout);
    }
}
