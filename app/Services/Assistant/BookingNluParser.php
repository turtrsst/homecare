<?php

namespace App\Services\Assistant;

use App\Models\HomecareService;
use App\Models\PatientAddress;
use App\Models\PatientProfile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Pengurai kalimat bahasa Indonesia (berbasis aturan & deterministik) untuk
 * mengekstrak slot pemesanan homecare: layanan, tanggal, waktu kunjungan,
 * pasien, dan alamat.
 *
 * Sengaja tanpa layanan AI eksternal: hasilnya dapat diprediksi, dapat diuji,
 * tidak membocorkan data kesehatan ke pihak ketiga, dan tetap berjalan offline.
 */
final class BookingNluParser
{
    /**
     * Kata kunci → kode layanan (kode mengacu pada database/data/sk_tarif_homecare.json).
     * Urutan tidak penting; kata kunci dicocokkan sebagai kata utuh.
     *
     * @var array<string, list<string>>
     */
    private const SERVICE_KEYWORDS = [
        'kateter' => ['MED-01'],
        'cateter' => ['MED-01'],
        'ngt' => ['MED-02'],
        'sonde' => ['MED-02'],
        'irigasi' => ['MED-05'],
        'spooling' => ['MED-05'],
        'infus' => ['MED-07'],
        'suntik' => ['MED-08'],
        'injeksi' => ['MED-08'],
        'intravena' => ['MED-09'],
        'iv' => ['MED-09'],
        'nebul' => ['MED-10'],
        'nebulizer' => ['MED-10'],
        'tali pusat' => ['MED-22'],
        'pijat bayi' => ['MED-14'],
        'mandi bayi' => ['MED-21'],
        'memandikan bayi' => ['MED-21'],
        'jahit' => ['MED-25'],
        'rohani' => ['MED-15'],
        'ruqyah' => ['MED-17'],
        'ruyah' => ['MED-17'],
        'nazak' => ['MED-16'],
        'sakaratul' => ['MED-16'],
        'ambil darah' => ['MED-18'],
        'pengambilan darah' => ['MED-18'],
        'gula darah' => ['LAB-01'],
        'gds' => ['LAB-01'],
        'asam urat' => ['LAB-02'],
        'kolesterol' => ['LAB-03'],
        'darah rutin' => ['LAB-04'],
        'sgot' => ['LAB-05'],
        'sgpt' => ['LAB-06'],
        'kultur' => ['LAB-07'],
        'laser' => ['REH-01'],
        'dry needling' => ['REH-02'],
        'taping' => ['REH-20'],
        'tens' => ['REH-39'],
        'fisioterapi' => ['REH-12'],
        'fisio' => ['REH-12'],
        'rehabilitasi' => ['REH-12'],
        'okupasi' => ['REH-25'],
        'dokter' => ['DOK-01'],
        'visite' => ['DOK-01'],
        'spesialis' => ['DOK-02'],
        'sub spesialis' => ['DOK-03'],
        'psikolog' => ['DOK-04'],
        'psikologi' => ['DOK-04'],
        'tempat tidur' => ['SEW-01'],
        'kasur' => ['SEW-01'],
        'oksigen' => ['SEW-03'],
        'luka' => ['MED-12'],
    ];

    /** Kata yang tidak dipakai saat mencocokkan nama layanan secara bebas. */
    private const STOPWORDS = ['dan', 'atau', 'untuk', 'dengan', 'lepas', 'pasang', 'the', 'dari', 'pada', 'dalam', 'ke'];

    /** Bulan Indonesia & singkatannya → nomor bulan. */
    private const MONTHS = [
        'januari' => 1, 'jan' => 1,
        'februari' => 2, 'feb' => 2,
        'maret' => 3, 'mar' => 3,
        'april' => 4, 'apr' => 4,
        'mei' => 5,
        'juni' => 6, 'jun' => 6,
        'juli' => 7, 'jul' => 7,
        'agustus' => 8, 'agu' => 8, 'agt' => 8, 'aug' => 8,
        'september' => 9, 'sep' => 9,
        'oktober' => 10, 'okt' => 10, 'oct' => 10,
        'november' => 11, 'nov' => 11,
        'desember' => 12, 'des' => 12, 'dec' => 12,
    ];

