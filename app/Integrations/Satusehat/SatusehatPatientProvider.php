<?php

namespace App\Integrations\Satusehat;

use App\Integrations\Contracts\PatientDataProvider;
use App\Integrations\Exceptions\IntegrationNotConfiguredException;
use App\Models\ApiIntegration;

/**
 * Stub integrasi SATUSEHAT — BELUM aktif.
 *
 * Rencana implementasi (saat kredensial tersedia):
 *  - OAuth2 client credentials ke platform SATUSEHAT.
 *  - FHIR Patient search by identifier (NIK).
 *  - Simpan mapping di `external_references`.
 */
class SatusehatPatientProvider implements PatientDataProvider
{
    public function __construct(private readonly ?ApiIntegration $integration = null) {}

    public function findByIdentifier(string $identifier): ?array
    {
        if (! $this->integration || ! $this->integration->is_active || ! $this->integration->base_url) {
            throw IntegrationNotConfiguredException::for('SATUSEHAT');
        }

        throw IntegrationNotConfiguredException::for('SATUSEHAT', 'Implementasi pemanggilan API belum tersedia.');
    }

    public function name(): string
    {
        return 'satusehat';
    }
}
