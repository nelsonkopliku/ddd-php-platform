<?php

declare(strict_types=1);

namespace Acme\Marketplace\Checkout\Infrastructure\Persistence;

use DateTimeImmutable;
use Doctrine\ORM\QueryBuilder;
use Acme\Common\Infrastructure\Persistence\Doctrine\DoctrineAggregateRepository;
use Acme\Marketplace\Checkout\Domain\Checkout;
use Acme\Marketplace\Checkout\Domain\Repository\CheckoutReadRepository;
use Acme\Marketplace\Checkout\Infrastructure\Persistence\Doctrine\Entity\Checkout as CheckoutEntity;

final class DoctrineCheckoutReadRepository extends DoctrineAggregateRepository implements CheckoutReadRepository
{
    protected static string $entityClass = CheckoutEntity::class;

    public function findAgreedByClient(string $clientId, DateTimeImmutable $since = null): array
    {
        $qb = $this->clientCheckoutsQb($clientId, true);

        if ($since) {
            $this->applySinceFilter($qb, $since);
        }

        return $this->mapToDomainCheckouts($qb->getQuery()->execute());
    }

    public function findAgreedByJobSeeker(string $jobseekerId, DateTimeImmutable $since = null): array
    {
        $qb = $this->jobSeekerCheckoutsQb($jobseekerId, true);

        if ($since) {
            $this->applySinceFilter($qb, $since);
        }

        return $this->mapToDomainCheckouts($qb->getQuery()->execute());
    }

    public function findPendingByClient(string $clientId): array
    {
        $query = $this->clientCheckoutsQb($clientId)->getQuery();

        return $this->mapToDomainCheckouts($query->execute(), fn (Checkout $c) => $c->isJobSeekerLatestProposal());
    }

    public function findPendingByJobSeeker(string $jobSeekerId): array
    {
        $query = $this->jobSeekerCheckoutsQb($jobSeekerId)->getQuery();

        return $this->mapToDomainCheckouts($query->execute(), fn (Checkout $c) => $c->isClientLatestProposal());
    }

    private function clientCheckoutsQb(string $clientId, bool $agreed = false): QueryBuilder
    {
        return $this->repository()
            ->createQueryBuilder('c')
            ->andWhere('c.clientId = :clientId')
            ->andWhere('c.agreed = :agreed')
            ->setParameter('clientId', $clientId)
            ->setParameter('agreed', $agreed)
            ->leftJoin('c.proposals', 'p')
            ->addSelect('p');
    }

    private function jobSeekerCheckoutsQb(string $jobSeekerId, bool $agreed = false): QueryBuilder
    {
        return $this
            ->repository()
            ->createQueryBuilder('c')
            ->andWhere('c.jobSeekerId = :jobSeekerId')
            ->andWhere('c.agreed = :agreed')
            ->setParameter('jobSeekerId', $jobSeekerId)
            ->setParameter('agreed', $agreed)
            ->leftJoin('c.proposals', 'p')
            ->addSelect('p');
    }

    private function applySinceFilter(QueryBuilder $queryBuilder, DateTimeImmutable $since): void
    {
        $queryBuilder
            ->andWhere('c.startedAt > :startedAt')
            ->setParameter('startedAt', $since);
    }

    /**
     * @param CheckoutEntity[] $checkouts
     *
     * @return Checkout[]
     */
    private function mapToDomainCheckouts(array $checkouts, callable $filterBy = null): array
    {
        $domainCheckouts = array_map(fn (CheckoutEntity $entity) => $entity->toDomainCheckout(), $checkouts);

        $domainCheckouts = $filterBy ? array_filter($domainCheckouts, $filterBy) : $domainCheckouts;

        return array_values($domainCheckouts);
    }
}
