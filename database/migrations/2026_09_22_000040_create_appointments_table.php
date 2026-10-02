<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('homecare_request_id')->constrained()->cascadeOnDelete();
            $table->string('status', 32)->default('scheduled')->index();
            $table->timestamp('scheduled_at')->index();
            $table->unsignedSmallInteger('estimated_duration_minutes')->default(60);
            $table->json('address_snapshot')->nullable(); // salinan alamat saat dijadwalkan
            $table->timestamp('checkin_at')->nullable();
            $table->timestamp('checkout_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
