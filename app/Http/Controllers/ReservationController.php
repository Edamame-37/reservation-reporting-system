<?php

/**
 * ====================================================================================================
 * NAMA FILE    : ReservationController.php
 * LOKASI       : app/Http/Controllers/ReservationController.php
 * FUNGSI       : Controller Pengelolaan Siklus Hidup Transaksi Reservasi Ruang Kampus oleh Sivitas
 * MODUL CAVA   : 10. Portal Pengguna: Formulir Pengajuan Reservasi Ruangan Mandiri (USR-01)
 *                (Termasuk USR-02 Riwayat & USR-03 Pembatalan Mandiri H-1)
 * ====================================================================================================
 * 
 * ARSITEKTUR & CARA KERJA SISTEM:
 * 1. Alur Pengajuan Reservasi Mandiri (USR-01):
 *    - `create()`: Menyediakan data master fasilitas aktif, mendeteksi parameter pra-pilih dari katalog,
 *      dan merender antarmuka formulir interaktif 3 level (Gedung -> Lantai -> Ruangan).
 *    - `store()` : Menjalankan validasi ketat tingkat server (StoreReservationRequest), memverifikasi kelayakan
 *      status fasilitas, menghitung durasi slot 30 menit, mengevaluasi overlap bentrok jadwal terhadap
 *      reservasi yang telah disetujui (status = 'approved'), serta mencatat transaksi baru dalam blok
 *      atomik `DB::transaction()`.
 * 
 * 2. Logika Algoritma Anti-Bentrok Jadwal (Anti Double-Booking):
 *    - Dua interval waktu [A_start, A_end] dan [B_start, B_end] saling bertumpukan (overlap) jika dan hanya jika:
 *      `start_time < B_end` AND `end_time > B_start`.
 *    - Kueri ini dievaluasi secara presisi terhadap tabel `reservations` yang telah disetujui pada tanggal dan
 *      fasilitas yang sama sebelum data baru diizinkan tersimpan.
 * 
 * 3. Integritas Kode Tiket Unik:
 *    - Menerbitkan kode tiket dengan format baku: `TKT-YYYYMMDD-XXXX`.
 *    - Menggunakan entropi kriptografis acak (`random_bytes`) dan perulangan `while` untuk menjamin
 *      keunikan kode tiket tanpa peluang benturan (zero-collision).
 * 
 * ====================================================================================================
 */

namespace App\Http\Controllers;

// Mengimpor Form Request khusus validasi payload permohonan reservasi
use App\Http\Requests\StoreReservationRequest;

// Mengimpor Model Eloquent Facility untuk membaca data master ruangan kampus
use App\Models\Facility;

// Mengimpor Model Eloquent Reservation untuk transaksi permohonan dan kueri overlap
use App\Models\Reservation;

// Mengimpor Model Eloquent User untuk manajemen entitas pengguna sivitas
use App\Models\User;

// Mengimpor pustaka Carbon untuk manipulasi tanggal dan waktu
use Carbon\Carbon;

// Mengimpor kelas respons pengalihan (redirect) HTTP
use Illuminate\Http\RedirectResponse;

// Mengimpor kelas representasi HTTP Request standar
use Illuminate\Http\Request;

// Mengimpor Facade Auth untuk mengidentifikasi akun pengguna yang sedang login
use Illuminate\Support\Facades\Auth;

// Mengimpor Facade DB untuk mengelola transaksi basis data atomik
use Illuminate\Support\Facades\DB;

// Mengimpor interface View untuk type-hinting tampilan Blade yang dirender
use Illuminate\View\View;

/**
 * Class ReservationController
 * 
 * Mengendalikan seluruh interaksi portal pengguna sivitas yang berkaitan dengan pengajuan,
 * riwayat peninjauan, dan pembatalan reservasi fasilitas kampus.
 */
