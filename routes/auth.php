<?php
/**
 * ==============================================================================
 * NAMA FILE    : auth.php
 * MODUL        : Modul 5 - Alur Autentikasi Pengguna & Registrasi Mandiri Sivitas (Laravel Breeze Core)
 * KODE FITUR   : AUTH-01 (Autentikasi Sesi & Login) & AUTH-02 (Registrasi Mandiri Sivitas)
 * ==============================================================================
 * DESKRIPSI:
 * Berkas pendaftaran seluruh rute (routing table) yang mengatur siklus hidup otentikasi
 * pengguna sistem CAVA. Rute dibagi menjadi dua gerbang middleware utama:
 * 1. Grup Middleware 'guest':
 *    Hanya dapat diakses oleh pengunjung yang belum login. Jika pengguna yang sudah login
 *    mencoba mengakses rute ini, middleware 'guest' otomatis mengarahkannya ke dashboard masing-masing.
 *    Meliputi: Registrasi Mandiri, Login Kredensial, Lupa Kata Sandi, dan Reset Sandi Bertoken.
 * 2. Grup Middleware 'auth':
 *    Hanya dapat diakses oleh pengguna yang telah memiliki sesi aktif sah.
 *    Meliputi: Verifikasi Email bertandatangan kriptografis (signed URL), Konfirmasi Sandi Sensitif,
 *    Pembaruan Sandi Mandiri, dan Pengakhiran Sesi (Logout).
 * ==============================================================================
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

/*
|--------------------------------------------------------------------------
| GERBANG 1: RUTE KHUSUS TAMU / BELUM LOGIN (Middleware: 'guest')
|--------------------------------------------------------------------------
| Rute-rute ini dilindungi middleware RedirectIfAuthenticated.
| Mencegah duplikasi login bagi pengguna yang sudah terautentikasi.
*/
Route::middleware('guest')->group(function () {

    /**
     * --------------------------------------------------------------------------
     * RUTE: GET /register (Form Registrasi Mandiri Sivitas - AUTH-02)
     * --------------------------------------------------------------------------
     * Syntax: Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
     * - Method HTTP : GET.
     * - Handler    : RegisteredUserController@create.
     * - Name       : 'register' (dipanggil via route('register') di tombol daftar).
     * - Cara Kerja : Merender antarmuka pendaftaran sivitas (auth/register.blade.php)
     *                dengan field nama, NIM/NIP, email institusi, password, dan unggah berkas KTM.
     */
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');

    /**
     * --------------------------------------------------------------------------
     * RUTE: POST /register (Pemrosesan Pendaftaran Mandiri Sivitas - AUTH-02)
     * --------------------------------------------------------------------------
     * Syntax: Route::post('register', [RegisteredUserController::class, 'store']);
     * - Method HTTP : POST (enctype multipart/form-data).
     * - Handler    : RegisteredUserController@store.
     * - Cara Kerja : Memvalidasi data pendaftaran, mengunggah bukti kartu identitas ke disk publik,
     *                menyimpan akun baru ke tabel `users` dengan status 'pending', menugaskan role 'pengguna',
     *                dan mengarahkan pengguna ke halaman info antrean verifikasi tanpa auto-login.
     */
    Route::post('register', [RegisteredUserController::class, 'store']);

    /**
     * --------------------------------------------------------------------------
     * RUTE: GET /login (Halaman Muka Masuk Sistem CAVA - AUTH-01)
     * --------------------------------------------------------------------------
     * Syntax: Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
     * - Method HTTP : GET.
     * - Handler    : AuthenticatedSessionController@create.
     * - Name       : 'login'.
     * - Cara Kerja : Menampilkan formulir login (auth/login.blade.php) beserta kartu jalan pintas
     *                akun demo (Super Admin, Petugas Sarpras, Pengguna Sivitas).
     */
    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    /**
     * --------------------------------------------------------------------------
     * RUTE: POST /login (Verifikasi Kredensial & Pembuatan Sesi - AUTH-01)
     * --------------------------------------------------------------------------
     * Syntax: Route::post('login', [AuthenticatedSessionController::class, 'store']);
     * - Method HTTP : POST.
     * - Handler    : AuthenticatedSessionController@store.
     * - Cara Kerja : Menerima request yang divalidasi oleh LoginRequest, memverifikasi hash kata sandi,
     *                memastikan status akun bernilai 'active', meregenerasi ID sesi untuk mencegah
     *                serangan Session Fixation, dan mengarahkan pengguna ke dashboard perannya.
     */
    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    /**
     * --------------------------------------------------------------------------
     * RUTE: GET /forgot-password (Form Permintaan Tautan Reset Sandi)
     * --------------------------------------------------------------------------
     * Syntax: Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
     * - Handler    : PasswordResetLinkController@create.
     * - Name       : 'password.request'.
     * - Cara Kerja : Menampilkan form input email untuk pemulihan kata sandi lupa.
     */
    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    /**
     * --------------------------------------------------------------------------
     * RUTE: POST /forgot-password (Penerbitan Token & Pengiriman Email Reset)
     * --------------------------------------------------------------------------
     * Syntax: Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
     * - Handler    : PasswordResetLinkController@store.
     * - Name       : 'password.email'.
     * - Cara Kerja : Mengenerate token acak di tabel `password_reset_tokens` dan mengirimkan
     *                email berisi tautan aman ke alamat surat elektronik pemohon.
     */
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    /**
     * --------------------------------------------------------------------------
     * RUTE: GET /reset-password/{token} (Formulir Penggantian Sandi Baru)
     * --------------------------------------------------------------------------
     * Syntax: Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
     * - Handler    : NewPasswordController@create.
     * - Name       : 'password.reset'.
     * - Cara Kerja : Menangkap token reset dari URL dan menampilkan form input sandi baru.
     */
    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    /**
     * --------------------------------------------------------------------------
     * RUTE: POST /reset-password (Eksekusi Pembaruan Hash Kata Sandi Baru)
     * --------------------------------------------------------------------------
     * Syntax: Route::post('reset-password', [NewPasswordController::class, 'store'])->name('password.store');
     * - Handler    : NewPasswordController@store.
     * - Name       : 'password.store'.
     * - Cara Kerja : Memverifikasi keabsahan token, meng-hash sandi baru dengan Bcrypt,
     *                memperbarui tabel `users`, dan menghapus token reset yang telah terpakai.
     */
    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');
});

