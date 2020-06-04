<?php

declare(strict_types=1);

namespace Acme\Marketplace\Checkout\Domain\Command;

use Acme\Common\Domain\Command\Command;

final class MarkJobSeekerAsNoShow implements Command
{
    private string $checkoutId;

    public function __construct(string $checkoutId)
    {
        $this->checkoutId = $checkoutId;
    }

    final public function checkoutId(): string
    {
        return $this->checkoutId;
    }
}