class ReservationController extends Controller
{
    /**
     * Menampilkan halaman formulir pengajuan reservasi ruangan kampus (USR-01).
     * 
     * CARA KERJA & LOGIKA:
     * 1. Menarik seluruh fasilitas yang berstatus 'aktif' dari database diurutkan secara alfabetis.
     * 2. Menangkap parameter query URL 'facility_id' (jika pengguna mengklik "Ajukan Sewa" dari halaman katalog publik).
     * 3. Membaca draft permohonan dari session (jika ada) untuk memulihkan isian form yang belum selesai.
     * 4. Merender view Blade `user.reservation-form` dengan meneruskan variabel `facilities`, `selectedFacilityId`, dan `draft`.
     * 
     * @param  \Illuminate\Http\Request  $request Objek permohonan HTTP saat ini
     * @return \Illuminate\View\View Tampilan formulir pengajuan reservasi
     */
    public function create(Request $request): View
    {
        // Menarik daftar fasilitas berstatus aktif agar ruangan dalam masa perbaikan tidak muncul di form
        $facilities = Facility::where('status', 'aktif')
            ->orderBy('name')
            ->get();

        // Menangkap parameter facility_id dari query string (?facility_id=X) untuk pra-seleksi otomatis
        $selectedFacilityId = $request->query('facility_id');

        // Mengambil data draft formulir sementara dari sesi pengguna (jika tersedia)
        $draft = session('reservation_draft', []);

        // Mengembalikan view 'user.reservation-form' dengan variabel pendukung yang dipadatkan (compact)
        return view('user.reservation-form', compact('facilities', 'selectedFacilityId', 'draft'));
    }

    /**
     * Memvalidasi dan menyimpan transaksi permohonan reservasi baru ke basis data (USR-01).
     * 
     * CARA KERJA & LOGIKA:
     * 1. Validasi Input: Menggunakan StoreReservationRequest untuk menyaring jam operasional (07-20),
     *    interval 30 menit, tanggal minimal hari ini, dan panjang teks deskripsi tujuan.
     * 2. Verifikasi Fasilitas: Memastikan fasilitas yang dipilih berstatus 'aktif'. Jika dalam masa perbaikan,
     *    kembalikan error dengan pesan ramah.
     * 3. Penghitungan Slot: Mengonversi waktu mulai dan selesai ke objek Carbon untuk menghitung selisih menit,
     *    lalu membaginya dengan 30 untuk mendapatkan nilai `total_slots`.
     * 4. Pembangkitan Kode Tiket: Membentuk string `TKT-YYYYMMDD-XXXX` dan mengecek keunikan di basis data.
     * 5. Transaksi Atomik (DB::transaction):
     *    - Menjalankan kueri deteksi overlap terhadap reservasi lain yang telah 'approved'.
     *    - Jika bentrok terdeteksi, batalkan transaksi dan beri peringatan ke pengguna.
     *    - Jika aman, buat baris reservasi baru berstatus 'pending'.
     *    - Hapus draft sesi dan redirect ke halaman riwayat tiket dengan notifikasi sukses.
     * 
     * @param  \App\Http\Requests\StoreReservationRequest  $request Payload tervalidasi dari form
     * @return \Illuminate\Http\RedirectResponse Redireksi ke riwayat reservasi atau kembali dengan pesan error
     */
    public function store(StoreReservationRequest $request): RedirectResponse
    {
        // Mengambil array data yang telah lolos seluruh aturan validasi FormRequest
        $validated = $request->validated();

        // 1. Verifikasi Status Kelayakan Fasilitas di Database
        $facility = Facility::findOrFail($validated['facility_id']);
        if ($facility->status !== 'aktif') {
            return back()
                ->withInput()
                ->withErrors([
                    'facility_id' => 'Fasilitas "' . $facility->name . '" sedang dalam masa perbaikan atau non-aktif sehingga tidak dapat dipesan.'
                ]);
        }

        // 2. Mengambil Identitas Unik Pengguna yang Sedang Terotentikasi
        $userId = Auth::id();

        // 3. Menghitung Total Slot Durasi (Kelipatan 30 Menit)
        $startTime     = Carbon::createFromFormat('H:i', $validated['start_time']);
        $endTime       = Carbon::createFromFormat('H:i', $validated['end_time']);
        $diffInMinutes = $startTime->diffInMinutes($endTime);
        $totalSlots    = max(1, (int) round($diffInMinutes / 30));

        // 4. Generate Kode Tiket Unik (Format: TKT-YYYYMMDD-XXXX)
        $datePrefix = Carbon::parse($validated['reservation_date'])->format('Ymd');
        $randomSeq  = strtoupper(substr(bin2hex(random_bytes(2)), 0, 4));
        $ticketCode = sprintf('TKT-%s-%s', $datePrefix, $randomSeq);

        // Memastikan tidak ada tabrakan kode tiket unik di database (Zero-Collision Protection)
        while (Reservation::where('ticket_code', $ticketCode)->exists()) {
            $randomSeq  = strtoupper(substr(bin2hex(random_bytes(2)), 0, 4));
            $ticketCode = sprintf('TKT-%s-%s', $datePrefix, $randomSeq);
        }

        // 5. Eksekusi Pengecekan Bentrok Jadwal dan Penyimpanan dalam Transaksi Database Atomik
        return DB::transaction(function () use ($validated, $facility, $userId, $totalSlots, $ticketCode, $request) {
            
            // Pengecekan Irisan Waktu (Overlap Detection):
            // Rumus baku interval tumpang tindih: (start_A < end_B) AND (end_A > start_B)
            // Hanya memeriksa reservasi yang telah disetujui (status = 'approved') pada ruangan dan tanggal yang sama
            $overlapExists = Reservation::where('facility_id', $validated['facility_id'])
                ->whereDate('reservation_date', $validated['reservation_date'])
                ->where('status', 'approved')
                ->where(function ($query) use ($validated) {
                    $query->where('start_time', '<', $validated['end_time'])
                          ->where('end_time', '>', $validated['start_time']);
                })
                ->exists();

            // Jika jadwal yang diminta bertabrakan dengan jadwal yang sudah disetujui
            if ($overlapExists) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'start_time' => 'Jadwal bentrok! Fasilitas "' . $facility->name . '" telah terisi oleh reservasi lain yang telah disetujui pada jam tersebut. Silakan pilih slot waktu lain.'
                    ]);
            }

