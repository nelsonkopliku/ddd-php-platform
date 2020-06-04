<?php

declare(strict_types=1);

namespace Acme\Marketplace\Checkout\Domain\Proposal;

final class ClientProposal extends Proposal
{
    public static function fromClientAndContent(string $clientId, ProposalContent $content): self
    {
        return new self($content, $clientId);
    }
}
