<?php

namespace App\Http\Controllers;

use App\Models\HomecareService;
use App\Services\Assistant\AiBookingAssistant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Halaman & endpoint "Sora" — AI Assistant pemesanan homecare.
 */
class AiAssistantController extends Controller
{
    public function __construct(private readonly AiBookingAssistant $assistant) {}

    public function show(Request $request): View
    {
        $draft = $this->assistant->draft($request);
        $suggestions = [
            'Saya butuh perawatan luka untuk ibu saya besok pagi di Klaten',
            'Ganti kateter untuk ayah, lusa jam 9 pagi',
            'Visite dokter umum hari Jumat sore',
            'Ambil darah dan cek gula darah, minggu depan siang',
        ];

        return view('assistant.index', [
            'draft' => $this->assistant->snapshot($request),
            'prefill' => Str::limit(trim((string) $request->query('tanya', '')), 300, ''),
            'isLoggedIn' => $request->user() !== null,
            'autoResume' => $request->user() !== null && ($draft['awaiting_login'] ?? false) === true,
            'suggestions' => $suggestions,
            'quickReplies' => array_map(fn (string $text) => ['label' => Str::limit($text, 40), 'text' => $text], array_slice($suggestions, 0, 3)),
            'services' => HomecareService::query()->active()->ordered()->limit(12)->get(['id', 'name']),
        ]);
    }

    public function message(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'min:2', 'max:1000'],
        ], [
            'message.required' => 'Silakan ketik kebutuhan Anda terlebih dahulu.',
            'message.max' => 'Pesan terlalu panjang (maksimal 1000 karakter).',
        ]);

        return response()->json($this->assistant->handle($request, $validated['message']));
    }

    public function reset(Request $request): RedirectResponse|JsonResponse
    {
        $this->assistant->reset($request);

        if ($request->expectsJson()) {
            return response()->json(['status' => 'reset']);
        }

        return redirect()->route('ai-assistant')->with('success', 'Percakapan dimulai ulang.');
    }
}
