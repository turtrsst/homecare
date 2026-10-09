<?php

namespace App\Http\Controllers;

use App\Models\HomecareService;
use App\Services\AiQuickBookingAssistant;
use App\Services\HomecareCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicPageController extends Controller
{
    public function __construct(
        private readonly HomecareCatalog $catalog,
        private readonly AiQuickBookingAssistant $aiAssistant,
    ) {}

    public function home(): View
    {
        return view('public.home', [
            'featuredServices' => $this->catalog->featuredServices(4),
            'services' => $this->catalog->activeServices()->take(8),
        ]);
    }

    public function aiAssistant(): View
    {
        return view('public.ai-assistant', [
            'services' => $this->catalog->activeServices()->take(6),
        ]);
    }

    public function processAiAssistant(Request $request): RedirectResponse
    {
        $request->validate([
            'message' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        $parsed = $this->aiAssistant->parse($request->string('message')->toString(), $request->user());

        $booking = $request->session()->get('booking', []);
        $booking = array_merge($booking, [
            'service_ids' => $parsed['service_ids'],
            'complaint' => $parsed['complaint'],
            'preferred_date' => $parsed['preferred_date'],
            'preferred_time_window' => $parsed['preferred_time_window'],
        ]);

        $request->session()->put('booking', $booking);

        if (! $request->user()) {
            return redirect()->route('login')
                ->with('info', 'Silakan masuk terlebih dahulu agar AI bisa lanjut ke form booking otomatis.');
        }

        $user = $request->user();
        $patient = $user->patientProfiles()->orderBy('name')->first();

        if ($patient) {
            $booking['patient_profile_id'] = $patient->id;
            $address = $patient->addresses()->orderBy('is_primary')->first();
            if ($address) {
                $booking['patient_address_id'] = $address->id;
            }
        }

        $request->session()->put('booking', $booking);

        $nextStep = ($patient && ($booking['patient_address_id'] ?? null)) ? 'review' : 'pasien';

        return redirect()->route('akun.pesan.step', $nextStep)
            ->with('success', 'AI berhasil menyiapkan kebutuhan Anda. Silakan cek ringkasan dan lanjutkan konfirmasi.');
    }

    public function services(): View
    {
        return view('public.services', [
            'services' => $this->catalog->activeServices(),
        ]);
    }

    public function serviceDetail(HomecareService $service): View
    {
        abort_unless($service->is_active, 404);

        return view('public.service-detail', [
            'service' => $service,
            'relatedServices' => $this->catalog->activeServices()
                ->where('id', '!=', $service->id)
                ->where('category', $service->category)
                ->take(3),
        ]);
    }

    public function howItWorks(): View
    {
        return view('public.how-it-works');
    }

    public function faq(): View
    {
        return view('public.faq', ['faqs' => $this->faqs()]);
    }

    public function contact(): View
    {
        return view('public.contact');
    }

    /** @return list<array{q: string, a: string}> */
    private function faqs(): array
    {
        return [
            ['q' => 'Apa itu layanan Homecare?', 'a' => 'Homecare adalah pelayanan kesehatan yang diberikan oleh tenaga profesional rumah sakit langsung di rumah Anda — mulai dari kunjungan dokter, perawat, fisioterapi, hingga pemeriksaan mandiri.'],
            ['q' => 'Siapa yang bisa menggunakan layanan ini?', 'a' => 'Siapa saja: pasien yang sulit bepergian ke rumah sakit, lansia, ibu dan bayi, pasien pasca-rawat inap, atau keluarga yang membutuhkan bantuan layanan kesehatan rumah.'],
            ['q' => 'Bagaimana cara memesan?', 'a' => 'Daftar atau masuk, pilih "Pesan Homecare", isi kebutuhan Anda dalam beberapa langkah singkat, lalu tunggu verifikasi tim kami. Anda akan dibantu setiap tahapnya.'],
            ['q' => 'Berapa lama proses verifikasinya?', 'a' => 'Pada jam kerja, pengajuan biasanya diverifikasi dalam beberapa jam. Anda dapat memantau statusnya kapan saja di halaman Pengajuan.'],
            ['q' => 'Berapa biayanya?', 'a' => 'Setiap layanan memiliki tarif yang tercantum jelas sebelum Anda mengirim pengajuan. Total biaya dikonfirmasi kembali oleh koordinator setelah skrining awal.'],
            ['q' => 'Apakah ini layanan darurat?', 'a' => 'Bukan. Homecare tidak untuk kegawatdaruratan. Bila kondisi gawat darurat, segera hubungi '.config('homecare.emergency_number').' atau IGD sesuai kebutuhan.'],
            ['q' => 'Apakah data saya aman?', 'a' => 'Ya. Dokumen seperti KTP atau hasil pemeriksaan disimpan di penyimpanan privat yang hanya bisa diakses pihak berwenang, dan setiap akses tercatat dengan audit trail.'],
            ['q' => 'Bisakah saya memesan untuk anggota keluarga?', 'a' => 'Tentu. Dalam satu akun Anda dapat menambahkan beberapa profil pasien (diri sendiri, orang tua, anak, dsb.) dan memilih untuk siapa layanan ingin dipesan.'],
        ];
    }
}

