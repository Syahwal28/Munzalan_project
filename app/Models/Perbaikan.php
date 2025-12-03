<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Perbaikan extends Model
{
    use HasFactory;

    /**
     * Nama tabel di database.
     */
    protected $table = 'tb_perbaikan';

    /**
     * Kolom yang boleh diisi secara massal (Mass Assignment).
     */
    protected $fillable = [
        'aset_id',
        'jumlah_perbaikan',
        'tgl_masuk',
        'tgl_selesai',
        'tempat_service',
        'penanggung_jawab',
        'status',               // Enum: 'Proses', 'Selesai', 'Batal'
        'biaya',
        'bukti_nota',           // Menyimpan path/nama file gambar
        'keterangan_kerusakan',
        'keterangan_perbaikan'
    ];

    /**
     * Casting tipe data otomatis.
     * Ini penting agar tanggal bisa langsung diformat di View ($item->tgl_masuk->format('...'))
     */
    protected $casts = [
        'tgl_masuk' => 'date',
        'tgl_selesai' => 'date',
        'biaya' => 'decimal:2', // Memastikan format desimal untuk uang
    ];

    /**
     * Relasi ke Model Aset (AsetModel).
     * Satu data perbaikan adalah milik satu aset.
     */
    public function aset()
    {
        // Parameter 2: foreign_key di tabel perbaikan (aset_id)
        // Parameter 3: primary_key di tabel aset (id)
        return $this->belongsTo(AsetModel::class, 'aset_id', 'id');
    }
}