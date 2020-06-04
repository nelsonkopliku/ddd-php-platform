<?php

declare(strict_types=1);

namespace Acme\Marketplace\Checkout\Domain\Repository;

use Acme\Marketplace\Checkout\Domain\Checkout;
use Acme\Marketplace\Checkout\Domain\Exception\CannotCreateCheckout;
use Acme\Marketplace\Checkout\Domain\Exception\CannotSaveCheckout;
use Acme\Marketplace\Checkout\Domain\Exception\CheckoutNotFound;
use Acme\Marketplace\Checkout\Domain\ValueObject\CheckoutId;

interface CheckoutRepository
{
    /**
     * @throws CannotCreateCheckout
     */
    public function create(Checkout $checkout): void;

    /**
     * @throws CannotSaveCheckout
     */
    public function save(Checkout $checkout): void;

    /**
     * @throws CheckoutNotFound
     */
    public function search(CheckoutId $id): Checkout;
}
