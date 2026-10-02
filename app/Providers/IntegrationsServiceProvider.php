<?php

namespace App\Providers;

use App\Integrations\Contracts\MapsProvider;
use App\Integrations\Contracts\MessageSender;
use App\Integrations\Contracts\PatientDataProvider;
use App\Integrations\Contracts\PaymentGatewayProvider;
use App\Integrations\Exceptions\IntegrationNotConfiguredException;
use App\Integrations\Local\LocalPatientProvider;
use App\Integrations\Maps\NullMapsProvider;
use App\Integrations\Messaging\LogMessageSender;
use App\Integrations\Payment\ManualPaymentProvider;
use App\Integrations\Satusehat\SatusehatPatientProvider;
use App\Integrations\Simrs\SimrsPatientProvider;
use App\Models\ApiIntegration;
use Illuminate\Support\ServiceProvider;

/**
 * Binding kontrak integrasi eksternal berdasarkan konfigurasi.
 * Default standalone: semua provider lokal/null — tidak ada fake integration.
 */
class IntegrationsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PatientDataProvider::class, function () {
            return match (config('integrations.patient_provider', 'local')) {
                'simrs' => new SimrsPatientProvider($this->integration('simrs')),
                'satusehat' => new SatusehatPatientProvider($this->integration('satusehat')),
                default => new LocalPatientProvider,
            };
        });

        $this->app->bind(PaymentGatewayProvider::class, function () {
            return match (config('integrations.payment_provider', 'manual')) {
                default => new ManualPaymentProvider,
            };
        });

        $this->app->bind(MessageSender::class, function () {
            return match (config('integrations.message_sender', 'log')) {
                default => new LogMessageSender,
            };
        });

        $this->app->bind(MapsProvider::class, function () {
            return match (config('integrations.maps_provider', 'null')) {
                default => new NullMapsProvider,
            };
        });
    }

    private function integration(string $code): ?ApiIntegration
    {
        try {
            return ApiIntegration::query()->where('code', $code)->first();
        } catch (\Throwable) {
            // Tabel belum dimigrasi (mis. saat migrasi pertama) — anggap tidak aktif.
            return null;
        }
    }
}
