<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('staff_assignments', function (Blueprint $table) {
            // Tanda tangan digital petugas: kapan ia menekan "Konfirmasi Tugas".
            $table->timestamp('confirmed_at')->nullable()->after('assigned_at');
        });
    }

    public function down(): void
    {
        Schema::table('staff_assignments', function (Blueprint $table) {
            $table->dropColumn('confirmed_at');
        });
    }
};
