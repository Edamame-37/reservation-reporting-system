<?php
/**
 * ====================================================================================================
 * SISTEM INFORMASI RESERVASI & PELAPORAN FASILITAS KAMPUS (CAVA)
 * MODUL 16: OPERASIONAL PETUGAS SARPRAS: MANAJEMEN TIKET KERUSAKAN, RESOLUSI & MODE PERBAIKAN (PTG-04 & PTG-05)
 * BERKAS: app/Http/Controllers/ReportManagementController.php
 * ====================================================================================================
 *
 * DESKRIPSI BERKAS:
 * Controller operasional pemeliharaan sarpras yang mengelola seluruh siklus hidup tiket keluhan kerusakan
 * fasilitas kampus yang dilaporkan oleh sivitas akademika (PTG-04 / US-11), serta saklar penguncian
 * operasional ruangan kampus ke dalam Mode Perbaikan / Maintenance Mode (PTG-05 / US-12 / UR12).
 *
 * PERAN ARSITEKTURAL DALAM SISTEM CAVA:
 * 1. Manajemen Disposisi & Resolusi Tiket Sarpras (PTG-04):
 *    Menyediakan alur kerja transisi status:
 *    `baru` (laporan masuk) -> `diproses` (sedang ditangani teknisi) -> `selesai` (perbaikan tuntas) / `ditolak`.
 * 2. Kewajiban Catatan Resolusi Teknis:
 *    Mewajibkan input catatan tindakan perbaikan (`resolution_note`) saat status diubah menjadi 'selesai',
 *    lengkap dengan pencatatan ID petugas (`handled_by`) dan timestamp selesai (`resolved_at`).
 * 3. Sinkronisasi Otomatis Mode Perbaikan Fasilitas (UR12 Auto-Lock Guard):
 *    - Jika petugas menandai fasilitas terkunci saat penanganan, status fasilitas diubah ke 'dalam perbaikan'.
 *    - Jika laporan dinyatakan selesai dan tidak ada tiket kerusakan aktif lain yang mengunci fasilitas tersebut,
 *      sistem secara cerdas membuka kembali fasilitas ke status 'aktif' di kalender reservasi.
 * 4. Saklar Manual Mode Perbaikan (PTG-05 / Maintenance Toggle):
 *    Menyediakan tombol cepat on/off untuk memblokir ruangan sewaktu-waktu tanpa harus menunggu laporan sivitas.
 * ====================================================================================================
 */

namespace App\Http\Controllers;

// Import FormRequest untuk validasi status dan catatan resolusi
use App\Http\Requests\UpdateReportStatusRequest;

// Import Model Eloquent tiket laporan kerusakan dan master fasilitas
use App\Models\DamageReport;
use App\Models\Facility;

// Import antarmuka Response View dan Redirect dari Laravel
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

// Import Façade Database untuk transaksi aman (ACID Transaction)
use Illuminate\Support\Facades\DB;

/**
 * Class ReportManagementController
 *
 * Mengelola lembar kerja tiket kerusakan sarpras, catatan resolusi teknisi, dan saklar pemeliharaan fasilitas.
 */
