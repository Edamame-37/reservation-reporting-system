<?php
/**
 * NAMA FILE    : DashboardUserController.php
 * FUNGSI       : Kontroler Dasbor Utama Mahasiswa & Dosen
 * DESKRIPSI    : Menampilkan ringkasan reservasi aktif dan status tiket kerusakan milik pengguna.
 */

namespace App\Http\Controllers;

use App\Models\DamageReport;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardUserController extends Controller
{
    /**
     * FUNCTION/PROCEDURE : index()
     * KEGUNAAN           : Merender halaman dasbor utama pengguna dengan data dinamis.
     */
    public function index(): View
    {
        $userId = Auth::id();

        // 1. Ambil Reservasi Mendatang (Status Approved, Tanggal >= Hari Ini)
        $upcomingReservation = Reservation::with('facility')
            ->where('user_id', $userId)
            ->where('status', 'approved')
            ->whereDate('reservation_date', '>=', now()->toDateString())
            ->orderBy('reservation_date', 'asc')
            ->orderBy('start_time', 'asc')
            ->first();

        // 2. Ambil 3 Riwayat Reservasi Terbaru (Apapun statusnya)
        $recentReservations = Reservation::with('facility')
            ->where('user_id', $userId)
            ->latest()
            ->take(3)
            ->get();
        $totalReservations = Reservation::where('user_id', $userId)->count();

        // 3. Ambil 2 Laporan Kerusakan Aktif (baru atau diproses)
        $activeDamageReports = DamageReport::with('facility')
            ->where('user_id', $userId)
            ->whereIn('status', ['baru', 'diproses'])
            ->latest()
            ->take(2)
            ->get();
        $totalDamageReports = DamageReport::where('user_id', $userId)->count();

        return view('user.dashboard', compact(
            'upcomingReservation',
            'recentReservations',
            'totalReservations',
            'activeDamageReports',
            'totalDamageReports'
        ));
    }
}
