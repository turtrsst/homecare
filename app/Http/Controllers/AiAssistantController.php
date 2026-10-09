<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class AiAssistantController extends Controller
{
    /**
     * Display AI booking assistant page
     */
    public function show(): View
    {
        return view('ai-assistant.booking', [
            'services' => \App\Models\Service::active()->get(),
            'timeWindows' => config('homecare.time_windows'),
        ]);
    }

    /**
     * Process natural language booking request
     * Parses user input and returns structured booking data
     */
    public function parseBooking(Request $request)
    {
        $userInput = $request->input('message');

        // Parse intent dari user input
        $parsedData = $this->parseUserIntent($userInput);

        return response()->json([
            'success' => true,
            'data' => $parsedData,
            'nextStep' => 'confirm', // confirm atau proceed langsung ke booking
        ]);
    }

    /**
     * Parse user input menjadi structured booking data
     * Kebutuhan: service, tanggal, jam, lokasi, catatan
     */
    private function parseUserIntent(string $input): array
    {
        $services = \App\Models\Service::active()->pluck('name', 'id')->toArray();
        $result = [
            'service_id' => null,
            'service_name' => null,
            'preferred_date' => null,
            'preferred_time' => null,
            'location' => null,
            'notes' => $input,
            'confidence' => 0.5,
        ];

        // Deteksi service berdasarkan keywords
        $serviceKeywords = [
            'luka' => ['Perawatan Luka'],
            'dokter' => ['Konsultasi Dokter Umum'],
            'fisioterapi' => ['Fisioterapi'],
            'bidan' => ['Konsultasi Bidan'],
            'vaksin' => ['Vaksinasi'],
            'darah' => ['Pemeriksaan Darah'],
            'injeksi' => ['Injeksi'],
            'cateter' => ['Perawatan Kateter'],
        ];

        foreach ($serviceKeywords as $keyword => $serviceNames) {
            if (stripos($input, $keyword) !== false) {
                $matchedService = \App\Models\Service::whereIn('name', $serviceNames)
                    ->active()
                    ->first();
                if ($matchedService) {
                    $result['service_id'] = $matchedService->id;
                    $result['service_name'] = $matchedService->name;
                    $result['confidence'] += 0.25;
                }
                break;
            }
        }

        // Deteksi waktu (besok pagi, hari ini sore, dll)
        if (preg_match('/besok|tomorrow/i', $input)) {
            $result['preferred_date'] = now()->addDay()->format('Y-m-d');
            $result['confidence'] += 0.15;
        }

        if (preg_match('/pagi|morning|08|09|10|11/i', $input)) {
            $result['preferred_time'] = 'morning';
            $result['confidence'] += 0.1;
        } elseif (preg_match('/siang|sore|afternoon|12|13|14|15|16/i', $input)) {
            $result['preferred_time'] = 'afternoon';
            $result['confidence'] += 0.1;
        }

        // Deteksi lokasi
        if (preg_match('/(jl\.|jalan|jl)\s+([\w\s]+)/i', $input, $matches)) {
            $result['location'] = trim($matches[2]);
            $result['confidence'] += 0.1;
        } elseif (preg_match(/(klaten|jogja|yogya|yogyakarta|solo|semarang)/i, $input)) {
            $result['location'] = 'Klaten';
            $result['confidence'] += 0.05;
        }

        $result['confidence'] = min($result['confidence'], 1);

        return $result;
    }

    /**
     * Redirect ke booking wizard dengan data yang sudah di-parse
     */
    public function proceedToBooking(Request $request)
    {
        $validated = $request->validate([
            'service_id' => 'required|exists:services,id',
            'preferred_date' => 'required|date|after_or_equal:today',
            'preferred_time' => 'nullable|in:morning,midday,afternoon',
            'location' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        // Store ke session untuk dipakai booking wizard
        session([
            'ai_booking_data' => $validated,
        ]);

        // Redirect ke booking wizard step pertama (service)
        return redirect()->route('akun.pesan.step', 'pasien');
    }
}
