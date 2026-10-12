<?php
/**
 * ====================================================================================================
 * SISTEM INFORMASI RESERVASI & PELAPORAN FASILITAS KAMPUS (CAVA)
 * MODUL 13: OPERASIONAL PETUGAS SARPRAS: DASBOR MONITORING ANTREAN CERDAS (PTG-01 / US-8)
 * BERKAS: app/Http/Controllers/DashboardPetugasController.php
 * ====================================================================================================
 *
 * DESKRIPSI BERKAS:
 * Controller ini bertindak sebagai pusat komando operasional bagi staf UPT Sarana & Prasarana Kampus
 * (Role: 'petugas'). Menyediakan agregasi metrik beban kerja real-time, antrean permohonan reservasi
 * prioritas berbasis FIFO (First-In First-Out), dan daftar keluhan kerusakan fisik fasilitas kampus.
 *
 * PERAN ARSITEKTURAL DALAM SISTEM CAVA:
 * 1. Agregasi Metrik Kuantitatif Cepat (KPI Bento Cards):
 *    - Menghitung total permohonan reservasi berstatus 'pending' yang menuntut verifikasi petugas.
 *    - Menghitung tiket laporan kerusakan berstatus 'baru' atau 'diproses' yang memerlukan disposisi teknisi.
 *    - Menghitung fasilitas kampus yang sedang berada dalam 'Mode Perbaikan' (status = 'dalam perbaikan').
 * 2. Ekstraksi Antrean Prioritas FIFO (First-In, First-Out):
 *    - Mengambil 5 tiket reservasi 'pending' paling awal diajukan (berdasarkan `created_at ASC`)
 *      untuk menjamin keadilan layanan sivitas akademika tanpa diskriminasi.
 * 3. Pencegahan Masalah N+1 Query Problem:
 *    - Menerapkan Eager Loading (`with(['facility', 'user'])`) saat memuat relasi relasional
 *      sehingga seluruh data gedung, ruangan, nama pemohon, dan identitas sivitas termuat dalam
 *      kueri SQL terindeks tunggal yang sangat efisien.
 * 4. Integrasi Antarmuka Operasional:
 *    - Mengirimkan seluruh variabel teragregasi ke view `petugas.dashboard` melalui helper `compact()`.
 * ====================================================================================================
 */

namespace App\Http\Controllers;

// Import Model Eloquent yang merepresentasikan tabel di basis data MySQL
use App\Models\DamageReport; // Model tiket laporan kerusakan fisik fasilitas (tabel: damage_reports)
use App\Models\Facility;     // Model master data ruang & venue kampus (tabel: facilities)
use App\Models\Reservation;  // Model transaksi peminjaman ruangan kampus (tabel: reservations)

// Import antarmuka Response View dari Laravel Contract
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * Class DashboardPetugasController
 *
 * Controller operasional petugas sarpras untuk monitoring antrean dan metrik kampus.
 */
