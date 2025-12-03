<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run()
    {
        User::create([
            'nama_user'  => 'Admin Munzalan',
            'username'   => 'admin', // LOGIN PAKAI INI
            'password'   => Hash::make('password'),
            'role_user'  => 'admin',
            'no_hp_user' => '081234567890'
        ]);
        
        User::create([
            'nama_user'  => 'Ustadz Fulan',
            'username'   => 'ustadz',
            'password'   => Hash::make('password'),
            'role_user'  => 'ustadz',
            'no_hp_user' => '089876543210'
        ]);
    }
}