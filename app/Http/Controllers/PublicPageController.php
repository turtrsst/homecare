<?php

namespace App\Http\Controllers;

use App\Models\HomecareService;
use App\Services\HomecareCatalog;
use Illuminate\View\View;

class PublicPageController extends Controller
{
    public function __construct(private readonly HomecareCatalog $catalog) {}

    public function home(): View
    {
        return view('public.home', [
            'featuredServices' => $this->catalog->featuredServices(4),
            'services' => $this->catalog->activeServices()->take(8),
        ]);
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
            ['q' => 'Apa itu layanan Homecare?', 'a' => 'Homecare adalah pelayanan kesehatan yang diberikan oleh tenaga profesional rumah sakit langsung di rumah Anda — mulai dari kunjungan dokter, perawatan luka, fisioterapi, hingga pengambilan sampel laboratorium.'],
            ['q' => 'Siapa yang bisa menggunakan layanan ini?', 'a' => 'Siapa saja: pasien yang sulit bepergian ke rumah sakit, lansia, ibu dan bayi, pasien pasca-rawat inap, atau keluarga yang membutuhkan pendampingan perawatan di rumah.'],
            ['q' => 'Bagaimana cara memesan?', 'a' => 'Daftar atau masuk, pilih "Pesan Homecare", isi kebutuhan Anda dalam beberapa langkah singkat, lalu tunggu verifikasi tim kami. Anda akan diberi tahu setiap tahapnya lewat notifikasi.'],
            ['q' => 'Berapa lama proses verifikasinya?', 'a' => 'Pada jam kerja, pengajuan biasanya diverifikasi dalam beberapa jam. Anda dapat memantau statusnya kapan saja di halaman Pengajuan.'],
            ['q' => 'Berapa biayanya?', 'a' => 'Setiap layanan memiliki tarif yang tercantum jelas sebelum Anda mengirim pengajuan. Total biaya dikonfirmasi kembali oleh koordinator setelah skrining — tidak ada biaya tersembunyi.'],
            ['q' => 'Apakah ini layanan darurat?', 'a' => 'Bukan. Homecare tidak untuk kegawatdaruratan. Bila kondisi gawat darurat, segera hubungi '.config('homecare.emergency_number').' atau IGD rumah sakit terdekat.'],
            ['q' => 'Apakah data saya aman?', 'a' => 'Ya. Dokumen seperti KTP atau hasil pemeriksaan disimpan di penyimpanan privat yang hanya bisa diakses pihak berwenang, dan setiap akses tercatat.'],
            ['q' => 'Bisakah saya memesan untuk anggota keluarga?', 'a' => 'Tentu. Dalam satu akun Anda dapat menambahkan beberapa profil pasien (diri sendiri, orang tua, anak, dsb.) dan memilih untuk siapa layanan dipesan.'],
        ];
    }
}
