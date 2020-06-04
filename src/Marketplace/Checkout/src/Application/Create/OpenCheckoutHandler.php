<?php

declare(strict_types=1);

namespace Acme\Marketplace\Checkout\Application\Create;

use DateTimeImmutable;
use Acme\Common\Application\Bus\Command\CommandHandler;
use Acme\Marketplace\Checkout\Domain\Checkout;
use Acme\Marketplace\Checkout\Domain\Command\OpenCheckout;
use Acme\Marketplace\Checkout\Domain\Repository\CheckoutRepository;
use Acme\Marketplace\Checkout\Domain\ValueObject\CheckoutId;
use Acme\Marketplace\Checkout\Domain\ValueObject\Parties;
use Acme\Marketplace\Checkout\Domain\ValueObject\StartedShift;

final class OpenCheckoutHandler implements CommandHandler
{
    private CheckoutRepository $checkoutRepository;

    public function __construct(CheckoutRepository $checkoutRepository)
    {
        $this->checkoutRepository = $checkoutRepository;
    }

    public function __invoke(OpenCheckout $command): void
    {
        $id = CheckoutId::fromString($command->id());

        $startedShift = StartedShift::fromValues(
            $command->shiftId(),
            new DateTimeImmutable($command->startedAt()),
            new DateTimeImmutable($command->endsAt())
        );

        $parties = Parties::fromClientAndJobSeeker($command->clientId(), $command->jobSeekerId());

        $this->checkoutRepository->create(Checkout::open($id, $startedShift, $parties));
    }
}
