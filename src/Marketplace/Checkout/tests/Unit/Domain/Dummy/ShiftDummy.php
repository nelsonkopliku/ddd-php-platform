<?php

declare(strict_types=1);

namespace Acme\Marketplace\Checkout\Tests\Unit\Domain\Dummy;

use DateTimeImmutable;
use Acme\Marketplace\Checkout\Domain\ValueObject\StartedShift;

final class ShiftDummy
{
    public static function aShiftStartedLessThanOneHourAgo(): StartedShift
    {
        return StartedShift::fromValues(
            'shiftId',
            $from = (new DateTimeImmutable())->modify('-50 minutes'),
            $from->modify('+8 hours')
        );
    }

    public static function aShiftStartedLessThanOneWeekAgo(): StartedShift
    {
        return StartedShift::fromValues(
            'shiftId',
            $from = (new DateTimeImmutable())->modify('-2 days'),
            $from->modify('+8 hours')
        );
    }

    public static function aShiftStartedMoreThanOneWeekAgo(): StartedShift
    {
        return StartedShift::fromValues(
            'shiftId',
            $from = (new DateTimeImmutable())->modify('-8 days'),
            $from->modify('+8 hours')
        );
    }
}
