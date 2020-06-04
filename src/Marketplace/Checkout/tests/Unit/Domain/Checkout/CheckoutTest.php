<?php

declare(strict_types=1);

namespace Acme\Marketplace\Checkout\Tests\Unit\Domain\Checkout;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Acme\Marketplace\Checkout\Domain\Checkout;
use Acme\Marketplace\Checkout\Domain\Event\AgreementWasSubmittedByClient;
use Acme\Marketplace\Checkout\Domain\Event\AgreementWasSubmittedByJobSeeker;
use Acme\Marketplace\Checkout\Domain\Event\JobSeekerWasMarkedAsNoShow;
use Acme\Marketplace\Checkout\Domain\Event\ProposalWasSubmittedByClient;
use Acme\Marketplace\Checkout\Domain\Event\ProposalWasSubmittedByJobSeeker;
use Acme\Marketplace\Checkout\Domain\Exception\CannotMarkNoShow;
use Acme\Marketplace\Checkout\Domain\Exception\CannotSubmitAgreement;
use Acme\Marketplace\Checkout\Domain\Exception\CannotSubmitProposal;
use Acme\Marketplace\Checkout\Domain\Exception\CheckoutAlreadyAgreed;
use Acme\Marketplace\Checkout\Domain\Exception\NoProposalAvailable;
use Acme\Marketplace\Checkout\Domain\Proposal\Proposal;
use Acme\Marketplace\Checkout\Domain\ValueObject\CheckoutId;
use Acme\Marketplace\Checkout\Domain\ValueObject\Parties;
use Acme\Marketplace\Checkout\Domain\ValueObject\StartedShift;
use Acme\Marketplace\Checkout\Tests\Unit\Domain\Dummy\CheckoutDummy;
use Acme\Marketplace\Checkout\Tests\Unit\Domain\Dummy\ProposalDummy;
use Acme\Marketplace\Checkout\Tests\Unit\Domain\Dummy\ShiftDummy;

final class CheckoutTest extends TestCase
{
    private const CLIENT_ID = 'clientId';

    private const JOBSEEKER_ID = 'jobSeekerId';

    private CheckoutId $checkoutId;

    private Parties $parties;

    private StartedShift $startedShift;

    public function setUp(): void
    {
        $from = new DateTimeImmutable('2020-01-01 12:00:00');
        $to = $from->modify('+8 hours');
        $this->checkoutId = CheckoutId::fromString('someCheckoutId');

        $this->parties = Parties::fromClientAndJobSeeker(
            self::CLIENT_ID,
            self::JOBSEEKER_ID
        );

        $this->startedShift = StartedShift::fromValues(
            'shiftId',
            $from,
            $to
        );
    }

    /**
     * @test
     */
    public function checkout_can_be_opened(): void
    {
        $checkout = Checkout::open(
            $this->checkoutId,
            $this->startedShift,
            $this->parties
        );

        $this->assertTrue($checkout->id()->equalsTo($this->checkoutId));
        $this->assertSame($this->parties, $checkout->parties());
        $this->assertSame($this->startedShift, $checkout->shift());
        $this->assertFalse($checkout->isAgreed());
        $this->assertFalse($checkout->isClientLatestProposal());
        $this->assertFalse($checkout->isJobSeekerLatestProposal());
        $this->assertFalse($checkout->hasProposal());

        $this->expectException(NoProposalAvailable::class);

        $checkout->currentProposal();
    }

    /**
     * @test
     */
    public function agreed_checkout_cannot_be_created_without_a_proposal(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Checkout::fromValues(
            $this->checkoutId,
            $this->startedShift,
            $this->parties,
            null,
            true
        );
    }

    /**
     * @test
     */
    public function exception_should_be_thrown_when_accessing_unavailable_proposal(): void
    {
        $this->expectException(NoProposalAvailable::class);

        $checkout = Checkout::fromValues(
            $this->checkoutId,
            $this->startedShift,
            $this->parties,
            null,
            false
        );

        $checkout->currentProposal();
    }

