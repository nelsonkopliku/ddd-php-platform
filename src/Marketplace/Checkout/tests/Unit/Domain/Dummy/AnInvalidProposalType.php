<?php

declare(strict_types=1);

namespace Acme\Marketplace\Checkout\Tests\Unit\Domain\Dummy;

use Acme\Marketplace\Checkout\Domain\Proposal\Proposal;

final class AnInvalidProposalType extends Proposal
{
    public static function create(): self
    {
        return new self(ProposalContentDummy::aProposalContent(), 'someone');
    }
}
