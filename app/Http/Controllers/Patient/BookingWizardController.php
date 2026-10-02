<?php

namespace App\Http\Controllers\Patient;

use App\Actions\SubmitHomecareRequest;
use App\Http\Controllers\Controller;
use App\Http\Requests\Booking\StepAddressRequest;
use App\Http\Requests\Booking\StepNeedRequest;
use App\Http\Requests\Booking\StepPatientRequest;
use App\Http\Requests\Booking\StepScheduleRequest;
use App\Http\Requests\Booking\StepServiceRequest;
use App\Models\HomecareService;
use App\Models\PatientAddress;
use App\Models\PatientProfile;
use App\Services\AttachmentService;
use App\Services\HomecareCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\View\View;

/**
 * Wizard booking 7 langkah. State disimpan di session sehingga input tidak
 * hilang saat validasi gagal atau pengguna berpindah langkah.
 */
class BookingWizardController extends Controller
{
    public const STEPS = ['pasien', 'layanan', 'kebutuhan', 'lokasi', 'jadwal', 'review', 'konfirmasi'];

    public const STEP_TITLES = [
        'pasien' => 'Untuk siapa?',
        'layanan' => 'Layanan apa yang dibutuhkan?',
        'kebutuhan' => 'Ceritakan kondisinya',
        'lokasi' => 'Kunjungi ke mana?',
        'jadwal' => 'Kapan petugas datang?',
        'review' => 'Periksa kembali',
        'konfirmasi' => 'Kirim pengajuan',
    ];

    private const STEP_REQUESTS = [
        'pasien' => StepPatientRequest::class,
        'layanan' => StepServiceRequest::class,
        'kebutuhan' => StepNeedRequest::class,
        'lokasi' => StepAddressRequest::class,
        'jadwal' => StepScheduleRequest::class,
    ];

    public function __construct(
        private readonly HomecareCatalog $catalog,
        private readonly SubmitHomecareRequest $submitAction,
        private readonly AttachmentService $attachments,
    ) {}

    public function show(Request $request, ?string $step = null): View|RedirectResponse
    {
        $step = $step ?? $this->currentStep($request);

        // Lindungi urutan: jangan izinkan melompat bila data sebelumnya belum ada.
        $missing = $this->firstMissingStep($request);
        if ($missing && array_search($step, self::STEPS, true) > array_search($missing, self::STEPS, true)) {
            return redirect()->route('akun.pesan.step', $missing);
        }

        return view('patient.booking.'.$step, $this->viewModel($request, $step));
    }

    public function save(Request $request, string $step): RedirectResponse
    {
        $formRequestClass = self::STEP_REQUESTS[$step] ?? null;

        if ($formRequestClass === null) {
            return redirect()->route('akun.pesan.step', 'review');
        }

        /** @var \Illuminate\Foundation\Http\FormRequest $formRequest */
        // Resolusi dari container otomatis menjalankan authorize() + validasi
        // (ValidatesWhenResolved); gagal → ValidationException → redirect back.
        $formRequest = app($formRequestClass);

        $validated = $formRequest->validated();

        // Berkas tidak boleh masuk ke session (UploadedFile tak dapat diserialisasi);
        // simpan dulu ke disk lalu simpan daftarnya saja.
        $files = $validated['files'] ?? [];
        $collection = (string) ($validated['collection'] ?? 'other');
        unset($validated['files'], $validated['collection']);

        $booking = $request->session()->get('booking', []);

        // Bila pasien berganti, alamat lama bisa jadi tidak valid lagi.
        if ($step === 'pasien' && ($booking['patient_profile_id'] ?? null) !== $validated['patient_profile_id']) {
            unset($booking['patient_address_id']);
        }

        if (is_array($files) && $files !== [] && $request->user() !== null) {
            $user = $request->user();
            $remaining = max(0, 5 - count($booking['draft_attachments'] ?? []));

            $drafts = array_slice(array_values(array_filter(array_map(
                fn ($file) => $file instanceof UploadedFile
                    ? $this->attachments->storeDraft($file, $user, $collection)
                    : null,
                $files,
            ))), 0, $remaining);

            if ($drafts !== []) {
                $booking['draft_attachments'] = array_merge($booking['draft_attachments'] ?? [], $drafts);
            }
        }

        $request->session()->put('booking', array_merge($booking, $validated));

        $next = self::STEPS[array_search($step, self::STEPS, true) + 1] ?? 'review';

        return redirect()->route('akun.pesan.step', $next);
    }

