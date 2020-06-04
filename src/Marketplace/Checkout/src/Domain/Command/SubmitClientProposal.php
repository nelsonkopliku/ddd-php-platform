<?php

declare(strict_types=1);

namespace Acme\Marketplace\Checkout\Domain\Command;

final class SubmitClientProposal extends SubmitProposal
{
    private string $clientId;

    public function __construct(
        string $clientId,
        string $checkoutId,
        string $workedFrom,
        string $workedUntil,
        int $minutesBreak,
        string $compensation
    ) {
        parent::__construct($checkoutId, $workedFrom, $workedUntil, $minutesBreak, $compensation);

        $this->clientId = $clientId;
    }

    public function clientId(): string
    {
        return $this->clientId;
    }
}
