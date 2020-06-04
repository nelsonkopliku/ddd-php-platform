<?php

declare(strict_types=1);

namespace Acme\Marketplace\Checkout\Domain\Exception;

use DomainException;
use Acme\Marketplace\Checkout\Domain\ValueObject\CheckoutId;

final class CannotSubmitAgreement extends DomainException
{
    public static function jobSeekerIsNotAllowed(string $jobSeeker, CheckoutId $checkout): self
    {
        return new self(sprintf(
            'JobSeeker "%s" not allowed to submit Agreement for Checkout "%s"',
            $jobSeeker,
            $checkout->value()
        ));
    }

    public static function clientIsNotAllowed(string $client, CheckoutId $checkout): self
    {
        return new self(sprintf(
            'Client "%s" not allowed to submit Agreement for Checkout "%s"',
            $client,
            $checkout->value()
        ));
    }

    public static function clientTryingToAgreeOnOwnProposal(string $client, CheckoutId $checkout): self
    {
        return new self(sprintf(
            'Client "%s" not allowed to submit Agreement on own proposal for Checkout "%s"',
            $client,
            $checkout->value()
        ));
    }

    public static function jobSeekerTryingToAgreeOnOwnProposal(string $jobSeeker, CheckoutId $checkout): self
    {
        return new self(sprintf(
            'JobSeeker "%s" not allowed to submit Agreement on own proposal for Checkout "%s"',
            $jobSeeker,
            $checkout->value()
        ));
    }

    public static function noAvailableProposalsForCheckout(CheckoutId $checkout): self
    {
        return new self(sprintf(
            'Unable to submit Agreement for Checkout "%s". No proposals available',
            $checkout->value()
        ));
    }

    public static function becauseCheckoutWasMarkedAsNoShow(CheckoutId $checkout): self
    {
        return new self(sprintf(
            'Unable to submit agreement for Checkout "%s". It was marked as NoShow.',
            $checkout->value()
        ));
    }
}
