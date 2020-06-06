<?php

declare(strict_types=1);

namespace Acme\Marketplace\Checkout\Tests\Unit\Application\Create;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Acme\Marketplace\Checkout\Application\Create\OpenCheckoutHandler;
use Acme\Marketplace\Checkout\Domain\Checkout;
use Acme\Marketplace\Checkout\Domain\Repository\CheckoutRepository;
use Acme\Marketplace\Checkout\Domain\Command\OpenCheckout;
use Acme\Marketplace\Checkout\Domain\ValueObject\CheckoutId;
use Acme\Marketplace\Checkout\Domain\ValueObject\Parties;
use Acme\Marketplace\Checkout\Domain\ValueObject\StartedShift;
use Prophecy\PhpUnit\ProphecyTrait;

final class OpenCheckoutHandlerTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @test
     */
    public function it_should_handle_checkout_opening(): void
    {
        $repository = $this->prophesize(CheckoutRepository::class);

        $checkoutIdAsString = 'some-checkout-id';

        $id = CheckoutId::fromString($checkoutIdAsString);

        $startedShift = StartedShift::fromValues(
            $shiftId = 'some-shift-id',
            new DateTimeImmutable($startDate = '2020-01-01 09:30:00'),
            new DateTimeImmutable($endDate = '2020-01-01 18:30:00')
        );

        $parties = Parties::fromClientAndJobSeeker($client = 'client', $jobSeeker = 'jobSeeker');

        $repository
            ->create(Argument::exact(Checkout::open($id, $startedShift, $parties)))
            ->shouldBeCalled()
        ;

        $handler = new OpenCheckoutHandler($repository->reveal());

        $command = new OpenCheckout(
            $checkoutIdAsString,
            $shiftId,
            $startDate,
            $endDate,
            $jobSeeker,
            $client,
            $hourlyRate = 17.5
        );

        $handler($command);
    }
}
