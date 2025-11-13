<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// --- IMPORT SEMUA MODEL & HELPER YANG KITA BUTUHKAN ---
use App\Models\Booking;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    /**
     * Logika untuk LOGIN
     * POST /api/admin/login
     */
    public function login(Request $request)
    {
        // 1. Validasi input: email dan password wajib ada
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // 2. Coba lakukan login
        if (Auth::attempt($credentials)) {
            // 3. Jika berhasil:
            $user = Auth::user();
            
            // Buat "Kunci" (Token) baru untuk user ini
            $token = $user->createToken('admin-token')->plainTextToken;

            // 4. Kirim respon sukses beserta "Kunci" (Token)
            return response()->json([
                'message' => 'Login berhasil',
                'user' => $user,
                'token' => $token,
            ], 200);
        }

        // 5. Jika gagal (email atau password salah)
        return response()->json(['message' => 'Email atau password salah.'], 401);
    }

    /**
     * Logika untuk LOGOUT
     * POST /api/admin/logout (Perlu login)
     */
    public function logout(Request $request)
    {
        // Hapus "Kunci" (Token) yang sedang dipakai
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout berhasil'], 200);
    }

    /**
     * Mengambil data user yang sedang login
     * GET /api/admin/user (Perlu login)
     */
    public function getUser(Request $request)
    {
        // Kirim kembali data user yang sedang login
        return response()->json($request->user());
    }

    /**
     * Mengambil SEMUA data pesanan
     * GET /api/admin/bookings (Perlu login)
     */
    public function getBookings()
    {
        // Ambil semua pesanan, urutkan dari yang terbaru
        // 'with('seats')' = Ambil juga data kursi yang dipesan (dari relasi)
        $bookings = Booking::with('seats')
                           ->orderBy('created_at', 'desc')
                           ->get();
        
        return response()->json($bookings);
    }

    /**
     * Mengonfirmasi 1 pesanan (Ubah status)
     * PUT /api/admin/bookings/{id}/confirm (Perlu login)
     */
    public function confirmBooking($id)
    {
        $booking = Booking::find($id);

        if (!$booking) {
            return response()->json(['message' => 'Pesanan tidak ditemukan'], 404);
        }

        // Ubah status dan simpan
        $booking->status = 'confirmed';
        $booking->save();

        return response()->json(['message' => 'Pesanan berhasil dikonfirmasi', 'booking' => $booking]);
    }

    /**
     * Menghapus 1 pesanan
     * DELETE /api/admin/bookings/{id} (Perlu login)
     */
    public function deleteBooking($id)
    {
        $booking = Booking::find($id);

        if (!$booking) {
            return response()->json(['message' => 'Pesanan tidak ditemukan'], 404);
        }

        // Hapus pesanan
        // (onDelete('cascade') di database akan otomatis hapus data di 'booking_seat')
        $booking->delete();

        return response()->json(['message' => 'Pesanan berhasil dihapus']);
    }
}