    /**
     * @test
     */
    public function agreed_checkout_can_be_created_from_valid_values(): void
    {
        $proposal = ProposalDummy::clientProposal();

        $checkout = Checkout::fromValues(
            $this->checkoutId,
            $this->startedShift,
            $this->parties,
            $proposal,
            true
        );

        $this->assertTrue($checkout->id()->equalsTo($this->checkoutId));
        $this->assertSame($this->parties, $checkout->parties());
        $this->assertSame($this->startedShift, $checkout->shift());
        $this->assertTrue($checkout->isAgreed());
        $this->assertTrue($checkout->hasProposal());
        $this->assertSame('clientId', $checkout->currentProposal()->proposedBy());
    }

    /**
     * @test
     */
    public function checkout_can_be_created_from_values(): void
    {
        $proposal = ProposalDummy::clientProposal();

        $checkout = Checkout::fromValues(
            $this->checkoutId,
            $this->startedShift,
            $this->parties,
            $proposal,
            false
        );

        $this->assertTrue($checkout->id()->equalsTo($this->checkoutId));
        $this->assertSame($this->parties, $checkout->parties());
        $this->assertSame($this->startedShift, $checkout->shift());
        $this->assertFalse($checkout->isAgreed());
        $this->assertSame($proposal, $checkout->currentProposal());
    }

    /**
     * @test
     */
    public function jobseeker_cannot_submit_proposal_when_not_allowed(): void
    {
        $proposal = ProposalDummy::clientProposal();

        $checkout = Checkout::fromValues(
            $this->checkoutId,
            $this->startedShift,
            $this->parties,
            $proposal,
            false
        );

        $this->expectException(CannotSubmitProposal::class);

        $checkout->submitJobSeekerProposal(ProposalDummy::jobSeekerProposal('notAllowed'));
    }

    /**
     * @test
     */
    public function jobseeker_cannot_submit_proposal_when_already_submitted(): void
    {
        $proposal = ProposalDummy::jobSeekerProposal();

        $checkout = Checkout::fromValues(
            $this->checkoutId,
            $this->startedShift,
            $this->parties,
            $proposal,
            false
        );

        $this->expectException(CannotSubmitProposal::class);

        $checkout->submitJobSeekerProposal(ProposalDummy::jobSeekerProposal(self::JOBSEEKER_ID));
    }

    /**
     * @test
     */
    public function client_cannot_submit_proposal_when_not_allowed(): void
    {
        $proposal = ProposalDummy::jobSeekerProposal();

        $checkout = Checkout::fromValues(
            $this->checkoutId,
            $this->startedShift,
            $this->parties,
            $proposal,
            false
        );

        $this->expectException(CannotSubmitProposal::class);

        $checkout->submitClientProposal(ProposalDummy::clientProposal('notAllowed'));
    }

    /**
     * @test
     */
    public function client_cannot_submit_proposal_when_already_submitted(): void
    {
        $proposal = ProposalDummy::clientProposal();

        $checkout = Checkout::fromValues(
            $this->checkoutId,
            $this->startedShift,
            $this->parties,
            $proposal,
            false
        );

        $this->expectException(CannotSubmitProposal::class);

        $checkout->submitClientProposal(ProposalDummy::clientProposal(self::CLIENT_ID));
    }

    /**
     * @test
     */
    public function jobseeker_cannot_submit_proposal_on_already_agreed_checkout(): void
    {
        $proposal = ProposalDummy::clientProposal();

        $checkout = Checkout::fromValues(
            $this->checkoutId,
            $this->startedShift,
            $this->parties,
            $proposal,
            true
        );

        $this->expectException(CannotSubmitProposal::class);

        $checkout->submitJobSeekerProposal(ProposalDummy::jobSeekerProposal(self::JOBSEEKER_ID));
    }

    /**
     * @test
     */
    public function jobseeker_cannot_submit_proposal_on_checkout_marked_as_no_show(): void
    {
        $checkout = Checkout::fromValues(
            $this->checkoutId,
            $this->startedShift,
            $this->parties,
            null,
            false,
            true
        );

        $this->expectException(CannotSubmitProposal::class);
        $this->expectExceptionMessage('It was marked as NoShow');

        $checkout->submitJobSeekerProposal(ProposalDummy::jobSeekerProposal(self::JOBSEEKER_ID));
    }

