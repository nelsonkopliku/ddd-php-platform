<?php

declare(strict_types=1);

namespace Acme\Marketplace\Checkout\Infrastructure\Persistence\DataFixtures;

use DateTimeImmutable;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Acme\Common\Infrastructure\Persistence\DataFixtures\Fixture;
use Acme\Marketplace\Checkout\Infrastructure\Persistence\Doctrine\Entity\Checkout;
use Acme\Marketplace\Checkout\Infrastructure\Persistence\Doctrine\Entity\Proposal;

class CheckoutFixtures extends AbstractFixture implements Fixture
{
    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i < 10; ++$i) {
            $checkout = $this->oneCheckout(
                'checkout-id-'.$i,
                'shift-id-'.$i,
                'client-id-'.$i,
                'jobseeker-id-'.$i,
                $start = new DateTimeImmutable('2020-02-02 09:30:00'),
                $start->modify('+6 hours'),
                false
            );

            $lastProposal = new DateTimeImmutable('2020-02-04 17:30:00');

            for ($j = 0; $j < 5; ++$j) {
                $checkout->addProposal(
                    $this->oneProposal(
                        $from = new DateTimeImmutable('2020-02-02 09:30:00'),
                        $from->modify('+7 hours'),
                        30,
                        'some',
                        0 === $j % 2,
                        $lastProposal = $lastProposal->modify('+3 hours')
                    )
                );
            }

            $manager->persist($checkout);
        }

        $this->loadSomeAgreedCheckouts($manager);
        $this->loadSomePendingCheckouts($manager);

        $manager->flush();
    }

    private function loadSomeAgreedCheckouts(ObjectManager $manager): void
    {
        for ($i = 0; $i < 5; ++$i) {
            $checkout = $this->oneCheckout(
                'agreed-checkout-id-'.$i,
                'agreed-checkout-shift-id-'.$i,
                'agreed-checkout-client-id-'.$i,
                'agreed-checkout-jobseeker-id-'.$i,
                $start = new DateTimeImmutable('2020-02-02 09:30:00'),
                $start->modify('+6 hours'),
                true
            );

            $lastProposal = new DateTimeImmutable('2020-02-04 17:30:00');

            for ($j = 0; $j < 3; ++$j) {
                $checkout->addProposal(
                    $this->oneProposal(
                        $from = new DateTimeImmutable('2020-02-02 09:30:00'),
                        $from->modify('+7 hours'),
                        30,
                        'some',
                        0 === $j % 2,
                        $lastProposal = $lastProposal->modify('+3 hours')
                    )
                );
            }

            $manager->persist($checkout);
        }
    }

    private function loadSomePendingCheckouts(ObjectManager $manager): void
    {
        for ($i = 0; $i < 5; ++$i) {
            $checkout = $this->oneCheckout(
                'pending-checkout-id-'.$i,
                'pending-checkout-shift-id-'.$i,
                'pending-checkout-client-id-'.$i,
                'pending-checkout-jobseeker-id-'.$i,
                $start = new DateTimeImmutable('2020-02-02 09:30:00'),
                $start->modify('+6 hours'),
                false
            );

            $lastProposal = new DateTimeImmutable('2020-02-04 17:30:00');

            $checkout->addProposal(
                $this->oneProposal(
                    $from = new DateTimeImmutable('2020-02-02 09:30:00'),
                    $from->modify('+7 hours'),
                    30,
                    'some',
                    0 === $i % 2,
                    $lastProposal = $lastProposal->modify('+3 hours')
                )
            );

            $manager->persist($checkout);
        }
    }

    private function oneCheckout(
        string $id,
        string $shiftId,
        string $clientId,
        string $jobSeekerId,
        DateTimeImmutable $startedAt,
        DateTimeImmutable $end,
        bool $agreed
    ): Checkout {
        $checkout = new Checkout();

        $checkout->id = $id;
        $checkout->shiftId = $shiftId;
        $checkout->clientId = $clientId;
        $checkout->jobSeekerId = $jobSeekerId;
        $checkout->startedAt = $startedAt;
        $checkout->end = $end;
        $checkout->agreed = $agreed;

        return $checkout;
    }

    private function oneProposal(
        DateTimeImmutable $workedFrom,
        DateTimeImmutable $workedUntil,
        int $minutesBreak,
        string $compensation,
        bool $byClient,
        DateTimeImmutable $proposedAt
    ): Proposal {
        $proposal = new Proposal();
        $proposal->workedFrom = $workedFrom;
        $proposal->workedUntil = $workedUntil;
        $proposal->minutesBreak = $minutesBreak;
        $proposal->compensation = $compensation;
        $proposal->proposedBy = $byClient ? 'client' : 'jobseeker';
        $proposal->proposedAt = $proposedAt;

        return $proposal;
    }
}
