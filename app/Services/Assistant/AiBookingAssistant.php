<?php

namespace App\Services\Assistant;

use App\Actions\SubmitHomecareRequest;
use App\Models\HomecareService;
use App\Models\PatientAddress;
use App\Models\PatientProfile;
use App\Models\User;
use App\Services\HomecareCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Throwable;

/**
 * Asisten pemesanan "ketik sekali": setiap pesan pengguna digabung ke dalam
 * draft pesanan (disimpan di session), kekurangan data ditanyakan secara
 * singkat, dan begitu lengkap pesanan dikirim otomatis melalui
 * SubmitHomecareRequest — alur yang sama dengan wizard, tanpa langkah manual.
 */
final class AiBookingAssistant
{
    public const SESSION_KEY = 'assistant.draft';

    public function __construct(
        private readonly BookingNluParser $parser,
        private readonly HomecareCatalog $catalog,
        private readonly SubmitHomecareRequest $submitAction,
    ) {}

    /** @return array<string, mixed> */
    public function draft(Request $request): array
    {
        return array_merge($this->emptyDraft(), (array) $request->session()->get(self::SESSION_KEY, []));
    }

    /**
     * Ringkasan draft saat ini (format yang sama dengan respons pesan) untuk render awal.
     *
     * @return array<string, mixed>
     */
    public function snapshot(Request $request): array
    {
        return $this->summary($this->draft($request), collect(), $request);
    }

    public function reset(Request $request): void
    {
        $request->session()->forget(self::SESSION_KEY);
    }

    /**
     * Memproses satu pesan pengguna dan mengembalikan balasan terstruktur untuk UI.
     *
     * @return array{status: string, reply: string, draft: array<string, mixed>, quick_replies: list<array{label: string, text: string}>, action: ?array{label: string, url: string}, request: ?array{code: string, url: string}}
     */
    public function handle(Request $request, string $message): array
    {
        $user = $request->user();
        $message = trim((string) preg_replace('/\s+/u', ' ', $message));
        $lower = mb_strtolower($message);

        if (preg_match('/\b(mulai ulang|batal semua|hapus semua|reset)\b/u', $lower)) {
            $this->reset($request);

            return $this->response($request, 'collecting', 'Baik, formulir dikosongkan. Ceritakan lagi kebutuhan Anda.');
        }

        $services = $this->catalog->activeServices();
        $patients = $user ? PatientProfile::ownedBy($user)->with('addresses')->orderBy('name')->get() : collect();

        $draft = $this->draft($request);
        $parsed = $this->parser->parse($message, $services, $patients, Carbon::now());

        // Layanan: gabungkan dengan pilihan sebelumnya.
        if ($parsed['service_ids'] !== []) {
            $draft['service_ids'] = array_values(array_unique(array_merge($draft['service_ids'], $parsed['service_ids'])));
        }
        if ($parsed['preferred_date'] !== null) {
            $draft['preferred_date'] = $parsed['preferred_date'];
        }
        if ($parsed['preferred_time_window'] !== null) {
            $draft['preferred_time_window'] = $parsed['preferred_time_window'];
        }

        // Pasien: pilihan eksplisit dari pesan > pilihan sebelumnya > satu-satunya profil.
        if ($parsed['patient_profile_id'] !== null && $parsed['patient_profile_id'] !== $draft['patient_profile_id']) {
            $draft['patient_profile_id'] = $parsed['patient_profile_id'];
            $draft['patient_address_id'] = null;
        }
        if ($draft['patient_profile_id'] === null && $patients->count() === 1) {
            $draft['patient_profile_id'] = (int) $patients->first()->id;
        }

        // Alamat: sebutan eksplisit > alamat default pasien.
        $patient = $patients->firstWhere('id', $draft['patient_profile_id']);
        if ($patient instanceof PatientProfile) {
            $mentioned = $this->parser->matchAddress($message, $patient);
            if ($mentioned !== null) {
                $draft['patient_address_id'] = (int) $mentioned->id;
            } elseif (! $this->addressBelongsTo($draft['patient_address_id'], $patient)) {
                $draft['patient_address_id'] = $this->defaultAddressId($patient);
            }
        } else {
            $draft['patient_address_id'] = null;
        }

        // Keluhan: simpan kalimat pengguna (kecuali konfirmasi singkat seperti "lanjutkan").
        if (! $this->isConfirmation($lower) && mb_strlen($message) > 3) {
            $notes = array_slice(array_merge($draft['complaints'], [$message]), -5);
            $draft['complaints'] = $notes;
        }

        $request->session()->put(self::SESSION_KEY, $draft);

        return $this->decide($request, $draft, $services, $patients, $user);
    }

