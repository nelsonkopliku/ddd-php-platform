<?php

declare(strict_types=1);

namespace Acme\Marketplace\Checkout\UI\Http\Rest\Responder;

use DateTimeInterface;
use Acme\Marketplace\Checkout\Domain\Checkout;
use Acme\Marketplace\Checkout\Domain\Proposal\Proposal;

final class CheckoutCollectionResponder
{
    /**
     * @param Checkout[] $checkouts
     *
     * @return array<string,mixed>
     */
    public static function respond(array $checkouts): array
    {
        return
            array_map(
                static function (Checkout $checkout) {
                    return [
                        'id' => $checkout->id()->value(),
                        'shiftId' => $checkout->shift()->id(),
                        'agreed' => $checkout->isAgreed(),
                        'proposal' => $checkout->hasProposal()
                            ? self::toProposalResource($checkout->currentProposal())
                            : null,
                    ];
                },
                $checkouts
            );
    }

    /**
     * @return array<string,string>
     */
    private static function toProposalResource(Proposal $proposal): array
    {
        return [
            'proposedBy' => $proposal->proposedBy(),
            'proposedAt' => $proposal->proposedAt()->format(DateTimeInterface::ATOM),
        ];
    }
}
