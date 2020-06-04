<?php

declare(strict_types=1);

namespace Acme\Marketplace\Checkout\Domain\Exception;

use DomainException;
use Acme\Marketplace\Checkout\Domain\ValueObject\CheckoutId;

final class CannotSubmitProposal extends DomainException
{
    public static function thereIsAlreadyAnAgreementOnCheckout(CheckoutId $checkout): self
    {
        return new self(sprintf(
            'Proposal cannot be submitted to Checkout "%s". Checkout has already been agreed',
            (string) $checkout
        ));
    }

    public static function jobSeekerIsNotAllowed(string $jobSeeker, CheckoutId $checkout): self
    {
        return new self(sprintf(
            'JobSeeker "%s" not allowed to submit proposal for Checkout "%s"',
            $jobSeeker,
            (string) $checkout
        ));
    }

    public static function clientIsNotAllowed(string $client, CheckoutId $checkout): self
    {
        return new self(sprintf(
            'Client "%s" not allowed to submit proposal for Checkout "%s"',
            $client,
            (string) $checkout
        ));
    }

    public static function clientAlreadySubmitted(string $client, CheckoutId $checkout): self
    {
        return new self(sprintf(
            'Client "%s" already submitted a proposal for Checkout "%s". '.
            'Waiting for job seeker agreement or for a counter proposal',
            $client,
            (string) $checkout
        ));
    }

    public static function jobSeekerAlreadySubmitted(string $jobSeeker, CheckoutId $checkout): self
    {
        return new self(sprintf(
            'JobSeeker "%s" already submitted a proposal for Checkout "%s". '.
            'Waiting for client agreement or for a counter proposal',
            $jobSeeker,
            (string) $checkout
        ));
    }

    public static function becauseShiftIsNotYetChecokutable(CheckoutId $checkout): self
    {
        return new self(sprintf(
            'Proposal submission not not allowed for Checkout "%s". Shift still not Checkoutable.',
            (string) $checkout
        ));
    }

    public static function becauseCheckoutWasMarkedAsNoShow(CheckoutId $checkout): self
    {
        return new self(sprintf(
            'Proposal submission not not allowed for Checkout "%s". It was marked as NoShow.',
            (string) $checkout
        ));
    }
}
