<?php

/**
 * ====================================================================================================
 * NAMA FILE    : ReportController.php
 * LOKASI       : app/Http/Controllers/ReportController.php
 * FUNGSI       : Controller Manajemen Siklus Hidup Pengaduan Kerusakan Sarana Prasarana oleh Sivitas
 * MODUL CAVA   : 12. Portal Pengguna: Pelaporan Kerusakan Fasilitas & Pelacakan Tiket (USR-04 & USR-05)
 * ====================================================================================================
 * 
 * ARSITEKTUR & CARA KERJA SISTEM:
 * 1. Formulir Pelaporan Mandiri (USR-04):
 *    - `create()`: Menarik daftar fasilitas berstatus 'aktif' agar sivitas dapat memilih ruangan/gedung
 *      yang mengalami kerusakan fisik atau kendala fasilitas (AC, proyektor, kelistrikan).
 *    - `store()` : Memvalidasi payload via `StoreDamageReportRequest`, menyimpan foto bukti secara aman ke
 *      disk storage publik (`storage/app/public/reports/`), membangkitkan kode tiket unik `RPT-YYYYMMDD-XXXX`,
 *      dan mencatat entitas laporan dengan status awal `'baru'`.
 * 
 * 2. Pelacakan Tiket Real-Time (USR-05):
 *    - `history()`: Mengisolasi data hanya untuk `Auth::id()`, menghitung rekapitulasi status secara cepat
 *      lewat agregasi kueri tunggal (`selectRaw`), memuat relasi relasional dengan Eager Loading (`with(['facility', 'handler'])`),
 *      dan menyajikan data dalam paginasi 10 baris per halaman.
 * 
 * ====================================================================================================
 */

namespace App\Http\Controllers;

// Mengimpor Form Request khusus validasi formulir pengaduan kerusakan sarana
use App\Http\Requests\StoreDamageReportRequest;

// Mengimpor Model Eloquent DamageReport untuk transaksi tiket pengaduan
use App\Models\DamageReport;

// Mengimpor Model Eloquent Facility untuk membaca data master ruangan kampus
use App\Models\Facility;

// Mengimpor Model Eloquent User untuk relasi identitas sivitas pelapor
use App\Models\User;

// Mengimpor pustaka manipulasi waktu Carbon
use Carbon\Carbon;

// Mengimpor kelas respons pengalihan (redirect) HTTP
use Illuminate\Http\RedirectResponse;

// Mengimpor kelas representasi HTTP Request standar
use Illuminate\Http\Request;

// Mengimpor Facade Auth untuk otentikasi session pengguna
use Illuminate\Support\Facades\Auth;

// Mengimpor contract View untuk type-hinting return value tampilan Blade
use Illuminate\View\View;

/**
 * Class ReportController
 * 
 * Mengendalikan seluruh interaksi portal sivitas yang berhubungan dengan pelaporan malfungsi
 * sarana prasarana serta pelacakan progres tindak lanjut oleh teknisi.
 */
class ReportController extends Controller
{
    /**
     * Menampilkan antarmuka formulir pengaduan kerusakan fasilitas (USR-04).
     * 
     * CARA KERJA & LOGIKA:
     * 1. Menarik seluruh fasilitas yang berstatus 'aktif' dari basis data diurutkan alfabetis.
     * 2. Menangkap parameter query string 'facility_id' jika pengguna datang dari katalog atau kartu ruangan.
     * 3. Membaca draft formulir sementara dari sesi pengguna (jika tersedia).
     * 4. Merender view Blade `user.report-form`.
     * 
     * @param  \Illuminate\Http\Request  $request Objek permohonan HTTP saat ini
     * @return \Illuminate\View\View Tampilan formulir pengaduan kerusakan
     */
    public function create(Request $request): View
    {
        // Menarik seluruh fasilitas aktif dari database
        $facilities = Facility::where('status', 'aktif')
            ->orderBy('name')
            ->get();

        // Mengambil parameter query facility_id (misal: /user/report-form?facility_id=5) untuk pra-seleksi
        $selectedFacilityId = $request->query('facility_id');

        // Membaca draft sesi sementara jika ada
        $draft = session('report_draft', []);

        // Mengembalikan view 'user.report-form' dengan data fasilitas yang dipadatkan (compact)
        return view('user.report-form', compact('facilities', 'selectedFacilityId', 'draft'));
    }

