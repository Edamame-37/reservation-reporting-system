<?php
/**
 * NAMA FILE    : web.php
 * FUNGSI       : Pendaftaran rute web aplikasi CAVA (Campus Venue Access)
 * DESKRIPSI    : Menyediakan pemetaan endpoint URL ke view mockup antarmuka Blade untuk portal publik, operasional petugas, konsol admin, dan portal pengguna.
 * CARA KERJA   : Menerima HTTP GET request dari peramban dan merender berkas Blade mockup terkait secara langsung tanpa ketergantungan kueri database.
 */

use App\Http\Controllers\DashboardPetugasController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportManagementController;
use App\Http\Controllers\ReservationManagementController;
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

// Mockup Routes - Public
Route::get('/public/catalog', function () { return view('public.catalog'); })->name('public.catalog');
Route::get('/public/availability', function () { return view('public.availability'); })->name('public.availability');

// Mockup Routes - User
Route::get('/user/dashboard', function () { return view('user.dashboard'); })->name('user.dashboard');
Route::get('/user/reservation-form', function () { return view('user.reservation-form'); })->name('user.reservation-form');
Route::get('/user/reservation-history', function () { return view('user.reservation-history'); })->name('user.reservation-history');
Route::get('/user/report-form', function () { return view('user.report-form'); })->name('user.report-form');
Route::get('/user/report-history', function () { return view('user.report-history'); })->name('user.report-history');

// Mockup Routes - Admin
Route::get('/admin/dashboard', function () { return view('admin.dashboard'); })->name('admin.dashboard');
Route::get('/admin/facility-master', function () { return view('admin.facility-master'); })->name('admin.facility-master');
Route::get('/admin/user-management', function () { return view('admin.user-management'); })->name('admin.user-management');
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

// ROUTE: Menerima GET request ke '/admin/dashboard'
// FUNGSI: Menampilkan dasbor analitik dan metrik penggunaan fasilitas untuk Admin
Route::get('/admin/dashboard', function () {
    return view('admin.dashboard');
})->name('admin.dashboard');

// ROUTE: Menerima GET request ke '/admin/user-management'
// FUNGSI: Menampilkan halaman manajemen verifikasi pengguna dan hak akses (UR15)
Route::get('/admin/user-management', function () {
    return view('admin.user-management');
})->name('admin.user-management');

// ROUTE: Menerima GET request ke '/admin/facility-master'
// FUNGSI: Menampilkan halaman pengelolaan master data fasilitas kampus
Route::get('/admin/facility-master', function () {
    return view('admin.facility-master');
})->name('admin.facility-master');

// ROUTE: Menerima GET request ke '/admin/export-report'
// FUNGSI: Menampilkan antarmuka ekspor laporan resmi sarpras (PDF/Excel)
Route::get('/admin/export-report', function () {
    return view('admin.export-report');
})->name('admin.export-report');

/*
|--------------------------------------------------------------------------
| Operasional Petugas Sarpras
|--------------------------------------------------------------------------
*/

// ROUTE: Rute operasional petugas sarpras dengan proteksi middleware auth dan role:petugas
Route::middleware(['auth', 'role:petugas'])->group(function () {
    // ROUTE: Menerima GET request ke '/petugas/dashboard'
    // FUNGSI: Menampilkan dasbor operasional pemantauan status ruang dan antrean verifikasi sarpras (PTG-01 / US-8)
    Route::get('/petugas/dashboard', [DashboardPetugasController::class, 'index'])->name('petugas.dashboard');

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

    // ROUTE: Menerima PATCH request ke '/petugas/facilities/{id}/toggle-maintenance'
    // FUNGSI: Mengunci/membuka kunci fasilitas ke status pemeliharaan / Mode Perbaikan (PTG-05 / US-12)
    Route::patch('/petugas/facilities/{id}/toggle-maintenance', [ReportManagementController::class, 'toggleMaintenance'])->name('petugas.facilities.toggle-maintenance');
});

/*
|--------------------------------------------------------------------------
| Portal Pengguna (Mahasiswa & Dosen)
|--------------------------------------------------------------------------
*/

// ROUTE: Menerima GET request ke '/user/dashboard'
// FUNGSI: Menampilkan dasbor riwayat aktif dan pintasan reservasi untuk mahasiswa/dosen
Route::get('/user/dashboard', function () {
    return view('user.dashboard');
})->name('user.dashboard');

// ROUTE: Menerima GET request ke '/user/reservation-form'
// FUNGSI: Menampilkan formulir pengajuan reservasi peminjaman ruang baru
Route::get('/user/reservation-form', function () {
    return view('user.reservation-form');
})->name('user.reservation-form');

// ROUTE: Menerima GET request ke '/user/reservation-history'
// FUNGSI: Menampilkan daftar riwayat pengajuan reservasi dan status verifikasi
Route::get('/user/reservation-history', function () {
    return view('user.reservation-history');
})->name('user.reservation-history');

// ROUTE: Menerima GET request ke '/user/report-form'
// FUNGSI: Menampilkan formulir pelaporan keluhan kerusakan fasilitas
Route::get('/user/report-form', function () {
    return view('user.report-form');
})->name('user.report-form');

// ROUTE: Menerima GET request ke '/user/report-history'
// FUNGSI: Menampilkan riwayat tiket pelaporan kerusakan yang diajukan oleh pengguna
Route::get('/user/report-history', function () {
    return view('user.report-history');
})->name('user.report-history');

/*
|--------------------------------------------------------------------------
| Rute Navigasi Dashboard Default & Profil Pengguna (Mockup Mode)
|--------------------------------------------------------------------------
*/

// ROUTE: Menerima GET request ke '/dashboard'
// FUNGSI: Mengarahkan pengguna langsung ke dasbor mockup tanpa hambatan middleware auth database
Route::get('/dashboard', function () {
    return redirect()->route('user.dashboard');
})->name('dashboard');

// Rute Profil Pengguna (Disediakan opsi mock tanpa kueri database aktif)
Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

require __DIR__.'/auth.php';
