<?php

declare(strict_types=1);

namespace Acme\Marketplace\Checkout\Tests\Unit\Domain\Dummy;

use DateTimeImmutable;
use Acme\Marketplace\Checkout\Domain\Proposal\ClientProposal;
use Acme\Marketplace\Checkout\Domain\Proposal\JobSeekerProposal;
use Acme\Marketplace\Checkout\Domain\Proposal\ProposalContent;

final class ProposalDummy
{
    private const CLIENT_ID = 'clientId';

    private const JOBSEEKER_ID = 'jobSeekerId';

    public static function anInvalidProposalType(): AnInvalidProposalType
    {
        return AnInvalidProposalType::create();
    }

    public static function clientProposal(string $clientId = self::CLIENT_ID): ClientProposal
    {
        return ClientProposal::fromClientAndContent(
            $clientId,
            ProposalContent::fromValues(
                $start = new DateTimeImmutable('2020-01-01 09:30:00'),
                $start->modify('+6 hours'),
                30,
                'some'
            )
        );
    }

    public static function jobSeekerProposal(string $jobSeekerId = self::JOBSEEKER_ID): JobSeekerProposal
    {
        return JobSeekerProposal::fromJobSeekerAndContent(
            $jobSeekerId,
            ProposalContent::fromValues(
                $start = new DateTimeImmutable('2020-01-01 09:30:00'),
                $start->modify('+6 hours'),
                30,
                'some'
            )
        );
    }
}
