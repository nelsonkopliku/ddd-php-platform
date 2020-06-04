<?php

declare(strict_types=1);

namespace Acme\Marketplace\Checkout\UI\Http\Rest\Action;

use App\Controller\ApiController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Acme\Common\Application\Bus\Command\CommandBus;
use Acme\Marketplace\Checkout\Domain\Command\SubmitJobSeekerAgreement as SubmitJobSeekerAgreementCommand;

final class SubmitJobSeekerAgreement extends ApiController
{
    /**
     * @Route(
     *     "/jobSeeker/{jobSeekerId}/checkouts/{checkoutId}/agree",
     *     methods={"POST"}
     * )
     */
    public function __invoke(CommandBus $bus, string $jobSeekerId, string $checkoutId): Response
    {
        $bus->dispatch(
            new SubmitJobSeekerAgreementCommand(
                $checkoutId,
                $jobSeekerId
            )
        );

        return $this->json([
            'message' => sprintf(
                'Agreement by job seeker %s was correctly submitted to checkout %s',
                $jobSeekerId,
                $checkoutId
            ),
        ]);
    }
}
