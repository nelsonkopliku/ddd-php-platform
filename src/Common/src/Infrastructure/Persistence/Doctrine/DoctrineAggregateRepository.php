<?php

declare(strict_types=1);

namespace Acme\Common\Infrastructure\Persistence\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Acme\Common\Domain\Aggregate\AggregateRepository;
use Acme\Common\Domain\Aggregate\AggregateRoot;
use Acme\Common\Domain\Event\EventRecorder;
use Webmozart\Assert\Assert;

abstract class DoctrineAggregateRepository extends AggregateRepository
{
    /**
     * @var class-string
     */
    protected static string $entityClass;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    private EntityRepository $repository;

    public function __construct(ManagerRegistry $registry, EventRecorder $eventRecorder)
    {
        Assert::notNull(static::$entityClass);
        $em = $registry->getManagerForClass(static::$entityClass);

        Assert::isInstanceOf($em, EntityManagerInterface::class);
        $this->entityManager = $em;

        /** @var EntityRepository $repo */
        $repo = $this->entityManager->getRepository(static::$entityClass);
        Assert::isInstanceOf($repo, EntityRepository::class);

        $this->repository = $repo;
        parent::__construct($eventRecorder);
    }

    protected function entityManager(): EntityManagerInterface
    {
        return $this->entityManager;
    }

    protected function persist(AggregateRoot $aggregateRoot, $entity): void
    {
        $this->entityManager->persist($entity);
        $this->saveAggregateRoot($aggregateRoot);
    }

    protected function repository(): EntityRepository
    {
        return $this->repository;
    }
}
