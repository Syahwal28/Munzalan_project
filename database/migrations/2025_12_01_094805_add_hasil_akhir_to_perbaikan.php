<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tb_perbaikan', function (Blueprint $table) {
            $table->string('hasil_akhir')->nullable()->after('status'); // Isi: 'Baik', 'Rusak Berat'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tb_perbaikan', function (Blueprint $table) {
             $table->dropColumn('hasil_akhir');
        });
    }
};