/*
|--------------------------------------------------------------------------
| GERBANG 2: RUTE PENGGUNA TERAUTENTIKASI (Middleware: 'auth')
|--------------------------------------------------------------------------
| Seluruh endpoint di bawah ini mewajibkan pengguna telah memiliki sesi login aktif.
*/
Route::middleware('auth')->group(function () {

    /**
     * --------------------------------------------------------------------------
     * RUTE: GET /verify-email (Prompt Peringatan Verifikasi Email)
     * --------------------------------------------------------------------------
     * Syntax: Route::get('verify-email', EmailVerificationPromptController::class)->name('verification.notice');
     * - Handler    : Single-Action Invokable Controller EmailVerificationPromptController.
     * - Name       : 'verification.notice'.
     * - Cara Kerja : Menampilkan instruksi verifikasi email jika akun belum diverifikasi.
     */
    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    /**
     * --------------------------------------------------------------------------
     * RUTE: GET /verify-email/{id}/{hash} (Eksekusi Tautan Bertanda Tangan Kriptografis)
     * --------------------------------------------------------------------------
     * Syntax: Route::get('verify-email/{id}/{hash}', ...)->middleware(['signed', 'throttle:6,1']);
     * - Middleware : 'signed' (memastikan signature URL tidak dimanipulasi) dan
     *                'throttle:6,1' (maksimal 6 permintaan per menit).
     * - Name       : 'verification.verify'.
     * - Cara Kerja : Memvalidasi hash email, menandai `email_verified_at` dengan timestamp saat ini,
     *                dan memicu event Verified.
     */
    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    /**
     * --------------------------------------------------------------------------
     * RUTE: POST /email/verification-notification (Kirim Ulang Email Verifikasi)
     * --------------------------------------------------------------------------
     * Syntax: Route::post('email/verification-notification', ...)->middleware('throttle:6,1');
     * - Name       : 'verification.send'.
     * - Cara Kerja : Mengirimkan kembali tautan verifikasi ke email pengguna jika tautan sebelumnya hilang.
     */
    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    /**
     * --------------------------------------------------------------------------
     * RUTE: GET /confirm-password (Tampilan Konfirmasi Sandi Sebelum Aksi Sensitif)
     * --------------------------------------------------------------------------
     * Syntax: Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])->name('password.confirm');
     * - Handler    : ConfirmablePasswordController@show.
     * - Name       : 'password.confirm'.
     * - Cara Kerja : Menampilkan dialog modal / halaman konfirmasi sandi demi keamanan.
     */
    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    /**
     * --------------------------------------------------------------------------
     * RUTE: POST /confirm-password (Validasi Password Konfirmasi)
     * --------------------------------------------------------------------------
     * Syntax: Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);
     * - Handler    : ConfirmablePasswordController@store.
     * - Cara Kerja : Memeriksa kecocokan sandi dan mencatat timestamp konfirmasi ke sesi.
     */
    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    /**
     * --------------------------------------------------------------------------
     * RUTE: PUT /password (Pembaruan Sandi Mandiri oleh Pengguna)
     * --------------------------------------------------------------------------
     * Syntax: Route::put('password', [PasswordController::class, 'update'])->name('password.update');
     * - Handler    : PasswordController@update.
     * - Name       : 'password.update'.
     * - Cara Kerja : Memvalidasi sandi saat ini (`current_password`) dan meng-update ke sandi baru.
     */
    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    /**
     * --------------------------------------------------------------------------
     * RUTE: POST /logout (Pengakhiran Sesi Pengguna - AUTH-01)
     * --------------------------------------------------------------------------
     * Syntax: Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
     * - Method HTTP : POST (Mencegah serangan CSRF logout via tag <img> atau prefetching link GET).
     * - Handler    : AuthenticatedSessionController@destroy.
     * - Name       : 'logout'.
     * - Cara Kerja : Mengakhiri autentikasi via Auth::guard('web')->logout(), menghapus data sesi
     *                dengan $request->session()->invalidate(), meregenerasi CSRF token via
     *                $request->session()->regenerateToken(), dan mengarahkan pengguna kembali ke halaman utama.
     */
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});
