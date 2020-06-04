<?php

declare(strict_types=1);

namespace Acme\Marketplace\Checkout\Infrastructure\Persistence;

use Acme\Common\Infrastructure\Persistence\Doctrine\DoctrineAggregateRepository;
use Acme\Marketplace\Checkout\Domain\Checkout;
use Acme\Marketplace\Checkout\Domain\Exception\CannotCreateCheckout;
use Acme\Marketplace\Checkout\Domain\Exception\CannotSaveCheckout;
use Acme\Marketplace\Checkout\Domain\Exception\CheckoutNotFound;
use Acme\Marketplace\Checkout\Domain\Repository\CheckoutRepository;
use Acme\Marketplace\Checkout\Domain\ValueObject\CheckoutId;
use Acme\Marketplace\Checkout\Infrastructure\Persistence\Doctrine\Entity\Checkout as CheckoutEntity;

final class DoctrineCheckoutRepository extends DoctrineAggregateRepository implements CheckoutRepository
{
    protected static string $entityClass = CheckoutEntity::class;

    public function create(Checkout $checkout): void
    {
        $entity = $this->repository()->find($checkout->id()->value());

        if ($entity instanceof CheckoutEntity) {
            throw CannotCreateCheckout::becauseCheckoutWithSameIdAlreadyExists($checkout);
        }

        $this->persist($checkout, CheckoutEntity::fromDomainCheckout($checkout));
    }

    public function save(Checkout $checkout): void
    {
        $entity = $this->repository()->find($checkout->id()->value());

        if (!$entity instanceof CheckoutEntity) {
            throw CannotSaveCheckout::becauseCheckoutDoesNotExist($checkout);
        }

        $entity->refresh($checkout);

        $this->persist($checkout, $entity);
    }

    public function search(CheckoutId $id): Checkout
    {
        $checkoutEntity = $this->repository()->find($id->value());

        if (!$checkoutEntity instanceof CheckoutEntity) {
            throw CheckoutNotFound::withId($id);
        }

        return $checkoutEntity->toDomainCheckout();
    }
}
