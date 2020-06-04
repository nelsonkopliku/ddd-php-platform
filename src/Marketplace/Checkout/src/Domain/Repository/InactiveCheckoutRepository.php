<?php

declare(strict_types=1);

namespace Acme\Marketplace\Checkout\Domain\Repository;

use Acme\Marketplace\Checkout\Domain\Checkout;

interface InactiveCheckoutRepository
{
    /**
     * @return Checkout[]
     */
    public function findInactiveCheckouts(): array;
}
