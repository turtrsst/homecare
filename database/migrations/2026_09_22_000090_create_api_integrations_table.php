<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_integrations', function (Blueprint $table) {
            $table->id();
            $table->string('code', 64)->unique(); // simrs | satusehat | payment | whatsapp | maps
            $table->string('name');
            $table->string('driver', 64)->nullable();
            $table->string('base_url')->nullable();
            $table->boolean('is_active')->default(false);
            $table->json('settings')->nullable(); // di-encrypt pada model
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_integrations');
    }
};
