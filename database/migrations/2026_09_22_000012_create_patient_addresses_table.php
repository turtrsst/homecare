<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_profile_id')->constrained()->cascadeOnDelete();
            $table->string('label', 64)->nullable(); // "Rumah", "Kantor"
            $table->string('recipient_name');
            $table->string('phone', 32);
            $table->text('address_line');
            $table->string('city', 96);
            $table->string('province', 96)->nullable();
            $table->string('postal_code', 16)->nullable();
            $table->text('notes')->nullable(); // patokan, akses masuk
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->index(['patient_profile_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_addresses');
    }
};
