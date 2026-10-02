<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('homecare_services', function (Blueprint $table) {
            // Rincian tarif SK (Jasa Sarana + Jasa Pelayanan = price/Jumlah).
            $table->decimal('jasa_sarana', 14, 2)->default(0)->after('price');
            $table->decimal('jasa_pelayanan', 14, 2)->default(0)->after('jasa_sarana');

            // Ikon garis (lihat components/icon.blade.php) & thumbnail ilustrasi.
            $table->string('icon', 32)->nullable()->after('name');
            $table->string('thumbnail', 191)->nullable()->after('icon');
        });
    }

    public function down(): void
    {
        Schema::table('homecare_services', function (Blueprint $table) {
            $table->dropColumn(['jasa_sarana', 'jasa_pelayanan', 'icon', 'thumbnail']);
        });
    }
};
