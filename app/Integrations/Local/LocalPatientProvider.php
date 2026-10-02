<?php

namespace App\Integrations\Local;

use App\Integrations\Contracts\PatientDataProvider;
use App\Models\PatientProfile;

/**
 * Provider aktif untuk aplikasi standalone: database lokal adalah
 * source of truth data pasien.
 */
class LocalPatientProvider implements PatientDataProvider
{
    public function findByIdentifier(string $identifier): ?array
    {
        $patient = PatientProfile::query()
            ->with(['addresses', 'contacts'])
            ->where('nik', $identifier)
            ->first();

        if (! $patient) {
            return null;
        }

        return $this->normalize($patient);
    }

    public function name(): string
    {
        return 'local';
    }

    /** @return array<string, mixed> */
    private function normalize(PatientProfile $patient): array
    {
        return [
            'source' => $this->name(),
            'external_id' => (string) $patient->id,
            'name' => $patient->name,
            'nik' => $patient->nik,
            'gender' => $patient->gender?->value,
            'birth_date' => $patient->birth_date?->toDateString(),
            'phone' => $patient->contacts->firstWhere('is_primary', true)?->value
                ?? $patient->contacts->first()?->value,
            'address' => $patient->primaryAddress()?->oneLine(),
        ];
    }
}
