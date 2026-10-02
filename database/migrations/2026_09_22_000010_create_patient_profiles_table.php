<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // akun pemilik (pasien/keluarga)
            $table->string('name');
            $table->string('nik', 32)->nullable()->unique();
            $table->string('gender', 16)->nullable();
            $table->date('birth_date')->nullable();
            $table->string('blood_type', 8)->nullable();
            $table->string('relationship', 64)->nullable(); // diri_sendiri, ayah, ibu, ...
            $table->text('medical_notes')->nullable(); // riwayat penyakit, alergi, obat rutin
            $table->timestamps();

            $table->index(['user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_profiles');
    }
};
