<?php
/**
 * ====================================================================================================
 * SISTEM INFORMASI RESERVASI & PELAPORAN FASILITAS KAMPUS (CAVA)
 * MODUL 15: OPERASIONAL PETUGAS SARPRAS: PERSETUJUAN, PENOLAKAN, & PEMBATALAN DARURAT (PTG-02 & PTG-03)
 * BERKAS: app/Http/Controllers/ReservationManagementController.php
 * ====================================================================================================
 *
 * DESKRIPSI BERKAS:
 * Controller sentral manajemen operasional permohonan reservasi bagi staf Petugas UPT Sarpras Kampus.
 * Mengelola seluruh siklus hidup tiket reservasi mencakup:
 * 1. Lembar kerja antrean verifikasi dengan filter multi-tab status (Pending, Approved, Rejected, Cancelled).
 * 2. Eksekusi persetujuan (Approval - PTG-02 / US-9) dengan validasi anti-bentrok jadwal berkekuatan
 *    transaksional aman race-condition (Pessimistic Locking `lockForUpdate()`).
 * 3. Eksekusi penolakan (Rejection - PTG-02 / US-9) dengan justifikasi alasan resmi wajib.
 * 4. Pembatalan darurat sepihak (Force Cancel / Override Privilege - PTG-03 / US-10) untuk membebaskan
 *    slot ruangan yang telah disetujui jika terjadi kendala teknis mendesak.
 *
 * PERAN ARSITEKTURAL DALAM SISTEM CAVA:
 * 1. Pencegahan Bentrok Jadwal Mutlak (Zero Double-Booking):
 *    Mengimplementasikan rumus overlap rentang waktu SQL:
 *    `(req_start < existing_end AND req_end > existing_start)` pada fasilitas dan tanggal yang sama.
 * 2. Keamanan Bersamaan Tingkat Transaksi (ACID Pessimistic Locking):
 *    Menggunakan `DB::transaction()` bersama `lockForUpdate()` untuk mencegah dua petugas menyetujui
 *    dua permohonan yang bertabrakan pada detik mikro yang bersamaan.
 * 3. Sinkronisasi dengan Mode Perbaikan Fasilitas (Maintenance Mode Guard):
 *    Menolak persetujuan secara otomatis jika ruangan yang bersangkutan sedang berstatus 'dalam perbaikan'.
 * 4. Jejak Audit Resmi (Audit Trail):
 *    Merekam identitas ID petugas pemeriksa (`reviewed_by`) dan timestamp resmi (`reviewed_at`).
 * ====================================================================================================
 */

namespace App\Http\Controllers;

// Import FormRequest untuk validasi input penolakan dan pembatalan darurat
use App\Http\Requests\ForceCancelReservationRequest;
use App\Http\Requests\RejectReservationRequest;

// Import Model Eloquent fasilitas dan transaksi reservasi
use App\Models\Facility;
use App\Models\Reservation;

// Import antarmuka Response View dan Redirect dari Laravel
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

// Import Database Façade untuk manajemen transaksi ACID
use Illuminate\Support\Facades\DB;

/**
 * Class ReservationManagementController
 *
 * Mengelola antrean, verifikasi, approval anti-bentrok, penolakan, dan pembatalan darurat reservasi.
 */
