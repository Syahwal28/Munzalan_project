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
        Schema::table('tb_aset', function (Blueprint $table) {
            // Hapus aturan unique pada kolom kode_aset
            $table->dropUnique(['kode_aset']); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kode_aset', function (Blueprint $table) {
            //
        });
    }
};
