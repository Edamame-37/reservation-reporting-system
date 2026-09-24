<?php
/**
 * NAMA FILE    : ReportManagementController.php
 * FUNGSI       : Controller manajemen tiket keluhan kerusakan fasilitas kampus (PTG-04 / US-11 / UR12)
 * DESKRIPSI    : Menangani penarikan seluruh laporan kerusakan sarana, pembaruan status penanganan teknisi (Baru/Diproses/Selesai/Ditolak), pencatatan resolusi perbaikan, serta sinkronisasi gembok pemeliharaan fasilitas di kalender.
 * CARA KERJA   : Menerima HTTP request petugas, melakukan query eager loading (with) untuk meniadakan problem N+1, memvalidasi payload via UpdateReportStatusRequest, memperbarui entitas DamageReport & Facility, serta mengembalikan respon view atau redirect dengan notifikasi sukses.
 */

namespace App\Http\Controllers;

use App\Http\Requests\UpdateReportStatusRequest;
use App\Models\DamageReport;
use App\Models\Facility;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportManagementController extends Controller
{
    /**
     * FUNCTION/PROCEDURE : index()
     * KEGUNAAN           : Menampilkan lembar kerja manajemen tiket kerusakan fasilitas sarpras.
     * CARA KERJA         : Mengambil seluruh tiket pengaduan kerusakan beserta relasi user dan facility menggunakan eager loading (with) guna mengeliminasi problem N+1, menghitung kuantitas statistik filter tab, dan mengembalikan view 'petugas.report-management'.
     */
    public function index(): View
    {
        // 1. Penarikan Data Tiket Kerusakan dengan Eager Loading & Prioritas Status Aktif (FIFO)
        $reports = DamageReport::with(['facility', 'user', 'handler'])
            ->orderByRaw("CASE WHEN status = 'baru' THEN 0 WHEN status = 'diproses' THEN 1 ELSE 2 END")
            ->orderBy('created_at', 'asc')
            ->get();

        // 2. Perhitungan Statistik Kuantitas Tiket untuk Tab Filter Antarmuka
        $totalCount = $reports->count();
        $newCount = $reports->where('status', 'baru')->count();
        $inProgressCount = $reports->where('status', 'diproses')->count();
        $resolvedCount = $reports->where('status', 'selesai')->count();
        $rejectedCount = $reports->where('status', 'ditolak')->count();

        // 3. Pengembalian View Antarmuka
        return view('petugas.report-management', compact(
            'reports',
            'totalCount',
            'newCount',
            'inProgressCount',
            'resolvedCount',
            'rejectedCount'
        ));
    }

    /**
     * FUNCTION/PROCEDURE : updateStatus()
     * KEGUNAAN           : Memperbarui jenjang status penanganan tiket kerusakan, mencatat resolusi teknisi, dan mengelola status kunci fasilitas (PTG-04 / US-11 / UR12).
     * CARA KERJA         : Menerima payload tervalidasi dari UpdateReportStatusRequest, mengambil entitas DamageReport, memperbarui kolom status & resolution_note, menyinkronkan status fasilitas ke 'dalam perbaikan' atau 'aktif', mencatat handled_by & resolved_at, lalu menyimpan ke database.
     */
    public function updateStatus(UpdateReportStatusRequest $request, int|string $id): RedirectResponse
    {
        try {
            DB::transaction(function () use ($request, $id) {
                // 1. Ambil data tiket kerusakan dengan penguncian baris transaksi
                $report = DamageReport::lockForUpdate()->findOrFail($id);

                $newStatus = $request->input('status') ?? $request->input('status_laporan');
                $note = $request->input('catatan_resolusi') ?? $request->input('resolution_note');
                $isLocked = $request->boolean('is_facility_locked');

                // 2. Pembaruan Status dan Catatan Resolusi
                $report->status = $newStatus;
                if (! empty($note)) {
                    $report->resolution_note = $note;
                }

                $report->is_facility_locked = $isLocked;
                $report->handled_by = auth()->id();

                if ($newStatus === 'selesai') {
                    $report->resolved_at = now();
                }

                $report->save();

                // 3. Sinkronisasi Status Operasional Fasilitas Kampus (UR12)
                if ($report->facility_id) {
                    if ($isLocked) {
                        // Kunci fasilitas menjadi 'dalam perbaikan'
                        Facility::where('id', $report->facility_id)->update(['status' => 'dalam perbaikan']);
                    } elseif ($newStatus === 'selesai') {
                        // Jika laporan selesai dan tidak ada laporan aktif lain yang mengunci fasilitas ini, buka kembali ke 'aktif'
                        $hasOtherActiveLocks = DamageReport::where('facility_id', $report->facility_id)
                            ->where('id', '!=', $report->id)
                            ->where('is_facility_locked', true)
                            ->whereIn('status', ['baru', 'diproses'])
                            ->exists();

                        if (! $hasOtherActiveLocks) {
                            Facility::where('id', $report->facility_id)->update(['status' => 'aktif']);
                        }
                    }
                }
            });

            return back()->with('success', 'Status tiket laporan kerusakan dan catatan resolusi berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui status tiket: ' . $e->getMessage());
        }
    }
}