    /**
     * Menentukan langkah berikutnya: tanyakan kekurangan, minta masuk, atau kirim pesanan.
     *
     * @param  array<string, mixed>  $draft
     * @param  Collection<int, HomecareService>  $services
     * @param  Collection<int, PatientProfile>  $patients
     * @return array<string, mixed>
     */
    private function decide(Request $request, array $draft, Collection $services, Collection $patients, ?User $user): array
    {
        $selected = $services->whereIn('id', $draft['service_ids'])->values();

        if ($selected->isEmpty()) {
            return $this->response($request, 'collecting', 'Baik, saya bantu siapkan. Layanan apa yang Anda butuhkan? Contoh: perawatan luka, ganti kateter, ambil darah, atau visite dokter.', $this->serviceSuggestions($services));
        }

        $dateError = $this->dateError($draft['preferred_date']);
        if ($dateError !== null) {
            return $this->response($request, 'collecting', $dateError, $this->dateSuggestions(), null, $selected);
        }

        if ($draft['preferred_time_window'] === null) {
            return $this->response($request, 'collecting', 'Kunjungan sebaiknya pagi, siang, atau sore hari?', [
                ['label' => 'Pagi (08.00–12.00)', 'text' => 'pagi'],
                ['label' => 'Siang (12.00–16.00)', 'text' => 'siang'],
                ['label' => 'Sore (16.00–20.00)', 'text' => 'sore'],
            ], null, $selected, $draft);
        }

        if ($user === null) {
            $draft['awaiting_login'] = true;
            $request->session()->put(self::SESSION_KEY, $draft);
            $request->session()->put('url.intended', route('ai-assistant'));

            return $this->response($request, 'needs_login', 'Semua data sudah lengkap. Masuk atau daftar sebentar agar pesanan otomatis terkirim dan tersimpan di akun Anda — draft Anda tetap tersimpan.', [], ['label' => 'Masuk untuk memesan', 'url' => route('login')], $selected, $draft);
        }

        if ($patients->isEmpty()) {
            return $this->response($request, 'needs_profile', 'Untuk memesan, tambahkan dulu data pasien (bisa diri sendiri atau anggota keluarga).', [], ['label' => 'Tambah data pasien', 'url' => route('akun.pasien.create')], $selected, $draft);
        }

        if ($draft['patient_profile_id'] === null) {
            $names = $patients->map(fn (PatientProfile $p) => ['label' => $p->name, 'text' => 'untuk '.$p->name])->values()->all();

            return $this->response($request, 'collecting', 'Pesanan ini untuk siapa? Pilih pasiennya atau sebut namanya.', $names, null, $selected, $draft);
        }

        $patient = $patients->firstWhere('id', $draft['patient_profile_id']);

        if ($patient->addresses->isEmpty()) {
            return $this->response($request, 'needs_address', 'Alamat kunjungan untuk '.$patient->name.' belum ada. Tambahkan alamat dulu ya.', [], ['label' => 'Tambah alamat', 'url' => route('akun.pasien.alamat.create', $patient)], $selected, $draft);
        }

        if ($draft['patient_address_id'] === null) {
            $addresses = $patient->addresses->map(fn (PatientAddress $a) => ['label' => $a->label ?: $a->city, 'text' => 'alamat '.($a->label ?: $a->city)])->values()->all();

            return $this->response($request, 'collecting', 'Kunjungan ke alamat mana?', $addresses, null, $selected, $draft);
        }

        return $this->submit($request, $draft, $selected, $patient, $user);
    }

    /**
     * @param  array<string, mixed>  $draft
     * @param  Collection<int, HomecareService>  $selected
     * @return array<string, mixed>
     */
    private function submit(Request $request, array $draft, Collection $selected, PatientProfile $patient, User $user): array
    {
        try {
            $homecare = $this->submitAction->execute($user, [
                'patient_profile_id' => $patient->id,
                'patient_address_id' => (int) $draft['patient_address_id'],
                'complaint' => $this->complaintText($draft),
                'notes' => 'Dibuat melalui AI Assistant Sora.',
                'preferred_date' => $draft['preferred_date'],
                'preferred_time_window' => $draft['preferred_time_window'],
                'services' => $selected->map(fn (HomecareService $s) => ['homecare_service_id' => $s->id, 'quantity' => 1])->values()->all(),
            ]);
        } catch (Throwable $e) {
            report($e);

            return $this->response($request, 'error', 'Maaf, pesanan belum bisa dikirim saat ini. Draft Anda tersimpan — coba kirim ulang sebentar lagi, atau pesan lewat formulir.', [], ['label' => 'Buka formulir pemesanan', 'url' => route('akun.pesan.step', 'pasien')], $selected, $draft);
        }

        $this->reset($request);

        $reply = 'Pesanan Anda sudah terkirim otomatis ✅ Kode pengajuan: **'.$homecare->code.'**. Tim koordinator akan memverifikasi, dan Anda akan mendapat notifikasi di setiap tahap.';

        return $this->response($request, 'submitted', $reply, [], null, $selected, null, [
            'code' => $homecare->code,
            'url' => route('akun.pengajuan.show', $homecare),
        ]);
    }

    /**
     * @param  list<array{label: string, text: string}>  $quickReplies
     * @param  ?array{label: string, url: string}  $action
     * @param  ?Collection<int, HomecareService>  $selected
     * @param  ?array<string, mixed>  $draft
     * @param  ?array{code: string, url: string}  $requestInfo
     * @return array<string, mixed>
     */
    private function response(Request $request, string $status, string $reply, array $quickReplies = [], ?array $action = null, ?Collection $selected = null, ?array $draft = null, ?array $requestInfo = null): array
    {
        $draft ??= $this->draft($request);
        $selected ??= collect();

        return [
            'status' => $status,
            'reply' => $reply,
            'draft' => $this->summary($draft, $selected, $request),
            'quick_replies' => $quickReplies,
            'action' => $action,
            'request' => $requestInfo,
        ];
    }

