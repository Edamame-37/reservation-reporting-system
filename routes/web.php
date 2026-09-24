<?php
/**
 * NAMA FILE    : web.php
 * FUNGSI       : Pendaftaran rute web aplikasi CAVA (Campus Venue Access)
 * DESKRIPSI    : Menyediakan pemetaan endpoint URL ke view mockup antarmuka Blade untuk portal publik, operasional petugas, konsol admin, dan portal pengguna.
 * CARA KERJA   : Menerima HTTP GET request dari peramban dan merender berkas Blade mockup terkait secara langsung tanpa ketergantungan kueri database.
 */

use App\Http\Controllers\DashboardPetugasController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminUserManagementController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\FacilityController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportManagementController;
use App\Http\Controllers\ReservationManagementController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReservationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Portal Publik (Terbuka untuk Sivitas & Umum)
|--------------------------------------------------------------------------
*/

// ROUTE: Menerima GET request ke root domain ('/')
// FUNGSI: Menampilkan halaman beranda (landing page) publik CAVA
Route::get('/', function () {
    return view('public.home');
})->name('home');


// Mockup Routes - Admin
Route::get('/admin/dashboard', function () { return view('admin.dashboard'); })->name('admin.dashboard');
Route::get('/admin/export-report', function () { return view('admin.export-report'); })->name('admin.export-report');

// ROUTE: Menerima GET request ke '/public/catalog'
// FUNGSI: Menampilkan katalog daftar fasilitas dan ruang kampus beserta filter
Route::get('/public/catalog', function () {
    return view('public.catalog');
})->name('public.catalog');

// ROUTE: Menerima GET request ke '/public/availability'
// FUNGSI: Menampilkan matriks jadwal slot ketersediaan ruang 30 menit
Route::get('/public/availability', function () {
    return view('public.availability');
})->name('public.availability');

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

// Master Fasilitas Routes
Route::get('/admin/facility-master', [App\Http\Controllers\FacilityController::class, 'index'])->name('admin.facility-master');
Route::get('/admin/facilities', [App\Http\Controllers\FacilityController::class, 'index'])->name('facilities.index');
Route::post('/admin/facilities', [App\Http\Controllers\FacilityController::class, 'store'])->name('facilities.store');
Route::put('/admin/facilities/{facility}', [App\Http\Controllers\FacilityController::class, 'update'])->name('facilities.update');
Route::delete('/admin/facilities/{facility}', [App\Http\Controllers\FacilityController::class, 'destroy'])->name('facilities.destroy');
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
    Route::get('/petugas/dashboard', function () {
        return view('petugas.dashboard');
    })->name('petugas.dashboard');

    // ROUTE: Menerima GET request ke '/petugas/reservation-management'
    // FUNGSI: Menampilkan lembar kerja manajemen dan seluruh antrean persetujuan reservasi (PTG-02 / US-9)
    Route::get('/petugas/reservation-management', [ReservationManagementController::class, 'index'])->name('petugas.reservation-management');
    Route::get('/petugas/reservations', [ReservationManagementController::class, 'index'])->name('petugas.reservations.index');

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
});

/*
|--------------------------------------------------------------------------
| Portal Pengguna (Mahasiswa & Dosen)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:pengguna'])->group(function () {
    // ROUTE: Menerima GET request ke '/user/dashboard'
    // FUNGSI: Menampilkan dasbor riwayat aktif dan pintasan reservasi untuk mahasiswa/dosen
    Route::get('/user/dashboard', function () {
        return view('user.dashboard');
    })->name('user.dashboard');

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
| Rute Navigasi Dashboard Default & Profil Pengguna (Mockup Mode)
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
