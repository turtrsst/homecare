<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('homecare_requests', function (Blueprint $table) {
            $table->id();
            $table->string('code', 32)->unique(); // HC-YYYYMM-XXXX
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // pengaju (akun)
            $table->foreignId('patient_profile_id')->constrained()->restrictOnDelete();
            $table->foreignId('patient_address_id')->constrained('patient_addresses')->restrictOnDelete();

            $table->string('status', 32)->default('draft')->index();
            $table->text('complaint')->nullable(); // kebutuhan/keluhan utama
            $table->text('notes')->nullable();

            // Preferensi pasien
            $table->date('preferred_date')->nullable();
            $table->string('preferred_time_window', 32)->nullable();

            // Verifikasi
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->text('rejected_reason')->nullable();
            $table->text('information_request')->nullable(); // pertanyaan saat need_information
            $table->text('information_response')->nullable(); // jawaban pasien

            // Skrining
            $table->foreignId('screened_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('screened_at')->nullable();
            $table->text('screening_notes')->nullable();

            // Biaya
            $table->decimal('total_amount', 14, 2)->default(0);
            $table->string('payment_status', 32)->default('unpaid')->index();

            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index(['status', 'preferred_date']);
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('homecare_requests');
    }
};
