<?php

declare(strict_types=1);

namespace Acme\Marketplace\Checkout\Domain\ValueObject;

use DateTimeImmutable;
use Webmozart\Assert\Assert;

final class HoursWorked
{
    private const WORKED_HOURS_LIMIT = 18;

    private DateTimeImmutable $from;

    private DateTimeImmutable $until;

    private int $minutesBreak;

    private float $totalHours = 0.0;

    private function __construct(DateTimeImmutable $from, DateTimeImmutable $until, int $minutesBreak)
    {
        Assert::greaterThan($until, $from, 'Proposed Start date can not be greater than end date');

        $diff = $from->diff($until);
        $hours = round($diff->s / 3600 + $diff->i / 60 + $diff->h + $diff->days * 24, 2);

        Assert::lessThan($hours, self::WORKED_HOURS_LIMIT, 'Shift longer than 18 hours');

        $this->from = $from;
        $this->until = $until;
        $this->totalHours = $hours;
        $this->minutesBreak = $minutesBreak;
    }

    public static function fromEdgesAndMinutesBreak(
        DateTimeImmutable $from,
        DateTimeImmutable $until,
        int $minutesBreak = 0
    ): self {
        return new self($from, $until, $minutesBreak);
    }

    public function total(): float
    {
        return $this->totalHours - ($this->minutesBreak / 60);
    }

    public function minutesBreak(): int
    {
        return $this->minutesBreak;
    }

    public function from(): DateTimeImmutable
    {
        return $this->from;
    }

    public function until(): DateTimeImmutable
    {
        return $this->until;
    }

    public function equalsTo(HoursWorked $other): bool
    {
        return
            $this->from === $other->from &&
            $this->until === $other->until &&
            $this->totalHours === $other->totalHours &&
            $this->minutesBreak === $other->minutesBreak;
    }
}
