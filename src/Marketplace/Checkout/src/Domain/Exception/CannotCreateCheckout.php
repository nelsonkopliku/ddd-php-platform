<?php

declare(strict_types=1);

namespace Acme\Marketplace\Checkout\Domain\Exception;

use DomainException;
use Acme\Marketplace\Checkout\Domain\Checkout;

final class CannotCreateCheckout extends DomainException
{
    public static function becauseCheckoutWithSameIdAlreadyExists(Checkout $checkout): self
    {
        return new self(
            sprintf('Cannot create Checkout. Checkout with id "%s" already exists', $checkout->id()->value())
        );
    }
}
