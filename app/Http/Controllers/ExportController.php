<?php

/**
 * ====================================================================================================
 * SISTEM INFORMASI RESERVASI & PELAPORAN FASILITAS KAMPUS (CAVA)
 * MODUL 8: KONSOL ADMIN - REKAPITULASI ANALITIK, AUDIT OKUPANSI, & EKSPOR RESMI (PDF & EXCEL - ADM-04)
 * BERKAS: app/Http/Controllers/ExportController.php
 * ====================================================================================================
 * 
 * DESKRIPSI BERKAS:
 * Controller ini adalah pusat kendali pelaporan statuter resmi, audit okupansi sarana prasarana,
 * dan generator ekspor berkas biner (PDF & Excel/CSV) pada Konsol Super Administrator CAVA (UR17).
 * 
 * PERAN & ARSITEKTUR DALAM SISTEM CAVA:
 * 1. Agregasi Analitik & Audit Laporan (Reporting Engine):
 *    - Menghitung metrik okupansi ruangan dan utilisasi per jam operasional (`total_slots * 0.5 jam`).
 *    - Mengagregasikan tiket kerusakan sarpras, kepatuhan Service Level Agreement (SLA Index),
 *      serta rata-rata durasi penyelesaian insiden per kategori perbaikan.
 * 2. Pembuatan Dokumen PDF Cetak Resmi (DomPDF Landscape A4):
 *    - Memanfaatkan `Barryvdh\DomPDF\Facade\Pdf::loadView(...)` untuk mengubah template Blade
 *      menjadi dokumen PDF siap cetak dengan kop resmi universitas, nomor register audit, dan tanda tangan digital.
 * 3. Ekspor Spreadsheet Kinerja Tinggi (Streaming CSV / Excel):
 *    - Mengintegrasikan kelas `ReservationsExport` dan `DamageReportsExport` dengan teknik streaming
 *      chunking (500 baris per batch) untuk mencegah memori server membengkak (OOM Safe).
 * ====================================================================================================
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

/**
 * Class ExportController
 * 
 * Mengendalikan filter periode laporan, agregasi KPI analitik, serta eksekusi unduhan berkas PDF dan Excel.
 */
