<?php
/**
 * ==============================================================================
 * NAMA FILE    : PasswordResetLinkController.php
 * MODUL        : Modul 5 - Alur Autentikasi Pengguna & Registrasi Mandiri Sivitas (Laravel Breeze Core)
 * KODE FITUR   : AUTH-01 (Pemulihan Akun & Pengiriman Tautan Reset Kata Sandi)
 * ==============================================================================
 * DESKRIPSI:
 * Controller yang menangani alur permohonan pemulihan kata sandi ketika pengguna lupa sandi.
 * Bertanggung jawab menampilkan formulir permohonan tautan reset sandi serta memproses
 * pembuatan token bertandatangan kriptografis via Laravel Password Broker
 * yang dikirimkan langsung ke alamat email terdaftar pengguna.
 *
 * ALUR ARSITEKTUR:
 * 1. GET /forgot-password ➔ create() ➔ View auth/forgot-password.blade.php
 * 2. POST /forgot-password ➔ store() ➔ Validasi Email ➔ Password::sendResetLink()
 *    ├── Buat token acak di tabel `password_reset_tokens`
 *    ├── Kirim email notifikasi reset sandi
 *    └── Kembalikan response redirect dengan pesan status
 * ==============================================================================
 */

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Menampilkan formulir permintaan tautan reset kata sandi.
     *
     * @return \Illuminate\View\View Tampilan resources/views/auth/forgot-password.blade.php
     *
     * CARA KERJA & SYNTAX:
     * Mengembalikan view Blade auth.forgot-password yang dibungkus oleh layout tamu (<x-guest-layout>).
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Memproses permintaan dan mengirimkan tautan reset kata sandi ke email pemohon.
     *
     * @param  \Illuminate\Http\Request  $request Objek request berisi input 'email'
     * @return \Illuminate\Http\RedirectResponse Redireksi kembali dengan pesan status atau error
     *
     * @throws \Illuminate\Validation\ValidationException Jika email tidak valid
     *
     * CARA KERJA & SYNTAX:
     * 1. $request->validate(['email' => ['required', 'email']]): Memastikan email diisi dan valid.
     * 2. Password::sendResetLink($request->only('email')):
     *    - Menghubungi Password Broker bawaan Laravel.
     *    - Mencari pengguna dengan email tersebut pada tabel `users`.
     *    - Membuat token aman dengan hash SHA-256 yang disimpan di tabel `password_reset_tokens`.
     *    - Memicu pengiriman notifikasi email `Illuminate\Auth\Notifications\ResetPassword`.
     * 3. $status == Password::RESET_LINK_SENT:
     *    - Jika sukses, kembalikan ke halaman sebelumnya dengan flash message 'status' (bahasa lokal: passphrases.sent).
     *    - Jika gagal (misal email tidak ditemukan), kembalikan dengan pesan error pada field email.
     */
    public function store(Request $request): RedirectResponse
    {
        // 1. Validasi format alamat email
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // 2. Kirim tautan reset sandi via Password Broker
        $status = Password::sendResetLink(
            $request->only('email')
        );

        // 3. Evaluasi status pengiriman
        return $status == Password::RESET_LINK_SENT
                    ? back()->with('status', __($status))
                    : back()->withInput($request->only('email'))
                            ->withErrors(['email' => __($status)]);
    }
}
