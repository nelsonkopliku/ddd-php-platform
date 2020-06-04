<?php

declare(strict_types=1);

namespace Acme\Marketplace\Checkout\Tests\Integration\Infrastructure\Persistence;

use Acme\Common\Domain\Event\EventRecorder;
use Acme\Marketplace\Checkout\Domain\Repository\CheckoutReadRepository;
use Acme\Marketplace\Checkout\Infrastructure\Persistence\DoctrineCheckoutReadRepository;

final class DoctrineCheckoutReadRepositoryTest extends RepositoryTestCase
{
    private CheckoutReadRepository $repo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repo = new DoctrineCheckoutReadRepository($this->managerRegistry, new EventRecorder());
    }

    /**
     * @test
     * @dataProvider provideFindAgreedCheckoutByClientCases
     */
    public function it_finds_agreed_checkout_by_client(string $client, \DateTimeImmutable $since = null, int $expectedItems = 0): void
    {
        $agreedCheckouts = $this->repo->findAgreedByClient($client, $since);

        $this->assertCount($expectedItems, $agreedCheckouts);
    }

    public function provideFindAgreedCheckoutByClientCases(): array
    {
        return [
            [
                'agreed-checkout-client-id-1',
                null,
                1,
            ],
            [
                'agreed-checkout-client-id-2',
                new \DateTimeImmutable('2020-02-02 09:45:00'),
                0,
            ],
            [
                'agreed-checkout-client-id-3',
                new \DateTimeImmutable('2020-02-02 09:00:00'),
                1,
            ],
        ];
    }

    /**
     * @test
     * @dataProvider provideFindAgreedCheckoutByJobSeekerCases
     */
    public function it_finds_agreed_checkout_by_job_seeker(string $jobSeeker, \DateTimeImmutable $since = null, int $expectedItems = 0): void
    {
        $agreedCheckouts = $this->repo->findAgreedByJobSeeker($jobSeeker, $since);

        $this->assertCount($expectedItems, $agreedCheckouts);
    }

    public function provideFindAgreedCheckoutByJobSeekerCases(): array
    {
        return [
            [
                'agreed-checkout-jobseeker-id-1',
                null,
                1,
            ],
            [
                'agreed-checkout-jobseeker-id-2',
                new \DateTimeImmutable('2020-02-02 09:45:00'),
                0,
            ],
            [
                'agreed-checkout-jobseeker-id-3',
                new \DateTimeImmutable('2020-02-02 09:00:00'),
                1,
            ],
        ];
    }

    /**
     * @test
     * @dataProvider provideFindPendingCheckoutsByClientCases
     */
    public function it_finds_pending_checkouts_by_client(string $client, int $expectedItems = 0): void
    {
        $pendingCheckouts = $this->repo->findPendingByClient($client);

        $this->assertCount($expectedItems, $pendingCheckouts);
    }

    public function provideFindPendingCheckoutsByClientCases(): array
    {
        return [
            [
                'pending-checkout-client-id-0',
                0,
            ],
            [
                'pending-checkout-client-id-1',
                1,
            ],
            [
                'pending-checkout-client-id-2',
                0,
            ],
            [
                'pending-checkout-client-id-4',
                0,
            ],
        ];
    }

    /**
     * @test
     * @dataProvider provideFindPendingCheckoutsByJobSeekerCases
     */
    public function it_finds_pending_checkouts_by_job_seeker(string $jobSeeker, int $expectedItems = 0): void
    {
        $pendingCheckouts = $this->repo->findPendingByJobSeeker($jobSeeker);

        $this->assertCount($expectedItems, $pendingCheckouts);
    }

    public function provideFindPendingCheckoutsByJobSeekerCases(): array
    {
        return [
            [
                'pending-checkout-jobseeker-id-0',
                1,
            ],
            [
                'pending-checkout-jobseeker-id-1',
                0,
            ],
            [
                'pending-checkout-jobseeker-id-2',
                1,
            ],
        ];
    }
}
