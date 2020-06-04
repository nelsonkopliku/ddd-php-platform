<?php

declare(strict_types=1);

namespace Acme\Marketplace\Checkout\UI\Http\Rest\Action;

use App\Controller\ApiController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Acme\Marketplace\Checkout\Domain\Repository\AgreedCheckoutRepository;
use Acme\Marketplace\Checkout\UI\Http\Rest\Responder\CheckoutCollectionResponder;

final class FindJobSeekerssAgreedCheckout extends ApiController
{
    /**
     * @Route(
     *     "/jobSeeker/{jobSeekerId}/checkouts/agreed",
     *     methods={"GET"}
     * )
     */
    public function __invoke(AgreedCheckoutRepository $readRepository, string $jobSeekerId): Response
    {
        $checkouts = $readRepository->findAgreedByJobSeeker($jobSeekerId);

        return $this->json(CheckoutCollectionResponder::respond($checkouts));
    }
}