class DashboardPetugasController extends Controller
{
    /**
     * METHOD: index()
     * ------------------------------------------------------------------------------------------------
     * FUNGSI / KEGUNAAN:
     * Menampilkan dasbor operasional utama petugas sarpras beserta antrean verifikasi prioritas FIFO.
     *
     * PARAMETER:
     * Tidak menerima parameter request langsung.
     *
     * RETURN VALUE:
     * \Illuminate\Contracts\View\View : Mengembalikan instance View terkompilasi ('petugas.dashboard').
     *
     * CARA KERJA & ALUR LOGIKA:
     * 1. Menghitung kuantitas reservasi pending melalui kueri agregasi SQL `COUNT(*)` pada model Reservation.
     * 2. Menghitung kuantitas tiket kerusakan berstatus aktif ('baru' dan 'diproses') pada model DamageReport.
     * 3. Menghitung kuantitas fasilitas kampus yang terkunci dalam mode pemeliharaan pada model Facility.
     * 4. Mengambil 5 data antrean reservasi teratas berstatus 'pending' secara FIFO (`orderBy('created_at', 'asc')`)
     *    disertai eager loading relasi 'facility' dan 'user'.
     * 5. Mengambil 5 data laporan kerusakan teratas berstatus 'baru'/'diproses' secara FIFO (`orderBy('created_at', 'asc')`)
     *    disertai eager loading relasi 'facility' dan 'user'.
     * 6. Mengemas seluruh hasil agregasi dan koleksi data menggunakan helper `compact()` ke view `petugas.dashboard`.
     */
    public function index(): View
    {
        // --------------------------------------------------------------------------------------------
        // LANGKAH 1: PERHITUNGAN METRIK KUANTITATIF (AGREGASI DATA ANTREAN REAL-TIME)
        // --------------------------------------------------------------------------------------------

        /**
         * Syntax: Reservation::where('status', 'pending')->count()
         * - `where('status', 'pending')` : Menambahkan klausa SQL `WHERE status = 'pending'`.
         * - `count()`                    : Menghasilkan kueri SQL agregat `SELECT COUNT(*) FROM reservations WHERE status = 'pending'`.
         * Tujuan: Mengetahui beban kerja reservasi yang saat ini membutuhkan aksi verifikasi dari petugas sarpras.
         */
        $pendingReservationsCount = Reservation::where('status', 'pending')->count();

        /**
         * Syntax: DamageReport::whereIn('status', ['baru', 'diproses'])->count()
         * - `whereIn('status', [...])` : Menambahkan klausa SQL `WHERE status IN ('baru', 'diproses')`.
         * - `count()`                  : Menghasilkan kueri SQL agregat `SELECT COUNT(*) FROM damage_reports WHERE status IN ('baru', 'diproses')`.
         * Tujuan: Mengetahui jumlah tiket insiden kerusakan sarana yang belum terselesaikan tuntas oleh teknisi.
         */
        $newReportsCount = DamageReport::whereIn('status', ['baru', 'diproses'])->count();

        /**
         * Syntax: Facility::where('status', 'dalam perbaikan')->count()
         * - `where('status', 'dalam perbaikan')` : Menambahkan klausa SQL `WHERE status = 'dalam perbaikan'`.
         * - `count()`                            : Menghasilkan kueri SQL agregat `SELECT COUNT(*) FROM facilities WHERE status = 'dalam perbaikan'`.
         * Tujuan: Mengetahui berapa banyak ruangan kampus yang saat ini diblokir/dikunci dari katalog reservasi sivitas.
         */
        $lockedFacilitiesCount = Facility::where('status', 'dalam perbaikan')->count();

        // --------------------------------------------------------------------------------------------
        // LANGKAH 2: EKSTRAKSI ANTREAN PRIORITAS FIFO (FIRST-IN FIRST-OUT) & EAGER LOADING
        // --------------------------------------------------------------------------------------------

        /**
         * Syntax: Reservation::with(['facility', 'user'])->where(...)->orderBy(...)->limit(5)->get()
         * - `with(['facility', 'user'])` : Eager Loading relasi ORM untuk menarik data ruangan dan identitas pemohon.
         *                                  Mencegah N+1 query problem saat tabel Blade mengakses `$res->facility->name`
         *                                  dan `$res->user->name`.
         * - `where('status', 'pending')` : Memfilter data hanya untuk permohonan yang belum tervalidasi.
         * - `orderBy('created_at', 'asc')`: Menjamin asas keadilan antrean FIFO (permohonan tertua tampil pertama).
         * - `limit(5)`                   : Membatasi hasil hanya 5 record teratas demi performa render dasbor yang cepat.
         * - `get()`                      : Mengeksekusi kueri SQL dan mengembalikan koleksi objek Eloquent (`Illuminate\Database\Eloquent\Collection`).
         */
        $recentReservations = Reservation::with(['facility', 'user'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'asc')
            ->limit(5)
            ->get();

        /**
         * Syntax: DamageReport::with(['facility', 'user'])->whereIn(...)->orderBy(...)->limit(5)->get()
         * - `with(['facility', 'user'])` : Eager Loading relasi fasilitas dan pelapor sivitas.
         * - `whereIn('status', ['baru', 'diproses'])`: Hanya menyertakan laporan yang belum berstatus 'selesai' atau 'ditolak'.
         * - `orderBy('created_at', 'asc')`: Memprioritaskan tiket laporan kerusakan yang paling lama belum terselesaikan.
         * - `limit(5)`                   : Membatasi pratinjau 5 tiket kerusakan teratas.
         * - `get()`                      : Mengeksekusi kueri ke basis data.
         */
        $recentReports = DamageReport::with(['facility', 'user'])
            ->whereIn('status', ['baru', 'diproses'])
            ->orderBy('created_at', 'asc')
            ->limit(5)
            ->get();

        // --------------------------------------------------------------------------------------------
        // LANGKAH 3: PENGEMBALIAN VIEW DASBOR DENGAN BINDING DATA VARIABEL
        // --------------------------------------------------------------------------------------------

        /**
         * Syntax: return view('petugas.dashboard', compact(...))
         * - `view('petugas.dashboard')` : Merender template Blade yang beralamat di `resources/views/petugas/dashboard.blade.php`.
         * - `compact(...)`              : Mengonversi nama-nama variabel PHP menjadi array asosiatif pasangan key-value
         *                                 yang dapat langsung diakses di dalam file Blade view sebagai `$pendingReservationsCount`,
         *                                 `$recentReservations`, dll.
         */
        return view('petugas.dashboard', compact(
            'pendingReservationsCount',
            'newReportsCount',
            'lockedFacilitiesCount',
            'recentReservations',
            'recentReports'
        ));
    }
}