    /**
     * @test
     */
    public function client_cannot_submit_proposal_on_already_agreed_checkout(): void
    {
        $proposal = ProposalDummy::jobSeekerProposal();

        $checkout = Checkout::fromValues(
            $this->checkoutId,
            $this->startedShift,
            $this->parties,
            $proposal,
            true
        );

        $this->expectException(CannotSubmitProposal::class);

        $checkout->submitClientProposal(ProposalDummy::clientProposal(self::CLIENT_ID));
    }

    /**
     * @test
     */
    public function client_cannot_submit_proposal_on_checkout_marked_as_no_show(): void
    {
        $checkout = Checkout::fromValues(
            $this->checkoutId,
            $this->startedShift,
            $this->parties,
            null,
            false,
            true
        );

        $this->expectException(CannotSubmitProposal::class);
        $this->expectExceptionMessage('It was marked as NoShow');

        $checkout->submitClientProposal(ProposalDummy::clientProposal(self::CLIENT_ID));
    }

    /**
     * @test
     */
    public function jobseeker_can_submit_proposal(): void
    {
        $proposal = ProposalDummy::clientProposal();

        $checkout = Checkout::fromValues(
            $this->checkoutId,
            $this->startedShift,
            $this->parties,
            $proposal,
            false
        );

        $checkout->submitJobSeekerProposal($latest = ProposalDummy::jobSeekerProposal(self::JOBSEEKER_ID));

        $this->assertSame($latest, $checkout->currentProposal());

        $events = $checkout->getRecordedEvents();

        $this->assertInstanceOf(ProposalWasSubmittedByJobSeeker::class, end($events));
    }

    /**
     * @test
     */
    public function client_can_submit_proposal(): void
    {
        $currentProposal = ProposalDummy::jobSeekerProposal();

        $checkout = Checkout::fromValues(
            $this->checkoutId,
            $this->startedShift,
            $this->parties,
            $currentProposal,
            false
        );

        $checkout->submitClientProposal($latest = ProposalDummy::clientProposal(self::CLIENT_ID));

        $this->assertSame($latest, $checkout->currentProposal());

        $events = $checkout->getRecordedEvents();

        $this->assertInstanceOf(ProposalWasSubmittedByClient::class, end($events));
    }

    /**
     * @test
     */
    public function jobseeker_cannot_submit_agreement_when_not_allowed(): void
    {
        $proposal = ProposalDummy::clientProposal();

        $checkout = Checkout::fromValues(
            $this->checkoutId,
            $this->startedShift,
            $this->parties,
            $proposal,
            false
        );

        $this->expectException(CannotSubmitAgreement::class);

        $checkout->submitJobSeekerAgreement('notAllowed');
    }

    /**
     * @test
     */
    public function jobseeker_cannot_submit_agreement_when_proposal_not_available(): void
    {
        $checkout = Checkout::fromValues(
            $this->checkoutId,
            $this->startedShift,
            $this->parties,
            null,
            false
        );

        $this->expectException(CannotSubmitAgreement::class);

        $checkout->submitJobSeekerAgreement(self::JOBSEEKER_ID);
    }

    /**
     * @test
     */
    public function jobseeker_cannot_submit_agreement_on_own_proposal(): void
    {
        $proposal = ProposalDummy::jobSeekerProposal();

        $checkout = Checkout::fromValues(
            $this->checkoutId,
            $this->startedShift,
            $this->parties,
            $proposal,
            false
        );

        $this->expectException(CannotSubmitAgreement::class);

        $checkout->submitJobSeekerAgreement(self::JOBSEEKER_ID);
    }

    /**
     * @test
     */
    public function jobseeker_cannot_submit_agreement_on_already_agreed_checkout(): void
    {
        $proposal = ProposalDummy::clientProposal();

        $checkout = Checkout::fromValues(
            $this->checkoutId,
            $this->startedShift,
            $this->parties,
            $proposal,
            true
        );

        $this->expectException(CheckoutAlreadyAgreed::class);

        $checkout->submitJobSeekerAgreement(self::JOBSEEKER_ID);
    }

