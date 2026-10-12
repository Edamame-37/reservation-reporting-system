<?php
/**
 * ==============================================================================
 * NAMA FILE    : PasswordController.php
 * MODUL        : Modul 5 - Alur Autentikasi Pengguna & Registrasi Mandiri Sivitas (Laravel Breeze Core)
 * KODE FITUR   : AUTH-01 (Pembaruan Mandiri Kata Sandi oleh Pengguna dari Profil)
 * ==============================================================================
 * DESKRIPSI:
 * Controller yang menangani fitur penggantian kata sandi secara mandiri oleh pengguna
 * yang sedang aktif dari halaman pengaturan profil (`profile.edit`).
 * Menerapkan named error bag ('updatePassword') agar pesan kesalahan validasi sandi
 * tidak bercampur dengan form pembaruan informasi profil umum.
 * ==============================================================================
 */

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    /**
     * Memperbarui kata sandi akun pengguna yang sedang login.
     *
     * @param  \Illuminate\Http\Request  $request Objek request berisi 'current_password', 'password', dan 'password_confirmation'
     * @return \Illuminate\Http\RedirectResponse Redireksi kembali dengan flash status 'password-updated'
     *
     * CARA KERJA & SYNTAX:
     * 1. $request->validateWithBag('updatePassword', [...]):
     *    - Menggunakan error bag bernama `updatePassword` ($errors->updatePassword)
     *      agar error field ditampilkan secara tepat pada kartu form ubah password di Blade.
     *    - 'current_password' => ['required', 'current_password']:
     *      Aturan bawaan Laravel yang memverifikasi kecocokan sandi saat ini dengan hash di database.
     *    - 'password' => ['required', Password::defaults(), 'confirmed']:
     *      Memvalidasi kekuatan sandi baru (minimal 8 karakter) dan memastikan sama dengan konfirmasi.
     * 2. Hash::make($validated['password']):
     *    - Mengenkripsi kata sandi baru menggunakan Bcrypt hashing.
     * 3. $request->user()->update([...]):
     *    - Memperbarui kolom `password` pada tabel `users` untuk user yang terotentikasi.
     * 4. back()->with('status', 'password-updated'):
     *    - Mengembalikan respons ke halaman profil disertai penanda sukses agar teks "Saved." muncul.
     */
    public function update(Request $request): RedirectResponse
    {
        // 1. Validasi kata sandi lama dan kata sandi baru menggunakan named error bag
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', Password::defaults(), 'confirmed'],
        ]);

        // 2. Perbarui kolom password pengguna di basis data
        if ($request->user()) {
            $request->user()->update([
                'password' => Hash::make($validated['password']),
            ]);
        }

        // 3. Kembalikan ke halaman profil dengan status berhasil
        return back()->with('status', 'password-updated');
    }
}
