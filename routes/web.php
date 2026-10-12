<?php
/**
 * NAMA FILE    : web.php
 * FUNGSI       : Pendaftaran rute web aplikasi CAVA (Campus Venue Access)
 * DESKRIPSI    : Menyediakan pemetaan endpoint URL terpusat untuk portal publik, operasional petugas, konsol admin, dan portal pengguna.
 * CARA KERJA   : Menerima HTTP Request, menerapkan middleware autentikasi dan Spatie RBAC (role:admin, role:petugas), serta mengarahkan ke Controller terkait dengan integrasi basis data MySQL.
 */

use App\Http\Controllers\DashboardPetugasController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminUserManagementController;
use App\Http\Controllers\DashboardUserController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\FacilityController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportManagementController;
use App\Http\Controllers\ReservationManagementController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\PublicFacilityController;
use App\Http\Controllers\PublicInformationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Portal Publik (Terbuka untuk Sivitas & Tamu Umum - Modul 4 / PUB-01 & PUB-02)
|--------------------------------------------------------------------------
| Rute-rute di bawah ini bersifat publik dan TIDAK dilindungi oleh middleware 'auth'.
| Siapa pun, termasuk pengunjung anonim tanpa akun, dapat mengakses katalog ruang,
| memeriksa matriks ketersediaan slot waktu, dan melakukan pencarian fasilitas.
*/

/**
 * --------------------------------------------------------------------------
 * RUTE: GET / (Root URL / Landing Page Beranda)
 * --------------------------------------------------------------------------
 * Syntax: Route::get('/', [PublicFacilityController::class, 'home'])->name('home');
 * - Method HTTP : GET (pengambilan data representasi halaman).
 * - URI Path   : '/' (halaman muka aplikasi).
 * - Handler    : Controller PublicFacilityController, method 'home'.
 * - Name       : 'home' (digunakan pada fungsi helper route('home') di Blade/Controller).
 * - Cara Kerja : Menampilkan hero section, statistik ringkas fasilitas kampus,
 *                pilihan venue populer, dan direktori alur pengajuan peminjaman.
 */
Route::get('/', [PublicFacilityController::class, 'home'])->name('home');

/**
 * --------------------------------------------------------------------------
 * RUTE: GET /public/catalog (Katalog Fasilitas & Filter Multi-Dimensi - PUB-01)
 * --------------------------------------------------------------------------
 * Syntax: Route::get('/public/catalog', [PublicFacilityController::class, 'index'])->name('public.catalog');
 * - Method HTTP : GET.
 * - URI Path   : '/public/catalog'.
 * - Handler    : Controller PublicFacilityController, method 'index'.
 * - Name       : 'public.catalog'.
 * - Cara Kerja : Menerima parameter kueri filter (search, category, equipment, min_capacity),
 *                menyimpan riwayat filter terakhir ke cookie browser (1 minggu), dan
 *                mengembalikan view katalog daftar kartu fasilitas interaktif.
 */
Route::get('/public/catalog', [PublicFacilityController::class, 'index'])->name('public.catalog');

/**
 * --------------------------------------------------------------------------
 * RUTE: GET /public/availability (Kalender Matriks Ketersediaan Publik - PUB-01)
 * --------------------------------------------------------------------------
 * Syntax: Route::get('/public/availability', [PublicFacilityController::class, 'availability'])->name('public.availability');
 * - Method HTTP : GET.
 * - URI Path   : '/public/availability'.
 * - Handler    : Controller PublicFacilityController, method 'availability'.
 * - Name       : 'public.availability'.
 * - Cara Kerja : Merender tampilan antarmuka visual grid ketersediaan seluruh ruangan
 *                kampus pada tanggal tertentu (default: hari ini). Menghubungkan
 *                datepicker frontend dengan pemanggilan asynchronous ke endpoint API.
 */
Route::get('/public/availability', [PublicFacilityController::class, 'availability'])->name('public.availability');

/**
 * --------------------------------------------------------------------------
 * API ENDPOINT: GET /api/availability/{date} (JSON Matriks Slot Seluruh Fasilitas)
 * --------------------------------------------------------------------------
 * Syntax: Route::get('/api/availability/{date}', [PublicFacilityController::class, 'getMatrixAvailability'])->name('api.availability.matrix');
 * - Method HTTP : GET.
 * - URI Path   : '/api/availability/{date}' di mana parameter {date} berformat YYYY-MM-DD.
 * - Handler    : Controller PublicFacilityController, method 'getMatrixAvailability'.
 * - Name       : 'api.availability.matrix'.
 * - Cara Kerja : Menghasilkan payload JSON yang memetakan slot 30 menit (07:00 - 20:00 WIB)
 *                untuk seluruh ruangan aktif. Slot yang terisi oleh reservasi berstatus
 *                'approved' ditandai sebagai 'booked' dengan privasi identitas pemohon disembunyikan.
 */
