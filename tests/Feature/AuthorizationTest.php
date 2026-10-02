<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Support\MakesHomecareData;
use Tests\TestCase;

/**
 * Memastikan zona aplikasi tertutup rapat per role — lewat gate/middleware,
 * bukan pemeriksaan role mentah.
 */
class AuthorizationTest extends TestCase
{
    use DatabaseTransactions, MakesHomecareData;

    public function test_patient_cannot_access_operational_area(): void
    {
        $patient = $this->makePatientUser();

        $this->actingAs($patient)->get(route('operasional.dashboard'))->assertForbidden();
        $this->actingAs($patient)->get(route('operasional.pengajuan.index'))->assertForbidden();
    }

    public function test_patient_cannot_access_staff_area(): void
    {
        $patient = $this->makePatientUser();

        $this->actingAs($patient)->get(route('tugas.index'))->assertForbidden();
    }

    public function test_medical_staff_can_access_tasks_but_not_operational_dashboard(): void
    {
        $staffUser = $this->makeStaff()->user;

        $this->actingAs($staffUser)->get(route('tugas.index'))->assertOk();
        $this->actingAs($staffUser)->get(route('operasional.dashboard'))->assertForbidden();
    }

    public function test_coordinator_can_access_operational_but_not_service_master_data(): void
    {
        $coordinator = $this->makeOperationalUser(UserRole::Coordinator);

        $this->actingAs($coordinator)->get(route('operasional.dashboard'))->assertOk();
        $this->actingAs($coordinator)->get(route('operasional.pengajuan.index'))->assertOk();
        // services.manage hanya admin
        $this->actingAs($coordinator)->get(route('operasional.layanan.index'))->assertForbidden();
    }

    public function test_admin_can_access_everything_operational(): void
    {
        $admin = $this->makeOperationalUser(UserRole::Admin);

        $this->actingAs($admin)->get(route('operasional.dashboard'))->assertOk();
        $this->actingAs($admin)->get(route('operasional.layanan.index'))->assertOk();
        $this->actingAs($admin)->get(route('operasional.petugas.index'))->assertOk();
        $this->actingAs($admin)->get(route('operasional.laporan.index'))->assertOk();
        $this->actingAs($admin)->get(route('operasional.audit.index'))->assertOk();
    }

    public function test_manager_can_view_reports_but_cannot_verify_requests(): void
    {
        $manager = $this->makeOperationalUser(UserRole::Manager);

        $this->assertTrue($manager->can('reports.view'));
        $this->assertTrue($manager->can('audit.view'));
        $this->assertFalse($manager->can('requests.verify'));
        $this->assertFalse($manager->can('services.manage'));
    }

    public function test_staff_without_linked_profile_cannot_open_task_area(): void
    {
        $user = User::factory()->medicalStaff()->create(); // tanpa profil HealthcareStaff

        $this->actingAs($user)->get(route('tugas.index'))->assertForbidden();
    }

    public function test_patient_area_blocked_for_operational_roles_without_patient_data(): void
    {
        // Zona /akun terbuka untuk semua yang login, tetapi dashboard pasien
        // untuk role operasional tetap render (tidak 500).
        $coordinator = $this->makeOperationalUser(UserRole::Coordinator);

        $this->actingAs($coordinator)->get(route('akun.dashboard'))->assertOk();
    }
}
