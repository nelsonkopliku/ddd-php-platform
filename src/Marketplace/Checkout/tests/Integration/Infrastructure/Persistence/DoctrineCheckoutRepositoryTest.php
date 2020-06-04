<?php

declare(strict_types=1);

namespace Acme\Marketplace\Checkout\Tests\Integration\Infrastructure\Persistence;

use Doctrine\ORM\EntityManagerInterface;
use Acme\Common\Domain\Event\EventRecorder;
use Acme\Marketplace\Checkout\Domain\Repository\CheckoutRepository;
use Acme\Marketplace\Checkout\Domain\Exception\CannotCreateCheckout;
use Acme\Marketplace\Checkout\Domain\Exception\CannotSaveCheckout;
use Acme\Marketplace\Checkout\Domain\Exception\CheckoutNotFound;
use Acme\Marketplace\Checkout\Domain\ValueObject\CheckoutId;
use Acme\Marketplace\Checkout\Infrastructure\Persistence\Doctrine\Entity\Checkout;
use Acme\Marketplace\Checkout\Infrastructure\Persistence\DoctrineCheckoutRepository;
use Acme\Marketplace\Checkout\Tests\Unit\Domain\Dummy\CheckoutDummy;

final class DoctrineCheckoutRepositoryTest extends RepositoryTestCase
{
    private CheckoutRepository $repo;

    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repo = new DoctrineCheckoutRepository($this->managerRegistry, new EventRecorder());

        /** @var EntityManagerInterface $em */
        $em = $this->managerRegistry->getManager('checkout');

        $this->entityManager = $em;
    }

    /**
     * @test
     */
    public function it_cannot_create_checkout_because_already_existent(): void
    {
        $aCheckout = CheckoutDummy::aValidNotAgreedCheckout('checkout-id-1');

        $this->expectException(CannotCreateCheckout::class);

        $this->repo->create($aCheckout);
    }

    /**
     * @test
     */
    public function it_can_create_checkout(): void
    {
        $aCheckout = CheckoutDummy::aCheckoutReadyForClientProposal();

        $this->repo->create($aCheckout);

        $this->entityManager->flush();

        $checkoutRepo = $this->managerRegistry->getRepository(Checkout::class);

        /** @var Checkout $entity */
        $entity = $checkoutRepo->find($aCheckout->id()->value());

        self::assertEquals($aCheckout, $entity->toDomainCheckout());
    }

    /**
     * @test
     */
    public function it_cannot_save_a_checkout_because_it_does_not_exist_yet(): void
    {
        $aCheckout = CheckoutDummy::aValidNotAgreedCheckout('this-is-not-in-the-db');

        $this->expectException(CannotSaveCheckout::class);

        $this->repo->save($aCheckout);
    }

    /**
     * @test
     */
    public function it_can_save_a_checkout(): void
    {
        $checkoutRepo = $this->managerRegistry->getRepository(Checkout::class);

        /** @var Checkout $entity */
        $entity = $checkoutRepo->find('checkout-id-0');

        $domainCheckout = $entity->toDomainCheckout();

        $domainCheckout->submitJobSeekerAgreement('jobseeker-id-0');

        $this->repo->save($domainCheckout);

        $this->entityManager->flush();

        /** @var Checkout $entity */
        $entity = $checkoutRepo->find($domainCheckout->id()->value());

        self::assertEquals($domainCheckout, $entity->toDomainCheckout());
    }

    /**
     * @test
     */
    public function it_cannot_search_for_a_non_existent_checkout(): void
    {
        $this->expectException(CheckoutNotFound::class);

        $this->repo->search(CheckoutId::fromString('this-is-not-in-the-db'));
    }

    /**
     * @test
     */
    public function it_can_search_for_an_existent_checkout(): void
    {
        $checkoutId = CheckoutId::fromString('checkout-id-0');

        $checkout = $this->repo->search($checkoutId);

        $this->assertTrue($checkout->id()->equalsTo($checkoutId));
    }
}
