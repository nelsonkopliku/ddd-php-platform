<?php

declare(strict_types=1);

namespace Acme\Marketplace\Checkout\Domain\Exception;

use DomainException;
use Acme\Marketplace\Checkout\Domain\Checkout;

final class CannotMarkNoShow extends DomainException
{
    public static function forCheckout(Checkout $checkout, string $reason): self
    {
        return new self(sprintf(
            'Unable to mark Checkout "%s" as No Show. Reason: "%s"',
            $checkout->id()->value(),
            $reason
        ));
    }
}
