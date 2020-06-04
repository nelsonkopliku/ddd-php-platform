<?php

declare(strict_types=1);

namespace Acme\Marketplace\Checkout\UI\Http\Rest\Action;

use App\Controller\ApiController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Acme\Common\Application\Bus\Command\CommandBus;
use Acme\Marketplace\Checkout\Domain\Command\SubmitJobSeekerProposal as SubmitJobSeekerProposalCommand;
use Acme\Marketplace\Checkout\UI\Http\Rest\DTO\SubmitProposal;

final class SubmitJobSeekerProposal extends ApiController
{
    /**
     * @Route(
     *     "/jobSeeker/{jobSeekerId}/checkouts/{checkoutId}",
     *     methods={"POST"}
     * )
     */
    public function __invoke(CommandBus $bus, string $jobSeekerId, string $checkoutId): Response
    {
        /** @var SubmitProposal $dto */
        $dto = $this->deserialize(SubmitProposal::class);

        $bus->dispatch(
            new SubmitJobSeekerProposalCommand(
                $jobSeekerId,
                $checkoutId,
                $dto->workedFrom,
                $dto->workedUntil,
                $dto->minutesBreak,
                $dto->compensation
            )
        );

        return $this->json([
            'message' => sprintf(
                'Proposal by job seeker %s was correctly submitted to checkout %s',
                $jobSeekerId,
                $checkoutId
            ),
        ]);
    }
}