    /** Hari → nomor hari Carbon (0 = Minggu). */
    private const WEEKDAYS = [
        'senin' => Carbon::MONDAY,
        'selasa' => Carbon::TUESDAY,
        'rabu' => Carbon::WEDNESDAY,
        'kamis' => Carbon::THURSDAY,
        'jumat' => Carbon::FRIDAY,
        'sabtu' => Carbon::SATURDAY,
        'minggu' => Carbon::SUNDAY,
        'ahad' => Carbon::SUNDAY,
    ];

    /** Sinonim hubungan keluarga: kata di kalimat → nilai `relationship` di profil. */
    private const RELATION_SYNONYMS = [
        'ibu' => ['ibu', 'mama', 'bunda', 'emak'],
        'ayah' => ['ayah', 'bapak', 'papa', 'abi'],
        'anak' => ['anak', 'putra', 'putri'],
        'suami' => ['suami'],
        'istri' => ['istri'],
        'nenek' => ['nenek', 'oma', 'grandma'],
        'kakek' => ['kakek', 'opa', 'grandpa'],
        'adik' => ['adik'],
        'kakak' => ['kakak'],
        'mertua' => ['mertua'],
    ];

    /** Kata yang berarti pasiennya adalah pemesan sendiri. */
    private const SELF_WORDS = ['saya', 'aku', 'diri sendiri', 'untuk saya', 'buat saya', 'pribadi'];

    /**
     * @param  Collection<int, HomecareService>  $services  layanan aktif
     * @param  Collection<int, PatientProfile>  $patients  pasien milik pengguna (bisa kosong)
     * @return array{service_ids: list<int>, preferred_date: ?string, preferred_time_window: ?string, patient_profile_id: ?int, has_date_mention: bool}
     */
    public function parse(string $message, Collection $services, Collection $patients, ?Carbon $now = null): array
    {
        $now ??= Carbon::now();
        $text = $this->normalize($message);

        $patient = $this->matchPatient($text, $patients);

        return [
            'service_ids' => $this->matchServices($text, $services),
            // Ekspresi jam ("jam 10.12") dibuang dulu agar tidak terbaca sebagai tanggal 10 Desember.
            'preferred_date' => $this->matchDate($this->stripTimeExpressions($text), $now),
            'preferred_time_window' => $this->matchWindow($text),
            'patient_profile_id' => $patient?->id,
        ];
    }

    /**
     * Mencari alamat yang disebut pengguna (label, kota, atau nama jalan) untuk pasien tertentu.
     * Bila lebih dari satu alamat cocok (mis. "Rumah" dan "Rumah Ibu"), yang paling spesifik menang.
     */
    public function matchAddress(string $message, PatientProfile $patient): ?PatientAddress
    {
        $text = $this->normalize($message);
        $best = null;
        $bestLength = 0;

        foreach ($patient->addresses as $address) {
            foreach ([(string) $address->label, (string) $address->city] as $term) {
                $term = $this->normalize($term);
                $length = mb_strlen($term);

                if ($length > $bestLength && $this->containsWord($text, $term)) {
                    $best = $address;
                    $bestLength = $length;
                }
            }
        }

        return $best;
    }

