<?php

/**
 * ====================================================================================================
 * SISTEM INFORMASI RESERVASI & PELAPORAN FASILITAS KAMPUS (CAVA)
 * MODUL 7: KONSOL ADMIN - DASBOR EKSEKUTIF & STATISTIK UTAMA (ADM-04 / US-17)
 * BERKAS: app/Http/Controllers/AdminDashboardController.php
 * ====================================================================================================
 * 
 * DESKRIPSI BERKAS:
 * Controller ini adalah pusat kendali analitik eksekutif (Executive Dashboard Controller) bagi Super Admin.
 * Bertanggung jawab mengagregasikan seluruh indikator performa utama (Key Performance Indicators / KPI)
 * sistem CAVA secara real-time, meliputi verifikasi sivitas akademika, inventaris fasilitas fisik,
 * rasio okupansi peminjaman ruangan, dan indeks resolusi tiket perbaikan sarana prasarana.
 * 
 * PERAN & ARSITEKTUR DALAM SISTEM CAVA:
 * 1. Agregasi Real-Time Multi-Tabel:
 *    Menggabungkan metrik dari 4 entitas inti: `users` (status pending sivitas), `facilities` (status aktif &
 *    dalam perbaikan), `reservations` (transaksi sewa & persentase okupansi), dan `damage_reports` (tiket perbaikan & SLA).
 * 2. Perhitungan Statistik Dinamis (Downtime-Free Calculations):
 *    - Tingkat Okupansi Ruangan (Monthly Occupancy Rate): Menghitung rasio permohonan yang disetujui
 *      terhadap total permohonan dalam bulan berjalan.
 *    - Indeks Kepatuhan SLA Resolusi Kerusakan (SLA Resolution Index): Menghitung persentase tiket
 *      kerusakan yang berhasil diselesaikan ('selesai' / 'resolved') dalam siklus bulan berjalan.
 *    - Analitik Utilisasi per Gedung Kampus: Mengukur tingkat kepadatan penggunaan ruangan pada masing-masing gedung.
 * 3. Tampilan Eksekutif Bento UI:
 *    Menyiapkan dataset ringkas (preview 3 data teratas) untuk ditampilkan pada kartu-kartu Bento UI interaktif
 *    di `resources/views/admin/dashboard.blade.php`.
 * ====================================================================================================
 */

namespace App\Http\Controllers;

use App\Models\DamageReport;
use App\Models\Facility;
use App\Models\Reservation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Class AdminDashboardController
 * 
 * Mengendalikan kalkulasi metrik, agregasi kueri analitik, dan perenderan dasbor eksekutif super admin.
 */
