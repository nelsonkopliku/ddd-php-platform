<?php

declare(strict_types=1);

namespace Acme\Marketplace\Checkout\Tests\Unit\Application\NoShow;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Acme\Common\Application\Bus\Command\CommandBus;
use Acme\Marketplace\Checkout\Application\NoShow\MarkInactiveCheckoutsAsNoShow;
use Acme\Marketplace\Checkout\Domain\Checkout;
use Acme\Marketplace\Checkout\Domain\Command\MarkJobSeekerAsNoShow;
use Acme\Marketplace\Checkout\Domain\Repository\InactiveCheckoutRepository;
use Acme\Marketplace\Checkout\Domain\ValueObject\CheckoutId;
use Acme\Marketplace\Checkout\Tests\Unit\Domain\Dummy\CheckoutDummy;
use Prophecy\PhpUnit\ProphecyTrait;

final class MarkInactiveCheckoutsAsNoShowTest extends TestCase
{
    use ProphecyTrait;
    /**
     * @test
     * @dataProvider provideInactiveCheckouts
     */
    public function it_should_dispatch_MarkJobSeekerAsNoShow_command(array $inactiveCheckouts): void
    {
        $commandBus = $this->prophesize(CommandBus::class);
        $repository = $this->prophesize(InactiveCheckoutRepository::class);

        $isInactive = static function (CheckoutId $checkoutId) use ($inactiveCheckouts): bool {
            foreach ($inactiveCheckouts as $inactiveCheckout) {
                /** @var Checkout $inactiveCheckout */
                if ($inactiveCheckout->id()->equalsTo($checkoutId)) {
                    return true;
                }
            }
            return false;
        };

        $repository
            ->findInactiveCheckouts()
            ->willReturn($inactiveCheckouts)
            ->shouldBeCalled()
        ;

        $methodProphecy = $commandBus
            ->dispatch(Argument::that(
                fn ($arg) =>
                    $arg instanceof MarkJobSeekerAsNoShow &&
                    $isInactive(CheckoutId::fromString($arg->checkoutId()))
            ))
        ;

        count($inactiveCheckouts) > 0 ? $methodProphecy->shouldBeCalled() : $methodProphecy->shouldNotBeCalled();

        $underTest = new MarkInactiveCheckoutsAsNoShow($commandBus->reveal(), $repository->reveal());

        $underTest->allInactiveCheckouts();
    }

    public function provideInactiveCheckouts(): array
    {
        return [
            [
                []
            ],
            [
                CheckoutDummy::inactiveCheckoutList()
            ]
        ];
    }
}
