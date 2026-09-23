<?php
/**
 * NAMA FILE    : DashboardPetugasController.php
 * FUNGSI       : Controller pengelola dasbor operasional pemantauan sarpras (PTG-01 / US-8)
 * DESKRIPSI    : Menyediakan agregasi kuantitas antrean (pending reservasi, keluhan kerusakan aktif, fasilitas maintenance) dan ringkasan 5 data teratas antrean verifikasi untuk Petugas Sarpras.
 * CARA KERJA   : Menerima HTTP GET request, mengeksekusi kueri agregasi dan eager loading (with) untuk mencegah N+1 problem, dan merender view petugas.dashboard dengan passing data compact().
 */

namespace App\Http\Controllers;

use App\Models\DamageReport;
use App\Models\Facility;
use App\Models\Reservation;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardPetugasController extends Controller
{
    /**
     * FUNCTION/PROCEDURE : index()
     * KEGUNAAN           : Menampilkan dasbor operasional utama dan antrean prioritas petugas sarpras.
     * CARA KERJA         : Mengambil metrik kuantitas reservasi pending, tiket laporan aktif (baru/diproses), fasilitas dalam perbaikan, serta mengambil 5 data antrean teratas secara FIFO dengan relasi user & facility (eager loaded), lalu mengembalikan view 'petugas.dashboard'.
     */
    public function index(): View
    {
        // 1. Perhitungan Metrik (Agregasi Data Antrean)
        $pendingReservationsCount = Reservation::where('status', 'pending')->count();
        $newReportsCount = DamageReport::whereIn('status', ['baru', 'diproses'])->count();
        $lockedFacilitiesCount = Facility::where('status', 'dalam perbaikan')->count();

        // 2. Pengambilan Data Pratinjau Mini-Table (5 Teratas, FIFO, Eager Loading Anti N+1)
        $recentReservations = Reservation::with(['facility', 'user'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'asc')
            ->limit(5)
            ->get();

        $recentReports = DamageReport::with(['facility', 'user'])
            ->whereIn('status', ['baru', 'diproses'])
            ->orderBy('created_at', 'asc')
            ->limit(5)
            ->get();

        // 3. Pengembalian View Dasbor Petugas dengan Variabel Ringkasan
        return view('petugas.dashboard', compact(
            'pendingReservationsCount',
            'newReportsCount',
            'lockedFacilitiesCount',
            'recentReservations',
            'recentReports'
        ));
    }
}
