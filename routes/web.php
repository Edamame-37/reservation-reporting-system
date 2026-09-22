<?php
/**
 * NAMA FILE    : web.php
 * FUNGSI       : Pendaftaran rute web aplikasi CAVA (Campus Venue Access)
 * DESKRIPSI    : Menyediakan pemetaan endpoint URL ke view mockup antarmuka Blade untuk portal publik, operasional petugas, konsol admin, dan portal pengguna.
 * CARA KERJA   : Menerima HTTP GET request dari peramban dan merender berkas Blade mockup terkait secara langsung tanpa ketergantungan kueri database.
 */

use App\Http\Controllers\ProfileController;
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

// Mockup Routes - Petugas
Route::get('/petugas/dashboard', function () { return view('petugas.dashboard'); })->name('petugas.dashboard');
Route::get('/petugas/reservation-management', function () { return view('petugas.reservation-management'); })->name('petugas.reservation-management');
Route::get('/petugas/report-management', function () { return view('petugas.report-management'); })->name('petugas.report-management');

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

// ROUTE: Menerima GET request ke '/petugas/dashboard'
// FUNGSI: Menampilkan dasbor operasional pemantauan status ruang dan antrean verifikasi
Route::get('/petugas/dashboard', function () {
    return view('petugas.dashboard');
})->name('petugas.dashboard');

// ROUTE: Menerima GET request ke '/petugas/reservation-management'
// FUNGSI: Menampilkan antarmuka persetujuan (approval) dan penolakan reservasi ruang
Route::get('/petugas/reservation-management', function () {
    return view('petugas.reservation-management');
})->name('petugas.reservation-management');

// ROUTE: Menerima GET request ke '/petugas/report-management'
// FUNGSI: Menampilkan daftar tiket keluhan kerusakan aset/fasilitas untuk tindak lanjut
Route::get('/petugas/report-management', function () {
    return view('petugas.report-management');
})->name('petugas.report-management');

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
