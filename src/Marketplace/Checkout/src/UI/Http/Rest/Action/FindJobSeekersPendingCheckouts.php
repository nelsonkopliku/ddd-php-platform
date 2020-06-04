<?php

declare(strict_types=1);

namespace Acme\Marketplace\Checkout\UI\Http\Rest\Action;

use App\Controller\ApiController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Acme\Marketplace\Checkout\Domain\Repository\PendingCheckoutRepository;
use Acme\Marketplace\Checkout\UI\Http\Rest\Responder\CheckoutCollectionResponder;

final class FindJobSeekersPendingCheckouts extends ApiController
{
    /**
     * @Route(
     *     "/jobSeeker/{jobSeekerId}/checkouts/pending",
     *     methods={"GET"}
     * )
     */
    public function __invoke(PendingCheckoutRepository $readRepository, string $jobSeekerId): Response
    {
        $checkouts = $readRepository->findPendingByJobSeeker($jobSeekerId);

        return $this->json(CheckoutCollectionResponder::respond($checkouts));
    }
}
