<?php
/**
 * ==============================================================================
 * NAMA FILE    : VerifyEmailController.php
 * MODUL        : Modul 5 - Alur Autentikasi Pengguna & Registrasi Mandiri Sivitas (Laravel Breeze Core)
 * KODE FITUR   : AUTH-01 (Eksekusi Verifikasi Email Bertanda Tangan Kriptografis / Signed URL)
 * ==============================================================================
 * DESKRIPSI:
 * Single-action controller (Invokable Controller) yang mengeksekusi konfirmasi verifikasi email pengguna.
 * Menerima request yang divalidasi secara otomatis oleh `EmailVerificationRequest`.
 * `EmailVerificationRequest` menjamin bahwa parameter `{id}` cocok dengan ID pengguna saat ini
 * dan parameter `{hash}` cocok dengan hash SHA-1 dari email pengguna, serta tanda tangan URL (`signature`)
 * belum kedaluwarsa dan tidak dimanipulasi oleh pihak ketiga.
 * ==============================================================================
 */

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Menandai alamat email pengguna yang sedang login sebagai telah terverifikasi.
     *
     * @param  \Illuminate\Foundation\Auth\EmailVerificationRequest  $request Form request khusus bertanda tangan sah
     * @return \Illuminate\Http\RedirectResponse Redireksi ke dashboard dengan query parameter verified=1
     *
     * CARA KERJA & SYNTAX:
     * 1. $request->user()->hasVerifiedEmail():
     *    - Jika email pengguna sudah berstatus terverifikasi sebelumnya, langsung alihkan
     *      ke dashboard tanpa perlu menulis ulang ke basis data.
     * 2. $request->user()->markEmailAsVerified():
     *    - Mengisi kolom `email_verified_at` pada tabel `users` dengan waktu saat ini (now()).
     *    - Menyimpan perubahan ke database via Eloquent.
     * 3. event(new Verified($request->user())):
     *    - Memicu event sistem `Verified` untuk mencatat log aktivitas atau menjalankan listener lainnya.
     * 4. redirect()->intended(route('dashboard', absolute: false).'?verified=1'):
     *    - Mengarahkan pengguna kembali ke halaman utama dashboard dengan parameter pemberitahuan sukses `?verified=1`.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        // 1. Cek apakah pengguna sudah berstatus terverifikasi
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false).'?verified=1');
        }

        // 2. Tandai email sebagai terverifikasi dan pancarkan event
        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        // 3. Alihkan ke dashboard tujuan dengan parameter sukses
        return redirect()->intended(route('dashboard', absolute: false).'?verified=1');
    }
}