    /**
     * Ringkasan draft untuk ditampilkan di panel samping.
     *
     * @param  array<string, mixed>  $draft
     * @param  Collection<int, HomecareService>  $selected
     * @return array<string, mixed>
     */
    private function summary(array $draft, Collection $selected, Request $request): array
    {
        $user = $request->user();
        $patient = $user && $draft['patient_profile_id']
            ? PatientProfile::ownedBy($user)->with('addresses')->find($draft['patient_profile_id'])
            : null;
        $address = $patient && $draft['patient_address_id'] ? $patient->addresses->firstWhere('id', $draft['patient_address_id']) : null;

        $services = $selected->isNotEmpty() ? $selected : $this->catalog->activeServices()->whereIn('id', $draft['service_ids'])->values();

        return [
            'services' => $services->map(fn (HomecareService $s) => ['id' => $s->id, 'name' => $s->name, 'price' => $s->formattedPrice()])->values()->all(),
            'total' => $services->sum(fn (HomecareService $s) => (float) $s->price),
            'total_label' => HomecareService::formatRupiah($services->sum(fn (HomecareService $s) => (float) $s->price)),
            'date' => $draft['preferred_date'],
            'date_label' => $draft['preferred_date'] ? Carbon::parse($draft['preferred_date'])->locale('id')->translatedFormat('l, j F Y') : null,
            'window' => $draft['preferred_time_window'],
            'window_label' => $draft['preferred_time_window'] ? config('homecare.time_windows.'.$draft['preferred_time_window'].'.label') : null,
            'patient' => $patient?->name,
            'address' => $address?->oneLine(),
            'ready' => $this->isComplete($draft, $user, $patient),
        ];
    }

    /** @param  array<string, mixed>  $draft */
    private function isComplete(array $draft, ?User $user, ?PatientProfile $patient): bool
    {
        return $user !== null
            && $draft['service_ids'] !== []
            && $this->dateError($draft['preferred_date']) === null
            && $draft['preferred_time_window'] !== null
            && $patient !== null
            && $draft['patient_address_id'] !== null;
    }

    private function dateError(?string $date): ?string
    {
        if ($date === null) {
            return 'Kapan kunjungan diinginkan? Contoh: besok, lusa, atau senin depan.';
        }

        $parsed = Carbon::parse($date)->startOfDay();
        $min = Carbon::now()->startOfDay()->addDays((int) config('homecare.min_lead_days', 1));
        $max = Carbon::now()->startOfDay()->addDays((int) config('homecare.max_advance_days', 30));

        if ($parsed->lt($min)) {
            return 'Pemesanan minimal H-'.config('homecare.min_lead_days', 1).' dari hari kunjungan. Pilih tanggal lain ya, misalnya besok.';
        }
        if ($parsed->gt($max)) {
            return 'Pemesanan dapat dilakukan paling lambat '.config('homecare.max_advance_days', 30).' hari ke depan.';
        }

        return null;
    }

    /** @return list<array{label: string, text: string}> */
    private function serviceSuggestions(Collection $services): array
    {
        return $services->take(4)->map(fn (HomecareService $s) => ['label' => $s->name, 'text' => $s->name])->values()->all();
    }

    /** @return list<array{label: string, text: string}> */
    private function dateSuggestions(): array
    {
        return [
            ['label' => 'Besok', 'text' => 'besok'],
            ['label' => 'Lusa', 'text' => 'lusa'],
            ['label' => 'Senin depan', 'text' => 'senin depan'],
        ];
    }

    /** @param  array<string, mixed>  $draft */
    private function complaintText(array $draft): string
    {
        return mb_substr(implode('. ', $draft['complaints']) ?: 'Kebutuhan homecare via AI Assistant', 0, 1000);
    }

    private function isConfirmation(string $lower): bool
    {
        return (bool) preg_match('/^(lanjut(kan)?|ya|oke|ok|pesan( sekarang)?|kirim|konfirmasi)[\s!.]*$/u', $lower);
    }

    private function addressBelongsTo(mixed $addressId, PatientProfile $patient): bool
    {
        return $addressId !== null && $patient->addresses->contains('id', (int) $addressId);
    }

    private function defaultAddressId(PatientProfile $patient): ?int
    {
        if ($patient->addresses->count() === 1) {
            return (int) $patient->addresses->first()->id;
        }

        $primary = $patient->addresses->firstWhere('is_primary', true);

        return $primary ? (int) $primary->id : null;
    }

    /** @return array<string, mixed> */
    private function emptyDraft(): array
    {
        return [
            'service_ids' => [],
            'preferred_date' => null,
            'preferred_time_window' => null,
            'patient_profile_id' => null,
            'patient_address_id' => null,
            'complaints' => [],
            'awaiting_login' => false,
        ];
    }
}
