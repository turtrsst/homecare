<?php

namespace App\Services;

use App\Enums\ContactType;
use App\Integrations\Contracts\PatientDataProvider;
use App\Models\PatientAddress;
use App\Models\PatientContact;
use App\Models\PatientProfile;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Pengelolaan data pasien milik sebuah akun (pasien/keluarga).
 */
class PatientService
{
    public function __construct(
        private readonly AuditService $audit,
        private readonly PatientDataProvider $patientDataProvider,
    ) {}

    /** @return \Illuminate\Database\Eloquent\Collection<int, PatientProfile> */
    public function profilesFor(User $user)
    {
        return $user->patientProfiles()
            ->with(['addresses', 'contacts'])
            ->orderBy('name')
            ->get();
    }

    public function findOwnedProfile(User $user, int $id): ?PatientProfile
    {
        return PatientProfile::query()
            ->with(['addresses', 'contacts'])
            ->where('user_id', $user->id)
            ->find($id);
    }

    /**
     * @param  array<string, mixed>  $data  data tervalidasi Form Request
     */
    public function createProfile(User $user, array $data, ?string $phone = null): PatientProfile
    {
        return DB::transaction(function () use ($user, $data, $phone) {
            /** @var PatientProfile $profile */
            $profile = $user->patientProfiles()->create($data);

            $primaryPhone = $phone ?: $user->phone;
            if ($primaryPhone) {
                $profile->contacts()->create([
                    'type' => ContactType::Phone,
                    'value' => $primaryPhone,
                    'label' => 'Kontak utama',
                    'is_primary' => true,
                ]);
            }

            $this->audit->log('CREATED_PATIENT_PROFILE', $profile, "Profil pasien dibuat: {$profile->name}", user: $user);

            return $profile->load('contacts');
        });
    }

    /** @param array<string, mixed> $data */
    public function updateProfile(User $user, PatientProfile $profile, array $data): PatientProfile
    {
        $profile->update($data);
        $this->audit->log('UPDATED_PATIENT_PROFILE', $profile, "Profil pasien diperbarui: {$profile->name}", user: $user);

        return $profile->refresh();
    }

    /** @param array<string, mixed> $data */
    public function addAddress(User $user, PatientProfile $profile, array $data): PatientAddress
    {
        $isFirst = $profile->addresses()->count() === 0;
        $data['is_primary'] = $isFirst || ($data['is_primary'] ?? false);

        $address = $profile->addresses()->create($data);

        if ($address->is_primary) {
            $profile->addresses()->whereKeyNot($address->id)->update(['is_primary' => false]);
        }

        $this->audit->log('CREATED_PATIENT_ADDRESS', $address, 'Alamat pasien ditambahkan', user: $user);

        return $address;
    }

    /** @param array<string, mixed> $data */
    public function updateAddress(User $user, PatientAddress $address, array $data): PatientAddress
    {
        $address->update($data);

        if ($address->is_primary) {
            $address->patientProfile->addresses()
                ->whereKeyNot($address->id)
                ->update(['is_primary' => false]);
        }

        $this->audit->log('UPDATED_PATIENT_ADDRESS', $address, 'Alamat pasien diperbarui', user: $user);

        return $address->refresh();
    }

    /**
     * Cari pasien dari provider eksternal (mis. SIMRS) — untuk masa depan.
     *
     * @return array<string, mixed>|null
     */
    public function lookupExternal(string $identifier): ?array
    {
        return $this->patientDataProvider->findByIdentifier($identifier);
    }
}
