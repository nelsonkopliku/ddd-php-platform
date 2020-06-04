<?php

declare(strict_types=1);

namespace Acme\Marketplace\Checkout\Tests\Unit\Domain\Dummy;

use DateTimeImmutable;
use Acme\Marketplace\Checkout\Domain\Checkout;
use Acme\Marketplace\Checkout\Domain\Proposal\Proposal;
use Acme\Marketplace\Checkout\Domain\ValueObject\CheckoutId;
use Acme\Marketplace\Checkout\Domain\ValueObject\Parties;
use Acme\Marketplace\Checkout\Domain\ValueObject\StartedShift;

final class CheckoutDummy
{
    public const CLIENT_ID = 'clientId';

    public const JOBSEEKER_ID = 'jobSeekerId';

    public static function aValidAgreedCheckout(string $id = null): Checkout
    {
        return self::aCheckout(true,  ProposalDummy::jobSeekerProposal(), $id);
    }

    public static function aValidNotAgreedCheckout(string $id = null): Checkout
    {
        return self::aCheckout(false,  ProposalDummy::jobSeekerProposal(), $id);
    }

    public static function aCheckoutReadyForClientProposal(): Checkout
    {
        return self::aCheckout(false, ProposalDummy::jobSeekerProposal());
    }

    public static function aCheckoutReadyForClientAgreement(): Checkout
    {
        return self::aCheckoutReadyForClientProposal();
    }

    public static function aCheckoutReadyForJobSeekerAgreement(): Checkout
    {
        return self::aCheckoutReadyForJobSeekerProposal();
    }

    public static function aCheckoutReadyForJobSeekerProposal(): Checkout
    {
        return self::aCheckout(false, ProposalDummy::clientProposal());
    }

    public static function anInactiveCheckout(string $id = null): Checkout
    {
        return self::aCheckout(false, null, $id, true);
    }

    public static function inactiveCheckoutList(int $items = 5): array
    {
        $list = [];
        for ($i = 0; $i < $items; $i++) {
            $list[] = self::aCheckout(false, null);
            $list[] = self::aCheckout(false, null);
        }

        return $list;
    }

    private static function aCheckout(bool $agreed, Proposal $proposal = null, string $checkoutId = null, bool $eligibleForNoShow = false): Checkout
    {
        $from = $eligibleForNoShow ? new DateTimeImmutable('-8 days') : new DateTimeImmutable('-2 days');
        $to = $from->modify('+8 hours');

        return Checkout::fromValues(
            CheckoutId::fromString($checkoutId ?? 'someCheckoutId'),
            StartedShift::fromValues(
                'shiftId',
                $from,
                $to
            ),
            Parties::fromClientAndJobSeeker(
                self::CLIENT_ID,
                self::JOBSEEKER_ID
            ),
            $proposal,
            $agreed
        );
    }
}
