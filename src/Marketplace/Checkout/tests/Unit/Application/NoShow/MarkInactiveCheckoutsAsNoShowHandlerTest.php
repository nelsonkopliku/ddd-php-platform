<?php

declare(strict_types=1);

namespace Acme\Marketplace\Checkout\Tests\Unit\Application\NoShow;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Acme\Marketplace\Checkout\Application\NoShow\MarkInactiveCheckoutsAsNoShowHandler;
use Acme\Marketplace\Checkout\Domain\Checkout;
use Acme\Marketplace\Checkout\Domain\Repository\CheckoutRepository;
use Acme\Marketplace\Checkout\Domain\Command\MarkJobSeekerAsNoShow;
use Acme\Marketplace\Checkout\Tests\Unit\Domain\Dummy\CheckoutDummy;
use Prophecy\PhpUnit\ProphecyTrait;

final class MarkInactiveCheckoutsAsNoShowHandlerTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @test
     */
    public function it_should_mark_as_no_show(): void
    {
        $repo = $this->prophesize(CheckoutRepository::class);

        $checkoutId = 'someCheckout';
        $checkout = CheckoutDummy::anInactiveCheckout($checkoutId);

        $repo
            ->search(Argument::exact($checkout->id()))
            ->willReturn($checkout)
            ->shouldBeCalled()
        ;

        $repo
            ->save(Argument::that(
                fn (Checkout $modifiedCheckout) => !$modifiedCheckout->hasProposal() && $modifiedCheckout->isNoShow()
            ))
            ->shouldBeCalled()
        ;

        $handler = new MarkInactiveCheckoutsAsNoShowHandler($repo->reveal());

        $handler(new MarkJobSeekerAsNoShow($checkoutId));
    }
}
