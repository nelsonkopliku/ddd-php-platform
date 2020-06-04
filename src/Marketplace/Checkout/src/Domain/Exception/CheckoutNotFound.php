<?php

declare(strict_types=1);

namespace Acme\Marketplace\Checkout\Domain\Exception;

use DomainException;
use Acme\Marketplace\Checkout\Domain\ValueObject\CheckoutId;

final class CheckoutNotFound extends DomainException
{
    public static function withId(CheckoutId $id): self
    {
        return new self(sprintf('Checkout with id "%s" not found', $id->value()));
    }
}
