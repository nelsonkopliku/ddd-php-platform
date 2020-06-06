<?php

declare(strict_types=1);

namespace Acme\Marketplace\Checkout\Tests\Unit\Application\Agreement;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Acme\Marketplace\Checkout\Application\Agreement\SubmitJobSeekerAgreementHandler;
use Acme\Marketplace\Checkout\Domain\Checkout;
use Acme\Marketplace\Checkout\Domain\Repository\CheckoutRepository;
use Acme\Marketplace\Checkout\Domain\Command\SubmitJobSeekerAgreement;
use Acme\Marketplace\Checkout\Tests\Unit\Domain\Dummy\CheckoutDummy;
use Prophecy\PhpUnit\ProphecyTrait;

final class SubmitJobSeekerAgreementHandlerTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @test
     */
    public function it_should_handle_client_agreement_submission(): void
    {
        $repository = $this->prophesize(CheckoutRepository::class);

        $aCheckout = CheckoutDummy::aCheckoutReadyForJobSeekerAgreement();

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

        $handler = new SubmitJobSeekerAgreementHandler($repository->reveal());

        $handler(new SubmitJobSeekerAgreement(
            $aCheckout->id()->value(),
            CheckoutDummy::JOBSEEKER_ID
        ));
    }
}
