<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('homecare_services', function (Blueprint $table) {
            $table->id();
            $table->string('code', 32)->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('category', 96)->nullable();
            $table->text('description')->nullable();
            $table->text('short_description')->nullable();
            $table->unsignedSmallInteger('duration_minutes')->default(60);
            $table->decimal('price', 14, 2)->default(0);
            $table->string('price_note', 191)->nullable(); // mis. "belum termasuk obat"
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('is_featured')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('homecare_services');
    }
};
