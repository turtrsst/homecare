<?php

namespace Tests\Support;

use App\Enums\ContactType;
use App\Enums\Gender;
use App\Enums\Profession;
use App\Enums\TimeWindow;
use App\Enums\UserRole;
use App\Models\HealthcareStaff;
use App\Models\HomecareRequest;
use App\Models\HomecareService;
use App\Models\PatientAddress;
use App\Models\PatientContact;
use App\Models\PatientProfile;
use App\Models\User;

/**
 * Pembantu pembuatan data untuk test.
 */
trait MakesHomecareData
{
    protected function makePatientUser(): User
    {
        $user = User::factory()->patient()->create();

        $profile = PatientProfile::create([
            'user_id' => $user->id,
            'name' => 'Pasien Uji '.$user->id,
            'relationship' => 'diri_sendiri',
            'gender' => Gender::Female,
            'birth_date' => '1970-01-01',
        ]);

        PatientContact::create([
            'patient_profile_id' => $profile->id,
            'type' => ContactType::Phone,
            'value' => '081200000000',
            'is_primary' => true,
        ]);

        PatientAddress::create([
            'patient_profile_id' => $profile->id,
            'label' => 'Rumah',
            'recipient_name' => $profile->name,
            'phone' => '081200000000',
            'address_line' => 'Jl. Uji Coba No. 1, RT 01/RW 02, Kelurahan Uji',
            'city' => 'Klaten',
            'province' => 'Jawa Tengah',
            'postal_code' => '57411',
            'is_primary' => true,
        ]);

        return $user;
    }

    protected function makeOperationalUser(UserRole $role): User
    {
        $state = match ($role) {
            UserRole::Admin => 'admin',
            UserRole::Coordinator => 'coordinator',
            UserRole::Manager => 'manager',
            UserRole::MedicalStaff => 'medicalStaff',
            default => 'patient',
        };

        return User::factory()->{$state}()->create();
    }

    protected function makeStaff(?User $user = null, Profession $profession = Profession::Nurse): HealthcareStaff
    {
        $user ??= User::factory()->medicalStaff()->create();

        return HealthcareStaff::create([
            'user_id' => $user->id,
            'name' => 'Nakes Uji '.uniqid(),
            'profession' => $profession,
            'license_number' => 'STR-UJI-'.random_int(1000, 9999),
            'specialization' => 'Umum',
            'phone' => '0811000000'.random_int(10, 99),
            'email' => 'nakes'.random_int(1000, 9999).'@homecare.rs',
            'is_active' => true,
        ]);
    }

    protected function makeService(array $overrides = []): HomecareService
    {
        static $seq = 0;
        $seq++;

        return HomecareService::create(array_merge([
            'code' => 'UJI-'.$seq.'-'.random_int(100, 999),
            'name' => 'Layanan Uji '.$seq,
            'slug' => 'layanan-uji-'.$seq.'-'.random_int(100, 999),
            'category' => 'Uji',
            'short_description' => 'Layanan untuk kebutuhan pengujian.',
            'description' => 'Deskripsi layanan uji yang cukup panjang untuk kebutuhan tampilan.',
            'duration_minutes' => 45,
            'price' => 150000,
            'is_active' => true,
            'is_featured' => false,
            'sort_order' => $seq,
        ], $overrides));
    }

    protected function makeSubmittedRequest(User $user, ?HomecareService $service = null): HomecareRequest
    {
        $service ??= $this->makeService();

        $profile = $user->patientProfiles()->firstOrFail();
        $address = $profile->addresses()->firstOrFail();

        return app(\App\Actions\SubmitHomecareRequest::class)->execute($user, [
            'patient_profile_id' => $profile->id,
            'patient_address_id' => $address->id,
            'complaint' => 'Butuh perawatan luka pasca operasi untuk pengujian alur.',
            'notes' => null,
            'preferred_date' => now()->addDays(2)->toDateString(),
            'preferred_time_window' => TimeWindow::Morning,
            'services' => [
                ['homecare_service_id' => $service->id, 'quantity' => 1],
            ],
        ]);
    }
}
