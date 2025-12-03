<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    use HasFactory;
    protected $table = 'tb_transaksi'; // Sambungkan ke tabel baru
    protected $guarded = ['id'];
    protected $casts = ['tanggal_keluar' => 'date'];

    // Relasi: Transaksi milik satu Aset
    public function aset()
    {
        return $this->belongsTo(AsetModel::class, 'aset_id', 'id');
    }
}