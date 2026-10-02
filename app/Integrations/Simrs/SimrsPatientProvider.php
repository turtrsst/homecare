<?php

namespace App\Integrations\Simrs;

use App\Integrations\Contracts\PatientDataProvider;
use App\Integrations\Exceptions\IntegrationNotConfiguredException;
use App\Models\ApiIntegration;

/**
 * Stub integrasi SIMRS — BELUM aktif.
 *
 * Diaktifkan bila rumah sakit menyediakan API SIMRS:
 *  1. Isi baris `api_integrations` (code=simrs): base_url + settings (token).
 *  2. Set INTEGRATION_PATIENT_PROVIDER=simrs.
 *  3. Implementasikan pemanggilan HTTP di bawah (gunakan Http::withToken(...)).
 *
 * Sampai saat itu, provider ini melempar IntegrationNotConfiguredException
 * sehingga tidak ada "fake integration" yang terlihat hidup.
 */
class SimrsPatientProvider implements PatientDataProvider
{
    public function __construct(private readonly ?ApiIntegration $integration = null) {}

    public function findByIdentifier(string $identifier): ?array
    {
        if (! $this->integration || ! $this->integration->is_active || ! $this->integration->base_url) {
            throw IntegrationNotConfiguredException::for('SIMRS');
        }

        // TODO: panggil API SIMRS, normalisasi hasil ke format PatientDataProvider.
        // $response = Http::withToken($this->integration->setting('token'))
        //     ->get($this->integration->base_url.'/patients', ['nik' => $identifier]);

        throw IntegrationNotConfiguredException::for('SIMRS', 'Implementasi pemanggilan API belum tersedia.');
    }

    public function name(): string
    {
        return 'simrs';
    }
}
