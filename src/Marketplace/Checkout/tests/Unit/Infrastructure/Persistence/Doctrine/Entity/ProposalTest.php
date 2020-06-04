<?php

declare(strict_types=1);

namespace Acme\Marketplace\Checkout\Tests\Unit\Infrastructure\Persistence\Doctrine\Entity;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Acme\Marketplace\Checkout\Infrastructure\Persistence\Doctrine\Entity\Checkout;
use Acme\Marketplace\Checkout\Infrastructure\Persistence\Doctrine\Entity\Proposal;
use Acme\Marketplace\Checkout\Tests\Unit\Domain\Dummy\ProposalDummy;

final class ProposalTest extends TestCase
{
    /**
     * @test
     */
    public function should_not_be_able_to_be_created_from_a_proposal_of_unsupported_type(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Proposal::fromDomainProposal(ProposalDummy::anInvalidProposalType());
    }

    /**
     * @test
     */
    public function it_should_be_possible_to_create_from_a_domain_proposal_and_a_checkout_entity(): void
    {
        $domainProposal = ProposalDummy::clientProposal();

        $proposalEntity = Proposal::fromDomainProposal($domainProposal);

        $this->assertEquals($domainProposal->content()->hoursWorked()->from(), $proposalEntity->workedFrom);
        $this->assertEquals($domainProposal->content()->hoursWorked()->until(), $proposalEntity->workedUntil);
        $this->assertEquals($domainProposal->content()->hoursWorked()->minutesBreak(), $proposalEntity->minutesBreak);
        $this->assertEquals(5.5, $domainProposal->content()->hoursWorked()->total());
        $this->assertEquals('client', $proposalEntity->proposedBy);
    }

    /**
     * @test
     */
    public function it_should_be_possible_to_be_converted_to_a_domain_proposal(): void
    {
        foreach (['jobseeker', 'client'] as $proposedBy) {
            $checkoutEntity = new Checkout();
            $checkoutEntity->id = 'some';
            $checkoutEntity->shiftId = 'someShiftd';
            $checkoutEntity->startedAt = new DateTimeImmutable('2020-01-01 09:30:00');
            $checkoutEntity->end = $checkoutEntity->startedAt->modify('+7 hours');
            $checkoutEntity->jobSeekerId = 'someJobSeekerId';
            $checkoutEntity->clientId = 'someClientId';
            $checkoutEntity->agreed = false;

            $proposalEntity = new Proposal();
            $proposalEntity->id = Uuid::fromString('0d2ce07b-6592-4c85-be08-9b69d9e711f4');
            $proposalEntity->proposedBy = $proposedBy;
            $proposalEntity->minutesBreak = 30;
            $proposalEntity->workedFrom = new DateTimeImmutable('2020-01-01 09:30:00');
            $proposalEntity->workedUntil = $proposalEntity->workedFrom->modify('+6 hours');
            $proposalEntity->compensation = 'some';
            $proposalEntity->checkout = $checkoutEntity;
            $proposalEntity->proposedAt = new DateTimeImmutable('2020-01-02 09:30:00');

            $targetDomainProposal = $proposalEntity->toDomainProposal();

            $this->assertEquals($proposalEntity->proposedAt, $targetDomainProposal->proposedAt());
            $this->assertEquals($proposalEntity->minutesBreak, $targetDomainProposal->content()->hoursWorked()->minutesBreak());
            $this->assertEquals($proposalEntity->workedFrom, $targetDomainProposal->content()->hoursWorked()->from());
            $this->assertEquals($proposalEntity->workedUntil, $targetDomainProposal->content()->hoursWorked()->until());
            $this->assertEquals(5.5, $targetDomainProposal->content()->hoursWorked()->total());
        }
    }
}
