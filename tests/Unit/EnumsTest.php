<?php

namespace Tests\Unit;

use App\Enums\AppointmentStatus;
use App\Enums\HomecareRequestStatus;
use PHPUnit\Framework\TestCase;

class EnumsTest extends TestCase
{
    public function test_completed_appointment_projects_to_completed_request_status(): void
    {
        $this->assertSame(
            HomecareRequestStatus::Completed,
            AppointmentStatus::Completed->toRequestStatus(),
        );
    }

    public function test_in_service_appointment_projects_to_in_progress(): void
    {
        $this->assertSame(
            HomecareRequestStatus::InProgress,
            AppointmentStatus::InService->toRequestStatus(),
        );

        $this->assertSame(
            HomecareRequestStatus::Scheduled,
            AppointmentStatus::Scheduled->toRequestStatus(),
        );
    }

    public function test_request_status_transition_rules(): void
    {
        $submitted = HomecareRequestStatus::Submitted;

        $this->assertTrue($submitted->canTransitionTo(HomecareRequestStatus::UnderReview));
        $this->assertFalse(HomecareRequestStatus::Completed->canTransitionTo(HomecareRequestStatus::Submitted));
        $this->assertTrue(HomecareRequestStatus::Completed->isFinal());
        $this->assertFalse(HomecareRequestStatus::Scheduled->isFinal());
    }

    public function test_patient_cancellation_rules(): void
    {
        $this->assertTrue(HomecareRequestStatus::Submitted->cancellableByPatient());
        $this->assertTrue(HomecareRequestStatus::Approved->cancellableByPatient());
        $this->assertFalse(HomecareRequestStatus::InProgress->cancellableByPatient());
        $this->assertFalse(HomecareRequestStatus::Completed->cancellableByPatient());
    }

    public function test_every_status_has_indonesian_labels(): void
    {
        foreach (HomecareRequestStatus::cases() as $status) {
            $this->assertNotEmpty($status->label());
            $this->assertNotEmpty($status->patientLabel());
            $this->assertNotEmpty($status->badgeClasses());
        }

        foreach (AppointmentStatus::cases() as $status) {
            $this->assertNotEmpty($status->label());
            $this->assertNotEmpty($status->patientLabel());
        }
    }
}
