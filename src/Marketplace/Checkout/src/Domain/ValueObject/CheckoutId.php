<?php

declare(strict_types=1);

namespace Acme\Marketplace\Checkout\Domain\ValueObject;

use Acme\Common\Domain\ValueObject\StringValueObject;

final class CheckoutId extends StringValueObject
{
    public static function fromString(string $id): self
    {
        return new self($id);
    }
}
