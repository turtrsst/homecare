<?php

namespace Database\Seeders;

use App\Enums\ContactType;
use App\Enums\Gender;
use App\Enums\Profession;
use App\Enums\UserRole;
use App\Models\HealthcareStaff;
use App\Models\PatientAddress;
use App\Models\PatientContact;
use App\Models\PatientProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Akun demo: tim rumah sakit + dua keluarga pasien.
 * Semua password: "password" — hanya untuk data demo.
 */
class DemoUserSeeder extends Seeder
{
    public function run(): array
    {
        $password = Hash::make('password');

        $make = fn (array $attrs) => User::updateOrCreate(
            ['email' => $attrs['email']],
            $attrs + ['password' => $password, 'email_verified_at' => now(), 'is_active' => true],
        );

        // --- Tim rumah sakit -------------------------------------------------
        $admin = $make(['name' => 'Rina Wijaya', 'email' => 'admin@homecare.rs', 'role' => UserRole::Admin, 'phone' => '081100000001']);
        $coordinator = $make(['name' => 'Dwi Hartono', 'email' => 'koordinator@homecare.rs', 'role' => UserRole::Coordinator, 'phone' => '081100000002']);
        $manager = $make(['name' => 'dr. Hendra Kusuma, MARS', 'email' => 'manajer@homecare.rs', 'role' => UserRole::Manager, 'phone' => '081100000003']);

        $doctor = $make(['name' => 'dr. Andini Prameswari', 'email' => 'dr.andini@homecare.rs', 'role' => UserRole::MedicalStaff, 'phone' => '081100000010']);
        $nurse1 = $make(['name' => 'Ns. Siti Rahayu, S.Kep.', 'email' => 'ns.siti@homecare.rs', 'role' => UserRole::MedicalStaff, 'phone' => '081100000011']);
        $nurse2 = $make(['name' => 'Ns. Bagus Setiawan, S.Kep.', 'email' => 'ns.bagus@homecare.rs', 'role' => UserRole::MedicalStaff, 'phone' => '081100000012']);
        $physio = $make(['name' => 'Rina Ayu, S.Ft.', 'email' => 'ft.rina@homecare.rs', 'role' => UserRole::MedicalStaff, 'phone' => '081100000013']);

        // --- Profil tenaga kesehatan -----------------------------------------
        $staff = [
            'drAndini' => HealthcareStaff::updateOrCreate(['email' => 'dr.andini@homecare.rs'], [
                'user_id' => $doctor->id, 'name' => 'dr. Andini Prameswari', 'profession' => Profession::Doctor,
                'license_number' => 'STR-DR-2019-0341', 'specialization' => 'Dokter Umum', 'phone' => '081100000010',
                'email' => 'dr.andini@homecare.rs', 'is_active' => true,
                'bio' => 'Dokter umum dengan pengalaman 8 tahun, fokus pada perawatan lansia dan pasca-rawat inap.',
            ]),
            'nsSiti' => HealthcareStaff::updateOrCreate(['email' => 'ns.siti@homecare.rs'], [
                'user_id' => $nurse1->id, 'name' => 'Ns. Siti Rahayu, S.Kep.', 'profession' => Profession::Nurse,
                'license_number' => 'STR-NRS-2020-1188', 'specialization' => 'Wound Care', 'phone' => '081100000011',
                'email' => 'ns.siti@homecare.rs', 'is_active' => true,
                'bio' => 'Perawat bersertifikat wound care; berpengalaman menangani luka diabetes dan luka operasi.',
            ]),
            'nsBagus' => HealthcareStaff::updateOrCreate(['email' => 'ns.bagus@homecare.rs'], [
                'user_id' => $nurse2->id, 'name' => 'Ns. Bagus Setiawan, S.Kep.', 'profession' => Profession::Nurse,
                'license_number' => 'STR-NRS-2021-2245', 'specialization' => 'Gawat Darurat & Injeksi', 'phone' => '081100000012',
                'email' => 'ns.bagus@homecare.rs', 'is_active' => true,
                'bio' => 'Perawat dengan latar IGD; teliti dalam tindakan injeksi dan pemasangan infus.',
            ]),
            'ftRina' => HealthcareStaff::updateOrCreate(['email' => 'ft.rina@homecare.rs'], [
                'user_id' => $physio->id, 'name' => 'Rina Ayu, S.Ft.', 'profession' => Profession::Physiotherapist,
                'license_number' => 'STR-FT-2021-0872', 'specialization' => 'Neuro & Muskuloskeletal', 'phone' => '081100000013',
                'email' => 'ft.rina@homecare.rs', 'is_active' => true,
                'bio' => 'Fisioterapis pemulihan pasca-stroke dan cedera ortopedi.',
            ]),
        ];

        // --- Keluarga pasien --------------------------------------------------
        $budi = $make(['name' => 'Budi Santoso', 'email' => 'budi@example.com', 'role' => UserRole::Patient, 'phone' => '081234567890']);
        $sari = $make(['name' => 'Sari Melati', 'email' => 'sari@example.com', 'role' => UserRole::Patient, 'phone' => '081298765432']);

        // Budi: diri sendiri + ibunya (pasien utama homecare)
        $budiSelf = PatientProfile::updateOrCreate(
            ['user_id' => $budi->id, 'relationship' => 'diri_sendiri'],
            [
                'name' => 'Budi Santoso', 'gender' => Gender::Male,
                'birth_date' => '1985-04-12', 'blood_type' => 'O',
            ],
        );

        $ibunyaBudi = PatientProfile::updateOrCreate(
            ['user_id' => $budi->id, 'name' => 'Siti Aminah'],
            [
                'relationship' => 'ibu', 'gender' => Gender::Female,
                'birth_date' => '1958-09-02', 'blood_type' => 'B',
                'medical_notes' => 'Diabetes melitus tipe 2 (metformin 2×500mg), hipertensi terkontrol. Alergi penisilin.',
            ],
        );

        PatientContact::updateOrCreate(
            ['patient_profile_id' => $ibunyaBudi->id, 'type' => ContactType::Phone],
            ['value' => '081234567891', 'label' => 'Telepon rumah pasien', 'is_primary' => true],
        );

        $alamatBudi = PatientAddress::updateOrCreate(
            ['patient_profile_id' => $ibunyaBudi->id, 'label' => 'Rumah Ibu'],
            [
                'recipient_name' => 'Budi Santoso', 'phone' => '081234567890',
                'address_line' => 'Jl. Merpati No. 12, RT 03/RW 07, Kelurahan Bareng, Kecamatan Klaten Tengah',
                'city' => 'Klaten', 'province' => 'Jawa Tengah', 'postal_code' => '57416',
                'notes' => 'Pagar hijau sebelah warung Bu Ning. Kabari 30 menit sebelum sampai.',
                'is_primary' => true,
            ],
        );

        // Sari: diri sendiri + ayahnya (pasien pasca-stroke)
        $sariSelf = PatientProfile::updateOrCreate(
            ['user_id' => $sari->id, 'relationship' => 'diri_sendiri'],
            ['name' => 'Sari Melati', 'gender' => Gender::Female, 'birth_date' => '1992-01-20', 'blood_type' => 'A'],
        );

        $ayahnyaSari = PatientProfile::updateOrCreate(
            ['user_id' => $sari->id, 'name' => 'Slamet Riyadi'],
            [
                'relationship' => 'ayah', 'gender' => Gender::Male,
                'birth_date' => '1955-03-17', 'blood_type' => 'O',
                'medical_notes' => 'Pasca-stroke iskemik 2 bulan lalu; kelemahan sisi kiri, masih bisa berjalan dengan bantuan.',
            ],
        );

        $alamatSari = PatientAddress::updateOrCreate(
            ['patient_profile_id' => $ayahnyaSari->id, 'label' => 'Rumah'],
            [
                'recipient_name' => 'Sari Melati', 'phone' => '081298765432',
                'address_line' => 'Perumahan Griya Asri Blok C5, Jl. Pemuda Km 3, Kelurahan Jetis, Kecamatan Klaten Selatan',
                'city' => 'Klaten', 'province' => 'Jawa Tengah', 'postal_code' => '57424',
                'notes' => 'Kamar pasien di lantai 1 dekat ruang tamu.',
                'is_primary' => true,
            ],
        );

        return [
            'admin' => $admin, 'coordinator' => $coordinator, 'manager' => $manager,
            'staff' => $staff,
            'budi' => $budi, 'sari' => $sari,
            'patients' => [
                'budiSelf' => $budiSelf, 'ibunyaBudi' => $ibunyaBudi, 'alamatBudi' => $alamatBudi,
                'sariSelf' => $sariSelf, 'ayahnyaSari' => $ayahnyaSari, 'alamatSari' => $alamatSari,
            ],
        ];
    }
}
