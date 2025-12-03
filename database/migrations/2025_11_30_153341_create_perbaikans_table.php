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
        Schema::create('tb_perbaikan', function (Blueprint $table) {
            $table->id();
        
            // Aset mana yang rusak?
            $table->unsignedBigInteger('aset_id');
            
            // Informasi Service
            $table->date('tgl_masuk');
            $table->date('tgl_selesai')->nullable(); // Nullable karena belum tentu langsung selesai
            $table->string('tempat_service')->nullable(); // Nama Toko/Teknisi
            $table->string('penanggung_jawab'); // Siapa yang mengantar
            
            // Status Pengerjaan
            $table->enum('status', ['Proses', 'Selesai', 'Batal'])->default('Proses');
            
            // Biaya (Diisi nanti saat selesai)
            $table->decimal('biaya', 15, 2)->default(0);

            $table->string('bukti_nota')->nullable();
            $table->text('keterangan_kerusakan');
            $table->text('keterangan_perbaikan')->nullable();
            
            $table->timestamps();

            // Relasi
            $table->foreign('aset_id')->references('id')->on('tb_aset')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_perbaikan');
    }
};
