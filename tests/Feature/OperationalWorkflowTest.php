<?php

namespace Tests\Feature;

use App\Enums\HomecareRequestStatus;
use App\Enums\UserRole;
use App\Models\Appointment;
use App\Models\Payment;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Support\MakesHomecareData;
use Tests\TestCase;

class OperationalWorkflowTest extends TestCase
{
    use DatabaseTransactions, MakesHomecareData;

    public function test_coordinator_can_start_review(): void
    {
        $coordinator = $this->makeOperationalUser(UserRole::Coordinator);
        $patient = $this->makePatientUser();
        $request = $this->makeSubmittedRequest($patient);

        $this->actingAs($coordinator)
            ->post(route('operasional.pengajuan.verifikasi', $request->code))
            ->assertSessionHas('success');

        $request->refresh();

        $this->assertSame(HomecareRequestStatus::UnderReview, $request->status);
        $this->assertSame($coordinator->id, $request->verified_by);
    }

    public function test_coordinator_can_request_information_and_patient_gets_notification(): void
    {
        $coordinator = $this->makeOperationalUser(UserRole::Coordinator);
        $patient = $this->makePatientUser();
        $request = $this->makeSubmittedRequest($patient);

        $this->actingAs($coordinator)->post(route('operasional.pengajuan.minta-informasi', $request->code), [
            'questions' => 'Mohon kirimkan foto resep terbaru.',
        ])->assertSessionHas('success');

        $request->refresh();

        $this->assertSame(HomecareRequestStatus::NeedInformation, $request->status);
        $this->assertSame('Mohon kirimkan foto resep terbaru.', $request->information_request);
        $this->assertGreaterThan(0, $patient->notifications()->count());
    }

    public function test_coordinator_can_approve_with_screening_notes(): void
    {
        $coordinator = $this->makeOperationalUser(UserRole::Coordinator);
        $patient = $this->makePatientUser();
        $request = $this->makeSubmittedRequest($patient);

        $this->actingAs($coordinator)->post(route('operasional.pengajuan.setujui', $request->code), [
            'screening_notes' => 'Layak homecare; tugaskan perawat wound care.',
        ])->assertSessionHas('success');

        $request->refresh();

        $this->assertSame(HomecareRequestStatus::Approved, $request->status);
        $this->assertSame($coordinator->id, $request->screened_by);
    }

    public function test_approval_can_replace_service_items(): void
    {
        $coordinator = $this->makeOperationalUser(UserRole::Coordinator);
        $patient = $this->makePatientUser();
        $original = $this->makeService(['price' => 100000]);
        $replacement = $this->makeService(['price' => 250000]);
        $request = $this->makeSubmittedRequest($patient, $original);

        $this->actingAs($coordinator)->post(route('operasional.pengajuan.setujui', $request->code), [
            'screening_notes' => 'Sesuaikan layanan setelah telepon keluarga.',
            'services' => [
                ['homecare_service_id' => $replacement->id, 'quantity' => 2],
            ],
        ]);

        $request->refresh();

        $this->assertSame(1, $request->items()->count());
        $this->assertSame($replacement->name, $request->items()->first()->service_name);
        $this->assertEquals(500000, (float) $request->total_amount);
    }

    public function test_coordinator_can_reject_with_reason(): void
    {
        $coordinator = $this->makeOperationalUser(UserRole::Coordinator);
        $patient = $this->makePatientUser();
        $request = $this->makeSubmittedRequest($patient);

        $this->actingAs($coordinator)->post(route('operasional.pengajuan.tolak', $request->code), [
            'reason' => 'Kondisi memerlukan rawat inap, bukan homecare.',
        ])->assertSessionHas('success');

        $request->refresh();

        $this->assertSame(HomecareRequestStatus::Rejected, $request->status);
        $this->assertStringContainsString('rawat inap', $request->rejected_reason);
    }

