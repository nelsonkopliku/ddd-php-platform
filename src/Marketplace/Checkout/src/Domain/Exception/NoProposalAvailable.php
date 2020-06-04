<?php

declare(strict_types=1);

namespace Acme\Marketplace\Checkout\Domain\Exception;

use DomainException;
use Acme\Marketplace\Checkout\Domain\Checkout;

final class NoProposalAvailable extends DomainException
{
    public static function forCheckout(Checkout $checkout): self
    {
        return new self(sprintf('No proposal found for Checkout %s', $checkout->id()->value()));
    }
}
