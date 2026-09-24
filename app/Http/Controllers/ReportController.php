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
            'category'           => $validated['category'],
            'description'        => $validated['description'],
            'attachment_photo'   => $photoPath,
            'status'             => 'baru',
            'is_facility_locked' => false,
        ]);

        return redirect()->route('user.report-history')
            ->with('success', "Laporan kerusakan berhasil dikirim dengan kode tiket {$reportCode}. Petugas sarpras akan segera menindaklanjuti pengaduan Anda.");
    }
}