Route::get('/api/availability/{date}', [PublicFacilityController::class, 'getMatrixAvailability'])->name('api.availability.matrix');

/**
 * --------------------------------------------------------------------------
 * API ENDPOINT: GET /api/availability/{id}/{date} (JSON Ketersediaan Tunggal)
 * --------------------------------------------------------------------------
 * Syntax: Route::get('/api/availability/{id}/{date}', [PublicFacilityController::class, 'showAvailability'])->name('api.availability.single');
 * - Method HTTP : GET.
 * - URI Path   : '/api/availability/{id}/{date}' ({id}: ID fasilitas, {date}: format YYYY-MM-DD).
 * - Handler    : Controller PublicFacilityController, method 'showAvailability'.
 * - Name       : 'api.availability.single'.
 * - Cara Kerja : Mengembalikan rincian jam mulai & selesai permohonan yang disetujui
 *                untuk 1 fasilitas tertentu guna validasi slot pada formulir pemesanan.
 */
Route::get('/api/availability/{id}/{date}', [PublicFacilityController::class, 'showAvailability'])->name('api.availability.single');

/**
 * --------------------------------------------------------------------------
 * API ENDPOINT: GET /api/facilities/search (Autocomplete & Pencarian Instan - PUB-02)
 * --------------------------------------------------------------------------
 * Syntax: Route::get('/api/facilities/search', [PublicFacilityController::class, 'searchSuggestions'])->name('api.facilities.search');
 * - Method HTTP : GET.
 * - URI Path   : '/api/facilities/search?q=keyword'.
 * - Handler    : Controller PublicFacilityController, method 'searchSuggestions'.
 * - Name       : 'api.facilities.search'.
 * - Cara Kerja : Menangani AJAX request dari search bar untuk menampilkan dropdown
 *                rekomendasi ruangan (nama, kode, gedung, kapasitas) secara instan.
 */
Route::get('/api/facilities/search', [PublicFacilityController::class, 'searchSuggestions'])->name('api.facilities.search');

/**
 * --------------------------------------------------------------------------
 * RUTE: GET /informasi/{section?} (Pusat Informasi, Kebijakan, S&K, dan Bantuan)
 * --------------------------------------------------------------------------
 * Syntax: Route::get('/informasi/{section?}', [PublicInformationController::class, 'index'])->name('public.information');
 * - Parameter opsional: {section?} dapat bernilai 'kebijakan', 'syarat-ketentuan', atau 'bantuan'.
 */
Route::get('/informasi/{section?}', [PublicInformationController::class, 'index'])->name('public.information');

/**
 * --------------------------------------------------------------------------
 * RUTE ALIAS / SHORTCUT: Redireksi Cepat ke Tab Pusat Informasi
 * --------------------------------------------------------------------------
 * Syntax: Route::get('/...', fn() => redirect()->route(...));
 * Menggunakan Arrow Function (PHP 7.4+) untuk mengembalikan respon redirect HTTP 302
 * ke rute 'public.information' dengan parameter tab yang bersangkutan.
 */
Route::get('/kebijakan', fn() => redirect()->route('public.information', ['section' => 'kebijakan']))->name('public.kebijakan');
Route::get('/syarat-ketentuan', fn() => redirect()->route('public.information', ['section' => 'syarat-ketentuan']))->name('public.syarat-ketentuan');
Route::get('/bantuan', fn() => redirect()->route('public.information', ['section' => 'bantuan']))->name('public.bantuan');

