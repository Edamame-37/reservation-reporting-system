<?php

/**
 * ====================================================================================================
 * SISTEM INFORMASI RESERVASI & PELAPORAN FASILITAS KAMPUS (CAVA)
 * MODUL 9: PORTAL PENGGUNA (SIVITAS) - DASBOR PRIBADI & RINGKASAN TIKET AKTIF
 * BERKAS: app/Http/Controllers/DashboardUserController.php
 * ====================================================================================================
 * 
 * DESKRIPSI BERKAS:
 * Controller ini adalah pengendali antarmuka beranda personal sivitas akademika (Mahasiswa, Dosen, Tendik).
 * Bertanggung jawab mengumpulkan data ringkasan aktivitas peminjaman ruangan mandiri dan tiket keluhan
 * sarana prasarana yang diajukan oleh pengguna yang sedang aktif masuk (logged-in user).
 * 
 * PERAN & ARSITEKTUR DALAM SISTEM CAVA:
 * 1. Spotlight Hero Schedule:
 *    Mendeteksi agenda kegiatan terdekat pemohon yang telah disetujui resmi oleh petugas (`status = 'approved'`)
 *    untuk hari ini atau masa mendatang, guna ditampilkan sebagai kartu sorotan utama (Spotlight Card) pada dasbor.
 * 2. Pelacakan Cepat Riwayat Permohonan (Recent Bookings):
 *    Menyajikan 3 transaksi permohonan reservasi paling mutakhir milik pengguna beserta status verifikasinya
 *    (pending, approved, rejected, cancelled).
 * 3. Pemantauan Tiket Kerusakan Aktif (Active Incident Monitoring):
 *    Menampilkan 2 tiket aduan malfungsi fasilitas yang masih dalam proses investigasi atau perbaikan
 *    oleh teknisi sarpras kampus (status: 'baru' atau 'diproses').
 * 4. Isolasi Data Berbasis Pengguna (Data Multi-Tenancy / Scoping):
 *    Setiap kueri Eloquent secara ketat dibatasi oleh klausa `where('user_id', $userId)` sehingga sivitas
 *    hanya dapat melihat data miliknya sendiri demi menjaga privasi dan integritas data kampus.
 * ====================================================================================================
 */

namespace App\Http\Controllers;

use App\Models\DamageReport;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Class DashboardUserController
 * 
 * Mengatur pengambilan data dinamis dan perenderan dasbor personal pengguna sivitas akademika.
 */
class DashboardUserController extends Controller
{
    /**
     * FUNCTION: index()
     * ------------------------------------------------------------------------------------------------
     * KEGUNAAN   : Merender halaman dasbor utama sivitas dengan data agenda terdekat dan ringkasan tiket.
     * CARA KERJA :
     * 1. Mengidentifikasi primary key akun pengguna yang sedang login (`Auth::id()`).
     * 2. Menarik 1 reservasi mendatang terdekat yang telah disetujui (`upcomingReservation`).
     * 3. Mengambil 3 riwayat pengajuan reservasi terbaru serta menghitung total seluruh reservasi pengguna.
     * 4. Mengambil 2 tiket laporan kerusakan fasilitas aktif serta menghitung total laporan yang diajukan.
     * 5. Mengirimkan seluruh kumpulan variabel ke view `user.dashboard`.
     * 
     * @return View
     */
    public function index(): View
    {
        // Mengambil ID akun pengguna yang terotentikasi saat ini
        $userId = Auth::id();

        // ============================================================================================
        // 1. AMBIL RESERVASI MENDATANG TERDEKAT (STATUS APPROVED, TANGGAL >= HARI INI)
        // ============================================================================================
        // Menyaring hanya permohonan milik user yang sudah sah disetujui untuk kegiatan hari ini ke depan
        $upcomingReservation = Reservation::with('facility')
            ->where('user_id', $userId)
            ->where('status', 'approved')
            // Membatasi tanggal reservasi sama dengan atau setelah tanggal hari ini (now()->toDateString())
            ->whereDate('reservation_date', '>=', now()->toDateString())
            // Mengurutkan dari tanggal paling awal dan jam mulai paling pagi (agenda terdekat)
            ->orderBy('reservation_date', 'asc')
            ->orderBy('start_time', 'asc')
            ->first();

        // ============================================================================================
        // 2. AMBIL 3 RIWAYAT RESERVASI TERBARU (SEMUA STATUS: PENDING, APPROVED, REJECTED, CANCELLED)
        // ============================================================================================
        // Memuat metadata fasilitas ruangan dengan eager loading guna mencegah N+1 query
        $recentReservations = Reservation::with('facility')
            ->where('user_id', $userId)
            // latest() setara dengan orderBy('created_at', 'desc')
            ->latest()
            // Membatasi hanya 3 data teratas untuk widget ringkasan
            ->take(3)
            ->get();

        // Menghitung akumulasi seluruh tiket reservasi yang pernah diajukan oleh pengguna ini
        $totalReservations = Reservation::where('user_id', $userId)->count();

        // ============================================================================================
        // 3. AMBIL 2 LAPORAN KERUSAKAN FASILITAS AKTIF (STATUS 'BARU' ATAU 'DIPROSES')
        // ============================================================================================
        // Memantau penanganan insiden yang belum selesai ditangani oleh teknisi UPT Sarpras
        $activeDamageReports = DamageReport::with('facility')
            ->where('user_id', $userId)
            ->whereIn('status', ['baru', 'diproses', 'submitted', 'in_progress', 'in_review'])
            ->latest()
            ->take(2)
            ->get();

        // Menghitung total seluruh tiket aduan kerusakan yang pernah dikirimkan oleh sivitas
        $totalDamageReports = DamageReport::where('user_id', $userId)->count();

        // Meneruskan seluruh dataset ke view Blade template user.dashboard
        return view('user.dashboard', compact(
            'upcomingReservation',
            'recentReservations',
            'totalReservations',
            'activeDamageReports',
            'totalDamageReports'
        ));
    }
}
