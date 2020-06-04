<?php

declare(strict_types=1);

namespace Acme\Marketplace\Checkout\Domain\ValueObject;

use DateTimeImmutable;
use Webmozart\Assert\Assert;

final class StartedShift
{
    private string $shiftId;

    private DateTimeImmutable $startedAt;

    private DateTimeImmutable $end;

    private function __construct(string $shiftId, DateTimeImmutable $startedAt, DateTimeImmutable $end)
    {
        Assert::greaterThan($end, $startedAt, 'End of a shift must be after start of a shift');
        Assert::greaterThanEq(new DateTimeImmutable(), $startedAt, 'A shift must be started before now');

        $this->shiftId = $shiftId;
        $this->startedAt = $startedAt;
        $this->end = $end;
    }

    public static function fromValues(string $shiftId, DateTimeImmutable $startedAt, DateTimeImmutable $end): self
    {
        return new self($shiftId, $startedAt, $end);
    }

    public function canBeCheckedOut(): bool
    {
        return $this->startedAt < new DateTimeImmutable('-1 hour');
    }

    public function id(): string
    {
        return $this->shiftId;
    }

    public function start(): DateTimeImmutable
    {
        return $this->startedAt;
    }

    public function end(): DateTimeImmutable
    {
        return $this->end;
    }
}
