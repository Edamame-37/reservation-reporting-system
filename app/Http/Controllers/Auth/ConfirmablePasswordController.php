<?php
/**
 * ==============================================================================
 * NAMA FILE    : ConfirmablePasswordController.php
 * MODUL        : Modul 5 - Alur Autentikasi Pengguna & Registrasi Mandiri Sivitas (Laravel Breeze Core)
 * KODE FITUR   : AUTH-01 (Konfirmasi Sandi Ulang Sebelum Tindakan Sensitif / Kritis)
 * ==============================================================================
 * DESKRIPSI:
 * Controller keamanan tingkat lanjut yang meminta pengguna untuk memasukkan kembali
 * kata sandi mereka sebelum mengakses fitur atau menjalankan tindakan yang berisiko tinggi
 * (misal: perubahan konfigurasi profil sensitif, token API, atau otorisasi akun).
 * Mengimplementasikan middleware 'password.confirm' bawaan Laravel dengan mencatat
 * timestamp waktu konfirmasi pada sesi server (`auth.password_confirmed_at`).
 * ==============================================================================
 */

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ConfirmablePasswordController extends Controller
{
    /**
     * Menampilkan formulir konfirmasi kata sandi kepada pengguna yang sedang login.
     *
     * @return \Illuminate\View\View Tampilan resources/views/auth/confirm-password.blade.php
     *
     * CARA KERJA & SYNTAX:
     * Merender template Blade auth.confirm-password yang memberi tahu pengguna bahwa
     * area yang dituju memerlukan verifikasi ulang identitas demi keamanan.
     */
    public function show(): View
    {
        return view('auth.confirm-password');
    }

    /**
     * Memvalidasi kecocokan kata sandi yang dimasukkan dengan akun aktif pengguna.
     *
     * @param  \Illuminate\Http\Request  $request Objek request berisi masukan 'password'
     * @return \Illuminate\Http\RedirectResponse Redireksi ke rute yang semula dituju
     *
     * @throws \Illuminate\Validation\ValidationException Jika kata sandi salah
     *
     * CARA KERJA & SYNTAX:
     * 1. $request->validate(['password' => ['required']]): Memastikan kata sandi diisi.
     * 2. Auth::guard('web')->validate([...]):
     *    - Menguji kebenaran password terhadap hash pengguna yang sedang login tanpa membuat sesi baru.
     *    - Jika salah, lempar ValidationException dengan pesan bawaan Laravel `auth.password`.
     * 3. $request->session()->put('auth.password_confirmed_at', time()):
     *    - Menyimpan waktu detik Unix saat ini ke sesi pengguna.
     *    - Middleware `password.confirm` memeriksa nilai ini (berlaku selama 3 jam default)
     *      sehingga pengguna tidak perlu berulang kali memasukkan sandi dalam rentang waktu aman.
     * 4. redirect()->intended(...): Melanjutkan pengguna ke aksi sensitif yang tertunda.
     */
    public function store(Request $request): RedirectResponse
    {
        // 1. Validasi keberadaan input kata sandi
        $request->validate([
            'password' => ['required'],
        ]);

        // 2. Validasi kredensial password pengguna aktif
        if (! Auth::guard('web')->validate([
            'email' => $request->user()->email,
            'password' => $request->password,
        ])) {
            throw ValidationException::withMessages([
                'password' => __('auth.password'),
            ]);
        }

        // 3. Catat timestamp konfirmasi sukses ke session pengguna
        $request->session()->put('auth.password_confirmed_at', time());

        // 4. Lanjutkan pengalihan ke tujuan awal pengguna
        return redirect()->intended(route('dashboard', absolute: false));
    }
}