class ReservationManagementController extends Controller
{
    /**
     * METHOD: index()
     * ------------------------------------------------------------------------------------------------
     * FUNGSI / KEGUNAAN:
     * Menampilkan lembar kerja manajemen reservasi dengan tab filter status dan pencarian terintegrasi.
     *
     * PARAMETER:
     * @param \Illuminate\Http\Request $request : Mengandung query parameter `status`, `search`, dan `per_page`.
     *
     * RETURN VALUE:
     * \Illuminate\Contracts\View\View : Mengembalikan view 'petugas.reservation-management'.
     *
     * CARA KERJA & ALUR LOGIKA:
     * 1. Menentukan status filter aktif (default 'pending' agar antrean yang belum diverifikasi langsung tampil).
     * 2. Membangun kueri Eloquent dengan Eager Loading relasi `facility`, `user`, dan `reviewer` (Anti N+1).
     * 3. Menerapkan klausa filter status jika bukan 'semua'.
     * 4. Menerapkan pencarian teks pada kode tiket, tujuan acara, identitas pemohon, dan nama gedung/ruang.
     * 5. Menerapkan aturan pengurutan: antrean 'pending' diurutkan secara FIFO (`created_at asc`),
     *    sedangkan status lainnya diurutkan kronologis terbalik (tanggal terbaru di atas).
     * 6. Menjalankan paginasi server-side dengan mempertahankan query string (`withQueryString()`).
     * 7. Menghitung kuantitas statistik seluruh status untuk badge angka pada tab Bento.
     * 8. Mengirimkan seluruh data ke view melalui helper `compact()`.
     */
    public function index(Request $request): View
    {
        // --------------------------------------------------------------------------------------------
        // LANGKAH 1: EKSTRAKSI PARAMETER FILTER & PENCARIAN
        // --------------------------------------------------------------------------------------------

        /**
         * Status default 'pending' mengarahkan fokus petugas langsung pada permohonan yang membutuhkan tindakan.
         */
        $currentStatus = $request->query('status', 'pending');
        $search = $request->query('search');

        // --------------------------------------------------------------------------------------------
        // LANGKAH 2: QUERY BUILDER DENGAN EAGER LOADING RELASIONAL (ANTI N+1)
        // --------------------------------------------------------------------------------------------

        /**
         * Syntax: Reservation::with(['facility', 'user', 'reviewer'])
         * Memuat sekaligus data ruang, sivitas pemohon, dan petugas reviewer dalam satu tarikan kueri SQL.
         */
        $query = Reservation::with(['facility', 'user', 'reviewer']);

        // Filter Spesifik Status Permohonan
        if ($currentStatus && $currentStatus !== 'semua') {
            $query->where('status', $currentStatus);
        }

        // Filter Pencarian Teks Multi-Atribut & Relasional
        if ($search) {
            $query->where(function ($q) use ($search) {
                // Pencarian langsung pada tabel reservations
                $q->where('ticket_code', 'like', "%{$search}%")
                  ->orWhere('purpose', 'like', "%{$search}%")
                  // Pencarian relasional pada tabel users
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('identity_number', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  })
                  // Pencarian relasional pada tabel facilities
                  ->orWhereHas('facility', function ($fq) use ($search) {
                      $fq->where('name', 'like', "%{$search}%")
                         ->orWhere('building', 'like', "%{$search}%");
                  });
            });
        }

        // --------------------------------------------------------------------------------------------
        // LANGKAH 3: PENGURUTAN DATA (FIFO UNTUK PENDING, DESC UNTUK LAINNYA)
        // --------------------------------------------------------------------------------------------

        if ($currentStatus === 'pending') {
            // Asas Keadilan Layanan Kampus: Permohonan yang masuk lebih awal dilayani terlebih dahulu (FIFO)
            $query->orderBy('created_at', 'asc');
        } else {
            // Riwayat lampau diurutkan dari yang paling baru
            $query->orderBy('reservation_date', 'desc')->orderBy('start_time', 'desc');
        }

        // --------------------------------------------------------------------------------------------
        // LANGKAH 4: PAGINASI SERVER-SIDE EFISIEN
        // --------------------------------------------------------------------------------------------

        $perPage = (int) $request->query('per_page', 10);
        $reservations = $query->paginate($perPage)->withQueryString();

        // --------------------------------------------------------------------------------------------
        // LANGKAH 5: AGREGASI JUMLAH TIKET PER STATUS UNTUK BADGE TAB BENTO
        // --------------------------------------------------------------------------------------------

