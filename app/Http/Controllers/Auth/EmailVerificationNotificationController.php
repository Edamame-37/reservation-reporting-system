<?php
/**
 * ==============================================================================
 * NAMA FILE    : EmailVerificationNotificationController.php
 * MODUL        : Modul 5 - Alur Autentikasi Pengguna & Registrasi Mandiri Sivitas (Laravel Breeze Core)
 * KODE FITUR   : AUTH-01 (Pengiriman Ulang Tautan Verifikasi Email)
 * ==============================================================================
 * DESKRIPSI:
 * Controller yang menangani permintaan pengiriman ulang (resend) notifikasi verifikasi email.
 * Digunakan saat tautan verifikasi sebelumnya tidak sampai ke kotak masuk pemohon,
 * kedaluwarsa, atau terhapus.
 * Dilindungi oleh middleware 'throttle:6,1' di routes/auth.php untuk mencegah spam email.
 * ==============================================================================
 */

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Mengirimkan ulang notifikasi email verifikasi ke alamat email pengguna yang sedang login.
     *
     * @param  \Illuminate\Http\Request  $request Objek request dari pengguna terautentikasi
     * @return \Illuminate\Http\RedirectResponse Redireksi ke dashboard atau kembali dengan status 'verification-link-sent'
     *
     * CARA KERJA & SYNTAX:
     * 1. $request->user()->hasVerifiedEmail():
     *    - Jika pengguna rupanya telah terverifikasi, cegah pengiriman email sia-sia
     *      dan alihkan langsung ke dashboard.
     * 2. $request->user()->sendEmailVerificationNotification():
     *    - Menghasilkan URL bertanda tangan baru (`signed URL`) dan memicu mailable notifikasi Laravel.
     * 3. back()->with('status', 'verification-link-sent'):
     *    - Mengembalikan pengguna ke halaman prompt verifikasi disertai flash status
     *      yang menampilkan pesan hijau "Tautan verifikasi baru telah dikirim".
     */
    public function store(Request $request): RedirectResponse
    {
        // 1. Cek apakah email sudah terverifikasi sebelumnya
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        // 2. Kirim ulang notifikasi verifikasi email bertanda tangan
        $request->user()->sendEmailVerificationNotification();

        // 3. Kembalikan ke halaman prompt dengan pesan status sukses
        return back()->with('status', 'verification-link-sent');
    }
}
