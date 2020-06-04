<?php

declare(strict_types=1);

namespace Acme\Marketplace\Checkout\Domain\Event;

use Acme\Marketplace\Checkout\Domain\Proposal\ProposalContent;
use Acme\Marketplace\Checkout\Domain\ValueObject\CheckoutId;

final class AgreementWasSubmittedByClient extends AgreementWasSubmitted
{
    public static function forCheckoutWithContent(
        string $clientId,
        CheckoutId $checkoutId,
        ProposalContent $content
    ): self {
        return new self($checkoutId, $content, $clientId);
    }
}
