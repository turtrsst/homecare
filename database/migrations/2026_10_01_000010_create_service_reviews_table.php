<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('homecare_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('appointment_id')->nullable()->constrained('appointments')->nullOnDelete();
            $table->foreignId('healthcare_staff_id')->nullable()->constrained('healthcare_staff')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('rating'); // 1–5
            $table->text('comment')->nullable();
            $table->timestamps();

            // Satu ulasan per pengajuan, untuk petugas utama yang memeriksa.
            $table->unique('homecare_request_id');
            $table->index('healthcare_staff_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_reviews');
    }
};