class AdminDashboardController extends Controller
{
    /**
     * FUNCTION: index()
     * ------------------------------------------------------------------------------------------------
     * KEGUNAAN   : Mengumpulkan seluruh data analitik, menghitung 4 metrik KPI utama, dan merender view dasbor.
     * CARA KERJA :
     * 1. Menentukan parameter temporal (tanggal hari ini, awal bulan, dan akhir bulan via Carbon).
     * 2. Menghitung metrik antrean verifikasi pendaftaran mandiri sivitas (total pending, pending hari ini, total akun).
     * 3. Mengagregasikan status inventaris sarana prasarana kampus (total ruangan, ruangan aktif, dalam perbaikan, total kapasitas kursi).
     * 4. Menghitung tingkat okupansi reservasi bulan berjalan (`occupancyRate`).
     * 5. Menghitung metrik insiden kerusakan aset dan rasio resolusi tiket tepat waktu (`slaResolutionPercent`).
     * 6. Menghitung utilisasi per gedung kampus berdasarkan persentase reservasi disetujui.
     * 7. Merender template `admin.dashboard` dengan seluruh variabel terkompaksi.
     * 
     * @return View
     */
    public function index(): View
    {
        // Penentuan parameter waktu lokal menggunakan pustaka Carbon (Time Engine)
        $today        = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth()->toDateString(); // Format Y-m-d awal bulan berjalan
        $endOfMonth   = Carbon::now()->endOfMonth()->toDateString();   // Format Y-m-d akhir bulan berjalan

        // ============================================================================================
        // 1. METRIK VERIFIKASI AKUN SIVITAS AKADEMIKA (ADM-01 / UR15)
        // ============================================================================================
        // Menghitung total pendaftar mandiri yang masih berstatus 'pending' dan membutuhkan verifikasi berkas
        $pendingUsersCount   = User::where('status', 'pending')->count();

        // Menghitung jumlah pendaftar baru yang masuk khusus pada hari ini
        $pendingUsersToday   = User::where('status', 'pending')->whereDate('created_at', $today)->count();

        // Total seluruh pengguna terdaftar dalam ekosistem kampus (sivitas, petugas, admin)
        $totalUsersCount     = User::count();

        // Mengambil 3 antrean pendaftar mandiri teratas untuk disajikan pada widget tinjauan cepat dasbor
        $pendingUsersPreview = User::where('status', 'pending')->latest()->take(3)->get();

        // ============================================================================================
        // 2. METRIK INVENTARIS FASILITAS & SARANA PRASARANA KAMPUS (ADM-03)
        // ============================================================================================
        // Total seluruh venue kampus (auditorium, laboratorium, aula, ruang rapat) yang tercatat di master data
        $totalFacilities       = Facility::count();

        // Jumlah fasilitas yang berstatus operasional aktif dan siap dipinjam
        $activeFacilities      = Facility::where('status', 'aktif')->count();

        // Jumlah fasilitas yang sedang dinonaktifkan sementara karena pemeliharaan / perbaikan
        $maintenanceFacilities = Facility::where('status', 'dalam perbaikan')->count();

        // Total akumulasi daya tampung kursi dari seluruh ruangan aktif di kampus
        $totalCapacitySeats    = Facility::sum('capacity');

        // Mengambil 3 fasilitas terbaru untuk pratinjau kartu inventaris
        $facilitiesPreview     = Facility::latest()->take(3)->get();

        // ============================================================================================
        // 3. METRIK OKUPANSI & TRANSAKSI RESERVASI BULAN BERJALAN
        // ============================================================================================
        // Mengambil seluruh riwayat pengajuan peminjaman ruangan yang terjadwal pada bulan ini
        $monthReservations         = Reservation::whereBetween('reservation_date', [$startOfMonth, $endOfMonth])->get();
        $monthTotalReservations    = $monthReservations->count();
        $monthApprovedReservations = $monthReservations->where('status', 'approved')->count();

        // Perhitungan persentase rasio okupansi: (Permohonan Disetujui / Total Permohonan) * 100%
        $occupancyRate = $monthTotalReservations > 0
            ? round(($monthApprovedReservations / $monthTotalReservations) * 100, 1)
            : 0;

        // ============================================================================================
        // 4. METRIK TIKET KERUSAKAN ASET & KEPATUHAN RESOLUSI SLA
        // ============================================================================================
        // Menghitung jumlah tiket keluhan sarpras yang sedang aktif membutuhkan investigasi teknisi
        $activeDamageReports = DamageReport::whereIn('status', ['baru', 'diproses', 'submitted', 'in_progress'])->count();

        // Tiket berkategori baru yang belum ditinjau oleh operator sarpras
        $urgentDamageCount   = DamageReport::whereIn('status', ['baru', 'submitted'])->count();

        // Total tiket kerusakan yang dilaporkan dalam bulan berjalan
        $totalDamageThisMonth = DamageReport::whereBetween('created_at', [$startOfMonth . ' 00:00:00', $endOfMonth . ' 23:59:59'])->count();

        // Tiket kerusakan yang berhasil diperbaiki dan ditutup (status 'selesai' atau 'resolved')
        $resolvedDamageThisMonth = DamageReport::whereBetween('created_at', [$startOfMonth . ' 00:00:00', $endOfMonth . ' 23:59:59'])
            ->whereIn('status', ['selesai', 'resolved'])
            ->count();

        // Perhitungan Service Level Agreement (SLA Resolution Index): Persentase tiket terselesaikan
        $slaResolutionPercent = $totalDamageThisMonth > 0
            ? round(($resolvedDamageThisMonth / $totalDamageThisMonth) * 100, 1)
            : 100;

        // ============================================================================================
        // 5. ANALITIK TINGKAT UTILISASI PER GEDUNG KAMPUS
        // ============================================================================================
        // Mengelompokkan fasilitas berdasarkan nama gedung untuk mengidentifikasi gedung paling padat pemakaian
        $buildingUtilization = Facility::select('building')
            ->distinct()
            ->get()
            ->map(function ($item) use ($startOfMonth, $endOfMonth) {
                // Mengambil seluruh ID ruangan yang berada di dalam gedung terkait
                $facilityIds = Facility::where('building', $item->building)->pluck('id');

                // Mengambil transaksi peminjaman ruangan pada gedung tersebut di bulan berjalan
                $reservations = Reservation::whereIn('facility_id', $facilityIds)
                    ->whereBetween('reservation_date', [$startOfMonth, $endOfMonth])
                    ->get();

                $total    = $reservations->count();
                $approved = $reservations->where('status', 'approved')->count();
                $rate     = $total > 0 ? round(($approved / $total) * 100) : 0;

                return [
                    'building' => $item->building,
                    'rate'     => $rate,
                ];
            })->take(3);

        // Meneruskan seluruh dataset metrik ke template Blade admin.dashboard
        return view('admin.dashboard', compact(
            'pendingUsersCount',
            'pendingUsersToday',
            'totalUsersCount',
            'pendingUsersPreview',
            'totalFacilities',
            'activeFacilities',
            'maintenanceFacilities',
            'totalCapacitySeats',
            'facilitiesPreview',
            'occupancyRate',
            'activeDamageReports',
            'urgentDamageCount',
            'slaResolutionPercent',
            'buildingUtilization'
        ));
    }
}
