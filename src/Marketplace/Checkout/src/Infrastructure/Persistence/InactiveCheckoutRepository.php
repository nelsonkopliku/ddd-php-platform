<?php

declare(strict_types=1);

namespace Acme\Marketplace\Checkout\Infrastructure\Persistence;

use DateTimeImmutable;
use Acme\Common\Infrastructure\Persistence\Doctrine\DoctrineAggregateRepository;
use Acme\Marketplace\Checkout\Domain\Checkout;
use Acme\Marketplace\Checkout\Domain\Repository\InactiveCheckoutRepository as InactiveCheckoutRepositoryInterface;
use Acme\Marketplace\Checkout\Infrastructure\Persistence\Doctrine\Entity\Checkout as CheckoutEntity;

final class InactiveCheckoutRepository extends DoctrineAggregateRepository implements InactiveCheckoutRepositoryInterface
{
    protected static string $entityClass = CheckoutEntity::class;

    public function findInactiveCheckouts(): array
    {
        $dayLimit = new DateTimeImmutable(sprintf('-%d days', Checkout::NO_SHOW_DAYS_LIMIT));

        $entities = $this->repository()
            ->createQueryBuilder('c')
            ->andWhere('c.agreed = false')
            ->andWhere('c.noShow = false')
            ->andWhere('c.startedAt < :dateLimit')
            ->leftJoin('c.proposals', 'p')
            ->addSelect('p')
            ->andWhere('p IS NULL')
            ->setParameter('dateLimit', $dayLimit)
            ->getQuery()
            ->getResult()
        ;

        return array_map(fn (CheckoutEntity $checkout) => $checkout->toDomainCheckout(), $entities);
    }
}
