<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('homecare_request_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('homecare_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('homecare_service_id')->nullable()->constrained()->nullOnDelete();
            $table->string('service_name'); // snapshot nama layanan saat pengajuan
            $table->unsignedSmallInteger('quantity')->default(1);
            $table->decimal('unit_price', 14, 2)->default(0); // snapshot tarif
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['homecare_request_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('homecare_request_items');
    }
};
