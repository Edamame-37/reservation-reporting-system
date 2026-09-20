<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

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

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