            // Menyimpan entitas reservasi baru ke basis data dengan status awal 'pending'
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

            // Membersihkan draft sementara pada session setelah permohonan berhasil tersimpan
            session()->forget('reservation_draft');

            // Mengalihkan pengguna ke halaman riwayat tiket reservasi dengan notifikasi keberhasilan
            return redirect()
                ->route('user.reservation-history')
                ->with('success', 'Permohonan reservasi untuk ' . $facility->name . ' (Kode Tiket: ' . $ticketCode . ') berhasil diajukan dan sedang menunggu verifikasi Petugas Sarpras.');
        });
    }

    /**
     * Menampilkan riwayat permohonan reservasi milik pengguna aktif (USR-02).
     * 
     * CARA KERJA & LOGIKA:
     * 1. Menghitung ringkasan jumlah tiket per status (all, pending, approved, rejected, cancelled) untuk badge tab.
     * 2. Memuat relasi `facility` dan `reviewer` dengan teknik Eager Loading untuk mencegah masalah kueri N+1.
     * 3. Menerapkan filter status jika parameter query 'status' disertakan pada request.
     * 4. Menerapkan pencarian teks pada kode tiket, tujuan acara, atau nama fasilitas/gedung.
     * 5. Membatasi tampilan hasil dengan pagination 10 baris per halaman.
     * 
     * @param  \Illuminate\Http\Request  $request Permohonan HTTP berisi query status/search
     * @return \Illuminate\View\View Tampilan riwayat reservasi pengguna
     */
    public function history(Request $request): View
    {
        $userId = Auth::id();

        // Menghitung ringkasan jumlah tiket per kategori status milik pengguna login
        $counts = [
            'all'       => Reservation::where('user_id', $userId)->count(),
            'pending'   => Reservation::where('user_id', $userId)->where('status', 'pending')->count(),
            'approved'  => Reservation::where('user_id', $userId)->where('status', 'approved')->count(),
            'rejected'  => Reservation::where('user_id', $userId)->where('status', 'rejected')->count(),
            'cancelled' => Reservation::where('user_id', $userId)->where('status', 'cancelled')->count(),
        ];

        // Membangun kueri dasar dengan Eager Loading (Pencegahan Masalah Performa N+1)
        $query = Reservation::with(['facility', 'reviewer'])
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc');

        // Memfilter data berdasarkan tab status yang dipilih pengguna
        $activeStatus = $request->query('status', 'all');
        if ($activeStatus !== 'all' && in_array($activeStatus, ['pending', 'approved', 'rejected', 'cancelled', 'completed'])) {
            $query->where('status', $activeStatus);
        }

        // Memfilter data berdasarkan kata kunci pencarian (Search Filter)
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

        // Paginasi hasil data maksimal 10 baris per halaman dengan mempertahankan query string URL
        $reservations = $query->paginate(10)->withQueryString();

        // Mengambil seluruh fasilitas aktif untuk kebutuhan modal/drawer pengajuan reservasi terpadu
        $facilities = Facility::where('status', 'aktif')
            ->orderBy('name')
            ->get();

        return view('user.reservation-history', compact('reservations', 'counts', 'activeStatus', 'keyword', 'facilities'));
    }

    /**
     * Membatalkan tiket permohonan reservasi secara mandiri dengan validasi batas waktu H-1 / 24 Jam (USR-03).
     * 
     * CARA KERJA & LOGIKA:
     * 1. Otorisasi Kepemilikan: Memastikan reservasi adalah milik pengguna yang sedang login (`user_id === Auth::id()`).
     * 2. Kelayakan Status: Pembatalan hanya diizinkan untuk tiket berstatus 'pending' atau 'approved'.
     * 3. Validasi Batas Waktu H-1: Menghitung selisih waktu mulai kegiatan terhadap waktu sekarang (`now()`).
     *    Jika selisih waktu kurang dari 24 jam (86.400 detik), pembatalan ditolak demi melindungi kestabilan operasional kampus.
     * 4. Pembaruan Status: Mengubah status menjadi 'cancelled', menyimpan alasan pembatalan pemohon, dan melepas slot ruangan.
     * 
     * @param  \Illuminate\Http\Request  $request Objek permohonan HTTP dengan alasan pembatalan
     * @param  int|string                $id      ID reservasi yang akan dibatalkan
     * @return \Illuminate\Http\RedirectResponse Redireksi dengan status pembatalan
     */
    public function cancel(Request $request, int|string $id): RedirectResponse
    {
        // Validasi input alasan pembatalan (maksimal 500 karakter)
        $request->validate([
            'cancellation_reason' => ['nullable', 'string', 'max:500'],
        ], [
            'cancellation_reason.max' => 'Alasan pembatalan maksimal 500 karakter.',
        ]);

        $userId = Auth::id();

        // Menemukan entitas reservasi berdasarkan Primary Key atau melempar 404 jika tidak ditemukan
        $reservation = Reservation::findOrFail($id);

        // 1. Otorisasi Kepemilikan Mutlak (Strict Ownership Isolation - BR-USR03-01)
        if ((int) $reservation->user_id !== (int) $userId) {
            abort(403, 'Akses ditolak. Anda tidak memiliki izin untuk membatalkan tiket reservasi ini.');
        }

        // 2. Validasi Kelayakan Status Permohonan (BR-USR03-03)
        if (!in_array($reservation->status, ['pending', 'approved'])) {
            return back()->withErrors([
                'error' => 'Reservasi ini tidak dapat dibatalkan karena sudah dalam status ' . $reservation->status . '.',
            ]);
        }

        // 3. Validasi Batas Waktu Minimal H-1 / 24 Jam Sebelum Mulai (BR-USR03-02)
        $resDateTime = Carbon::parse($reservation->reservation_date)->setTimeFromTimeString($reservation->start_time);
        if (now()->diffInSeconds($resDateTime, false) < 86400) {
            return back()->withErrors([
                'error' => 'Pembatalan mandiri ditolak. Batas waktu pembatalan maksimal adalah H-1 (minimal 24 jam) sebelum acara dimulai.',
            ]);
        }

        // 4. Memperbarui Status Transaksi dan Mencatat Keterangan Pembatalan (BR-USR03-04 & BR-USR03-05)
        $inputReason = trim($request->input('cancellation_reason', ''));
        $reservation->status = 'cancelled';
        $reservation->cancellation_reason = !empty($inputReason) ? $inputReason : 'Dibatalkan mandiri oleh pemohon.';
        $reservation->save();

        // Mengalihkan kembali pengguna dengan pesan konfirmasi pelepasan slot jadwal
        return redirect()->route('user.reservation-history')
            ->with('success', "Tiket reservasi {$reservation->ticket_code} berhasil dibatalkan. Fasilitas telah dilepas kembali ke kalender ketersediaan.");
    }
}