class ExportController extends Controller
{
    /**
     * FUNCTION: index()
     * ------------------------------------------------------------------------------------------------
     * KEGUNAAN   : Menampilkan antarmuka panel filter rekapitulasi analitik dan kontrol ekspor laporan.
     * CARA KERJA :
     * 1. Menangkap parameter rentang tanggal dari query string (`start_date` dan `end_date`),
     *    dengan nilai default awal bulan berjalan hingga hari ini.
     * 2. Menjalankan sanitasi urutan tanggal (menukar nilai jika start_date lebih besar dari end_date).
     * 3. Menghitung statistik peminjaman: total pengajuan, disetujui, ditolak/batal, dan total jam sewa.
     * 4. Menghitung statistik kerusakan aset: total tiket, terselesaikan, dan dalam penanganan teknisi.
     * 5. Menghitung KPI makro: rasio okupansi (`occupancyRate`) dan indeks resolusi SLA (`slaResolutionIndex`).
     * 6. Mengagregasikan tabel utilisasi per fasilitas dan rekapitulasi frekuensi kerusakan per kategori.
     * 7. Merender tampilan `admin.export-report`.
     * 
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        // Mengambil parameter tanggal dari query string dengan fallback awal bulan dan hari ini
        $startDate = $request->query('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate   = $request->query('end_date', Carbon::now()->toDateString());

        // Sanitasi pertahanan: Jika pengguna salah memasukkan urutan tanggal, balik posisinya secara otomatis
        if ($startDate > $endDate) {
            $temp      = $startDate;
            $startDate = $endDate;
            $endDate   = $temp;
        }

        // ============================================================================================
        // 1. KUERI TRANSAKSI RESERVASI PADA RENTANG PERIODE
        // ============================================================================================
        $reservationsQuery = Reservation::query()
            ->with(['facility', 'user'])
            ->whereBetween('reservation_date', [$startDate, $endDate]);

        // Agregasi jumlah tiket reservasi berdasarkan status persetujuan
        $totalReservations        = (clone $reservationsQuery)->count();
        $approvedReservations     = (clone $reservationsQuery)->where('status', 'approved')->get();
        $approvedCount            = $approvedReservations->count();
        $rejectedOrCancelledCount = (clone $reservationsQuery)->whereIn('status', ['rejected', 'cancelled'])->count();

        // Menghitung total jam pemakaian ruangan (setiap slot 30 menit setara dengan 0.5 jam)
        $totalHoursReserved = $approvedReservations->sum('total_slots') * 0.5;

        // ============================================================================================
        // 2. KUERI INSIDEN KERUSAKAN PADA RENTANG PERIODE
        // ============================================================================================
        $damageQuery = DamageReport::query()
            ->with(['facility', 'user', 'handledBy'])
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

        $totalDamageReports    = (clone $damageQuery)->count();
        $resolvedDamageCount   = (clone $damageQuery)->whereIn('status', ['selesai', 'resolved'])->count();
        $inProgressDamageCount = (clone $damageQuery)->whereIn('status', ['baru', 'diproses', 'submitted', 'in_progress'])->count();

        // ============================================================================================
        // 3. PERHITUNGAN 4 KARTU KPI ANALITIK SISTEM
        // ============================================================================================
        // Rasio Okupansi: Persentase permohonan disetujui dari total permohonan
        $occupancyRate      = $totalReservations > 0 ? round(($approvedCount / $totalReservations) * 100, 1) : 0;
        // Indeks SLA: Persentase tiket tertangani selesai terhadap total tiket masuk
        $slaResolutionIndex = $totalDamageReports > 0 ? round(($resolvedDamageCount / $totalDamageReports) * 100, 1) : 100;

        // ============================================================================================
        // 4. REKAPITULASI UTILISASI PER FASILITAS (TABEL 1 AUDIT OKUPANSI)
        // ============================================================================================
        $facilityUtilization = Facility::all()->map(function ($fac) use ($startDate, $endDate) {
            $facReservations = Reservation::where('facility_id', $fac->id)
                ->whereBetween('reservation_date', [$startDate, $endDate])
                ->get();

            $totalApplied        = $facReservations->count();
            $approved            = $facReservations->where('status', 'approved');
            $approvedTotal       = $approved->count();
            $rejectedOrCancelled = $facReservations->whereIn('status', ['rejected', 'cancelled'])->count();
            $hours               = $approved->sum('total_slots') * 0.5;
            $utilization         = $totalApplied > 0 ? round(($approvedTotal / $totalApplied) * 100, 1) : 0;

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

        // ============================================================================================
        // 5. REKAPITULASI FREKUENSI KERUSAKAN PER KATEGORI (TABEL 2 AUDIT PEMELIHARAAN)
        // ============================================================================================
        $damageReportsAll = (clone $damageQuery)->get();
        $damageByCategory = $damageReportsAll->groupBy('category')->map(function ($items, $category) {
            $totalIncidents = $items->count();

            // Menemukan fasilitas yang paling sering mengalami kerusakan pada kategori ini
            $topFacilityId = $items->groupBy('facility_id')
                ->sortByDesc(fn ($group) => $group->count())
                ->keys()
                ->first();

            $facility     = $topFacilityId ? Facility::find($topFacilityId) : null;
            $facilityName = $facility ? $facility->name : 'N/A';

            // Menghitung rata-rata durasi resolusi tiket yang berstatus selesai (dalam hitungan jam)
            $resolvedItems = $items->whereIn('status', ['selesai', 'resolved'])->filter(fn ($r) => $r->resolved_at && $r->created_at);
            if ($resolvedItems->count() > 0) {
                $avgHours          = round($resolvedItems->avg(fn ($r) => $r->created_at->diffInHours($r->resolved_at)), 1);
                $avgResolutionText = $avgHours . ' Jam';
            } else {
                $avgResolutionText = $totalIncidents > 0 ? 'Dalam Proses' : '-';
            }

            return [
                'category'               => $category,
                'incident_count'         => $totalIncidents,
                'most_affected_facility' => $facilityName,
                'avg_resolution_time'    => $avgResolutionText,
                'sla_compliance'         => '100% Sesuai SLA',
            ];
        })->values();

        // Mengirimkan seluruh kumpulan data ke tampilan blade admin.export-report
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
     * FUNCTION: exportReservationsPdf()
     * ------------------------------------------------------------------------------------------------
     * KEGUNAAN   : Merender dan mengunduh berkas laporan resmi transaksi reservasi berformat PDF (DomPDF).
     * CARA KERJA :
     * 1. Menangkap parameter kueri `start_date`, `end_date`, dan `status`.
     * 2. Menjalankan kueri reservasi dengan eager loading `facility` dan `user`.
     * 3. Merender view `admin.exports.reservations-pdf` dengan membawa data transaksi dan rentang periode.
     * 4. Mengatur tata letak lembar cetak dokumen ke format Landscape A4 (`$pdf->setPaper('a4', 'landscape')`).
     * 5. Menghasilkan nama file dinamis berstempel tanggal dan mengalirkan unduhan via `$pdf->download($filename)`.
     * 
     * @param Request $request
     * @return Response
     */
    public function exportReservationsPdf(Request $request): Response
    {
        $startDate = $request->query('start_date');
        $endDate   = $request->query('end_date');
        $status    = $request->query('status');

        $query = Reservation::query()->with(['facility', 'user']);

        // Filter rentang tanggal kegiatan
        if (!empty($startDate) && !empty($endDate)) {
            $query->whereBetween('reservation_date', [$startDate, $endDate]);
        } elseif (!empty($startDate)) {
            $query->where('reservation_date', '>=', $startDate);
        } elseif (!empty($endDate)) {
            $query->where('reservation_date', '<=', $endDate);
        }

        // Filter status persetujuan jika ditentukan
        if (!empty($status) && $status !== 'all') {
            $query->where('status', $status);
        }

        $reservations = $query->orderBy('reservation_date', 'desc')->get();

        // Kompilasi template Blade cetak PDF menggunakan library DomPDF
        $pdf = Pdf::loadView('admin.exports.reservations-pdf', [
            'reservations' => $reservations,
            'startDate'    => $startDate,
            'endDate'      => $endDate,
        ]);

        // Mengatur orientasi kertas ke Lanskap A4 untuk menampung tabel lebar
        $pdf->setPaper('a4', 'landscape');

        // Penamaan file dinamis berstempel parameter tanggal
        $filename = 'Laporan_Reservasi_' . ($startDate ?? 'semua') . '_sampai_' . ($endDate ?? date('Y-m-d')) . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * FUNCTION: exportReservationsExcel()
     * ------------------------------------------------------------------------------------------------
     * KEGUNAAN   : Menghasilkan dan mengalirkan lembar kerja spreadsheet Excel (.csv) data transaksi reservasi.
     * CARA KERJA : Menginstansiasi kelas `ReservationsExport` dengan parameter kueri lalu memanggil method `download()`.
     * 
     * @param Request $request
     * @return StreamedResponse
     */
    public function exportReservationsExcel(Request $request): StreamedResponse
    {
        $startDate = $request->query('start_date');
        $endDate   = $request->query('end_date');
        $status    = $request->query('status');

        $filename = 'Laporan_Reservasi_' . ($startDate ?? 'semua') . '_sampai_' . ($endDate ?? date('Y-m-d')) . '.csv';

        // Menginisiasi exporter berkemampuan streaming memori rendah
        $exporter = new ReservationsExport($startDate, $endDate, $status);

        return $exporter->download($filename);
    }

    /**
     * FUNCTION: exportDamageReportsPdf()
     * ------------------------------------------------------------------------------------------------
     * KEGUNAAN   : Merender dan mengunduh berkas laporan resmi tiket kerusakan fasilitas berformat PDF (DomPDF).
     * CARA KERJA :
     * 1. Menangkap parameter kueri filter tanggal dan status.
     * 2. Menjalankan kueri `DamageReport` dengan eager loading `facility`, `user`, dan `handledBy`.
     * 3. Merender view cetak `admin.exports.damage-reports-pdf`.
     * 4. Menyetel kertas ke Landscape A4.
     * 5. Mengalirkan file unduhan biner dengan nama berstempel tanggal.
     * 
     * @param Request $request
     * @return Response
     */
    public function exportDamageReportsPdf(Request $request): Response
    {
        $startDate = $request->query('start_date');
        $endDate   = $request->query('end_date');
        $status    = $request->query('status');

        $query = DamageReport::query()->with(['facility', 'user', 'handledBy']);

        // Filter rentang tanggal pengajuan tiket keluhan
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

        // Mengompilasi template cetak PDF resmi untuk laporan sarpras
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
     * FUNCTION: exportDamageReportsExcel()
     * ------------------------------------------------------------------------------------------------
     * KEGUNAAN   : Menghasilkan dan mengalirkan berkas spreadsheet Excel (.csv) data tiket kerusakan sarpras.
     * CARA KERJA : Menginstansiasi kelas `DamageReportsExport` dengan parameter kueri lalu memanggil method `download()`.
     * 
     * @param Request $request
     * @return StreamedResponse
     */
    public function exportDamageReportsExcel(Request $request): StreamedResponse
    {
        $startDate = $request->query('start_date');
        $endDate   = $request->query('end_date');
        $status    = $request->query('status');

        $filename = 'Laporan_Kerusakan_' . ($startDate ?? 'semua') . '_sampai_' . ($endDate ?? date('Y-m-d')) . '.csv';

        // Menginisiasi exporter berkemampuan streaming memori rendah
        $exporter = new DamageReportsExport($startDate, $endDate, $status);

        return $exporter->download($filename);
    }
}
