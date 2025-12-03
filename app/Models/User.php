<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    // 1. Definisikan Nama Tabel Baru
    protected $table = 'tb_user'; 

    // 2. Primary Key Custom
    protected $primaryKey = 'id_user';

    // 3. Kolom yang boleh diisi
    protected $fillable = [
        'nama_user',
        'username',
        'password',
        'no_hp_user',
        'role_user',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
}