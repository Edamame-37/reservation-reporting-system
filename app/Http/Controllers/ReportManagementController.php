<?php
/**
 * NAMA FILE    : ReportManagementController.php
 * FUNGSI       : Controller manajemen tiket keluhan kerusakan fasilitas & blokir pemeliharaan ruang petugas (PTG-04 & PTG-05 / US-11 & US-12 / UR12)
 * DESKRIPSI    : Menangani penarikan seluruh laporan kerusakan sarana, pembaruan status penanganan teknisi (Baru/Diproses/Selesai/Ditolak), pencatatan resolusi perbaikan, sinkronisasi gembok fasilitas di kalender, serta saklar toggle Mode Perbaikan (Maintenance Mode).
 * CARA KERJA   : Menerima HTTP request petugas, melakukan query eager loading (with) untuk meniadakan problem N+1, memvalidasi payload, memperbarui entitas DamageReport & Facility dalam DB::transaction() dengan lockForUpdate(), serta mengembalikan respon view atau redirect dengan notifikasi sukses.
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

    /**
     * FUNCTION/PROCEDURE : toggleMaintenance()
     * KEGUNAAN           : Mengubah status saklar operasional fasilitas antara 'aktif' dan 'dalam perbaikan' (PTG-05 / US-12).
     * CARA KERJA         : Membuka DB::transaction() dengan lockForUpdate() pada data Facility; jika berstatus 'dalam perbaikan' dikembalikan ke 'aktif' dan membuka flag kunci pada laporan kerusakan terkait, jika berstatus 'aktif' diubah ke 'dalam perbaikan' dan menandai laporan aktif terkait menjadi terkunci.
     */
    public function toggleMaintenance(int|string $id): RedirectResponse
    {
        try {
            $message = DB::transaction(function () use ($id) {
                // 1. Ambil data fasilitas dengan penguncian baris transaksi (Pessimistic Locking)
                $facility = Facility::lockForUpdate()->findOrFail($id);

                // 2. Logika Saklar (Toggle) Status Master Fasilitas
                if ($facility->status === 'dalam perbaikan') {
                    $facility->status = 'aktif';
                    $facility->save();

                    // Buka penguncian laporan kerusakan fasilitas ini
                    DamageReport::where('facility_id', $facility->id)
                        ->where('is_facility_locked', true)
                        ->update(['is_facility_locked' => false]);

                    return "Fasilitas {$facility->name} berhasil dibuka dan dikembalikan ke status Aktif.";
                } else {
                    $facility->status = 'dalam perbaikan';
                    $facility->save();

                    // Sinkronkan penguncian pada laporan aktif yang belum selesai
                    DamageReport::where('facility_id', $facility->id)
                        ->whereIn('status', ['baru', 'diproses'])
                        ->update(['is_facility_locked' => true]);

                    return "Fasilitas {$facility->name} berhasil diblokir (Masuk Mode Perbaikan / Maintenance).";
                }
            });

            return back()->with('success', $message);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengubah status pemeliharaan fasilitas: ' . $e->getMessage());
        }
    }
}
