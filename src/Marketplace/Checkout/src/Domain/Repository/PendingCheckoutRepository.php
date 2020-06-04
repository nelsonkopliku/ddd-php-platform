<?php

declare(strict_types=1);

namespace Acme\Marketplace\Checkout\Domain\Repository;

use Acme\Marketplace\Checkout\Domain\Checkout;

interface PendingCheckoutRepository
{
    /**
     * @return Checkout[]
     */
    public function findPendingByClient(string $clientId): array;

    /**
     * @return Checkout[]
     */
    public function findPendingByJobSeeker(string $jobSeekerId): array;
}
