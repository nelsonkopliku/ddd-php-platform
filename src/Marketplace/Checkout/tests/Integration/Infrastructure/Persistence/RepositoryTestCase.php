<?php

declare(strict_types=1);

namespace Acme\Marketplace\Checkout\Tests\Integration\Infrastructure\Persistence;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

abstract class RepositoryTestCase extends KernelTestCase
{
    /**
     * @var ManagerRegistry
     */
    protected ManagerRegistry $managerRegistry;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        /** @var ManagerRegistry $managerRegistry */
        $managerRegistry = $kernel->getContainer()->get('doctrine');

        $this->managerRegistry = $managerRegistry;
    }
}
