<?php

declare(strict_types=1);

namespace Acme\Marketplace\Checkout\Domain\Command;

use Acme\Common\Domain\Command\Command;

abstract class SubmitProposal implements Command
{
    private string $checkoutId;

    private string $workedFrom;

    private string $workedUntil;

    private int $minutesBreak;

    /**
     * @TODO: fix type
     */
    private string $compensation;

    public function __construct(
        string $checkoutId,
        string $workedFrom,
        string $workedUntil,
        int $minutesBreak,
        string $compensation
    ) {
        $this->checkoutId = $checkoutId;
        $this->workedFrom = $workedFrom;
        $this->workedUntil = $workedUntil;
        $this->minutesBreak = $minutesBreak;
        $this->compensation = $compensation;
    }

    public function checkoutId(): string
    {
        return $this->checkoutId;
    }

    public function workedFrom(): string
    {
        return $this->workedFrom;
    }

    public function workedUntil(): string
    {
        return $this->workedUntil;
    }

    public function minutesBreak(): int
    {
        return $this->minutesBreak;
    }

    public function compensation(): string
    {
        return $this->compensation;
    }
}
