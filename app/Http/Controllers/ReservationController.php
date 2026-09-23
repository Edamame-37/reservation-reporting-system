<?php
/**
 * NAMA FILE    : ReservationController.php
 * FUNGSI       : Controller Manajemen Pengajuan & Riwayat Reservasi Ruang Kampus
 * DESKRIPSI    : Menangani formulir permohonan reservasi sivitas (USR-01) serta penyajian dasbor riwayat peminjaman terisolasi per akun pengguna (USR-02).
 * CARA KERJA   : Menyediakan method create() untuk formulir, store() dengan validasi anti-bentrok, dan history() dengan kueri Eager Loading, filter status, pencarian, dan paginasi.
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
     * KEGUNAAN           : Menampilkan riwayat permohonan reservasi milik pengguna aktif secara dinamis (USR-02).
     * CARA KERJA         : Mengisolasi kueri berdasarkan user_id, menjalankan Eager Loading relasi facility dan reviewer, menyaring status & kata kunci pencarian, serta menghitung badge count untuk tab filter.
     */
    public function history(Request $request): View
    {
        // 1. Tentukan ID Pengguna Pemohon (Isolasi Data Pribadi - BR-USR02-01)
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
}