    public function submit(Request $request): RedirectResponse
    {
        $missing = $this->firstMissingStep($request);

        if ($missing) {
            return redirect()->route('akun.pesan.step', $missing)
                ->with('error', 'Lengkapi dulu langkah «'.self::STEP_TITLES[$missing].'» ya.');
        }

        $booking = $request->session()->get('booking', []);

        $services = collect($booking['service_ids'])
            ->map(fn (int $id) => ['homecare_service_id' => $id, 'quantity' => 1])
            ->all();

        $homecareRequest = $this->submitAction->execute($request->user(), [
            'patient_profile_id' => $booking['patient_profile_id'],
            'patient_address_id' => $booking['patient_address_id'],
            'complaint' => $booking['complaint'] ?? null,
            'notes' => $booking['notes'] ?? null,
            'preferred_date' => $booking['preferred_date'],
            'preferred_time_window' => $booking['preferred_time_window'],
            'services' => $services,
        ]);

        // Lampiran draft baru dipindahkan ke lokasi permanen setelah pengajuan ada.
        $this->attachments->promoteDrafts($homecareRequest, $booking['draft_attachments'] ?? [], $request->user());

        $request->session()->forget('booking');

        return redirect()->route('akun.pengajuan.show', $homecareRequest->code)
            ->with('success', 'Pengajuan berhasil dikirim! Tim kami akan memverifikasi segera. Kode pengajuan Anda: '.$homecareRequest->code);
    }

    public function removeDraft(Request $request, int $index): RedirectResponse
    {
        $booking = $request->session()->get('booking', []);
        $drafts = $booking['draft_attachments'] ?? [];

        if (! isset($drafts[$index]) || $request->user() === null) {
            return back()->with('error', 'Lampiran tidak ditemukan.');
        }

        $draft = $drafts[$index];
        $filesystem = \Illuminate\Support\Facades\Storage::disk(config('homecare.uploads.disk', 'private'));
        $prefix = 'booking-drafts/'.$request->user()->id.'/';
        $path = is_array($draft) ? (string) ($draft['path'] ?? '') : '';

        if ($path !== '' && str_starts_with($path, $prefix)) {
            $filesystem->delete($path);
        }

        unset($drafts[$index]);
        $booking['draft_attachments'] = array_values($drafts);
        $request->session()->put('booking', $booking);

        return back()->with('success', 'Lampiran dihapus.');
    }

    public function reset(Request $request): RedirectResponse
    {
        $this->attachments->discardDrafts($request->user());

        $request->session()->forget('booking');

        return redirect()->route('akun.pesan.step', 'pasien')
            ->with('success', 'Formulir pemesanan dikosongkan.');
    }

    /* ------------------------------------------------------------------ */

    /** @return array<string, mixed> */
    private function viewModel(Request $request, string $step): array
    {
        $booking = $request->session()->get('booking', []);
        $user = $request->user();

        $base = [
            'step' => $step,
            'steps' => self::STEPS,
            'stepTitles' => self::STEP_TITLES,
            'stepIndex' => array_search($step, self::STEPS, true) + 1,
            'booking' => $booking,
        ];

        $patient = isset($booking['patient_profile_id'])
            ? PatientProfile::ownedBy($user)->with('addresses')->find($booking['patient_profile_id'])
            : null;

        return match ($step) {
            'pasien' => $base + [
                'patients' => $user->patientProfiles()->with('addresses')->orderBy('name')->get(),
            ],
            'layanan' => $base + [
                'services' => $this->catalog->activeServices()->groupBy('category'),
            ],
            'kebutuhan' => $base,
            'lokasi' => $base + [
                'patient' => $patient,
                'addresses' => $patient?->addresses ?? collect(),
            ],
            'jadwal' => $base + [
                'timeWindows' => config('homecare.time_windows'),
                'minDate' => now()->addDays((int) config('homecare.min_lead_days', 1))->toDateString(),
                'maxDate' => now()->addDays((int) config('homecare.max_advance_days', 30))->toDateString(),
            ],
            'review', 'konfirmasi' => $base + [
                'summary' => $this->summary($booking, $user),
            ],
            default => $base,
        };
    }

    /** @param array<string, mixed> $booking */
    private function summary(array $booking, $user): array
    {
        $patient = PatientProfile::ownedBy($user)->find($booking['patient_profile_id'] ?? 0);
        $address = PatientAddress::find($booking['patient_address_id'] ?? 0);
        $services = HomecareService::query()
            ->whereIn('id', $booking['service_ids'] ?? [])
            ->get();

        return [
            'patient' => $patient,
            'address' => $address,
            'services' => $services,
            'total' => $services->sum(fn (HomecareService $s) => (float) $s->price),
        ];
    }

    private function currentStep(Request $request): string
    {
        return $this->firstMissingStep($request) ?? 'review';
    }

    /** Langkah pertama yang datanya belum lengkap di session. */
    private function firstMissingStep(Request $request): ?string
    {
        $booking = $request->session()->get('booking', []);

        $required = [
            'pasien' => ['patient_profile_id'],
            'layanan' => ['service_ids'],
            'kebutuhan' => ['complaint'],
            'lokasi' => ['patient_address_id'],
            'jadwal' => ['preferred_date', 'preferred_time_window'],
        ];

        foreach ($required as $step => $keys) {
            foreach ($keys as $key) {
                if (blank($booking[$key] ?? null)) {
                    return $step;
                }
            }
        }

        return null;
    }
}
