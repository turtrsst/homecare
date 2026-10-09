<?php

namespace App\Services;

use App\Models\HomecareService;
use App\Models\User;
use Illuminate\Support\Carbon;

class AiQuickBookingAssistant
{
    /**
     * Parse kebutuhan pasien dari kalimat natural language lalu kembalikan draft booking.
     *
     * @return array{service_ids: array<int>, preferred_date: string, preferred_time_window: string, complaint: string}
     */
    public function parse(string $message, ?User $user = null): array
    {
        $normalized = trim($message);
        $lower = mb_strtolower($normalized);

        $services = HomecareService::query()
            ->active()
            ->ordered()
            ->get();

        $serviceIds = [];

        foreach ($services as $service) {
            $haystack = mb_strtolower($service->name.' '.$service->category.' '.($service->description ?? '').' '.($service->short_description ?? ''));
            if (str_contains($haystack, $lower) || str_contains($lower, $haystack)) {
                $serviceIds[] = (int) $service->id;
                continue;
            }

            foreach ($this->keywordMap() as $keyword => $target) {
                if (str_contains($lower, $keyword) && str_contains($haystack, $target)) {
                    $serviceIds[] = (int) $service->id;
                    break;
                }
            }
        }

        if ($serviceIds === []) {
            $candidate = $services->first();
            if ($candidate) {
                $serviceIds[] = (int) $candidate->id;
            }
        }

        $preferredDate = $this->detectPreferredDate($lower);
        $preferredWindow = $this->detectPreferredWindow($lower);

        return [
            'service_ids' => array_values(array_unique($serviceIds)),
            'preferred_date' => $preferredDate,
            'preferred_time_window' => $preferredWindow,
            'complaint' => $normalized,
        ];
    }

    private function keywordMap(): array
    {
        return [
            'luka' => 'luka',
            'perawatan luka' => 'luka',
            'bidan' => 'bidan',
            'dokter' => 'dokter',
            'dokter umum' => 'dokter',
            'dokter keluarga' => 'dokter',
            'fisioterapi' => 'fisioterapi',
            'terapi' => 'fisioterapi',
            'perawat' => 'perawat',
            'home visit' => 'home',
            'suntik' => 'suntik',
            'infus' => 'infus',
            'darah' => 'darah',
            'vaksin' => 'vaksin',
            'cek gula' => 'gula',
            'tekanan darah' => 'tekanan',
        ];
    }

    private function detectPreferredDate(string $lower): string
    {
        $date = Carbon::now();

        if (str_contains($lower, 'hari ini')) {
            return $date->toDateString();
        }

        if (str_contains($lower, 'besok')) {
            return $date->copy()->addDay()->toDateString();
        }

        if (str_contains($lower, 'minggu depan')) {
            return $date->copy()->addWeek()->toDateString();
        }

        if (str_contains($lower, 'lusa')) {
            return $date->copy()->addDays(2)->toDateString();
        }

        if (preg_match('/\b(\d{1,2})\s*(?:jan|feb|mar|apr|mei|jun|jul|agu|sep|okt|nov|des)\b/i', $lower, $match)) {
            $day = (int) $match[1];
            $date = Carbon::createFromFormat('Y-m-d', now()->format('Y').'-01-01')->day($day);
            return $date->toDateString();
        }

        return $date->toDateString();
    }

    private function detectPreferredWindow(string $lower): string
    {
        if (str_contains($lower, 'siang')) {
            return 'midday';
        }

        if (str_contains($lower, 'sore')) {
            return 'afternoon';
        }

        return 'morning';
    }
}