    /** @return list<int> */
    private function matchServices(string $text, Collection $services): array
    {
        $codes = [];

        foreach (self::SERVICE_KEYWORDS as $keyword => $targets) {
            if ($this->containsWord($text, $keyword)) {
                $codes = array_merge($codes, $targets);
            }
        }

        // Kunjungan spesialis menggantikan visite dokter umum.
        if (array_intersect(['DOK-02', 'DOK-03'], $codes) !== []) {
            $codes = array_values(array_diff($codes, ['DOK-01']));
        }
        // "Luka kecil/besar" lebih spesifik daripada default "luka sedang".
        if ($this->containsWord($text, 'luka kecil')) {
            $codes = array_merge(array_values(array_diff($codes, ['MED-12'])), ['MED-11']);
        } elseif ($this->containsWord($text, 'luka besar')) {
            $codes = array_merge(array_values(array_diff($codes, ['MED-12'])), ['MED-13']);
        }

        $ids = $services->whereIn('code', array_unique($codes))->pluck('id')->map(fn ($id) => (int) $id)->all();

        if ($ids === []) {
            // Cadangan: cocokkan kata-kata bermakna dari nama layanan resmi.
            foreach ($services as $service) {
                $words = collect(preg_split('/\s+/u', $this->normalize((string) $service->name)))
                    ->filter(fn ($w) => mb_strlen($w) >= 5 && ! in_array($w, self::STOPWORDS, true));

                if ($words->isNotEmpty() && $words->filter(fn ($w) => $this->containsWord($text, $w))->count() >= min(2, $words->count())) {
                    $ids[] = (int) $service->id;
                }
            }
        }

        return array_values(array_unique($ids));
    }

    private function matchDate(string $text, Carbon $now): ?string
    {
        $today = $now->copy()->startOfDay();

        if ($this->containsWord($text, 'lusa')) {
            return $today->copy()->addDays(2)->toDateString();
        }
        if ($this->containsWord($text, 'besok')) {
            return $today->copy()->addDay()->toDateString();
        }
        if ($this->containsWord($text, 'hari ini')) {
            return $today->toDateString();
        }
        if ($this->containsWord($text, 'minggu depan')) {
            return $today->copy()->addWeek()->toDateString();
        }

        // Nama hari: "senin", "jumat depan" → kemunculan berikutnya setelah hari ini.
        if (preg_match('/\b('.implode('|', array_keys(self::WEEKDAYS)).')\b/u', $text, $m)) {
            return $today->copy()->next(self::WEEKDAYS[$m[1]])->toDateString();
        }

        // 2026-10-15
        if (preg_match('/\b(\d{4})-(\d{1,2})-(\d{1,2})\b/', $text, $m)) {
            $date = $this->safeDate((int) $m[1], (int) $m[2], (int) $m[3]);
            if ($date !== null) {
                return $date;
            }
        }

        // 15/10 atau 15-10-2026 (bukan jam seperti 14.30)
        if (preg_match('/\b(\d{1,2})[\/\-.](\d{1,2})(?:[\/\-.](\d{2,4}))?\b/', $text, $m)) {
            $explicitYear = isset($m[3]) && $m[3] !== '';
            $year = $explicitYear ? (int) $m[3] : (int) $now->year;
            if ($year < 100) {
                $year += 2000;
            }

            $date = $this->withFutureRollover((int) $m[1], (int) $m[2], $year, $today, $explicitYear);
            if ($date !== null) {
                return $date;
            }
        }

        // 15 oktober (2026)
        $monthPattern = implode('|', array_map('preg_quote', array_keys(self::MONTHS)));
        if (preg_match('/\b(\d{1,2})\s*('.$monthPattern.')\b(?:\s*(\d{4}))?/u', $text, $m)) {
            $explicitYear = isset($m[3]) && $m[3] !== '';
            $year = $explicitYear ? (int) $m[3] : (int) $now->year;
            $date = $this->withFutureRollover((int) $m[1], self::MONTHS[$m[2]], $year, $today, $explicitYear);
            if ($date !== null) {
                return $date;
            }
        }

        // "tanggal 15" → bulan berjalan (atau bulan depan bila sudah lewat)
        if (preg_match('/\btanggal\s*(\d{1,2})\b/u', $text, $m)) {
            return $this->withFutureRollover((int) $m[1], (int) $today->month, (int) $today->year, $today, false);
        }

        return null;
    }

