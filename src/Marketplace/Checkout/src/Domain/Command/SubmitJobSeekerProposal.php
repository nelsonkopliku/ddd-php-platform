<?php

declare(strict_types=1);

namespace Acme\Marketplace\Checkout\Domain\Command;

final class SubmitJobSeekerProposal extends SubmitProposal
{
    private string $jobSeekerId;

    public function __construct(
        string $jobSeekerId,
        string $checkoutId,
        string $workedFrom,
        string $workedUntil,
        int $minutesBreak,
        string $compensation
    ) {
        parent::__construct($checkoutId, $workedFrom, $workedUntil, $minutesBreak, $compensation);

        $this->jobSeekerId = $jobSeekerId;
    }

    public function jobSeekerId(): string
    {
        return $this->jobSeekerId;
    }
}
