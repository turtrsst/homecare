<?php

namespace Database\Seeders;

use App\Models\HomecareService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Katalog layanan & tarif mengacu pada Keputusan Direktur Utama RSUP Dr. Soeradji
 * Tirtonegoro Klaten No. HK.02.03/D.XXVI/5441/2024 tanggal 18 Maret 2024 tentang
 * Tarif Tindakan Pelayanan Home Care.
 *
 * Sumber data: database/data/sk_tarif_homecare.json (82 baris lampiran SK) —
 * file yang sama juga dipakai oleh scripts/generate-service-thumbnails.mjs.
 *
 * Jalankan: php artisan db:seed --class=HomecareServiceSeeder
 */
class HomecareServiceSeeder extends Seeder
{
    /**
     * Kode layanan demo lama yang sudah digantikan katalog SK.
     * Tidak dihapus agar pengajuan demo lama tetap menemukan layanannya,
     * hanya dinonaktifkan dari katalog publik dan pemesanan.
     */
    public const LEGACY_CODES = [
        'KUNJ-DOKTER',
        'RAWAT-LUKA',
        'SUNTIK-INFUS',
        'KATETER-NGT',
        'FISIOTERAPI',
        'TERAPI-WICARA',
        'KONSUL-GIZI',
        'PERAWAT-BAYI',
        'AMBIL-DARAH',
        'PENDAMPINGAN-LANSIA',
    ];

    public function run(): void
    {
        $payload = $this->payload();
        $labels = collect($payload['categories'])->pluck('label', 'key');

        $usedSlugs = [];
        $sortOrder = 0;

        foreach ($payload['services'] as $row) {
            $slug = Str::slug($row['name']);
            if (isset($usedSlugs[$slug])) {
                // Dua baris lampiran SK memakai nama yang sama persis.
                $slug = $slug.'-'.Str::slug($row['code']);
            }
            $usedSlugs[$slug] = true;

            HomecareService::updateOrCreate(
                ['code' => $row['code']],
                [
                    'name' => $row['name'],
                    'slug' => $slug,
                    'icon' => $row['icon'] ?? null,
                    'thumbnail' => 'images/services/'.$row['code'].'.svg',
                    'category' => $labels[$row['category']] ?? $row['category'],
                    'short_description' => $row['short_description'],
                    'description' => $row['description'] ?? $row['short_description'],
                    'duration_minutes' => $row['duration_minutes'] ?? 30,
                    'jasa_sarana' => $row['jasa_sarana'],
                    'jasa_pelayanan' => $row['jasa_pelayanan'],
                    'price' => $row['jasa_sarana'] + $row['jasa_pelayanan'],
                    'price_note' => $row['price_note'] ?? null,
                    'is_active' => $row['is_active'] ?? true,
                    'is_featured' => $row['is_featured'] ?? false,
                    'sort_order' => ++$sortOrder,
                ],
            );
        }

        HomecareService::whereIn('code', self::LEGACY_CODES)
            ->update(['is_active' => false, 'is_featured' => false]);
    }

    private function payload(): array
    {
        $path = database_path('data/sk_tarif_homecare.json');

        if (! is_file($path)) {
            throw new RuntimeException("File data tarif tidak ditemukan: {$path}");
        }

        $payload = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);

        if (empty($payload['services'])) {
            throw new RuntimeException('File data tarif tidak berisi layanan.');
        }

        return $payload;
    }
}
