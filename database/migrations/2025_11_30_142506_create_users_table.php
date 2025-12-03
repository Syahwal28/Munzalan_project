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
        Schema::create('tb_user', function (Blueprint $table) {
            // Sesuai Gambar: id_user (BigInt, PK, Auto Increment)
            $table->bigIncrements('id_user'); 
            
            // Sesuai Gambar: nama_user (Varchar)
            $table->string('nama_user');
            
            // Sesuai Gambar: username (Varchar) - Kita buat Unique biar tidak duplikat
            $table->string('username')->unique();
            
            // Sesuai Gambar: password (Varchar)
            $table->string('password');
            
            // Sesuai Gambar: no_hp_user (Varchar, Nullable)
            $table->string('no_hp_user')->nullable();
            
            // Sesuai Gambar: role_user (Enum)
            $table->enum('role_user', ['admin', 'ustadz', 'direktur'])->default('admin');
            
            $table->timestamps(); // created_at & updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_user');
    }
};