    /**
     * Memvalidasi dan menyimpan transaksi tiket pengaduan kerusakan baru beserta berkas foto (USR-04).
     * 
     * CARA KERJA & LOGIKA:
     * 1. Mengambil data yang lolos validasi dari StoreDamageReportRequest (ukuran foto <= 2MB, deskripsi >= 10 karakter).
     * 2. Mengambil user_id pengguna aktif yang sedang terotentikasi.
     * 3. Membangkitkan kode tiket pengaduan unik format `RPT-YYYYMMDD-XXXX` dengan proteksi tabrakan data (do-while).
     * 4. Mengunggah file foto bukti ke direktori `storage/app/public/reports/`.
     * 5. Menyimpan data baru ke tabel `damage_reports` dengan status default 'baru'.
     * 6. Membersihkan draft session dan me-redirect pengguna ke halaman riwayat pelaporan.
     * 
     * @param  \App\Http\Requests\StoreDamageReportRequest  $request Payload tervalidasi form
     * @return \Illuminate\Http\RedirectResponse Redireksi ke riwayat laporan dengan pesan sukses
     */
    public function store(StoreDamageReportRequest $request): RedirectResponse
    {
        // Mengambil array data input yang telah divalidasi oleh FormRequest
        $validated = $request->validated();

        // Mengambil identitas akun pengguna yang sedang login
        $userId = Auth::id();

        // 1. Pembangkitan Kode Tiket Laporan Unik (Format: RPT-YYYYMMDD-XXXX)
        $datePrefix = Carbon::now()->format('Ymd');
        do {
            $randomSeq  = strtoupper(substr(bin2hex(random_bytes(2)), 0, 4));
            $reportCode = sprintf('RPT-%s-%s', $datePrefix, $randomSeq);
        } while (DamageReport::where('report_code', $reportCode)->exists());

        // 2. Mengunggah Berkas Foto Bukti ke Storage Disk Publik ('public' disk)
        $photoPath = null;
        if ($request->hasFile('attachment_photo')) {
            // Berkas disimpan di direktori public disk: storage/app/public/reports/
            $photoPath = $request->file('attachment_photo')->store('reports', 'public');
        }

        // 3. Menyimpan Entitas Tiket Pengaduan Baru ke Basis Data
        $report = DamageReport::create([
            'report_code'        => $reportCode,
            'user_id'            => $userId,
            'facility_id'        => $validated['facility_id'],
            'category'           => !empty($validated['category']) ? $validated['category'] : 'Lainnya',
            'description'        => $validated['description'],
            'attachment_photo'   => $photoPath,
            'status'             => 'baru',
            'is_facility_locked' => false,
        ]);

        // Membersihkan draf isian form dari session setelah pengaduan resmi tercatat
        session()->forget('report_draft');

        // Mengalihkan pengguna ke riwayat pelaporan dengan pesan notifikasi keberhasilan
        return redirect()->route('user.report-history')
            ->with('success', "Laporan kerusakan berhasil dikirim dengan kode tiket {$reportCode}. Petugas sarpras akan segera menindaklanjuti pengaduan Anda.");
    }

    /**
     * Menampilkan riwayat tiket pengaduan kerusakan fasilitas milik pengguna aktif (USR-05).
     * 
     * CARA KERJA & LOGIKA:
     * 1. Menghitung ringkasan jumlah tiket secara agregat dalam satu kueri SQL (`selectRaw`) untuk performa tinggi.
     * 2. Menyiapkan kueri dasar dengan Eager Loading relasi `facility` dan `handler` (teknisi) guna mencegah N+1 problem.
     * 3. Menerapkan filter status tab aktif ('baru', 'diproses', 'selesai', 'ditolak').
     * 4. Menerapkan pencarian teks pada kode laporan, deskripsi, kategori, atau nama fasilitas.
     * 5. Membatasi tampilan hasil dengan pagination 10 baris per halaman.
     * 
     * @param  \Illuminate\Http\Request  $request Permohonan HTTP berisi query filter/search
     * @return \Illuminate\View\View Tampilan riwayat pelacakan tiket pengaduan
     */
    public function history(Request $request): View
    {
        $userId = Auth::id();

        // 1. Menghitung akumulasi badge status menggunakan agregasi kueri tunggal SQL (Tinggi Performa)
        $rawCounts = DamageReport::where('user_id', $userId)
            ->selectRaw("
                COUNT(*) as total,
                COUNT(CASE WHEN status = 'baru' THEN 1 END) as baru,
                COUNT(CASE WHEN status = 'diproses' THEN 1 END) as diproses,
                COUNT(CASE WHEN status = 'selesai' THEN 1 END) as selesai,
                COUNT(CASE WHEN status = 'ditolak' THEN 1 END) as ditolak
            ")->first();

        // Memetakan hasil agregasi ke dalam array berformat integer
        $counts = [
            'all'      => (int) ($rawCounts->total ?? 0),
            'baru'     => (int) ($rawCounts->baru ?? 0),
            'diproses' => (int) ($rawCounts->diproses ?? 0),
            'selesai'  => (int) ($rawCounts->selesai ?? 0),
            'ditolak'  => (int) ($rawCounts->ditolak ?? 0),
        ];

        // 2. Membangun kueri riwayat tiket dengan isolasi kepemilikan ketat (Strict Ownership)
        $activeStatus = $request->query('status', 'all');
        $keyword      = $request->query('search', $request->query('keyword'));

        // Eager Loading relasi fasilitas dan staf teknisi penangan
        $query = DamageReport::with(['facility', 'handler'])
            ->where('user_id', $userId);

        // Filter Berdasarkan Tab Status yang Aktif
        if ($activeStatus && $activeStatus !== 'all' && in_array($activeStatus, ['baru', 'diproses', 'selesai', 'ditolak'])) {
            $query->where('status', $activeStatus);
        }

        // Filter Berdasarkan Kata Kunci Pencarian (Search Filter)
        if (!empty($keyword)) {
            $query->where(function ($q) use ($keyword) {
                $q->where('report_code', 'like', "%{$keyword}%")
                  ->orWhere('description', 'like', "%{$keyword}%")
                  ->orWhere('category', 'like', "%{$keyword}%")
                  ->orWhereHas('facility', function ($fq) use ($keyword) {
                      $fq->where('name', 'like', "%{$keyword}%");
                  });
            });
        }

        // 3. Mengurutkan data secara kronologis terbalik (terbaru di atas) dan mempaginasi 10 baris
        $reports = $query->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        // 4. Mengambil seluruh fasilitas aktif untuk kebutuhan modal pengajuan laporan cepat terpadu
        $facilities = Facility::where('status', 'aktif')
            ->orderBy('name')
            ->get();

        return view('user.report-history', compact('reports', 'counts', 'activeStatus', 'facilities'));
    }
}
