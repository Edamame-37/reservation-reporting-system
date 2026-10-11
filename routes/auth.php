<?php
/**
 * NAMA FILE    : auth.php
 * FUNGSI       : Pendaftaran rute alur autentikasi dan otorisasi sesi pengguna CAVA
 * DESKRIPSI    : Mendefinisikan endpoint registrasi sivitas, login SSO, lupa kata sandi, reset kata sandi, verifikasi email, konfirmasi sandi, dan logout.
 * CARA KERJA   : Membagi rute menjadi grup 'guest' (pengunjung belum login) dan 'auth' (pengguna terotentikasi), mengarahkan request ke Controller autentikasi terkait.
 */

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    // ROUTE: Tampilan form registrasi sivitas
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');

    // ROUTE: Pengiriman form pendaftaran mandiri sivitas (POST multipart/form-data)
    Route::post('register', [RegisteredUserController::class, 'store']);

    // ROUTE: Tampilan halaman login CAVA
    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    // ROUTE: Pemrosesan kredensial login pengguna
    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    // ROUTE: Tampilan form permohonan tautan lupa kata sandi
    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    // ROUTE: Pengiriman email tautan reset kata sandi
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    // ROUTE: Tampilan form reset kata sandi baru via token
    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    // ROUTE: Pembaruan kata sandi baru ke database
    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');
});

Route::middleware('auth')->group(function () {
    // ROUTE: Tampilan pemberitahuan verifikasi email
    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    // ROUTE: Konfirmasi tautan verifikasi email bertandatangan (signed URL)
    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    // ROUTE: Pengiriman ulang notifikasi verifikasi email
    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    // ROUTE: Tampilan form konfirmasi kata sandi sebelum aksi sensitif
    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    // ROUTE: Validasi kata sandi sebelum aksi sensitif
    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    // ROUTE: Pembaruan kata sandi dari halaman profil
    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    // ROUTE: Pengakhiran sesi pengguna (logout)
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});
