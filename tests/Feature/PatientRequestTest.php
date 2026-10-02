<?php

namespace Tests\Feature;

use App\Enums\HomecareRequestStatus;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Support\MakesHomecareData;
use Tests\TestCase;

class PatientRequestTest extends TestCase
{
    use DatabaseTransactions, MakesHomecareData;

    public function test_patient_sees_own_requests_only(): void
    {
        $me = $this->makePatientUser();
        $other = $this->makePatientUser();

        $mine = $this->makeSubmittedRequest($me);
        $theirs = $this->makeSubmittedRequest($other);

        $this->actingAs($me)->get(route('akun.pengajuan.index'))
            ->assertOk()
            ->assertSee($mine->code)
            ->assertDontSee($theirs->code);
    }

    public function test_patient_can_view_own_request_detail_with_timeline(): void
    {
        $user = $this->makePatientUser();
        $request = $this->makeSubmittedRequest($user);

        $this->actingAs($user)->get(route('akun.pengajuan.show', $request->code))
            ->assertOk()
            ->assertSee($request->code)
            ->assertSee('Perkembangan Pengajuan', false);
    }

    public function test_patient_cannot_view_someone_elses_request(): void
    {
        $owner = $this->makePatientUser();
        $intruder = $this->makePatientUser();
        $request = $this->makeSubmittedRequest($owner);

        $this->actingAs($intruder)
            ->get(route('akun.pengajuan.show', $request->code))
            ->assertForbidden();
    }

    public function test_patient_can_cancel_a_submitted_request(): void
    {
        $user = $this->makePatientUser();
        $request = $this->makeSubmittedRequest($user);

        $this->actingAs($user)->post(route('akun.pengajuan.batal', $request->code), [
            'reason' => 'Pasien sudah membaik, tidak jadi pesan.',
        ])->assertRedirect();

        $request->refresh();

        $this->assertSame(HomecareRequestStatus::Cancelled, $request->status);
        $this->assertNotNull($request->cancelled_at);
    }

    public function test_patient_cannot_cancel_a_completed_request(): void
    {
        $user = $this->makePatientUser();
        $request = $this->makeSubmittedRequest($user);
        $request->update(['status' => HomecareRequestStatus::Completed, 'completed_at' => now()]);

        // Policy menolak (403) karena pengajuan sudah berstatus akhir.
        $this->actingAs($user)->post(route('akun.pengajuan.batal', $request->code), [
            'reason' => 'Terlambat membatalkan.',
        ])->assertForbidden();

        $this->assertSame(HomecareRequestStatus::Completed, $request->refresh()->status);
    }

    public function test_patient_can_answer_information_request(): void
    {
        $user = $this->makePatientUser();
        $request = $this->makeSubmittedRequest($user);
        $request->update([
            'status' => HomecareRequestStatus::NeedInformation,
            'information_request' => 'Mohon kirim foto resep.',
        ]);

        $this->actingAs($user)->post(route('akun.pengajuan.informasi', $request->code), [
            'information_response' => 'Foto resep sudah diunggah, dosis insulin 10 unit.',
        ])->assertRedirect();

        $request->refresh();

        $this->assertSame('Foto resep sudah diunggah, dosis insulin 10 unit.', $request->information_response);
        $this->assertSame(HomecareRequestStatus::Submitted, $request->status);
    }
}
