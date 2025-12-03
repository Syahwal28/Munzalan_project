<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tb_aset', function (Blueprint $table) {
            $table->id();

            // Identitas Utama
            $table->string('kode_aset')->unique(); // Contoh: INV-2024-001
            $table->string('nama_barang');
            
            // Pengganti Enum -> Pakai String biasa
            // Nanti isinya: 'Elektronik', 'Furniture', 'Lahan', dll (diatur di View)
            $table->string('kategori'); 
            
            // Detail Fisik
            $table->integer('jumlah')->default(1);
            $table->string('satuan')->default('unit'); // Pcs, Unit, Buah, Meter
            
            // Kondisi & Sumber
            $table->string('kondisi'); // Baik, Rusak Ringan, Rusak Berat
            $table->string('sumber_aset'); // Wakaf, Hibah, Beli Sendiri
            
            // Data Tambahan
            $table->date('tanggal_perolehan')->nullable();
            $table->decimal('harga_perolehan', 15, 2)->nullable(); // Pakai decimal biar presisi rupiahnya
            $table->string('lokasi')->nullable(); // Gudang, Kantor, Masjid
            $table->string('penanggung_jawab');
            $table->text('keterangan')->nullable(); // Spesifikasi dll
            $table->string('foto_barang')->nullable();

            $table->timestamps(); // created_at & updated_at
        });
    }

    public function down()
    {
        Schema::dropIfExists('assets');
    }
};