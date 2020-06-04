<?php

declare(strict_types=1);

namespace Acme\Marketplace\Checkout\Domain\Command;

final class SubmitJobSeekerAgreement extends SubmitAgreement
{
    private string $jobSeekerId;

    public function __construct(string $checkoutId, string $jobSeekerId)
    {
        parent::__construct($checkoutId);

        $this->jobSeekerId = $jobSeekerId;
    }

    public function jobSeeker(): string
    {
        return $this->jobSeekerId;
    }
}
