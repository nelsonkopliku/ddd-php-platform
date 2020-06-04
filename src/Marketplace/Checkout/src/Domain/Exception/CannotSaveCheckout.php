<?php

declare(strict_types=1);

namespace Acme\Marketplace\Checkout\Domain\Exception;

use DomainException;
use Acme\Marketplace\Checkout\Domain\Checkout;

final class CannotSaveCheckout extends DomainException
{
    public static function becauseCheckoutDoesNotExist(Checkout $checkout): self
    {
        return new self(sprintf('Cannot save non existent Checkout "%s".', $checkout->id()->value()));
    }
}
