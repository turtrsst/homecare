<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('homecare_request_id')->constrained()->cascadeOnDelete();
            $table->string('method', 32); // App\Enums\PaymentMethod
            $table->string('status', 32)->default('paid')->index();
            $table->decimal('amount', 14, 2);
            $table->string('reference_number', 96)->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->string('gateway', 64)->nullable(); // nama gateway bila via integrasi
            $table->json('gateway_payload')->nullable();
            $table->timestamps();

            $table->index(['homecare_request_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
