<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Booking;
use Illuminate\Support\Carbon;

class PruneOldBookings extends Command
{
    /**
     * The signature of the command.
     */
    protected $signature = 'app:prune-bookings-v2';

    /**
     * The description of the command.
     */
    protected $description = 'Menghapus pesanan "pending" yang kadaluarsa (lebih dari 5 menit)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // 1. Tulis log ke file (bukan ke terminal)
        // Ini adalah cara profesional untuk mencatat
        $this->info('Mulai membersihkan pesanan kadaluarsa...');
        
        // 2. Tentukan batas waktu (kita tetap pakai 'Asia/Jakarta'
        //    untuk memastikan zona waktu 100% benar)
        $fiveMinutesAgo = Carbon::now('Asia/Jakarta')->subMinutes(15);

        // 3. Cari dan Hapus
        $deletedCount = Booking::where('status', 'pending')
                               ->where('created_at', '<=', $fiveMinutesAgo)
                               ->delete();

        // 4. Beri laporan (ini juga akan masuk log file)
        if ($deletedCount > 0) {
            $this->info("Selesai! Berhasil menghapus $deletedCount pesanan kadaluarsa.");
        } else {
            $this->info('Selesai! Tidak ada pesanan kadaluarsa untuk dihapus.');
        }
    }
}