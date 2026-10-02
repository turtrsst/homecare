<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('healthcare_staff_id')->constrained('healthcare_staff')->restrictOnDelete();
            $table->unsignedSmallInteger('systolic_bp')->nullable();
            $table->unsignedSmallInteger('diastolic_bp')->nullable();
            $table->unsignedSmallInteger('pulse')->nullable();
            $table->unsignedSmallInteger('respiratory_rate')->nullable();
            $table->decimal('temperature_c', 4, 1)->nullable();
            $table->unsignedSmallInteger('oxygen_saturation')->nullable();
            $table->string('consciousness', 32)->nullable(); // compos mentis, dll.
            $table->unsignedTinyInteger('pain_scale')->nullable(); // 0-10
            $table->decimal('weight_kg', 5, 1)->nullable();
            $table->decimal('height_cm', 5, 1)->nullable();
            $table->text('findings')->nullable();
            $table->json('payload')->nullable(); // kolom tambahan (extensible)
            $table->timestamp('assessed_at');
            $table->timestamps();

            $table->index(['appointment_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessments');
    }
};
