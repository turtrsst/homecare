<?php

namespace Tests\Unit;

use App\Models\HomecareService;
use App\Models\PatientAddress;
use App\Models\PatientProfile;
use App\Services\Assistant\BookingNluParser;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Tests\TestCase;

class BookingNluParserTest extends TestCase
{
    private BookingNluParser $parser;

    private Carbon $now;

    protected function setUp(): void
    {
        parent::setUp();

        // Jumat, 9 Oktober 2026 (WIB) — acuan tetap agar tanggal relatif bisa diuji.
        $this->now = Carbon::parse('2026-10-09 09:00:00', 'Asia/Jakarta');
        Carbon::setTestNow($this->now);

        $this->parser = new BookingNluParser;
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_it_extracts_service_relative_date_and_window_from_one_sentence(): void
    {
        $result = $this->parse('Ganti kateter untuk ibu besok pagi');

        $this->assertSame([1], $result['service_ids']);
        $this->assertSame('2026-10-10', $result['preferred_date']);
        $this->assertSame('morning', $result['preferred_time_window']);
    }

    public function test_lusa_and_clock_time_map_to_two_days_ahead_and_morning(): void
    {
        $result = $this->parse('Perawatan luka lusa jam 9 pagi');

        $this->assertSame('2026-10-11', $result['preferred_date']);
        $this->assertSame('morning', $result['preferred_time_window']);
    }

    public function test_weekday_resolves_to_next_occurrence_after_today(): void
    {
        // 9 Oktober 2026 adalah Jumat, jadi "jumat" berarti 16 Oktober.
        $this->assertSame('2026-10-16', $this->parse('visite dokter jumat sore')['preferred_date']);
        $this->assertSame('afternoon', $this->parse('visite dokter jumat sore')['preferred_time_window']);
    }

    public function test_day_and_month_without_year_in_the_future_stays_in_this_year(): void
    {
        $this->assertSame('2026-10-15', $this->parse('ganti kateter 15 oktober')['preferred_date']);
    }

    public function test_day_and_month_without_year_already_passed_rolls_to_next_year(): void
    {
        $this->assertSame('2027-10-05', $this->parse('ganti kateter 5 oktober')['preferred_date']);
    }

    public function test_explicit_year_is_kept_even_if_the_date_is_in_the_past(): void
    {
        $this->assertSame('2026-10-05', $this->parse('ganti kateter 5 Oktober 2026')['preferred_date']);
    }

    public function test_numeric_date_with_explicit_iso_year_is_parsed(): void
    {
        $this->assertSame('2026-12-01', $this->parse('ambil darah tanggal 2026-12-01')['preferred_date']);
    }

    public function test_invalid_calendar_date_does_not_stop_the_search_for_another_pattern(): void
    {
        // "31/02" tidak valid, tetapi tanggal "15 oktober" di kalimat yang sama tetap harus terbaca.
        $result = $this->parse('ganti kateter 31/02 atau 15 oktober');

        $this->assertSame('2026-10-15', $result['preferred_date']);
    }

    public function test_clock_time_is_not_mistaken_for_a_day_month_date(): void
    {
        $result = $this->parse('suntik insulin jam 10.12 pagi');

        $this->assertNull($result['preferred_date']);
        $this->assertSame('morning', $result['preferred_time_window']);
    }

    public function test_specialist_visit_replaces_general_doctor_visit(): void
    {
        $services = $this->services([
            ['id' => 1, 'code' => 'DOK-01', 'name' => 'Visite dokter umum'],
            ['id' => 2, 'code' => 'DOK-02', 'name' => 'Kunjungan dokter spesialis'],
        ]);

        $ids = $this->parser->parse('visite dokter spesialis sore', $services, collect(), $this->now)['service_ids'];

        $this->assertSame([2], $ids);
    }

    public function test_small_wound_is_preferred_over_default_wound_care(): void
    {
        $services = $this->services([
            ['id' => 10, 'code' => 'MED-11', 'name' => 'Perawatan luka kecil'],
            ['id' => 12, 'code' => 'MED-12', 'name' => 'Perawatan luka sedang'],
        ]);

        $ids = $this->parser->parse('perawatan luka kecil di kaki', $services, collect(), $this->now)['service_ids'];

        $this->assertSame([10], $ids);
    }

    public function test_patient_is_matched_by_family_relation(): void
    {
        $ayah = $this->patient(1, 'Slamet Riyadi', 'ayah');
        $ibu = $this->patient(2, 'Sri Wahyuni', 'ibu');

        $result = $this->parse('ganti kateter untuk ayah besok', [$ayah, $ibu]);

        $this->assertSame(1, $result['patient_profile_id']);
    }

    public function test_patient_is_matched_as_self_when_user_says_saya(): void
    {
        $self = $this->patient(3, 'Budi Santoso', 'diri_sendiri');
        $child = $this->patient(4, 'Dita Santoso', 'anak');

        $result = $this->parse('suntik vitamin untuk saya', [$child, $self]);

        $this->assertSame(3, $result['patient_profile_id']);
    }

    public function test_no_patient_is_selected_when_the_message_does_not_mention_one(): void
    {
        $result = $this->parse('ambil darah besok', [$this->patient(1, 'Slamet Riyadi', 'ayah')]);

        $this->assertNull($result['patient_profile_id']);
    }

    public function test_matches_address_by_label_or_city(): void
    {
        $patient = $this->patient(1, 'Slamet Riyadi', 'ayah');
        $patient->setRelation('addresses', collect([
            new PatientAddress(['label' => 'Rumah', 'city' => 'Klaten']),
            new PatientAddress(['label' => 'Rumah Ibu', 'city' => 'Sragen']),
        ]));

        $address = $this->parser->matchAddress('kunjungan ke rumah ibu', $patient);

        $this->assertNotNull($address);
        $this->assertSame('Rumah Ibu', $address->label);
    }

    /** @return array<string, mixed> */
    private function parse(string $message, array|Collection|null $patients = null): array
    {
        $services = $this->services([
            ['id' => 1, 'code' => 'MED-01', 'name' => 'Ganti kateter'],
            ['id' => 2, 'code' => 'MED-12', 'name' => 'Perawatan luka sedang'],
            ['id' => 3, 'code' => 'LAB-01', 'name' => 'Pemeriksaan gula darah'],
            ['id' => 4, 'code' => 'MED-18', 'name' => 'Pengambilan darah'],
            ['id' => 5, 'code' => 'DOK-01', 'name' => 'Visite dokter umum'],
            ['id' => 6, 'code' => 'MED-07', 'name' => 'Pemasangan infus'],
            ['id' => 7, 'code' => 'MED-08', 'name' => 'Injeksi intramuskular'],
        ]);

        $patients = collect($patients ?? []);

        return $this->parser->parse($message, $services, $patients, $this->now);
    }

    /** @param list<array{id: int, code: string, name: string}> $rows */
    private function services(array $rows): Collection
    {
        return collect($rows)->map(function (array $row): HomecareService {
            $service = new HomecareService(['code' => $row['code'], 'name' => $row['name']]);
            $service->id = $row['id'];

            return $service;
        });
    }

    private function patient(int $id, string $name, string $relationship): PatientProfile
    {
        $patient = new PatientProfile(['name' => $name, 'relationship' => $relationship]);
        $patient->id = $id;

        return $patient;
    }
}
