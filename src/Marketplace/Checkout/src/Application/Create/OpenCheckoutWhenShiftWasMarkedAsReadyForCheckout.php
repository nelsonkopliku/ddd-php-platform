<?php

declare(strict_types=1);

namespace Acme\Marketplace\Checkout\Application\Create;

use Acme\Common\Application\Bus\Command\CommandBus;
use Acme\Common\Domain\Event\DomainEventSubscriber;
use Acme\Marketplace\Checkout\Domain\Command\OpenCheckout;
use Acme\Marketplace\Checkout\Domain\Event\ShiftWasMarkedAsReadyForCheckout;

final class OpenCheckoutWhenShiftWasMarkedAsReadyForCheckout implements DomainEventSubscriber
{
    private CommandBus $bus;

    public function __construct(CommandBus $bus)
    {
        $this->bus = $bus;
    }

    public function __invoke(ShiftWasMarkedAsReadyForCheckout $event): void
    {
        $this->bus->dispatch(
            new OpenCheckout(
                $event->match(),
                $event->shift(),
                $event->startedAt(),
                $event->endsAt(),
                $event->jobSeekerId(),
                $event->clientId(),
                $event->hourlyRate() // at the moment hourly rate is not stored anywhere, check what is this information needed for
            )
        );
    }
}
