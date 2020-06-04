<?php

declare(strict_types=1);

namespace Acme\Marketplace\Checkout\Domain\Proposal;

final class JobSeekerProposal extends Proposal
{
    public static function fromJobSeekerAndContent(string $jobSeekerId, ProposalContent $content): self
    {
        return new self($content, $jobSeekerId);
    }
}
