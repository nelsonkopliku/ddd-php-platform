<?php

declare(strict_types=1);

namespace Acme\Marketplace\Checkout\Domain\Proposal;

use DateTimeImmutable;
use Acme\Marketplace\Checkout\Domain\ValueObject\HoursWorked;

final class ProposalContent
{
    protected HoursWorked $hoursWorked;

    /**
     * @TODO: fix types
     * what type should a compensation be?
     */
    protected string $compensation;

    private function __construct(
        DateTimeImmutable $from,
        DateTimeImmutable $until,
        int $minutesBreak,
        string $compensation
    ) {
        $this->hoursWorked = HoursWorked::fromEdgesAndMinutesBreak($from, $until, $minutesBreak);
        $this->compensation = $compensation;
    }

    public static function fromValues(
        DateTimeImmutable $from,
        DateTimeImmutable $until,
        int $minutesBreak,
        string $compensation
    ): self {
        return new self($from, $until, $minutesBreak, $compensation);
    }

    public function hoursWorked(): HoursWorked
    {
        return $this->hoursWorked;
    }

    public function compensation(): string
    {
        return $this->compensation;
    }

    public function equalsTo(ProposalContent $other): bool
    {
        return
            $this->hoursWorked->equalsTo($other->hoursWorked) &&
            $this->compensation === $other->compensation;
    }
}
