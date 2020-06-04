<?php

declare(strict_types=1);

namespace Acme\Marketplace\Checkout\Tests\Unit\Domain\Dummy;

use DateTimeImmutable;
use Acme\Marketplace\Checkout\Domain\Proposal\ProposalContent;

final class ProposalContentDummy
{
    public static function aProposalContent(
        DateTimeImmutable $from = null,
        DateTimeImmutable $until = null,
        int $minutesBreak = 30,
        string $compensation = 'some'
    ): ProposalContent {
        return ProposalContent::fromValues(
            $from ??= new DateTimeImmutable('2020-01-01 09:30:00'),
            $until ?? $from->modify('+6 hours'),
            30,
            'some'
        );
    }
}