class ReportManagementController extends Controller
{
    /**
     * METHOD: index()
     * ------------------------------------------------------------------------------------------------
     * FUNGSI / KEGUNAAN:
     * Menampilkan lembar kerja manajemen tiket kerusakan fasilitas sarpras dengan tab filter status,
     * pencarian teks, dan paginasi server-side.
     *
     * PARAMETER:
     * @param \Illuminate\Http\Request $request : Mengandung query parameter `status`, `search`, dll.
     *
     * RETURN VALUE:
     * \Illuminate\Contracts\View\View : Mengembalikan view 'petugas.report-management'.
     *
     * CARA KERJA & ALUR LOGIKA:
     * 1. Membaca filter status aktif (default 'semua' atau pilihan 'baru', 'diproses', 'selesai', 'ditolak').
     * 2. Membaca teks pencarian dari input search bar.
     * 3. Membangun kueri Eloquent dengan Eager Loading relasi `facility`, `user` (pelapor), dan `handler` (teknisi).
     * 4. Menyaring berdasarkan status jika bukan bernilai 'semua'.
     * 5. Menerapkan pencarian relasional multi-atribut (kode tiket, deskripsi masalah, nama pelapor, NIM, nama ruang).
     * 6. Mengurutkan berdasarkan urgensi bisnis melalui `orderByRaw()`: tiket 'baru' tampil paling atas (bobot 0),
     *    diikuti 'diproses' (bobot 1), lalu status lainnya (bobot 2), dan diurutkan kronologis terbaru (`created_at desc`).
     * 7. Membagi data menjadi 10 baris per halaman menggunakan `paginate(10)->withQueryString()`.
     * 8. Menghitung kuantitas statistik seluruh status untuk disematkan pada tab tombol filter.
     * 9. Mengembalikan view Blade `petugas.report-management` dengan data ter-compact.
     */
    public function index(Request $request): View
    {
        // --------------------------------------------------------------------------------------------
        // LANGKAH 1: EKSTRAKSI PARAMETER FILTER & PENCARIAN
        // --------------------------------------------------------------------------------------------
        $currentStatus = $request->query('status', 'semua');
        $search = $request->query('search');

        // --------------------------------------------------------------------------------------------
        // LANGKAH 2: QUERY BUILDER DENGAN EAGER LOADING RELASIONAL ANTI N+1
        // --------------------------------------------------------------------------------------------
        $query = DamageReport::with(['facility', 'user', 'handler']);

        // Filter Spesifik Status Tiket
        if ($currentStatus && $currentStatus !== 'semua') {
            $query->where('status', $currentStatus);
        }

        // Filter Pencarian Teks Multi-Kolom & Relasional
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('ticket_code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('identity_number', 'like', "%{$search}%");
                  })
                  ->orWhereHas('facility', function ($fq) use ($search) {
                      $fq->where('name', 'like', "%{$search}%")
                         ->orWhere('building', 'like', "%{$search}%");
                  });
            });
        }

        // --------------------------------------------------------------------------------------------
        // LANGKAH 3: PENGURUTAN BERDASARKAN SKALA PRIORITAS URGENSI (CASE WHEN ORDERING)
        // --------------------------------------------------------------------------------------------
        /**
         * Logika Urutan Urgensi:
         * 1. Status 'baru' (belum ditindaklanjuti) mendapat bobot 0 (tampil pertama).
         * 2. Status 'diproses' (sedang ditangani) mendapat bobot 1.
         * 3. Status 'selesai' dan 'ditolak' mendapat bobot 2.
         * Sub-urutan: Tanggal pelaporan paling baru (`created_at desc`).
         */
        $query->orderByRaw("CASE WHEN status = 'baru' THEN 0 WHEN status = 'diproses' THEN 1 ELSE 2 END")
              ->orderBy('created_at', 'desc');

        // --------------------------------------------------------------------------------------------
        // LANGKAH 4: PAGINASI SERVER-SIDE EFISIEN
        // --------------------------------------------------------------------------------------------
        $reports = $query->paginate(10)->withQueryString();

        // --------------------------------------------------------------------------------------------
        // LANGKAH 5: AGREGASI STATISTIK BEBAN KERJA TIKET KERUSAKAN
        // --------------------------------------------------------------------------------------------
        $totalCount = DamageReport::count();
        $newCount = DamageReport::where('status', 'baru')->count();
        $inProgressCount = DamageReport::where('status', 'diproses')->count();
        $resolvedCount = DamageReport::where('status', 'selesai')->count();
        $rejectedCount = DamageReport::where('status', 'ditolak')->count();

        return view('petugas.report-management', compact(
            'reports',
            'totalCount',
            'newCount',
            'inProgressCount',
            'resolvedCount',
            'rejectedCount',
            'currentStatus',
            'search'
        ));
    }

    /**
     * METHOD: updateStatus()
     * ------------------------------------------------------------------------------------------------
     * FUNGSI / KEGUNAAN:
     * Memperbarui jenjang status penanganan tiket kerusakan, mencatat resolusi teknisi, dan mengelola
     * sinkronisasi status kunci fasilitas kampus (PTG-04 / US-11 / UR12).
     *
     * PARAMETER:
     * @param \App\Http\Requests\UpdateReportStatusRequest $request : Payload terverifikasi berisi status & catatan.
     * @param int|string $id : Primary Key tiket laporan kerusakan target.
     *
     * RETURN VALUE:
     * \Illuminate\Http\RedirectResponse : Mengalihkan kembali dengan notifikasi sukses atau pesan error.
     *
     * CARA KERJA & ALUR LOGIKA:
     * 1. Menjalankan blok `DB::transaction()` untuk menjamin konsistensi ACID.
     * 2. Mengambil data tiket dengan Pessimistic Locking (`lockForUpdate()`).
     * 3. Membaca status baru, catatan resolusi, dan nilai checkbox penguncian fasilitas (`is_facility_locked`).
     * 4. Memperbarui atribut status tiket, catatan resolusi, ID penangan (`handled_by = auth()->id()`),
     *    dan timestamp selesai (`resolved_at = now()`) jika statusnya adalah 'selesai'.
     * 5. Sinkronisasi Operasional Fasilitas Kampus:
     *    - Jika flag `is_facility_locked` aktif: memperbarui status fasilitas target menjadi 'dalam perbaikan'.
     *    - Jika tiket diselesaikan (`newStatus === 'selesai'`): sistem memeriksa apakah masih ada tiket
     *      kerusakan lain pada fasilitas tersebut yang mengunci ruangan. Jika TIDAK ada laporan lain yang aktif,
     *      status fasilitas secara otomatis dikembalikan menjadi 'aktif'.
     * 6. Menyimpan perubahan ke basis data dan mereturn notifikasi sukses.
     */
    public function updateStatus(UpdateReportStatusRequest $request, int|string $id): RedirectResponse
    {
        try {
            DB::transaction(function () use ($request, $id) {
                // 1. Ambil data tiket kerusakan dengan penguncian baris transaksi (Anti Race Condition)
                $report = DamageReport::lockForUpdate()->findOrFail($id);

                $newStatus = $request->input('status') ?? $request->input('status_laporan');
                $note = $request->input('catatan_resolusi') ?? $request->input('resolution_note');
                $isLocked = $request->boolean('is_facility_locked');

                // 2. Pembaruan Status dan Catatan Resolusi Penanganan
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

                // 3. Sinkronisasi Cerdas Status Operasional Fasilitas Kampus (UR12 Auto-Lock)
                if ($report->facility_id) {
                    if ($isLocked) {
                        // Kunci fasilitas menjadi 'dalam perbaikan' agar tidak bisa direservasi di kalender
                        Facility::where('id', $report->facility_id)->update(['status' => 'dalam perbaikan']);
                    } elseif ($newStatus === 'selesai') {
                        /**
                         * Validasi Pelepasan Kunci Otomatis:
                         * Fasilitas hanya dibuka kembali ke 'aktif' jika tidak ada tiket kerusakan lain
                         * berstatus 'baru' atau 'diproses' yang masih meminta pemblokiran ruangan.
                         */
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
     * METHOD: toggleMaintenance()
     * ------------------------------------------------------------------------------------------------
     * FUNGSI / KEGUNAAN:
     * Mengubah saklar operasional fasilitas antara status 'aktif' dan 'dalam perbaikan' secara langsung (PTG-05 / US-12).
     *
     * PARAMETER:
     * @param int|string $id : Primary Key master fasilitas yang akan di-toggle.
     *
     * RETURN VALUE:
     * \Illuminate\Http\RedirectResponse : Mengalihkan kembali dengan pesan konfirmasi aksi saklar.
     *
     * CARA KERJA & ALUR LOGIKA:
     * 1. Membuka transaksi `DB::transaction()` dengan `Facility::lockForUpdate()->findOrFail($id)`.
     * 2. Jika fasilitas saat ini berstatus 'dalam perbaikan':
     *    - Mengubah status fasilitas menjadi 'aktif'.
     *    - Melepaskan flag penguncian (`is_facility_locked = false`) pada seluruh laporan kerusakan terkait.
     *    - Menghasilkan pesan sukses bahwa fasilitas telah kembali dibuka.
     * 3. Jika fasilitas saat ini berstatus 'aktif':
     *    - Mengubah status fasilitas menjadi 'dalam perbaikan' (diblokir dari katalog reservasi).
     *    - Menandai tiket laporan aktif yang belum selesai menjadi `is_facility_locked = true`.
     *    - Menghasilkan pesan sukses bahwa fasilitas telah diblokir ke Mode Perbaikan.
     */
    public function toggleMaintenance(int|string $id): RedirectResponse
    {
        try {
            $message = DB::transaction(function () use ($id) {
                // 1. Ambil data fasilitas dengan penguncian baris transaksi (Pessimistic Locking)
                $facility = Facility::lockForUpdate()->findOrFail($id);

                // 2. Logika Saklar (Toggle) Status Master Fasilitas Kampus
                if ($facility->status === 'dalam perbaikan') {
                    // Kasus A: Membuka kunci fasilitas kembali ke status normal
                    $facility->status = 'aktif';
                    $facility->save();

                    // Buka penguncian laporan kerusakan fasilitas ini
                    DamageReport::where('facility_id', $facility->id)
                        ->where('is_facility_locked', true)
                        ->update(['is_facility_locked' => false]);

                    return "Fasilitas {$facility->name} berhasil dibuka dan dikembalikan ke status Aktif.";
                } else {
                    // Kasus B: Mengunci fasilitas ke dalam status pemeliharaan / perbaikan
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
