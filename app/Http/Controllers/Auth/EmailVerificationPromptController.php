<?php
/**
 * ==============================================================================
 * NAMA FILE    : EmailVerificationPromptController.php
 * MODUL        : Modul 5 - Alur Autentikasi Pengguna & Registrasi Mandiri Sivitas (Laravel Breeze Core)
 * KODE FITUR   : AUTH-01 (Prompt Notifikasi Verifikasi Email Pengguna)
 * ==============================================================================
 * DESKRIPSI:
 * Single-action controller (Invokable Controller) yang menangani pengecekan status verifikasi email
 * pengguna yang sedang login.
 * Jika pengguna sudah memverifikasi alamat emailnya (`hasVerifiedEmail() == true`), controller ini
 * langsung mengalihkan pengguna ke halaman dashboard utama. Namun jika belum diverifikasi,
 * controller menampilkan halaman instruksi (prompt) `auth/verify-email.blade.php`.
 * ==============================================================================
 */

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailVerificationPromptController extends Controller
{
    /**
     * Menangani permintaan HTTP tunggal (Single-Action Invokable Method).
     *
     * @param  \Illuminate\Http\Request  $request Objek request dari pengguna yang terotentikasi
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
     *
     * CARA KERJA & SYNTAX:
     * 1. $request->user()->hasVerifiedEmail():
     *    - Memeriksa kolom `email_verified_at` pada tabel `users`.
     *    - Jika kolom tersebut tidak bernilai NULL, berarti email sudah sah diverifikasi.
     * 2. Ternary Operator:
     *    - Jika sudah terverifikasi: Alihkan via redirect()->intended(...) ke rute dashboard.
     *    - Jika belum terverifikasi: Tampilkan view auth.verify-email yang menyajikan tombol kirim ulang tautan.
     */
    public function __invoke(Request $request): RedirectResponse|View
    {
        return $request->user()->hasVerifiedEmail()
                    ? redirect()->intended(route('dashboard', absolute: false))
                    : view('auth.verify-email');
    }
}
