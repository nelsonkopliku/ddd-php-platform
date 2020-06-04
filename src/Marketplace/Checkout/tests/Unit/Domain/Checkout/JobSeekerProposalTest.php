<?php

declare(strict_types=1);

namespace Acme\Marketplace\Checkout\Tests\Unit\Domain\Checkout;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Acme\Marketplace\Checkout\Domain\Proposal\JobSeekerProposal;
use Acme\Marketplace\Checkout\Domain\Proposal\ProposalContent;

final class JobSeekerProposalTest extends TestCase
{
    private DateTimeImmutable $from;

    private DateTimeImmutable $to;

    private int $minutesBreak;

    private string $compensation;

    public function setUp(): void
    {
        $this->from = new DateTimeImmutable('2020-01-01 12:00:00');
        $this->to = $this->from->modify('+8 hours');
        $this->minutesBreak = 30;
        $this->compensation = 'wat?';
    }

    /**
     * @test
     */
    public function it_can_be_constructed_by_job_seeker_and_content(): void
    {
        $jobSeekerId = 'jobSeekerId';

        $proposal = JobSeekerProposal::fromJobSeekerAndContent(
            $jobSeekerId,
            $content = ProposalContent::fromValues($this->from, $this->to, $this->minutesBreak, $this->compensation)
        );

        $this->assertSame($jobSeekerId, $proposal->proposedBy());
        $this->assertSame($content, $proposal->content());
    }

    /**
     * @test
     */
    public function it_can_be_constructed_by_values(): void
    {
        $jobSeekerId = 'jobSeekerId';

        $proposesdAt = new DateTimeImmutable('2020-02-02');

        $proposal = JobSeekerProposal::fromValues(
            $content = ProposalContent::fromValues($this->from, $this->to, $this->minutesBreak, $this->compensation),
            $jobSeekerId,
            $proposesdAt
        );

        $this->assertSame($jobSeekerId, $proposal->proposedBy());
        $this->assertSame($content, $proposal->content());
        $this->assertSame($proposal->proposedAt(), $proposesdAt);
    }
}
