<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('external_references', function (Blueprint $table) {
            $table->id();
            $table->foreignId('api_integration_id')->constrained()->cascadeOnDelete();
            $table->morphs('externalizable'); // entitas lokal yang dipetakan
            $table->string('external_id', 128);
            $table->string('external_type', 96)->nullable();
            $table->json('payload')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->unique(['api_integration_id', 'external_type', 'external_id'], 'external_refs_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('external_references');
    }
};
