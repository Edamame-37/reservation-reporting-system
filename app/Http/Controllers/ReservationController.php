<?php
/**
 * NAMA FILE    : ReservationController.php
 * FUNGSI       : Controller Manajemen Pengajuan & Transaksi Reservasi Ruang Kampus
 * DESKRIPSI    : Menangani formulir pengajuan reservasi sivitas (Mahasiswa/Dosen/Staf), validasi bentrok jadwal (anti double-booking), dan pencatatan transaksi peminjaman.
 * CARA KERJA   : Mengambil katalog fasilitas aktif untuk form, memvalidasi rentang waktu 30 menit (07:00-20:00 WIB), memeriksa overlap kueri pada database, dan menyimpan reservasi berstatus pending secara aman.
 */

namespace App\Http\Controllers;

use App\Http\Requests\StoreReservationRequest;
use App\Models\Facility;
use App\Models\Reservation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReservationController extends Controller
{
    /**
     * FUNCTION/PROCEDURE : create()
     * KEGUNAAN           : Menampilkan halaman formulir pengajuan reservasi ruangan kampus (USR-01).
     * CARA KERJA         : Mengambil seluruh fasilitas aktif dari database dan merender view user.reservation-form.
     */
    public function create(Request $request): View
    {
        $facilities = Facility::where('status', 'aktif')
            ->orderBy('name')
            ->get();

        $selectedFacilityId = $request->query('facility_id');

        return view('user.reservation-form', compact('facilities', 'selectedFacilityId'));
    }

    /**
     * FUNCTION/PROCEDURE : store()
     * KEGUNAAN           : Memvalidasi dan menyimpan transaksi permohonan reservasi baru ke basis data (USR-01).
     * CARA KERJA         : Menerapkan StoreReservationRequest, memastikan fasilitas aktif, memeriksa kueri bentrok overlap dengan jadwal approved, membungkus penyimpanan dalam DB::transaction(), dan menerbitkan kode tiket unik.
     */
    public function store(StoreReservationRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        // 1. Verifikasi Status Kelayakan Fasilitas
        $facility = Facility::findOrFail($validated['facility_id']);
        if ($facility->status !== 'aktif') {
            return back()
                ->withInput()
                ->withErrors(['facility_id' => 'Fasilitas "' . $facility->name . '" sedang dalam masa perbaikan atau non-aktif sehingga tidak dapat dipesan.']);
        }

        // 2. Tentukan ID Pengguna Pemohon (Dukungan Auth dan Fallback Mode Mockup)
        $userId = Auth::id();
        if (!$userId) {
            $defaultUser = User::where('email', 'dimas@mahasiswa.ac.id')->first() ?? User::first();
            $userId = $defaultUser ? $defaultUser->id : 1;
        }

        // 3. Hitung Total Slot Durasi (Kelipatan 30 Menit)
        $startTime = Carbon::createFromFormat('H:i', $validated['start_time']);
        $endTime   = Carbon::createFromFormat('H:i', $validated['end_time']);
        $diffInMinutes = $startTime->diffInMinutes($endTime);
        $totalSlots = max(1, (int) round($diffInMinutes / 30));

        // 4. Generate Kode Tiket Unik (Format: TKT-YYYYMMDD-XXXX)
        $datePrefix = Carbon::parse($validated['reservation_date'])->format('Ymd');
        $randomSeq  = strtoupper(substr(bin2hex(random_bytes(2)), 0, 4));
        $ticketCode = sprintf('TKT-%s-%s', $datePrefix, $randomSeq);

        while (Reservation::where('ticket_code', $ticketCode)->exists()) {
            $randomSeq  = strtoupper(substr(bin2hex(random_bytes(2)), 0, 4));
            $ticketCode = sprintf('TKT-%s-%s', $datePrefix, $randomSeq);
        }

        // 5. Eksekusi Pengecekan Bentrok Jadwal dan Penyimpanan dalam Transaksi Database
        return DB::transaction(function () use ($validated, $facility, $userId, $totalSlots, $ticketCode, $request) {
            // Cek apakah ada jadwal bersinggungan (overlap) yang TELAH DISETUJUI (status = approved)
            $overlapExists = Reservation::where('facility_id', $validated['facility_id'])
                ->where('reservation_date', $validated['reservation_date'])
                ->where('status', 'approved')
                ->where(function ($query) use ($validated) {
                    $query->where('start_time', '<', $validated['end_time'])
                          ->where('end_time', '>', $validated['start_time']);
                })
                ->exists();

            if ($overlapExists) {
                return back()
                    ->withInput()
                    ->withErrors(['start_time' => 'Jadwal bentrok! Fasilitas "' . $facility->name . '" telah terisi oleh reservasi lain yang telah disetujui pada jam tersebut. Silakan pilih slot waktu lain.']);
            }

            // Simpan data reservasi baru dengan status default 'pending'
            Reservation::create([
                'ticket_code'        => $ticketCode,
                'user_id'            => $userId,
                'facility_id'        => $validated['facility_id'],
                'reservation_date'   => $validated['reservation_date'],
                'start_time'         => $validated['start_time'],
                'end_time'           => $validated['end_time'],
                'total_slots'        => $totalSlots,
                'purpose'            => $validated['purpose'],
                'participants_count' => $request->input('participants_count', 1),
                'status'             => 'pending',
            ]);

            return redirect()
                ->route('user.reservation-history')
                ->with('success', 'Permohonan reservasi untuk ' . $facility->name . ' (Kode Tiket: ' . $ticketCode . ') berhasil diajukan dan sedang menunggu verifikasi Petugas Sarpras.');
        });
    }

    /**
     * FUNCTION/PROCEDURE : history()
     * KEGUNAAN           : Menampilkan riwayat permohonan reservasi milik pengguna aktif.
     * CARA KERJA         : Mengambil reservasi milik user yang sedang aktif beserta relasi fasilitas, diurutkan dari yang terbaru.
     */
    public function history(): View
    {
        $userId = Auth::id();
        if (!$userId) {
            $defaultUser = User::where('email', 'dimas@mahasiswa.ac.id')->first() ?? User::first();
            $userId = $defaultUser ? $defaultUser->id : 1;
        }

        // 2. Hitung Ringkasan Jumlah Tiket per Kategori Status (Untuk Tab Badges)
        $counts = [
            'all'       => Reservation::where('user_id', $userId)->count(),
            'pending'   => Reservation::where('user_id', $userId)->where('status', 'pending')->count(),
            'approved'  => Reservation::where('user_id', $userId)->where('status', 'approved')->count(),
            'rejected'  => Reservation::where('user_id', $userId)->where('status', 'rejected')->count(),
            'cancelled' => Reservation::where('user_id', $userId)->where('status', 'cancelled')->count(),
        ];

        // 3. Bangun Kueri dengan Eager Loading (Pencegahan Masalah N+1 - BR-USR02-06)
        $query = Reservation::with(['facility', 'reviewer'])
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc');

        // 4. Filter Berdasarkan Tab Status Aktif
        $activeStatus = $request->query('status', 'all');
        if ($activeStatus !== 'all' && in_array($activeStatus, ['pending', 'approved', 'rejected', 'cancelled', 'completed'])) {
            $query->where('status', $activeStatus);
        }

        // 5. Filter Berdasarkan Pencarian Kata Kunci
        $keyword = trim($request->query('search', ''));
        if (!empty($keyword)) {
            $query->where(function ($q) use ($keyword) {
                $q->where('ticket_code', 'LIKE', '%' . $keyword . '%')
                  ->orWhere('purpose', 'LIKE', '%' . $keyword . '%')
                  ->orWhereHas('facility', function ($fQuery) use ($keyword) {
                      $fQuery->where('name', 'LIKE', '%' . $keyword . '%')
                             ->orWhere('building', 'LIKE', '%' . $keyword . '%');
                  });
            });
        }

        // 6. Paginasi Hasil Kueri (Maksimal 10 Baris per Halaman - BR-USR02-05)
        $reservations = $query->paginate(10)->withQueryString();

        return view('user.reservation-history', compact('reservations', 'counts', 'activeStatus', 'keyword'));
    }

    /**
     * FUNCTION/PROCEDURE : cancel()
     * FITUR              : USR-03 - Pembatalan Reservasi Mandiri oleh Pengguna
     * KEGUNAAN           : Membatalkan tiket permohonan reservasi dengan validasi batas waktu minimal H-1 (24 jam).
     * CARA KERJA         :
     *   1. Autentikasi & Otorisasi Kepemilikan (BR-USR03-01): Memastikan tiket milik pengguna aktif.
     *   2. Validasi Kelayakan Status (BR-USR03-03): Hanya status 'pending' atau 'approved' yang boleh dibatalkan.
     *   3. Validasi Batas Waktu H-1 (BR-USR03-02): Menghitung selisih waktu mulai kegiatan dengan waktu saat ini (>= 24 jam).
     *   4. Mengubah status menjadi 'cancelled' dan mencatat keterangan pembatalan (BR-USR03-04 & BR-USR03-05).
     *   5. Mengalihkan kembali pengguna dengan pesan notifikasi sukses.
     */
    public function cancel(Request $request, int|string $id): RedirectResponse
    {
        $userId = Auth::id();
        if (!$userId) {
            $defaultUser = User::where('email', 'dimas@mahasiswa.ac.id')->first() ?? User::first();
            $userId = $defaultUser ? $defaultUser->id : 1;
        }

        $reservation = Reservation::findOrFail($id);

        // 1. Otorisasi Kepemilikan (Strict Ownership - BR-USR03-01)
        if ((int) $reservation->user_id !== (int) $userId) {
            abort(403, 'Akses ditolak. Anda tidak memiliki izin untuk membatalkan tiket reservasi ini.');
        }

        // 2. Validasi Kelayakan Status (BR-USR03-03)
        if (!in_array($reservation->status, ['pending', 'approved'])) {
            return back()->withErrors([
                'error' => 'Reservasi ini tidak dapat dibatalkan karena sudah dalam status ' . $reservation->status . '.',
            ]);
        }

        // 3. Validasi Batas Waktu H-1 / 24 Jam (BR-USR03-02)
        $resDateTime = Carbon::parse($reservation->reservation_date)->setTimeFromTimeString($reservation->start_time);
        if (now()->diffInSeconds($resDateTime, false) < 86400) {
            return back()->withErrors([
                'error' => 'Pembatalan mandiri ditolak. Batas waktu pembatalan maksimal adalah H-1 (minimal 24 jam) sebelum acara dimulai.',
            ]);
        }

        // 4. Update Status dan Alasan Pembatalan (BR-USR03-04 & BR-USR03-05)
        $inputReason = trim($request->input('cancellation_reason', ''));
        $reservation->status = 'cancelled';
        $reservation->cancellation_reason = !empty($inputReason) ? $inputReason : 'Dibatalkan mandiri oleh pemohon.';
        $reservation->save();

        return redirect()->route('user.reservation-history')
            ->with('success', "Tiket reservasi {$reservation->ticket_code} berhasil dibatalkan. Fasilitas telah dilepas kembali ke kalender ketersediaan.");
    }
}
