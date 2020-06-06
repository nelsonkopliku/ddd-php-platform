<?php

declare(strict_types=1);

namespace Acme\Marketplace\Checkout\Tests\Unit\Application\Create;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Acme\Common\Application\Bus\Command\CommandBus;
use Acme\Marketplace\Checkout\Application\Create\OpenCheckoutWhenShiftWasMarkedAsReadyForCheckout;
use Acme\Marketplace\Checkout\Domain\Command\OpenCheckout;
use Acme\Marketplace\Checkout\Domain\Event\ShiftWasMarkedAsReadyForCheckout;
use Prophecy\PhpUnit\ProphecyTrait;

final class OpenCheckoutWhenShiftWasMarkedAsReadyForCheckoutTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @test
     */
    public function it_should_dispatch_open_checkout_command(): void
    {
        $commandBus = $this->prophesize(CommandBus::class);

        $event = new ShiftWasMarkedAsReadyForCheckout(
            $shiftId = 'some-shift-id',
            $matchId = 'some-match-id',
            $startDate = '2020-01-01 09:30:00',
            $endDate = '2020-01-01 18:30:00',
            $jobSeeker = 'jobSeeker',
            $client = 'jobSeeker',
            $hourlyRate = 18.5,
        );

        $expectedCommand = new OpenCheckout(
            $matchId,
            $shiftId,
            $startDate,
            $endDate,
            $jobSeeker,
            $client,
            $hourlyRate
        );

        $commandBus
            ->dispatch(Argument::exact($expectedCommand))
            ->shouldBeCalled()
        ;

        $subscriber = new OpenCheckoutWhenShiftWasMarkedAsReadyForCheckout($commandBus->reveal());

        $subscriber($event);
    }
}