    public function test_reject_requires_a_reason(): void
    {
        $coordinator = $this->makeOperationalUser(UserRole::Coordinator);
        $request = $this->makeSubmittedRequest($this->makePatientUser());

        $this->actingAs($coordinator)
            ->post(route('operasional.pengajuan.tolak', $request->code), [])
            ->assertSessionHasErrors('reason');
    }

    public function test_scheduling_creates_appointment_assignments_and_invoice(): void
    {
        $coordinator = $this->makeOperationalUser(UserRole::Coordinator);
        $patient = $this->makePatientUser();
        $request = $this->makeSubmittedRequest($patient);

        // Jadwal hanya bisa diterbitkan dari status approved.
        app(\App\Actions\ApproveHomecareRequest::class)->execute($request, null, null, $coordinator);

        $staffA = $this->makeStaff();
        $staffB = $this->makeStaff();

        $when = now()->addDays(2)->setTime(10, 0);

        $this->actingAs($coordinator)->post(route('operasional.pengajuan.jadwalkan', $request->code), [
            'scheduled_at' => $when->toDateTimeString(),
            'staff_ids' => [$staffA->id, $staffB->id],
            'notes' => 'Bawa set ganti balutan.',
            'total_amount' => 300000,
        ])->assertSessionHas('success');

        $request->refresh();

        $this->assertSame(HomecareRequestStatus::Scheduled, $request->status);
        $this->assertEquals(300000, (float) $request->total_amount);

        $appointment = Appointment::where('homecare_request_id', $request->id)->firstOrFail();
        $this->assertTrue($appointment->scheduled_at->equalTo($when));
        $this->assertSame(2, $appointment->assignments()->count());

        // Invoice otomatis dibuat saat jadwal terbit.
        $this->assertTrue(Payment::where('homecare_request_id', $request->id)->exists());

        // Pasien & petugas mendapat notifikasi database.
        $this->assertGreaterThan(0, $patient->notifications()->count());
        $this->assertGreaterThan(0, $staffA->user->notifications()->count());
    }

    public function test_scheduling_rejects_past_dates(): void
    {
        $coordinator = $this->makeOperationalUser(UserRole::Coordinator);
        $request = $this->makeSubmittedRequest($this->makePatientUser());
        app(\App\Actions\ApproveHomecareRequest::class)->execute($request, null, null, $coordinator);

        $this->actingAs($coordinator)->post(route('operasional.pengajuan.jadwalkan', $request->code), [
            'scheduled_at' => now()->subDay()->toDateTimeString(),
            'staff_ids' => [$this->makeStaff()->id],
        ])->assertSessionHasErrors('scheduled_at');
    }

    public function test_coordinator_can_record_payment(): void
    {
        $coordinator = $this->makeOperationalUser(UserRole::Coordinator);
        $request = $this->makeSubmittedRequest($this->makePatientUser());

        $this->actingAs($coordinator)->post(route('operasional.pengajuan.pembayaran', $request->code), [
            'method' => 'cash',
            'amount' => (float) $request->total_amount,
            'paid_at' => now()->toDateString(),
        ])->assertSessionHas('success');

        $request->refresh();

        $this->assertSame('paid', $request->payment_status->value);
    }

    public function test_staff_cannot_approve_requests(): void
    {
        $staffUser = $this->makeStaff()->user;
        $request = $this->makeSubmittedRequest($this->makePatientUser());

        $this->actingAs($staffUser)
            ->post(route('operasional.pengajuan.setujui', $request->code), ['screening_notes' => 'coba'])
            ->assertForbidden();
    }

    public function test_workflow_actions_are_audited(): void
    {
        $coordinator = $this->makeOperationalUser(UserRole::Coordinator);
        $request = $this->makeSubmittedRequest($this->makePatientUser());

        $this->actingAs($coordinator)->post(route('operasional.pengajuan.verifikasi', $request->code));

        $this->assertTrue(
            $request->auditLogs()->where('event', 'like', '%REVIEW%')->exists()
            || $request->auditLogs()->count() > 0,
        );
    }
}
