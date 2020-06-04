<?php

declare(strict_types=1);

namespace Acme\Marketplace\Checkout\Tests\Unit\Infrastructure\Persistence\Doctrine\Entity;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Acme\Marketplace\Checkout\Infrastructure\Persistence\Doctrine\Entity\Checkout;
use Acme\Marketplace\Checkout\Infrastructure\Persistence\Doctrine\Entity\Proposal;
use Acme\Marketplace\Checkout\Tests\Unit\Domain\Dummy\CheckoutDummy;
use Acme\Marketplace\Checkout\Tests\Unit\Domain\Dummy\ProposalDummy;

final class CheckoutTest extends TestCase
{
    /**
     * @test
     */
    public function it_should_be_possible_to_create_from_domain_checkout(): void
    {
        $domainCheckout = CheckoutDummy::aValidAgreedCheckout();

        $checkoutEntity = Checkout::fromDomainCheckout($domainCheckout);

        $this->assertEquals($domainCheckout->id()->value(), $checkoutEntity->id);
        $this->assertEquals($domainCheckout->isAgreed(), $checkoutEntity->agreed);
        $this->assertEquals($domainCheckout->shift()->id(), $checkoutEntity->shiftId);
        $this->assertEquals($domainCheckout->shift()->start(), $checkoutEntity->startedAt);
        $this->assertEquals($domainCheckout->shift()->end(), $checkoutEntity->end);
        $this->assertEquals($domainCheckout->parties()->client(), $checkoutEntity->clientId);
        $this->assertEquals($domainCheckout->parties()->jobSeeker(), $checkoutEntity->jobSeekerId);
        $this->assertEquals(1, $checkoutEntity->proposals->count());
    }

    /**
     * @test
     */
    public function it_should_be_possible_to_refresh_from_domain_object(): void
    {
        $domainCheckout = CheckoutDummy::aValidNotAgreedCheckout();

        $checkoutEntity = Checkout::fromDomainCheckout($domainCheckout);

        $domainCheckout->submitClientProposal($lastProposal = ProposalDummy::clientProposal());

        $checkoutEntity->refresh($domainCheckout);

        /** @var Proposal $lastEntityProposal */
        $lastEntityProposal = $checkoutEntity->proposals->last();

        $this->assertSame($lastProposal->content()->hoursWorked()->from(), $lastEntityProposal->workedFrom);
    }

    /**
     * @test
     */
    public function it_should_be_possible_to_refresh_from_domain_object_that_does_not_have_proposal(): void
    {
        $domainCheckout = CheckoutDummy::anInactiveCheckout();

        $checkoutEntity = Checkout::fromDomainCheckout($domainCheckout);

        $domainCheckout->markAsNoShow();

        $checkoutEntity->refresh($domainCheckout);

        $this->assertTrue($checkoutEntity->noShow);
    }

    /**
     * @test
     */
    public function it_should_be_possible_to_convert_to_domain_checkout(): void
    {
        $checkoutEntity = new Checkout();
        $checkoutEntity->id = 'some';
        $checkoutEntity->shiftId = 'someShiftd';
        $checkoutEntity->startedAt = new DateTimeImmutable('2020-01-01 09:30:00');
        $checkoutEntity->end = $checkoutEntity->startedAt->modify('+7 hours');
        $checkoutEntity->jobSeekerId = 'someJobSeekerId';
        $checkoutEntity->clientId = 'someClientId';
        $checkoutEntity->agreed = false;

        $domainCheckout = $checkoutEntity->toDomainCheckout();

        $this->assertEquals($checkoutEntity->id, $domainCheckout->id()->value());
        $this->assertEquals($checkoutEntity->agreed, $domainCheckout->isAgreed());
        $this->assertEquals($checkoutEntity->shiftId, $domainCheckout->shift()->id());
        $this->assertEquals($checkoutEntity->startedAt, $domainCheckout->shift()->start());
        $this->assertEquals($checkoutEntity->end, $domainCheckout->shift()->end());
        $this->assertEquals($checkoutEntity->clientId, $domainCheckout->parties()->client());
        $this->assertEquals($checkoutEntity->jobSeekerId, $domainCheckout->parties()->jobSeeker());
        $this->assertEquals(0, $checkoutEntity->proposals->count());
    }
}
