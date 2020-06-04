<?php

declare(strict_types=1);

namespace Acme\Marketplace\Checkout\Domain\Exception;

use DomainException;
use Acme\Marketplace\Checkout\Domain\ValueObject\CheckoutId;

final class CheckoutAlreadyAgreed extends DomainException
{
    public static function triedToAgreeAgain(CheckoutId $checkout): self
    {
        return new self(sprintf('Checkout "%s" has already been agreed. Cannot agree again.', $checkout->value()));
    }
}
