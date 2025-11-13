<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User; // <-- Kita hanya perlu Model User

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Kosongkan tabel
        User::truncate(); 

        // 2. Buat User dengan Teks Biasa
        User::create([
            'name' => 'Admin Xione',
            'email' => 'admin@xione.com',
            'password' => 'admin1357', // <-- TULIS TEKS BIASA DI SINI
        ]);

        // 3. Panggil SeatSeeder
        $this->call(SeatSeeder::class);
    }
}