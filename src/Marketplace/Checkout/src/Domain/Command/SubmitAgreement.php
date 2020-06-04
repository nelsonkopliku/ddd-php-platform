<?php

declare(strict_types=1);

namespace Acme\Marketplace\Checkout\Domain\Command;

use Acme\Common\Domain\Command\Command;

abstract class SubmitAgreement implements Command
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
