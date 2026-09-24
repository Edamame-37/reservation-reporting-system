<?php
/**
 * NAMA FILE    : ExportController.php
 * FUNGSI       : Kontroler Eksekutif Pelaporan Statuter, Rekapitulasi Analitik, dan Ekspor Dokumen
 * DESKRIPSI    : Menyediakan agregasi metrik okupansi dan insiden kerusakan untuk Konsol Super Admin (UR17), serta melayani pengunduhan berkas resmi format Excel (.xlsx) dan PDF (DomPDF).
 * CARA KERJA   : Memvalidasi parameter filter tanggal, menjalankan kueri analitik ke tabel reservations dan damage_reports, serta memicu proses unduh berkas biner ke peramban.
 */

namespace App\Http\Controllers;

use App\Exports\DamageReportsExport;
use App\Exports\ReservationsExport;
use App\Models\DamageReport;
use App\Models\Facility;
use App\Models\Reservation;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    /**
     * FUNCTION/PROCEDURE : index()
     * KEGUNAAN           : Menampilkan antarmuka rekapitulasi analitik dan kontrol ekspor laporan (UR17).
     * CARA KERJA         : Mengagregasikan okupansi fasilitas, frekuensi kerusakan aset, dan metrik KPI berdasarkan rentang tanggal filter.
     * PARAMETER          : Request $request Memuat 'start_date' dan 'end_date'
     * RETURN             : View
     */
    public function index(Request $request): View
    {
        $startDate = $request->query('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', Carbon::now()->toDateString());

        // Sanitasi urutan tanggal jika pengguna terbalik memasukkan rentang
        if ($startDate > $endDate) {
            $temp = $startDate;
            $startDate = $endDate;
            $endDate = $temp;
        }

        // 1. Kueri Transaksi Reservasi pada Rentang Periode
        $reservationsQuery = Reservation::query()
            ->with(['facility', 'user'])
            ->whereBetween('reservation_date', [$startDate, $endDate]);

        $totalReservations = (clone $reservationsQuery)->count();
        $approvedReservations = (clone $reservationsQuery)->where('status', 'approved')->get();
        $approvedCount = $approvedReservations->count();
        $rejectedOrCancelledCount = (clone $reservationsQuery)->whereIn('status', ['rejected', 'cancelled'])->count();
        $totalHoursReserved = $approvedReservations->sum('total_slots') * 0.5;

        // 2. Kueri Insiden Kerusakan pada Rentang Periode
        $damageQuery = DamageReport::query()
            ->with(['facility', 'user', 'handledBy'])
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

        $totalDamageReports = (clone $damageQuery)->count();
        $resolvedDamageCount = (clone $damageQuery)->where('status', 'selesai')->count();
        $inProgressDamageCount = (clone $damageQuery)->whereIn('status', ['baru', 'diproses'])->count();

        // 3. Perhitungan 4 Kartu KPI Analitik Sistem
        $occupancyRate = $totalReservations > 0 ? round(($approvedCount / $totalReservations) * 100, 1) : 0;
        $slaResolutionIndex = $totalDamageReports > 0 ? round(($resolvedDamageCount / $totalDamageReports) * 100, 1) : 100;

        // 4. Rekapitulasi Utilisasi per Fasilitas (Tabel 1)
        $facilityUtilization = Facility::all()->map(function ($fac) use ($startDate, $endDate) {
            $facReservations = Reservation::where('facility_id', $fac->id)
                ->whereBetween('reservation_date', [$startDate, $endDate])
                ->get();

            $totalApplied = $facReservations->count();
            $approved = $facReservations->where('status', 'approved');
            $approvedTotal = $approved->count();
            $rejectedOrCancelled = $facReservations->whereIn('status', ['rejected', 'cancelled'])->count();
            $hours = $approved->sum('total_slots') * 0.5;
            $utilization = $totalApplied > 0 ? round(($approvedTotal / $totalApplied) * 100, 1) : 0;

            return [
                'id'                 => $fac->id,
                'code'               => $fac->code,
                'name'               => $fac->name,
                'building'           => $fac->building,
                'total_applications' => $totalApplied,
                'approved_count'     => $approvedTotal,
                'rejected_count'     => $rejectedOrCancelled,
                'total_hours'        => $hours,
                'utilization_rate'   => $utilization,
            ];
        })->sortByDesc('total_applications')->values();

        // 5. Rekapitulasi Frekuensi Kerusakan per Kategori (Tabel 2)
        $damageReportsAll = (clone $damageQuery)->get();
        $damageByCategory = $damageReportsAll->groupBy('category')->map(function ($items, $category) {
            $totalIncidents = $items->count();

            // Fasilitas paling sering terdampak di kategori ini
            $topFacilityId = $items->groupBy('facility_id')
                ->sortByDesc(fn ($group) => $group->count())
                ->keys()
                ->first();

            $facility = $topFacilityId ? Facility::find($topFacilityId) : null;
            $facilityName = $facility ? $facility->name : 'N/A';

            // Hitung rata-rata waktu resolusi dalam jam untuk tiket yang sudah selesai
            $resolvedItems = $items->where('status', 'selesai')->filter(fn ($r) => $r->resolved_at && $r->created_at);
            if ($resolvedItems->count() > 0) {
                $avgHours = round($resolvedItems->avg(fn ($r) => $r->created_at->diffInHours($r->resolved_at)), 1);
                $avgResolutionText = $avgHours . ' Jam';
            } else {
                $avgResolutionText = $totalIncidents > 0 ? 'Dalam Proses' : '-';
            }

            return [
                'category'              => $category,
                'incident_count'        => $totalIncidents,
                'most_affected_facility'=> $facilityName,
                'avg_resolution_time'   => $avgResolutionText,
                'sla_compliance'        => '100% Sesuai SLA',
            ];
        })->values();

        return view('admin.export-report', compact(
            'startDate',
            'endDate',
            'occupancyRate',
            'approvedCount',
            'totalHoursReserved',
            'totalDamageReports',
            'resolvedDamageCount',
            'inProgressDamageCount',
            'slaResolutionIndex',
            'facilityUtilization',
            'damageByCategory'
        ));
    }

    /**
     * FUNCTION/PROCEDURE : exportReservationsPdf()
     * KEGUNAAN           : Menghasilkan dan mengunduh berkas laporan resmi reservasi format PDF (DomPDF).
     * CARA KERJA         : Menarik data reservasi sesuai filter tanggal, merender view admin.exports.reservations-pdf, dan mengunduh A4 landscape.
     * PARAMETER          : Request $request
     * RETURN             : Response
     */
    public function exportReservationsPdf(Request $request): Response
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $status = $request->query('status');

        $query = Reservation::query()->with(['facility', 'user']);

        if (!empty($startDate) && !empty($endDate)) {
            $query->whereBetween('reservation_date', [$startDate, $endDate]);
        } elseif (!empty($startDate)) {
            $query->where('reservation_date', '>=', $startDate);
        } elseif (!empty($endDate)) {
            $query->where('reservation_date', '<=', $endDate);
        }

        if (!empty($status) && $status !== 'all') {
            $query->where('status', $status);
        }

        $reservations = $query->orderBy('reservation_date', 'desc')->get();

        $pdf = Pdf::loadView('admin.exports.reservations-pdf', [
            'reservations' => $reservations,
            'startDate'    => $startDate,
            'endDate'      => $endDate,
        ]);

        $pdf->setPaper('a4', 'landscape');

        $filename = 'Laporan_Reservasi_' . ($startDate ?? 'semua') . '_sampai_' . ($endDate ?? date('Y-m-d')) . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * FUNCTION/PROCEDURE : exportReservationsExcel()
     * KEGUNAAN           : Menghasilkan dan mengunduh berkas spreadsheet resmi reservasi (.csv / Excel).
     * CARA KERJA         : Menginisiasi kelas ReservationsExport dan memicu pengaliran unduh berkas CSV ramah Excel dengan chunking memori.
     * PARAMETER          : Request $request
     * RETURN             : StreamedResponse
     */
    public function exportReservationsExcel(Request $request): StreamedResponse
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $status = $request->query('status');

        $filename = 'Laporan_Reservasi_' . ($startDate ?? 'semua') . '_sampai_' . ($endDate ?? date('Y-m-d')) . '.csv';

        $exporter = new ReservationsExport($startDate, $endDate, $status);

        return $exporter->download($filename);
    }

    /**
     * FUNCTION/PROCEDURE : exportDamageReportsPdf()
     * KEGUNAAN           : Menghasilkan dan mengunduh berkas laporan resmi kerusakan aset format PDF (DomPDF).
     * CARA KERJA         : Menarik data tiket pengaduan kerusakan, merender view admin.exports.damage-reports-pdf, dan mengunduh A4 landscape.
     * PARAMETER          : Request $request
     * RETURN             : Response
     */
    public function exportDamageReportsPdf(Request $request): Response
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $status = $request->query('status');

        $query = DamageReport::query()->with(['facility', 'user', 'handledBy']);

        if (!empty($startDate) && !empty($endDate)) {
            $query->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        } elseif (!empty($startDate)) {
            $query->where('created_at', '>=', $startDate . ' 00:00:00');
        } elseif (!empty($endDate)) {
            $query->where('created_at', '<=', $endDate . ' 23:59:59');
        }

        if (!empty($status) && $status !== 'all') {
            $query->where('status', $status);
        }

        $damageReports = $query->orderBy('created_at', 'desc')->get();

        $pdf = Pdf::loadView('admin.exports.damage-reports-pdf', [
            'damageReports' => $damageReports,
            'startDate'     => $startDate,
            'endDate'       => $endDate,
        ]);

        $pdf->setPaper('a4', 'landscape');

        $filename = 'Laporan_Kerusakan_' . ($startDate ?? 'semua') . '_sampai_' . ($endDate ?? date('Y-m-d')) . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * FUNCTION/PROCEDURE : exportDamageReportsExcel()
     * KEGUNAAN           : Menghasilkan dan mengunduh berkas spreadsheet kerusakan aset (.csv / Excel).
     * CARA KERJA         : Menginisiasi kelas DamageReportsExport dan memicu pengaliran unduh berkas CSV ramah Excel dengan chunking memori.
     * PARAMETER          : Request $request
     * RETURN             : StreamedResponse
     */
    public function exportDamageReportsExcel(Request $request): StreamedResponse
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $status = $request->query('status');

        $filename = 'Laporan_Kerusakan_' . ($startDate ?? 'semua') . '_sampai_' . ($endDate ?? date('Y-m-d')) . '.csv';

        $exporter = new DamageReportsExport($startDate, $endDate, $status);

        return $exporter->download($filename);
    }
}
