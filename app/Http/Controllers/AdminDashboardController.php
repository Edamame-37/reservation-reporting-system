<?php
/**
 * NAMA FILE    : AdminDashboardController.php
 * FUNGSI       : Kontroler Dasbor Utama Konsol Tata Kelola Super Admin
 * DESKRIPSI    : Mengumpulkan statistik terpadu tata kelola kampus (verifikasi akun sivitas, okupansi reservasi, status inventaris fasilitas, dan tiket pengaduan kerusakan).
 * CARA KERJA   : Menerima request ke /admin/dashboard, menghitung metrik KPI dinamis, dan meneruskan data preview ringkas ke view admin.dashboard.
 */

namespace App\Http\Controllers;

use App\Models\DamageReport;
use App\Models\Facility;
use App\Models\Reservation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    /**
     * FUNCTION/PROCEDURE : index()
     * KEGUNAAN           : Merender halaman dasbor utama super admin dengan 4 metrik KPI dan antrean preview data.
     * CARA KERJA         : Mengagregasikan data tabel users, facilities, reservations, dan damage_reports lalu menyusun metrik ringkas.
     * PARAMETER          : Tidak ada.
     * RETURN             : View
     */
    public function index(): View
    {
        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth()->toDateString();
        $endOfMonth = Carbon::now()->endOfMonth()->toDateString();

        // 1. Metrik Verifikasi Akun Sivitas
        $pendingUsersCount = User::where('status', 'pending')->count();
        $pendingUsersToday = User::where('status', 'pending')->whereDate('created_at', $today)->count();
        $totalUsersCount = User::count();
        $pendingUsersPreview = User::where('status', 'pending')->latest()->take(3)->get();

        // 2. Metrik Inventaris Fasilitas Kampus
        $totalFacilities = Facility::count();
        $activeFacilities = Facility::where('status', 'aktif')->count();
        $maintenanceFacilities = Facility::where('status', 'dalam perbaikan')->count();
        $totalCapacitySeats = Facility::sum('capacity');
        $facilitiesPreview = Facility::latest()->take(3)->get();

        // 3. Metrik Okupansi & Peminjaman Bulan Ini
        $monthReservations = Reservation::whereBetween('reservation_date', [$startOfMonth, $endOfMonth])->get();
        $monthTotalReservations = $monthReservations->count();
        $monthApprovedReservations = $monthReservations->where('status', 'approved')->count();
        $occupancyRate = $monthTotalReservations > 0
            ? round(($monthApprovedReservations / $monthTotalReservations) * 100, 1)
            : 0;

        // 4. Metrik Tiket Kerusakan Aset
        $activeDamageReports = DamageReport::whereIn('status', ['baru', 'diproses'])->count();
        $urgentDamageCount = DamageReport::where('status', 'baru')->count();
        $totalDamageThisMonth = DamageReport::whereBetween('created_at', [$startOfMonth . ' 00:00:00', $endOfMonth . ' 23:59:59'])->count();
        $resolvedDamageThisMonth = DamageReport::whereBetween('created_at', [$startOfMonth . ' 00:00:00', $endOfMonth . ' 23:59:59'])
            ->where('status', 'selesai')
            ->count();
        $slaResolutionPercent = $totalDamageThisMonth > 0
            ? round(($resolvedDamageThisMonth / $totalDamageThisMonth) * 100, 1)
            : 100;

        // 5. Analitik Utilisasi per Gedung
        $buildingUtilization = Facility::select('building')
            ->distinct()
            ->get()
            ->map(function ($item) use ($startOfMonth, $endOfMonth) {
                $facilityIds = Facility::where('building', $item->building)->pluck('id');
                $reservations = Reservation::whereIn('facility_id', $facilityIds)
                    ->whereBetween('reservation_date', [$startOfMonth, $endOfMonth])
                    ->get();
                $total = $reservations->count();
                $approved = $reservations->where('status', 'approved')->count();
                $rate = $total > 0 ? round(($approved / $total) * 100) : 0;

                return [
                    'building' => $item->building,
                    'rate'     => $rate,
                ];
            })->take(3);

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
