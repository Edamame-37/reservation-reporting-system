<?php
/**
 * NAMA FILE    : ReservationManagementController.php
 * FUNGSI       : Controller manajemen verifikasi, persetujuan, dan penolakan reservasi petugas (PTG-02 / US-9)
 * DESKRIPSI    : Menangani penarikan seluruh antrean permohonan reservasi, verifikasi anti-bentrok jadwal bergaransi race-condition safe (pessimistic locking), eksekusi approval, dan penolakan berlandaskan alasan resmi.
 * CARA KERJA   : Menerima HTTP request petugas, melakukan lockForUpdate() dalam transaksi DB::transaction(), mengecek singgungan jadwal ke tabel reservations, dan mengubah status tiket.
 */

namespace App\Http\Controllers;

use App\Http\Requests\RejectReservationRequest;
use App\Models\Reservation;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReservationManagementController extends Controller
{
    /**
     * FUNCTION/PROCEDURE : index()
     * KEGUNAAN           : Menampilkan lembar kerja manajemen reservasi dan daftar seluruh antrean permohonan.
     * CARA KERJA         : Mengambil seluruh data reservasi beserta relasi user dan facility menggunakan eager loading (with) guna mengeliminasi problem N+1, menghitung metrik counter, dan mengembalikan view 'petugas.reservation-management'.
     */
    public function index(): View
    {
        // 1. Penarikan Data Reservasi dengan Eager Loading & Prioritas Status Pending (FIFO)
        $reservations = Reservation::with(['facility', 'user'])
            ->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END")
            ->orderBy('created_at', 'asc')
            ->get();

        // 2. Perhitungan Statistik Kuantitas Antrean untuk Filter Tab
        $totalCount = $reservations->count();
        $pendingCount = $reservations->where('status', 'pending')->count();
        $approvedCount = $reservations->where('status', 'approved')->count();
        $rejectedCount = $reservations->where('status', 'rejected')->count();

        // 3. Pengembalian View Antarmuka
        return view('petugas.reservation-management', compact(
            'reservations',
            'totalCount',
            'pendingCount',
            'approvedCount',
            'rejectedCount'
        ));
    }

    /**
     * FUNCTION/PROCEDURE : approve()
     * KEGUNAAN           : Mengeksekusi persetujuan reservasi secara resmi dengan validasi ketat anti-bentrok jadwal (SFR06 / US-9).
     * CARA KERJA         : Membuka DB::transaction() dengan lockForUpdate(), memverifikasi status pending, mengecek kueri overlap (req_start < existing_end AND req_end > existing_start) pada fasilitas & tanggal yang sama, mengunci slot jadwal jika aman, atau menggagalkan aksi jika terdeteksi bentrok.
     */
    public function approve(int|string $id): RedirectResponse
    {
        try {
            $reservation = DB::transaction(function () use ($id) {
                // 1. Penguncian baris database tingkat transaksi (Pessimistic Locking / Anti Race Condition)
                $res = Reservation::lockForUpdate()->findOrFail($id);

                // 2. Validasi Keabsahan Status Antrean
                if ($res->status !== 'pending') {
                    throw new \Exception('Reservasi ini sudah diproses sebelumnya dan tidak dapat disetujui ulang.');
                }

                // 3. Kueri Validasi Anti-Bentrok Jadwal (Overlap SQL Formula)
                // Rumus Singgungan: (req_start < existing_end AND req_end > existing_start)
                $overlap = Reservation::where('facility_id', $res->facility_id)
                    ->where('reservation_date', $res->reservation_date)
                    ->where('status', 'approved')
                    ->where('id', '!=', $res->id)
                    ->where(function ($query) use ($res) {
                        $query->where('start_time', '<', $res->end_time)
                              ->where('end_time', '>', $res->start_time);
                    })
                    ->exists();

                if ($overlap) {
                    throw new \Exception('Gagal menyetujui! Fasilitas telah dibooking oleh pihak lain di rentang waktu tersebut.');
                }

                // 4. Lolos Validasi: Tandai Reservasi Disetujui
                $res->status = 'approved';
                $res->reviewed_by = auth()->id();
                $res->reviewed_at = now();
                $res->save();

                return $res;
            });

            return back()->with('success', 'Permohonan reservasi ' . $reservation->ticket_code . ' berhasil disetujui. Slot jadwal fasilitas telah resmi dikunci.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * FUNCTION/PROCEDURE : reject()
     * KEGUNAAN           : Mengeksekusi penolakan permohonan reservasi dengan alasan resmi wajib (US-9 / PTG-02).
     * CARA KERJA         : Menerima payload terverifikasi dari RejectReservationRequest, mengunci baris data dengan lockForUpdate(), mengubah status tiket menjadi 'rejected', dan mencatat isi rejection_reason.
     */
    public function reject(RejectReservationRequest $request, int|string $id): RedirectResponse
    {
        try {
            $reservation = DB::transaction(function () use ($request, $id) {
                // 1. Penguncian baris database tingkat transaksi
                $res = Reservation::lockForUpdate()->findOrFail($id);

                // 2. Validasi Keabsahan Status Antrean
                if ($res->status !== 'pending') {
                    throw new \Exception('Reservasi ini sudah diproses sebelumnya.');
                }

                // 3. Eksekusi Penolakan dan Pencatatan Alasan Resmi
                $res->status = 'rejected';
                $res->rejection_reason = $request->input('rejection_reason');
                $res->reviewed_by = auth()->id();
                $res->reviewed_at = now();
                $res->save();

                return $res;
            });

            return back()->with('success', 'Permohonan reservasi ' . $reservation->ticket_code . ' berhasil ditolak beserta alasan resmi.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
