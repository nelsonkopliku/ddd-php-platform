<?php

declare(strict_types=1);

namespace Acme\Marketplace\Checkout\Domain\Repository;

interface CheckoutReadRepository extends PendingCheckoutRepository, AgreedCheckoutRepository
{
}