/*
|--------------------------------------------------------------------------
| Konsol Biro Sarpras (Super Admin)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin'])->group(function () {

// ROUTE: Menerima GET request ke '/admin/dashboard'
// FUNGSI: Menampilkan dasbor analitik dan metrik penggunaan fasilitas untuk Admin (ADM-04 / US-17)
Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');

// ROUTE: Menerima GET request ke '/admin/user-management' atau '/admin/users'
// FUNGSI: Menampilkan antrean verifikasi akun pending (ADM-01 / UR15), daftar sivitas terdaftar, dan direktori petugas sarpras via AdminUserManagementController
Route::get('/admin/user-management', [AdminUserManagementController::class, 'index'])->name('admin.user-management');
Route::get('/admin/users', [AdminUserManagementController::class, 'index'])->name('admin.users.index');

// ROUTE: Menerima POST atau PATCH request ke '/admin/users/{id}/verify' (atau alias '/admin/users/{id}/approve')
// FUNGSI: Mengubah status akun pendaftaran mandiri dari 'pending' menjadi 'active' (ADM-01 / UR15)
Route::match(['post', 'patch'], '/admin/users/{id}/verify', [AdminUserManagementController::class, 'verifyUser'])->name('admin.users.verify');
Route::match(['post', 'patch'], '/admin/users/{id}/approve', [AdminUserManagementController::class, 'verifyUser'])->name('admin.users.approve');

// ROUTE: Menerima POST atau PATCH request ke '/admin/users/{id}/reject' beserta payload FormRequest (reason & notes)
// FUNGSI: Menolak verifikasi pendaftaran akun dan mencatat alasan penolakan pada database (ADM-01 / UR15)
Route::match(['post', 'patch'], '/admin/users/{id}/reject', [AdminUserManagementController::class, 'rejectUser'])->name('admin.users.reject');

// ROUTE: Menerima POST request pendaftaran internal oleh Admin
Route::post('/admin/users/petugas', [AdminUserManagementController::class, 'storePetugas'])->name('admin.users.create-petugas');
Route::post('/admin/users/pengguna', [AdminUserManagementController::class, 'storePengguna'])->name('admin.users.create-user');
Route::post('/admin/users', [AdminUserManagementController::class, 'storeUser'])->name('admin.users.store');

// ROUTE: Mengubah status akun pengguna (Aktif <-> Non-aktif)
Route::post('/admin/users/{id}/toggle-status', [AdminUserManagementController::class, 'toggleStatus'])->name('admin.users.toggle-status');

// ROUTE: Soft delete akun pengguna (Pindahkan ke tab Non-aktif/Dihapus)
Route::delete('/admin/users/{id}', [AdminUserManagementController::class, 'destroy'])->name('admin.users.destroy');

// ROUTE: Memulihkan akun pengguna yang di-soft delete atau dinonaktifkan
Route::post('/admin/users/{id}/restore', [AdminUserManagementController::class, 'restore'])->name('admin.users.restore');

// ------------------------------------------------------------------------------------------
// MODUL MASTER FASILITAS & RUANGAN KAMPUS (ADM-03 / US-16)
// ------------------------------------------------------------------------------------------
// Memfasilitasi Administrator untuk mengelola seluruh siklus hidup venue kampus
// (Katalogisasi, Unggah Foto, Pengaturan Kapasitas & Alat, serta Toggle Status Operasional).

// ROUTE: Menerima GET request ke '/admin/facility-master' atau '/admin/facilities'
// FUNGSI: Menampilkan antarmuka Bento UI manajemen fasilitas kampus dengan filter pencarian & kategori
Route::get('/admin/facility-master', [App\Http\Controllers\FacilityController::class, 'index'])->name('admin.facility-master');
Route::get('/admin/facilities', [App\Http\Controllers\FacilityController::class, 'index'])->name('facilities.index');

// ROUTE: Menerima POST request ke '/admin/facilities/save'
// FUNGSI: Endpoint alias serbaguna untuk menyimpan data baru atau memperbarui data via modal pop-up
Route::post('/admin/facilities/save', [App\Http\Controllers\FacilityController::class, 'saveAlias'])->name('facilities.save');

// ROUTE: Menerima POST request ke '/admin/facilities'
// FUNGSI: Menyimpan entitas ruangan baru ke basis data dengan validasi StoreFacilityRequest & upload cover image
Route::post('/admin/facilities', [App\Http\Controllers\FacilityController::class, 'store'])->name('facilities.store');

// ROUTE: Menerima PUT request ke '/admin/facilities/{facility}'
// FUNGSI: Memperbarui spesifikasi ruangan yang sudah ada menggunakan Implicit Route Model Binding {facility}
Route::put('/admin/facilities/{facility}', [App\Http\Controllers\FacilityController::class, 'update'])->name('facilities.update');

// ROUTE: Menerima DELETE request ke '/admin/facilities/{facility}'
// FUNGSI: Melakukan penghapusan lunak (Soft Delete) pada ruangan sehingga tidak muncul di katalog tapi tetap tersimpan di arsip
Route::delete('/admin/facilities/{facility}', [App\Http\Controllers\FacilityController::class, 'destroy'])->name('facilities.destroy');

// ROUTE: Menerima POST request ke '/admin/facilities/{facility}/toggle'
// FUNGSI: Mengubah status operasional ruangan ('aktif' <-> 'dalam perbaikan' <-> 'nonaktif') secara cepat
Route::post('/admin/facilities/{facility}/toggle', [App\Http\Controllers\FacilityController::class, 'toggleStatus'])->name('admin.facilities.toggle');


// ROUTE: Menerima GET request ke '/admin/export-report'
// FUNGSI: Menampilkan antarmuka rekapitulasi okupansi dan frekuensi kerusakan aset resmi (ADM-04 / UR17)
Route::get('/admin/export-report', [ExportController::class, 'index'])->name('admin.export-report');

// ROUTE: Menerima GET request ke '/admin/export/reservations/pdf'
// FUNGSI: Mengunduh berkas laporan resmi rekapitulasi reservasi format PDF landscape A4 (ADM-04 / UR17)
Route::get('/admin/export/reservations/pdf', [ExportController::class, 'exportReservationsPdf'])->name('admin.export.reservations.pdf');

// ROUTE: Menerima GET request ke '/admin/export/reservations/excel'
// FUNGSI: Mengunduh berkas spreadsheet rekapitulasi peminjaman ruang format Excel/CSV (ADM-04 / UR17)
Route::get('/admin/export/reservations/excel', [ExportController::class, 'exportReservationsExcel'])->name('admin.export.reservations.excel');

// ROUTE: Menerima GET request ke '/admin/export/damage-reports/pdf'
// FUNGSI: Mengunduh berkas laporan resmi rekapitulasi kerusakan aset kampus format PDF landscape A4 (ADM-04 / UR17)
Route::get('/admin/export/damage-reports/pdf', [ExportController::class, 'exportDamageReportsPdf'])->name('admin.export.damage-reports.pdf');

// ROUTE: Menerima GET request ke '/admin/export/damage-reports/excel'
// FUNGSI: Mengunduh berkas spreadsheet rekapitulasi keluhan kerusakan fasilitas format Excel/CSV (ADM-04 / UR17)
Route::get('/admin/export/damage-reports/excel', [ExportController::class, 'exportDamageReportsExcel'])->name('admin.export.damage-reports.excel');

// ROUTE ALIAS: Kompatibilitas tautan mockup ekspor laporan statuter pada antarmuka admin
Route::get('/admin/reports/export-excel', [ExportController::class, 'exportReservationsExcel'])->name('admin.reports.export-excel');
Route::get('/admin/reports/export-pdf', [ExportController::class, 'exportReservationsPdf'])->name('admin.reports.export-pdf');
});

/*
|--------------------------------------------------------------------------
| Operasional Petugas Sarpras
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:petugas'])->group(function () {
    // ROUTE: Menerima GET request ke '/petugas/dashboard'
    // FUNGSI: Menampilkan dasbor operasional pemantauan status ruang dan antrean verifikasi
    Route::get('/petugas/dashboard', [DashboardPetugasController::class, 'index'])->name('petugas.dashboard');

    // ROUTE: Menerima GET request ke '/petugas/reservation-management'
    // FUNGSI: Menampilkan lembar kerja manajemen dan seluruh antrean persetujuan reservasi (PTG-02 / US-9)
    Route::get('/petugas/reservation-management', [ReservationManagementController::class, 'index'])->name('petugas.reservation-management');
    Route::get('/petugas/reservations', [ReservationManagementController::class, 'index'])->name('petugas.reservations.index');

    // ROUTE: Menerima GET request ke '/petugas/schedule-matrix'
    // FUNGSI: Menampilkan matriks jadwal slot 30m operasional untuk peninjauan detail pemohon & reservasi petugas
    Route::get('/petugas/schedule-matrix', [App\Http\Controllers\PetugasScheduleMatrixController::class, 'index'])->name('petugas.schedule-matrix');
    Route::get('/api/petugas/matrix/{date}', [App\Http\Controllers\PetugasScheduleMatrixController::class, 'getMatrixData'])->name('api.petugas.matrix');

    // ROUTE: Menerima PATCH request ke '/petugas/reservations/{id}/approve'
    // FUNGSI: Menyetujui permohonan reservasi dengan validasi anti-bentrok jadwal (PTG-02 / US-9)
    Route::patch('/petugas/reservations/{id}/approve', [ReservationManagementController::class, 'approve'])->name('petugas.reservations.approve');

    // ROUTE: Menerima PATCH request ke '/petugas/reservations/{id}/reject'
    // FUNGSI: Menolak permohonan reservasi dengan alasan penolakan resmi (PTG-02 / US-9)
    Route::patch('/petugas/reservations/{id}/reject', [ReservationManagementController::class, 'reject'])->name('petugas.reservations.reject');

    // ROUTE: Menerima DELETE request ke '/petugas/reservations/{id}/force-cancel'
    // FUNGSI: Pembatalan darurat sepihak (override privilege) oleh petugas untuk reservasi yang telah disetujui (PTG-03 / US-10)
    Route::delete('/petugas/reservations/{id}/force-cancel', [ReservationManagementController::class, 'forceCancel'])->name('petugas.reservations.force-cancel');

    // ROUTE: Menerima GET request ke '/petugas/report-management'
    // FUNGSI: Menampilkan lembar kerja manajemen tiket kerusakan fasilitas sarpras (PTG-04 / US-11)
    Route::get('/petugas/report-management', [ReportManagementController::class, 'index'])->name('petugas.report-management');
    Route::get('/petugas/reports', [ReportManagementController::class, 'index'])->name('petugas.reports.index');

    // ROUTE: Menerima PATCH request ke '/petugas/reports/{id}'
    // FUNGSI: Memperbarui status penanganan tiket keluhan kerusakan dan mencatat resolusi teknisi (PTG-04 / US-11)
    Route::patch('/petugas/reports/{id}', [ReportManagementController::class, 'updateStatus'])->name('petugas.reports.update');

    // ROUTE: Menerima PATCH request ke '/petugas/facilities/{id}/toggle-maintenance'
    // FUNGSI: Mengunci/membuka kunci fasilitas ke status pemeliharaan / Mode Perbaikan (PTG-05 / US-12)
    Route::patch('/petugas/facilities/{id}/toggle-maintenance', [ReportManagementController::class, 'toggleMaintenance'])->name('petugas.facilities.toggle-maintenance');
});

/*
|--------------------------------------------------------------------------
| Portal Pengguna (Mahasiswa & Dosen)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:pengguna'])->group(function () {
    // ROUTE: Menerima GET request ke '/user/dashboard'
    // FUNGSI: Menampilkan dasbor riwayat aktif dan pintasan reservasi untuk mahasiswa/dosen
    Route::get('/user/dashboard', [DashboardUserController::class, 'index'])->name('user.dashboard');

    // ROUTE: Menerima GET request ke '/user/reservation-form'
    // FUNGSI: Menampilkan formulir pengajuan reservasi peminjaman ruang baru dengan data fasilitas aktif
    Route::get('/user/reservation-form', [ReservationController::class, 'create'])->name('user.reservation-form');

    // ROUTE: Menerima POST request ke '/user/reservations'
    // FUNGSI: Memproses penyimpanan pengajuan reservasi baru dan validasi anti-bentrok
    Route::post('/user/reservations', [ReservationController::class, 'store'])->name('user.reservations.store');

    // ROUTE: Menerima GET request ke '/user/reservation-history'
    // FUNGSI: Menampilkan daftar riwayat pengajuan reservasi dan status verifikasi
    Route::get('/user/reservation-history', [ReservationController::class, 'history'])->name('user.reservation-history');

    // ROUTE: Menerima DELETE request ke '/user/reservations/{id}/cancel'
    // FUNGSI: Membatalkan pengajuan reservasi secara mandiri
    Route::delete('/user/reservations/{id}/cancel', [ReservationController::class, 'cancel'])->name('user.reservations.cancel');

    // ROUTE: Menerima GET request ke '/user/report-form'
    // FUNGSI: Menampilkan formulir pelaporan keluhan kerusakan fasilitas
    Route::get('/user/report-form', [ReportController::class, 'create'])->name('user.report-form');

    // ROUTE: Menerima POST request ke '/user/reports'
    // FUNGSI: Menyimpan laporan kerusakan fasilitas
    Route::post('/user/reports', [ReportController::class, 'store'])->name('user.reports.store');

    // ROUTE: Menerima GET request ke '/user/report-history'
    // FUNGSI: Menampilkan riwayat tiket pelaporan kerusakan yang diajukan oleh pengguna
    Route::get('/user/report-history', [ReportController::class, 'history'])->name('user.report-history');
});

/*
|--------------------------------------------------------------------------
| Rute Navigasi Dashboard Default & Profil Pengguna
|--------------------------------------------------------------------------
*/

// ROUTE: Menerima GET request ke '/dashboard'
// FUNGSI: Redirect dinamis berdasarkan role
Route::get('/dashboard', function () {
    $user = auth()->user();
    if ($user->hasRole('admin')) {
        return redirect()->route('admin.dashboard');
    } elseif ($user->hasRole('petugas')) {
        return redirect()->route('petugas.dashboard');
    }
    return redirect()->route('user.dashboard');
})->middleware('auth')->name('dashboard');

// Rute Profil Pengguna
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
