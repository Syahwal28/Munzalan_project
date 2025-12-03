<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AsetModel extends Model
{
    use HasFactory;

    // Menghubungkan Model ini ke tabel 'tb_aset'
    protected $table = 'tb_aset';

    // Mass assignment protection (Semua boleh diisi kecuali ID)
    protected $guarded = ['id'];

    // Auto-format kolom tanggal
    protected $casts = [
        'tanggal_perolehan' => 'date',
    ];

    // Relasi: Satu Aset bisa punya banyak Transaksi Keluar
    public function transaksis()
    {
        return $this->hasMany(Transaksi::class, 'aset_id', 'id');
    }

    public function perbaikans()
    {
        return $this->hasMany(Perbaikan::class, 'aset_id', 'id');
    }
}