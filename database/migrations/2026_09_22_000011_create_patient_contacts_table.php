<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_profile_id')->constrained()->cascadeOnDelete();
            $table->string('type', 32); // App\Enums\ContactType
            $table->string('value', 191);
            $table->string('label', 64)->nullable(); // contoh: "Ibu"
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->index(['patient_profile_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_contacts');
    }
};