    /**
     * @test
     */
    public function jobseeker_cannot_submit_agreement_on_checkout_marked_as_no_show(): void
    {
        $checkout = Checkout::fromValues(
            $this->checkoutId,
            $this->startedShift,
            $this->parties,
            null,
            false,
            true
        );

        $this->expectException(CannotSubmitAgreement::class);
        $this->expectExceptionMessage('It was marked as NoShow.');

        $checkout->submitJobSeekerAgreement(self::JOBSEEKER_ID);
    }

    /**
     * @test
     */
    public function jobseeker_can_submit_agreement_on_checkout(): void
    {
        $proposal = ProposalDummy::clientProposal();

        $checkout = Checkout::fromValues(
            $this->checkoutId,
            $this->startedShift,
            $this->parties,
            $proposal,
            false
        );

        $checkout->submitJobSeekerAgreement(self::JOBSEEKER_ID);

        $this->assertTrue($checkout->isAgreed());

        $events = $checkout->getRecordedEvents();

        $this->assertInstanceOf(AgreementWasSubmittedByJobSeeker::class, end($events));
    }

    /**
     * @test
     */
    public function client_cannot_submit_agreement_when_not_allowed(): void
    {
        $proposal = ProposalDummy::jobSeekerProposal();

        $checkout = Checkout::fromValues(
            $this->checkoutId,
            $this->startedShift,
            $this->parties,
            $proposal,
            false
        );

        $this->expectException(CannotSubmitAgreement::class);

        $checkout->submitClientAgreement('notAllowed');
    }

    /**
     * @test
     */
    public function client_cannot_submit_agreement_when_proposal_not_available(): void
    {
        $checkout = Checkout::fromValues(
            $this->checkoutId,
            $this->startedShift,
            $this->parties,
            null,
            false
        );

        $this->expectException(CannotSubmitAgreement::class);

        $checkout->submitClientAgreement(self::CLIENT_ID);
    }

    /**
     * @test
     */
    public function client_cannot_submit_agreement_on_own_proposal(): void
    {
        $proposal = ProposalDummy::clientProposal();

        $checkout = Checkout::fromValues(
            $this->checkoutId,
            $this->startedShift,
            $this->parties,
            $proposal,
            false
        );

        $this->expectException(CannotSubmitAgreement::class);

        $checkout->submitClientAgreement(self::CLIENT_ID);
    }

    /**
     * @test
     */
    public function client_cannot_submit_agreement_on_already_agreed_checkout(): void
    {
        $proposal = ProposalDummy::jobSeekerProposal();

        $checkout = Checkout::fromValues(
            $this->checkoutId,
            $this->startedShift,
            $this->parties,
            $proposal,
            true
        );

        $this->expectException(CheckoutAlreadyAgreed::class);

        $checkout->submitClientAgreement(self::CLIENT_ID);
    }

    /**
     * @test
     */
    public function client_cannot_submit_agreement_on_checkout_marked_as_no_show(): void
    {
        $checkout = Checkout::fromValues(
            $this->checkoutId,
            $this->startedShift,
            $this->parties,
            null,
            false,
            true
        );

        $this->expectException(CannotSubmitAgreement::class);
        $this->expectExceptionMessage('It was marked as NoShow.');

        $checkout->submitClientAgreement(self::CLIENT_ID);
    }

    /**
     * @test
     */
    public function client_can_submit_agreement_on_checkout(): void
    {
        $proposal = ProposalDummy::jobSeekerProposal();

        $checkout = Checkout::fromValues(
            $this->checkoutId,
            $this->startedShift,
            $this->parties,
            $proposal,
            false
        );

        $checkout->submitClientAgreement(self::CLIENT_ID);

        $this->assertTrue($checkout->isAgreed());

        $events = $checkout->getRecordedEvents();

        $this->assertInstanceOf(AgreementWasSubmittedByClient::class, end($events));
    }