    private function matchWindow(string $text): ?string
    {
        if ($this->containsWord($text, 'pagi')) {
            return 'morning';
        }
        if ($this->containsWord($text, 'siang')) {
            return 'midday';
        }
        if ($this->containsWord($text, 'sore') || $this->containsWord($text, 'malam')) {
            return 'afternoon';
        }

        // "jam 9", "pukul 14.30"
        if (preg_match('/\b(?:jam|pukul)\s*(\d{1,2})(?:[.:]\d{2})?\b/u', $text, $m)) {
            $hour = (int) $m[1];

            return match (true) {
                $hour >= 6 && $hour < 12 => 'morning',
                $hour >= 12 && $hour < 16 => 'midday',
                $hour >= 16 && $hour <= 20 => 'afternoon',
                default => null,
            };
        }

        return null;
    }

    /** @param Collection<int, PatientProfile> $patients */
    private function matchPatient(string $text, Collection $patients): ?PatientProfile
    {
        $best = null;
        $bestScore = 0;

        foreach ($patients as $patient) {
            $score = 0;
            $name = $this->normalize((string) $patient->name);
            $first = explode(' ', $name)[0] ?? '';
            $relation = $this->normalize(str_replace('_', ' ', (string) $patient->relationship));

            if ($name !== '' && $this->containsWord($text, $name)) {
                $score += 4;
            } elseif (mb_strlen($first) >= 3 && $this->containsWord($text, $first)) {
                $score += 3;
            }

            if ($relation === 'diri sendiri') {
                foreach (self::SELF_WORDS as $word) {
                    if ($this->containsWord($text, $word)) {
                        $score += 2;
                        break;
                    }
                }
            } else {
                foreach (self::RELATION_SYNONYMS as $canonical => $synonyms) {
                    if (! $this->containsAny($text, $synonyms)) {
                        continue;
                    }
                    if ($relation === $canonical || str_contains($relation, $canonical)) {
                        $score += 2;
                    }
                }
            }

            if ($score > $bestScore) {
                $best = $patient;
                $bestScore = $score;
            }
        }

        return $best;
    }

    /** Menyusun tanggal; tanpa tahun eksplisit dan sudah lewat → geser ke periode berikutnya. */
    private function withFutureRollover(int $day, int $month, int $year, Carbon $today, bool $explicitYear): ?string
    {
        $date = $this->safeDate($year, $month, $day);
        if ($date === null) {
            return null;
        }

        if (! $explicitYear && Carbon::parse($date)->lt($today)) {
            $nextYear = $this->safeDate($year + 1, $month, $day);

            return $nextYear ?? $date;
        }

        return $date;
    }

    private function safeDate(int $year, int $month, int $day): ?string
    {
        if ($month < 1 || $month > 12 || $day < 1 || $day > 31 || ! checkdate($month, $day, $year)) {
            return null;
        }

        return Carbon::create($year, $month, $day)->toDateString();
    }

    /** @param list<string> $needles */
    private function containsAny(string $text, array $needles): bool
    {
        foreach ($needles as $needle) {
            if ($this->containsWord($text, $needle)) {
                return true;
            }
        }

        return false;
    }

    private function containsWord(string $text, string $needle): bool
    {
        if ($needle === '') {
            return false;
        }

        $pattern = '/(?<![\p{L}\p{N}])'.preg_quote($needle, '/').'(?![\p{L}\p{N}])/u';

        return (bool) preg_match($pattern, $text);
    }

    private function stripTimeExpressions(string $text): string
    {
        return trim((string) preg_replace('/\b(?:jam|pukul)\s*\d{1,2}(?:[.:]\d{2})?\b/u', ' ', $text));
    }

    private function normalize(string $value): string
    {
        $value = mb_strtolower($value);
        $value = preg_replace('/[^\p{L}\p{N}\s\/\-.:]/u', ' ', $value) ?? '';

        return trim(preg_replace('/\s+/u', ' ', $value) ?? '');
    }
}
