<?php

namespace Tests\Feature;

use App\Actions\ScheduleHomecare;
use App\Enums\AppointmentStatus;
use App\Enums\HomecareRequestStatus;
use App\Enums\UserRole;
use App\Models\ServiceRecord;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Support\MakesHomecareData;
use Tests\TestCase;

class StaffVisitTest extends TestCase
{
    use DatabaseTransactions, MakesHomecareData;

    private function scheduledVisit(): array
    {
        $patientUser = $this->makePatientUser();
        $request = $this->makeSubmittedRequest($patientUser);

        $coordinator = $this->makeOperationalUser(UserRole::Coordinator);
        $staff = $this->makeStaff();

        app(\App\Actions\ApproveHomecareRequest::class)->execute($request, null, null, $coordinator);

        $appointment = app(ScheduleHomecare::class)->execute(
            $request,
            now()->setTime(9, 0),
            [$staff->id],
            'Bawa set ganti balutan.',
            $coordinator,
        );

        $request->refresh();

        return [$request->refresh(), $appointment, $staff];
    }

    public function test_assigned_staff_sees_the_visit_in_task_list(): void
    {
        [$request, $appointment, $staff] = $this->scheduledVisit();

        $this->actingAs($staff->user)->get(route('tugas.index'))
            ->assertOk()
            ->assertSee($request->patient->name);
    }

    public function test_visit_flow_depart_checkin_start_assess_complete(): void
    {
        [$request, $appointment, $staff] = $this->scheduledVisit();

        $this->actingAs($staff->user);

        // Berangkat
        $this->post(route('tugas.berangkat', $appointment))->assertSessionHas('success');
        $this->assertSame(AppointmentStatus::OnTheWay, $appointment->refresh()->status);

        // Check-in
        $this->post(route('tugas.checkin', $appointment), ['notes' => 'Diterima keluarga.']);
        $appointment->refresh();
        $this->assertSame(AppointmentStatus::CheckedIn, $appointment->status);
        $this->assertNotNull($appointment->checkin_at);

        // Mulai pelayanan
        $this->post(route('tugas.mulai', $appointment));
        $this->assertSame(AppointmentStatus::InService, $appointment->refresh()->status);

        // Asesmen
        $this->post(route('tugas.asesmen', $appointment), [
            'systolic_bp' => 130,
            'diastolic_bp' => 85,
            'pulse' => 80,
            'temperature_c' => 36.5,
            'oxygen_saturation' => 98,
            'findings' => 'Luka bersih, granulasi baik.',
        ])->assertSessionHas('success');
        $this->assertSame(1, $appointment->assessments()->count());

        // Check-out dengan dokumentasi
        $this->post(route('tugas.selesai', $appointment), [
            'actions_taken' => 'Mengganti balutan luka dengan teknik steril.',
            'results' => 'Luka bersih, tidak ada tanda infeksi.',
            'recommendations' => 'Jaga balutan tetap kering.',
            'follow_up_needed' => '1',
            'follow_up_notes' => 'Ganti balutan lagi 2 hari mendatang.',
        ])->assertRedirect(route('tugas.show', $appointment));

        $appointment->refresh();
        $this->assertSame(AppointmentStatus::Completed, $appointment->status);
        $this->assertNotNull($appointment->checkout_at);

        $record = ServiceRecord::where('appointment_id', $appointment->id)->firstOrFail();
        $this->assertTrue($record->follow_up_needed);

        // Request induk otomatis SELESAI.
        $this->assertSame(HomecareRequestStatus::Completed, $request->refresh()->status);
        $this->assertNotNull($request->completed_at);
    }

    public function test_complete_visit_requires_documentation(): void
    {
        [$request, $appointment, $staff] = $this->scheduledVisit();

        $this->actingAs($staff->user)
            ->post(route('tugas.selesai', $appointment), [])
            ->assertSessionHasErrors('actions_taken');

        $this->assertNotSame(AppointmentStatus::Completed, $appointment->refresh()->status);
    }

    public function test_unassigned_staff_cannot_execute_visit_actions(): void
    {
        [$request, $appointment, $assigned] = $this->scheduledVisit();

        $intruder = $this->makeStaff();

        $this->actingAs($intruder->user)
            ->post(route('tugas.berangkat', $appointment))
            ->assertSessionHas('error');

        $this->assertSame(AppointmentStatus::Scheduled, $appointment->refresh()->status);
    }

    public function test_staff_cannot_view_appointment_they_are_not_assigned_to(): void
    {
        [$request, $appointment, $assigned] = $this->scheduledVisit();

        $intruder = $this->makeStaff();

        $this->actingAs($intruder->user)
            ->get(route('tugas.show', $appointment))
            ->assertForbidden();
    }

    public function test_operational_can_monitor_any_appointment(): void
    {
        [$request, $appointment, $staff] = $this->scheduledVisit();

        $coordinator = $this->makeOperationalUser(UserRole::Coordinator);

        $this->actingAs($coordinator)
            ->get(route('operasional.jadwal.show', $appointment))
            ->assertOk()
            ->assertSee($request->patient->name);
    }
}
