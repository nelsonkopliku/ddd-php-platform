<?php

declare(strict_types=1);

namespace Acme\Marketplace\Checkout\Domain\Repository;

use DateTimeImmutable;
use Acme\Marketplace\Checkout\Domain\Checkout;

interface AgreedCheckoutRepository
{
    /**
     * @return Checkout[]
     */
    public function findAgreedByClient(string $clientId, DateTimeImmutable $since = null): array;

    /**
     * @return Checkout[]
     */
    public function findAgreedByJobSeeker(string $jobseekerId, DateTimeImmutable $since = null): array;
}
