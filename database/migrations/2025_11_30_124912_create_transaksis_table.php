<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('tb_transaksi', function (Blueprint $table) {
            $table->id();
            
            // Relasi ke tabel tb_aset
            // pastikan tipe data sama (unsignedBigInteger)
            $table->unsignedBigInteger('aset_id'); 
            
            $table->date('tanggal_keluar');
            $table->integer('jumlah_keluar');
            
            // Alasan: Pemakaian, Hibah, Rusak/Hilang
            $table->string('alasan'); 
            
            // Penerima / Pihak yang bertanggung jawab saat barang keluar
            $table->string('penerima')->nullable(); 
            
            // Field Khusus (Hanya diisi jika Rusak/Hilang)
            $table->decimal('biaya_tanggungan', 15, 2)->default(0); 
            
            $table->text('keterangan')->nullable();
            $table->timestamps();

            // Foreign Key (Opsional, agar data aman)
            $table->foreign('aset_id')->references('id')->on('tb_aset')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksis');
    }
};
