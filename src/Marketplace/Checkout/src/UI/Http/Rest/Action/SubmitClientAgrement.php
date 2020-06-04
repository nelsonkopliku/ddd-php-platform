<?php

declare(strict_types=1);

namespace Acme\Marketplace\Checkout\UI\Http\Rest\Action;

use App\Controller\ApiController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Acme\Common\Application\Bus\Command\CommandBus;
use Acme\Marketplace\Checkout\Domain\Command\SubmitClientAgreement;

final class SubmitClientAgrement extends ApiController
{
    /**
     * @Route(
     *     "/client/{clientId}/checkouts/{checkoutId}/agree",
     *     methods={"POST"}
     * )
     */
    public function __invoke(CommandBus $bus, string $clientId, string $checkoutId): Response
    {
        $bus->dispatch(
            new SubmitClientAgreement(
                $checkoutId,
                $clientId
            )
        );

        return $this->json([
            'message' => sprintf(
                'Agreement by client %s was correctly submitted to checkout %s',
                $clientId,
                $checkoutId
            ),
        ]);
    }
}