    /**
     * @dataProvider provideSomeProposal
     * @test
     */
    public function proposal_cannot_be_submitted_when_shift_not_ready_for_checkout_yet(Proposal $proposal): void
    {
        $aShiftStartedLessThanOneHourAgo = ShiftDummy::aShiftStartedLessThanOneHourAgo();

        $checkout = Checkout::fromValues(
            $this->checkoutId,
            $aShiftStartedLessThanOneHourAgo,
            $this->parties,
            $proposal,
            false
        );

        $this->expectException(CannotSubmitProposal::class);

        $checkout->submitJobSeekerProposal(ProposalDummy::jobSeekerProposal());
    }

    public function provideSomeProposal(): array
    {
        return [
            [
                ProposalDummy::jobSeekerProposal()
            ],
            [
                ProposalDummy::clientProposal()
            ]
        ];
    }

    /**
     * @test
     */
    public function an_agreed_checkout_cannot_be_marked_as_no_show(): void
    {
        $checkout = CheckoutDummy::aValidAgreedCheckout();

        $this->expectException(CannotMarkNoShow::class);
        $this->expectExceptionMessage('Checkout already Agreed');

        $checkout->markAsNoShow();
    }

    /**
     * @test
     */
    public function checkout_cannot_be_marked_as_no_show_when_shift_is_not_yet_checkoutable(): void
    {
        $aShiftStartedLessThanOneHourAgo = ShiftDummy::aShiftStartedLessThanOneHourAgo();

        $checkout = Checkout::fromValues(
            CheckoutId::fromString('some'),
            $aShiftStartedLessThanOneHourAgo,
            Parties::fromClientAndJobSeeker(
                self::CLIENT_ID,
                self::JOBSEEKER_ID
            ),
            null,
            false
        );

        $this->expectException(CannotMarkNoShow::class);
        $this->expectExceptionMessage('Shift not yet ready for Checkout');

        $checkout->markAsNoShow();
    }

    /**
     * @dataProvider provideACheckoutReadyForProposal
     * @test
     */
    public function checkout_cannot_be_marked_as_no_show_when_a_proposal_has_been_submitted(Checkout $checkout): void
    {
        $this->expectException(CannotMarkNoShow::class);
        $this->expectExceptionMessage('Pending Proposals on Checkout');

        $checkout->markAsNoShow();
    }

    public function provideACheckoutReadyForProposal(): array
    {
        return [
            [
                CheckoutDummy::aCheckoutReadyForClientProposal()
            ],
            [
                CheckoutDummy::aCheckoutReadyForJobSeekerProposal()
            ]
        ];
    }

    /**
     * @test
     */
    public function checkout_cannot_be_marked_as_no_show_before_7_days(): void
    {
        $shiftStartedLessThanOneWeekAgo = ShiftDummy::aShiftStartedLessThanOneWeekAgo();

        $checkout = Checkout::fromValues(
            CheckoutId::fromString('some'),
            $shiftStartedLessThanOneWeekAgo,
            Parties::fromClientAndJobSeeker(
                self::CLIENT_ID,
                self::JOBSEEKER_ID
            ),
            null,
            false
        );

        $this->expectException(CannotMarkNoShow::class);
        $this->expectExceptionMessage('Too early to Mark as No Show');

        $checkout->markAsNoShow();
    }

    /**
     * @test
     */
    public function checkout_can_be_marked_as_no_show(): void
    {
        $aShiftStartedMoreThanOneWeekAgo = ShiftDummy::aShiftStartedMoreThanOneWeekAgo();

        $checkout = Checkout::fromValues(
            CheckoutId::fromString('some'),
            $aShiftStartedMoreThanOneWeekAgo,
            Parties::fromClientAndJobSeeker(
                self::CLIENT_ID,
                self::JOBSEEKER_ID
            ),
            null,
            false
        );

        $checkout->markAsNoShow();
        $this->assertTrue($checkout->isNoShow());

        $events = $checkout->getRecordedEvents();

        $this->assertInstanceOf(JobSeekerWasMarkedAsNoShow::class, end($events));
    }
}
