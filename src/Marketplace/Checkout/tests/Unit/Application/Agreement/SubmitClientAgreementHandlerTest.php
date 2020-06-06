<?php

declare(strict_types=1);

namespace Acme\Marketplace\Checkout\Tests\Unit\Application\Agreement;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Acme\Marketplace\Checkout\Application\Agreement\SubmitClientAgreementHandler;
use Acme\Marketplace\Checkout\Domain\Checkout;
use Acme\Marketplace\Checkout\Domain\Repository\CheckoutRepository;
use Acme\Marketplace\Checkout\Domain\Command\SubmitClientAgreement;
use Acme\Marketplace\Checkout\Tests\Unit\Domain\Dummy\CheckoutDummy;
use Prophecy\PhpUnit\ProphecyTrait;

final class SubmitClientAgreementHandlerTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @test
     */
    public function it_should_handle_client_agreement_submission(): void
    {
        $repository = $this->prophesize(CheckoutRepository::class);

        $aCheckout = CheckoutDummy::aCheckoutReadyForClientAgreement();

        $repository
            ->search(Argument::exact($aCheckout->id()))
            ->willReturn($aCheckout)
            ->shouldBeCalled()
        ;

        $repository
            ->save(Argument::that(
                fn (Checkout $checkoutBeingSaved) => $checkoutBeingSaved->isAgreed()
            ))
            ->shouldBeCalled()
        ;

        $handler = new SubmitClientAgreementHandler($repository->reveal());

        $handler(new SubmitClientAgreement(
            $aCheckout->id()->value(),
            CheckoutDummy::CLIENT_ID
        ));
    }
}