        $totalCount = Reservation::count();
        $pendingCount = Reservation::where('status', 'pending')->count();
        $approvedCount = Reservation::where('status', 'approved')->count();
        $cancelledCount = Reservation::where('status', 'cancelled')->count();
        $rejectedCount = Reservation::where('status', 'rejected')->count();

        // --------------------------------------------------------------------------------------------
        // LANGKAH 6: PENGEMBALIAN VIEW
        // --------------------------------------------------------------------------------------------

        return view('petugas.reservation-management', compact(
            'reservations',
            'totalCount',
            'pendingCount',
            'approvedCount',
            'cancelledCount',
            'rejectedCount',
            'currentStatus',
            'search'
        ));
    }

    /**
     * METHOD: approve()
     * ------------------------------------------------------------------------------------------------
     * FUNGSI / KEGUNAAN:
     * Menyetujui permohonan reservasi secara resmi dengan validasi bentrok jadwal dan mode perbaikan (PTG-02 / US-9).
     *
     * PARAMETER:
     * @param int|string $id : Primary Key reservasi yang akan disetujui.
     *
     * RETURN VALUE:
     * \Illuminate\Http\RedirectResponse : Mengalihkan kembali ke halaman antrean dengan flash message sukses atau error.
     *
     * CARA KERJA & ALUR LOGIKA:
     * 1. Menjalankan blok `DB::transaction()` untuk menjamin integritas atomik (All-or-Nothing).
     * 2. Menerapkan Pessimistic Locking via `Reservation::lockForUpdate()->findOrFail($id)`
     *    sehingga baris data tidak dapat dimodifikasi oleh transaksi konkuren lain sampai blok ini commit.
     * 3. Memverifikasi status tiket saat ini wajib 'pending'.
     * 4. Memeriksa status operasional fasilitas: menolak jika sedang 'dalam perbaikan' (Maintenance Mode).
     * 5. Mengeksekusi Kueri Validasi Anti-Bentrok Jadwal (Overlap SQL Formula):
     *    Menyaring reservasi lain pada `facility_id` dan `reservation_date` yang sama dengan status 'approved'.
     *    Rumus singgungan: `start_time < req_end AND end_time > req_start`.
     * 6. Jika overlap terdeteksi, melempar Exception gagal.
     * 7. Jika lolos seluruh validasi: mengubah status menjadi 'approved', mengisi `reviewed_by = auth()->id()`,
     *    mengisi `reviewed_at = now()`, dan menyimpan perubahan (`save()`).
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

                // 3. Validasi Pemblokiran Fasilitas (Maintenance Mode Guard - PTG-05)
                $facility = Facility::find($res->facility_id);
                if ($facility && $facility->status === 'dalam perbaikan') {
                    throw new \Exception('Gagal menyetujui! Fasilitas ini sedang dalam Mode Perbaikan (Maintenance Mode) dan diblokir untuk pemesanan.');
                }

                /**
                 * 4. Kueri Validasi Anti-Bentrok Jadwal (Overlap SQL Formula)
                 * Rumus Matematika Interval: Dua rentang [A, B] dan [C, D] bertabrakan jika dan hanya jika:
                 * (A < D) DAN (B > C)
                 * Di mana:
                 * A = $start_time reservasi yang sudah disetujui
                 * B = $end_time reservasi yang sudah disetujui
                 * C = $res->start_time permohonan yang diajukan
                 * D = $res->end_time permohonan yang diajukan
                 */
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

                // 5. Lolos Seluruh Validasi: Tandai Reservasi Resmi Disetujui
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
     * METHOD: reject()
     * ------------------------------------------------------------------------------------------------
     * FUNGSI / KEGUNAAN:
     * Menolak permohonan reservasi dengan alasan penolakan resmi yang wajib diisi (PTG-02 / US-9).
     *
     * PARAMETER:
     * @param \App\Http\Requests\RejectReservationRequest $request : Payload terverifikasi berisi `rejection_reason`.
     * @param int|string $id : Primary Key reservasi yang ditolak.
     *
     * RETURN VALUE:
     * \Illuminate\Http\RedirectResponse : Redirect kembali dengan pesan sukses atau error.
     *
     * CARA KERJA & ALUR LOGIKA:
     * 1. Membuka transaksi `DB::transaction()`.
     * 2. Mengunci baris reservasi dengan `lockForUpdate()`.
     * 3. Memverifikasi status tiket saat ini masih 'pending'.
     * 4. Mengubah status menjadi 'rejected', menyimpan alasan penolakan, mengisi reviewer dan timestamp.
     * 5. Melakukan commit transaksi dan mengembalikan notifikasi sukses.
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

                // 3. Eksekusi Penolakan dan Pencatatan Alasan Resmi Transparan
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

    /**
     * METHOD: forceCancel()
     * ------------------------------------------------------------------------------------------------
     * FUNGSI / KEGUNAAN:
     * Mengeksekusi pembatalan darurat sepihak (Override Privilege) oleh Petugas Sarpras
     * untuk reservasi yang sebelumnya telah disetujui (PTG-03 / US-10).
     *
     * PARAMETER:
     * @param \App\Http\Requests\ForceCancelReservationRequest $request : Berisi kolom alasan darurat `alasan_batal`.
     * @param int|string $id : Primary Key reservasi berstatus 'approved'.
     *
     * RETURN VALUE:
     * \Illuminate\Http\RedirectResponse : Redirect kembali dengan pesan status pembatalan.
     *
     * CARA KERJA & ALUR LOGIKA:
     * 1. Membuka `DB::transaction()`.
     * 2. Mengunci baris data dengan `lockForUpdate()`.
     * 3. Memeriksa status awal reservasi: hanya status 'approved' yang boleh dibatalkan paksa.
     * 4. Memverifikasi bahwa kegiatan belum usai: membandingkan waktu selesai kegiatan dengan waktu saat ini (`now()`).
     *    Kegiatan yang sudah lampau tidak boleh dibatalkan surut.
     * 5. Mengubah status tiket menjadi 'cancelled', menyimpan alasan darurat ke `cancellation_reason`,
     *    dan mencatat identitas petugas yang melakukan override.
     * 6. Dengan berubahnya status ke 'cancelled', slot waktu fasilitas otomatis bebas kembali untuk pihak lain.
     */
    public function forceCancel(ForceCancelReservationRequest $request, int|string $id): RedirectResponse
    {
        try {
            $reservation = DB::transaction(function () use ($request, $id) {
                // 1. Penguncian baris database tingkat transaksi (Pessimistic Locking)
                $res = Reservation::lockForUpdate()->findOrFail($id);

                // 2. Validasi Prasyarat: Hanya reservasi yang telah disetujui (approved) yang boleh dibatalkan paksa
                if ($res->status !== 'approved') {
                    throw new \Exception('Hanya reservasi yang telah disetujui yang dapat dibatalkan paksa.');
                }

                // 3. Validasi Waktu: Tidak boleh membatalkan kegiatan yang waktu selesainya sudah terlewat
                $resEnd = \Carbon\Carbon::parse($res->reservation_date)->setTimeFromTimeString($res->end_time);
                if (now()->greaterThan($resEnd)) {
                    throw new \Exception('Gagal membatalkan! Waktu reservasi acara ini telah berlalu.');
                }

                // 4. Eksekusi Pembatalan Darurat dan Pencatatan Alasan Resmi
                $res->status = 'cancelled';
                $res->cancellation_reason = $request->input('alasan_batal') ?? $request->input('cancellation_reason');
                $res->reviewed_by = auth()->id();
                $res->reviewed_at = now();
                $res->save();

                return $res;
            });

            return back()->with('success', 'Pembatalan darurat tiket ' . $reservation->ticket_code . ' berhasil dieksekusi. Slot fasilitas telah dibebaskan.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
