<?php

declare(strict_types=1);

namespace Acme\Marketplace\Checkout\Domain\Command;

final class SubmitClientAgreement extends SubmitAgreement
{
    private string $clientId;

    public function __construct(string $checkoutId, string $clientId)
    {
        parent::__construct($checkoutId);

        $this->clientId = $clientId;
    }

    public function clientId(): string
    {
        return $this->clientId;
    }
}
