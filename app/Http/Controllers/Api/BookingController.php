<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Seat;
use App\Models\Booking;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class BookingController extends Controller
{
    /**
     * LOGIKA UNTUK: GET /api/seats
     * PERUBAHAN ADA DI SINI.
     * Mengambil data kursi dan MENGELOMPOKKANNYA per baris.
     */
    public function index()
    {
        // 1. Ambil ID kursi yang 'booked' (Tidak berubah)
        $bookedSeatIds = DB::table('booking_seat')
            ->join('bookings', 'bookings.id', '=', 'booking_seat.booking_id')
            ->where('bookings.status', 'confirmed')
            ->orWhere(function($query) {
                $query->where('bookings.status', 'pending')
                      ->where('bookings.created_at', '>', Carbon::now()->subMinutes(5));
            })
            ->pluck('booking_seat.seat_id');

        // 2. Ambil SEMUA kursi (Tidak berubah)
        $allSeats = Seat::all();

        // 3. Gabungkan data dengan status (Tidak berubah)
        $seatsWithStatus = $allSeats->map(function ($seat) use ($bookedSeatIds) {
            $status = $bookedSeatIds->contains($seat->id) ? 'booked' : 'available';
            
            return [
                'id' => $seat->id,
                'label' => $seat->label,
                'type' => $seat->type,
                'price' => $seat->price,
                'status' => $status,
                // Ekstrak nomor kursi untuk membagi
                'number' => (int) preg_replace('/[^0-9]/', '', $seat->label),
            ];
        });

        // 4. --- INI PERUBAHAN UTAMANYA ---
        //    Kelompokkan berdasarkan baris (A, B, C...)
        $groupedByRow = $seatsWithStatus->groupBy(function ($seat) {
            return substr($seat['label'], 0, 1);
        });

        // 5. Buat struktur Left/Right
        $structuredSeats = $groupedByRow->map(function ($rowSeats, $rowLabel) {
            
            // Tentukan titik pisah lorong
            $aisleSplitPoint = ($rowLabel >= 'A' && $rowLabel <= 'H') ? 15 : 12;

            return [
                'left' => $rowSeats->where('number', '<=', $aisleSplitPoint)->values(),
                'right' => $rowSeats->where('number', '>', $aisleSplitPoint)->values(),
            ];
        });
        
        // 6. Urutkan A-L
        $orderedKeys = collect(range('A', 'L'));
        $finalStructure = $orderedKeys->mapWithKeys(function ($key) use ($structuredSeats) {
            return [$key => $structuredSeats->get($key, ['left' => [], 'right' => []])];
        });

        // 7. Kirim data terstruktur
        return response()->json($finalStructure);
    }

    /**
     * LOGIKA UNTUK: POST /api/bookings
     * FUNGSI INI SUDAH BENAR. TIDAK ADA PERUBAHAN.
     */
    public function store(Request $request)
    {
        // 1. Validasi input (termasuk payment_method)
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_whatsapp' => 'required|string|max:20',
            'payment_method' => 'required|string|in:online,offline',
            'seats' => 'required|array|min:1',
            'seats.*' => 'integer|exists:seats,id',
        ]);

        // (Kita HAPUS semua logika $bookingCode di sini)

        $seatIds = $validated['seats'];
        $totalPrice = 0;

        try {
            // 2. Mulai Transaksi Database
            $booking = DB::transaction(function () use ($validated, $seatIds, &$totalPrice) {

                // 3. Cek ketersediaan kursi (Logika ini SAMA)
                $bookedSeats = Seat::whereIn('id', $seatIds)
                    ->whereIn('id', function ($query) {
                        $query->select('seat_id')
                            ->from('booking_seat')
                            ->join('bookings', 'bookings.id', '=', 'booking_seat.booking_id')
                            ->where('bookings.status', 'confirmed')
                            ->orWhere(function($q) {
                                $q->where('bookings.status', 'pending')
                                ->where('bookings.created_at', '>', Carbon::now()->subMinutes(5));
                            });
                    })->pluck('label');

                if ($bookedSeats->isNotEmpty()) {
                    throw new \Exception("Maaf, kursi " . $bookedSeats->implode(', ') . " sudah terisi.");
                }

                // 4. Hitung total harga (Logika ini SAMA)
                $totalPrice = Seat::whereIn('id', $seatIds)->sum('price');

                // 5. Buat data booking (TANPA booking_code)
                $newBooking = Booking::create([
                    'customer_name' => $validated['customer_name'],
                    'customer_whatsapp' => $validated['customer_whatsapp'],
                    'payment_method' => $validated['payment_method'], // <-- Data baru Anda
                    'total_price' => $totalPrice,
                    'status' => 'pending',
                ]);

                // 6. Lampirkan kursi (Logika ini SAMA)
                $seatData = collect($seatIds)->mapWithKeys(function ($id) {
                    return [$id => ['created_at' => now(), 'updated_at' => now()]];
                });
                $newBooking->seats()->sync($seatData);

                return $newBooking;
            });

            // 7. Kirim respon sukses (TANPA booking_code)
            return response()->json([
                'message' => 'Pesanan berhasil dibuat!',
                'booking_id' => $booking->id // Kirim ID saja (jika perlu)
            ], 201);

        } catch (\Exception $e) {
            if (Str::contains($e->getMessage(), 'Maaf, kursi')) {
                return response()->json(['message' => $e->getMessage()], 409);
            }
            return response()->json(['message' => 'Terjadi kesalahan internal. Silakan coba lagi.'], 500);
        }
    }
}
