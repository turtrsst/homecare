<?php

namespace Tests\Feature;

use App\Models\HomecareRequest;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Support\MakesHomecareData;
use Tests\TestCase;

class BookingWizardTest extends TestCase
{
    use DatabaseTransactions, MakesHomecareData;

    private function walkWizard($user, array $overrides = []): void
    {
        $profile = $user->patientProfiles()->firstOrFail();
        $address = $profile->addresses()->firstOrFail();
        $service = $overrides['service'] ?? $this->makeService();

        $this->actingAs($user);

        // 1. pasien
        $this->post(route('akun.pesan.save', 'pasien'), [
            'patient_profile_id' => $profile->id,
        ])->assertRedirect(route('akun.pesan.step', 'layanan'));

        // 2. layanan
        $this->post(route('akun.pesan.save', 'layanan'), [
            'service_ids' => [$service->id],
        ])->assertRedirect(route('akun.pesan.step', 'kebutuhan'));

        // 3. kebutuhan
        $this->post(route('akun.pesan.save', 'kebutuhan'), [
            'complaint' => 'Ibu butuh ganti balutan luka operasi setiap hari selama seminggu.',
            'notes' => 'Riwayat diabetes.',
        ])->assertRedirect(route('akun.pesan.step', 'lokasi'));

        // 4. lokasi
        $this->post(route('akun.pesan.save', 'lokasi'), [
            'patient_address_id' => $address->id,
        ])->assertRedirect(route('akun.pesan.step', 'jadwal'));

        // 5. jadwal
        $this->post(route('akun.pesan.save', 'jadwal'), [
            'preferred_date' => now()->addDays(2)->toDateString(),
            'preferred_time_window' => 'morning',
        ])->assertRedirect(route('akun.pesan.step', 'review'));

        // 6. review & konfirmasi dapat dirender
        $this->get(route('akun.pesan.step', 'review'))->assertOk();
        $this->get(route('akun.pesan.step', 'konfirmasi'))->assertOk();
    }

    public function test_patient_can_complete_the_full_booking_wizard(): void
    {
        $user = $this->makePatientUser();

        $this->walkWizard($user);

        $response = $this->post(route('akun.pesan.submit'));

        $request = HomecareRequest::firstOrFail();

        $response->assertRedirect(route('akun.pengajuan.show', $request->code));

        $this->assertSame('submitted', $request->status->value);
        $this->assertMatchesRegularExpression('/^HC-\d{6}-\d{4}$/', $request->code);
        $this->assertSame(1, $request->items()->count());
        $this->assertGreaterThan(0, (float) $request->total_amount);
        $this->assertNotNull($request->submitted_at);

        // Sesi wizard dibersihkan setelah submit.
        $this->assertEmpty(session('booking'));
    }

    public function test_cannot_submit_wizard_before_completing_steps(): void
    {
        $user = $this->makePatientUser();

        $this->actingAs($user)->post(route('akun.pesan.submit'))
            ->assertRedirect(route('akun.pesan.step', 'pasien'))
            ->assertSessionHas('error');

        $this->assertSame(0, HomecareRequest::count());
    }

    public function test_wizard_rejects_invalid_schedule(): void
    {
        $user = $this->makePatientUser();
        $profile = $user->patientProfiles()->firstOrFail();
        $service = $this->makeService();

        $this->actingAs($user);
        session(['booking' => ['patient_profile_id' => $profile->id, 'service_ids' => [$service->id]]]);

        // Tanggal kemarin → ditolak validasi.
        $this->post(route('akun.pesan.save', 'jadwal'), [
            'preferred_date' => now()->subDay()->toDateString(),
            'preferred_time_window' => 'morning',
        ])->assertSessionHasErrors('preferred_date');
    }

    public function test_wizard_reset_clears_session(): void
    {
        $user = $this->makePatientUser();

        $this->actingAs($user);
        session(['booking' => ['complaint' => 'sesuatu']]);

        $this->post(route('akun.pesan.reset'))
            ->assertRedirect(route('akun.pesan.step', 'pasien'));

        $this->assertEmpty(session('booking'));
    }
}
