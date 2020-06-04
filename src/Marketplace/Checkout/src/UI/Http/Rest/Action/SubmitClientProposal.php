<?php

declare(strict_types=1);

namespace Acme\Marketplace\Checkout\UI\Http\Rest\Action;

use App\Controller\ApiController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Acme\Common\Application\Bus\Command\CommandBus;
use Acme\Marketplace\Checkout\Domain\Command\SubmitClientProposal as SubmitClientProposalCommand;
use Acme\Marketplace\Checkout\UI\Http\Rest\DTO\SubmitProposal;

final class SubmitClientProposal extends ApiController
{
    /**
     * @Route(
     *     "/client/{clientId}/checkouts/{checkoutId}",
     *     methods={"POST"}
     * )
     */
    public function __invoke(CommandBus $bus, string $clientId, string $checkoutId): Response
    {
        /** @var SubmitProposal $dto */
        $dto = $this->deserialize(SubmitProposal::class);

        $bus->dispatch(
            new SubmitClientProposalCommand(
                $clientId,
                $checkoutId,
                $dto->workedFrom,
                $dto->workedUntil,
                $dto->minutesBreak,
                $dto->compensation
            )
        );

        return $this->json([
            'message' => sprintf(
                'Proposal by client %s was correctly submitted to checkout %s',
                $clientId,
                $checkoutId
            ),
        ]);
    }
}
