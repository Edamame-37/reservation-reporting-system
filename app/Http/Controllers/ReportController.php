<?php
/**
 * NAMA FILE    : ReportController.php
 * FUNGSI       : Controller Pengaduan & Pelaporan Kerusakan Fasilitas Kampus
 * DESKRIPSI    : Menyajikan formulir pelaporan kerusakan (USR-04) dengan data fasilitas dinamis dan memproses penyimpanan tiket pengaduan beserta unggahan foto bukti (maks 2MB) ke storage disk publik.
 * CARA KERJA   : Menerima request HTTP dari sivitas, memvalidasi payload via StoreDamageReportRequest, mengunggah foto ke storage, dan mencatat data ke tabel damage_reports dengan status awal 'baru'.
 */

namespace App\Http\Controllers;

use App\Http\Requests\StoreDamageReportRequest;
use App\Models\DamageReport;
use App\Models\Facility;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ReportController extends Controller
{
    /**
     * FUNCTION/PROCEDURE : create()
     * FITUR              : USR-04 - Formulir Pelaporan Kerusakan Fasilitas
     * KEGUNAAN           : Menampilkan antarmuka formulir pengaduan kerusakan dengan daftar fasilitas aktif.
     * CARA KERJA         : Menarik seluruh fasilitas berstatus 'aktif' dari database dan menangkap parameter facility_id dari URL (jika ada).
     */
    public function create(Request $request): View
    {
        $facilities = Facility::where('status', 'aktif')
            ->orderBy('name')
            ->get();

        $selectedFacilityId = $request->query('facility_id');

        return view('user.report-form', compact('facilities', 'selectedFacilityId'));
    }

    /**
     * FUNCTION/PROCEDURE : store()
     * FITUR              : USR-04 - Pemrosesan Laporan Kerusakan Fasilitas
     * KEGUNAAN           : Memvalidasi data keluhan, mengunggah foto bukti ke storage publik, dan menyimpan tiket pengaduan baru.
     * CARA KERJA         :
     *   1. Menerima data tervalidasi dari StoreDamageReportRequest.
     *   2. Menghasilkan kode tiket unik otomatis (RPT-YYYYMMDD-XXXX).
     *   3. Mengunggah file foto bukti ke direktori 'reports' di disk storage publik.
     *   4. Menyimpan record pengaduan ke tabel damage_reports dengan status default 'baru'.
     *   5. Mengalihkan pengguna ke riwayat laporan dengan session flash success.
     */
    public function store(StoreDamageReportRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $userId = Auth::id();
        if (!$userId) {
            $defaultUser = User::where('email', 'dimas@mahasiswa.ac.id')->first() ?? User::first();
            $userId = $defaultUser ? $defaultUser->id : 1;
        }

        // 1. Generate Kode Tiket Laporan Unik (BR-USR04-03)
        $datePrefix = Carbon::now()->format('Ymd');
        do {
            $randomSeq  = strtoupper(substr(bin2hex(random_bytes(2)), 0, 4));
            $reportCode = sprintf('RPT-%s-%s', $datePrefix, $randomSeq);
        } while (DamageReport::where('report_code', $reportCode)->exists());

        // 2. Unggah Foto Bukti ke Storage Public Disk (BR-USR04-01)
        $photoPath = null;
        if ($request->hasFile('attachment_photo')) {
            $photoPath = $request->file('attachment_photo')->store('reports', 'public');
        }

        // 3. Simpan Data Tiket Pengaduan ke Database (BR-USR04-04 & BR-USR04-05)
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

        return redirect()->route('user.report-history')
            ->with('success', "Laporan kerusakan berhasil dikirim dengan kode tiket {$reportCode}. Petugas sarpras akan segera menindaklanjuti pengaduan Anda.");
    }

    /**
     * FUNCTION/PROCEDURE : history()
     * FITUR              : USR-05 - Pelacakan Status & Riwayat Laporan Pengguna
     * KEGUNAAN           : Menampilkan daftar riwayat tiket kerusakan sarpras milik pengguna aktif dengan filter status dan pencarian dinamis.
     * CARA KERJA         :
     *   1. Mengidentifikasi ID pengguna aktif (dengan fallback ke pengguna default untuk pengujian/mockup).
     *   2. Menghitung rekapitulasi status tiket (all, baru, diproses, selesai, ditolak) secara agregat.
     *   3. Memfilter tiket berdasarkan status terpilih dan kata kunci pencarian (kode tiket, fasilitas, kategori, deskripsi).
     *   4. Melakukan eager loading pada relasi 'facility' dan 'handler' untuk mengeliminasi problem kueri N+1.
     *   5. Menyajikan data terpaginasi (10 baris per halaman) dengan mempertahankan query string URL.
     */
    public function history(Request $request): View
    {
        $userId = Auth::id();
        if (!$userId) {
            $defaultUser = User::where('email', 'dimas@mahasiswa.ac.id')->first() ?? User::first();
            $userId = $defaultUser ? $defaultUser->id : 1;
        }

        // 1. Hitung badge akumulasi status dengan agregasi tunggal (BR-USR05-02)
        $rawCounts = DamageReport::where('user_id', $userId)
            ->selectRaw("
                COUNT(*) as total,
                COUNT(CASE WHEN status = 'baru' THEN 1 END) as baru,
                COUNT(CASE WHEN status = 'diproses' THEN 1 END) as diproses,
                COUNT(CASE WHEN status = 'selesai' THEN 1 END) as selesai,
                COUNT(CASE WHEN status = 'ditolak' THEN 1 END) as ditolak
            ")->first();

        $counts = [
            'all'      => (int) ($rawCounts->total ?? 0),
            'baru'     => (int) ($rawCounts->baru ?? 0),
            'diproses' => (int) ($rawCounts->diproses ?? 0),
            'selesai'  => (int) ($rawCounts->selesai ?? 0),
            'ditolak'  => (int) ($rawCounts->ditolak ?? 0),
        ];

        // 2. Kueri data riwayat tiket laporan dengan isolasi user_id (BR-USR05-01)
        $activeStatus = $request->query('status', 'all');
        $keyword = $request->query('search', $request->query('keyword'));

        $query = DamageReport::with(['facility', 'handler'])
            ->where('user_id', $userId);

        // Filter Status Tab (BR-USR05-02)
        if ($activeStatus && $activeStatus !== 'all' && in_array($activeStatus, ['baru', 'diproses', 'selesai', 'ditolak'])) {
            $query->where('status', $activeStatus);
        }

        // Filter Pencarian Kata Kunci (BR-USR05-04)
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

        // 3. Urutkan dari yang paling baru dan paginasi 10 baris (BR-USR05-03)
        $reports = $query->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('user.report-history', compact('reports', 'counts', 'activeStatus'));
    }
}

