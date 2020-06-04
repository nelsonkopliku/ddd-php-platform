<?php

declare(strict_types=1);

namespace Acme\Marketplace\Checkout\Application\NoShow;

use Acme\Common\Application\Bus\Command\CommandBus;
use Acme\Marketplace\Checkout\Domain\Command\MarkJobSeekerAsNoShow;
use Acme\Marketplace\Checkout\Domain\Repository\InactiveCheckoutRepository;

final class MarkInactiveCheckoutsAsNoShow
{
    private CommandBus $bus;

    private InactiveCheckoutRepository $repository;

    public function __construct(CommandBus $bus, InactiveCheckoutRepository $repository)
    {
        $this->bus = $bus;
        $this->repository = $repository;
    }

    public function allInactiveCheckouts(): void
    {
        $inactiveCheckouts = $this->repository->findInactiveCheckouts();

        foreach ($inactiveCheckouts as $inactiveCheckout) {
            $this->bus->dispatch(new MarkJobSeekerAsNoShow($inactiveCheckout->id()->value()));
        }
    }
}
