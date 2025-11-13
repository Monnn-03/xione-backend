<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Seat;
use Carbon\Carbon;

class SeatSeeder extends Seeder
{
    public function run(): void
    {
        $priceVip = 30000;
        $priceRegular = 25000;
        $allSeats = [];
        $now = Carbon::now();

        // Loop untuk Baris A sampai H
        foreach (range('A', 'H') as $row) {
            for ($i = 1; $i <= 30; $i++) {
                $type = 'regular'; $price = $priceRegular;
                if (in_array($row, ['A', 'B', 'C']) && $i >= 4 && $i <= 26) $type = 'vip';
                if ($row == 'D' && $i >= 10 && $i <= 21) $type = 'vip';
                if ($type == 'vip') $price = $priceVip;
                
                $allSeats[] = ['label' => $row.$i, 'type' => $type, 'price' => $price, 'created_at' => $now, 'updated_at' => $now];
            }
        }

        // Loop untuk Baris I sampai L
        foreach (range('I', 'L') as $row) {
            for ($i = 1; $i <= 24; $i++) {
                $allSeats[] = ['label' => $row.$i, 'type' => 'regular', 'price' => $priceRegular, 'created_at' => $now, 'updated_at' => $now];
            }
        }

        // Masukkan data (336 kursi bersih)
        Seat::insert($allSeats);
    }
}