<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('healthcare_staff_id')->constrained('healthcare_staff')->restrictOnDelete();
            $table->text('actions_taken'); // tindakan yang dilakukan
            $table->text('results')->nullable(); // hasil/evaluasi tindakan
            $table->text('recommendations')->nullable(); // anjuran untuk pasien/keluarga
            $table->boolean('follow_up_needed')->default(false);
            $table->text('follow_up_notes')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();

            $table->index(['appointment_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_records');
    }
};
