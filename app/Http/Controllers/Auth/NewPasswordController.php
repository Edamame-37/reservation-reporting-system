<?php
/**
 * ==============================================================================
 * NAMA FILE    : NewPasswordController.php
 * MODUL        : Modul 5 - Alur Autentikasi Pengguna & Registrasi Mandiri Sivitas (Laravel Breeze Core)
 * KODE FITUR   : AUTH-01 (Eksekusi Reset Kata Sandi Baru dengan Token Kriptografis)
 * ==============================================================================
 * DESKRIPSI:
 * Controller yang menangani tahap akhir dari proses pemulihan kata sandi pengguna.
 * Menampilkan halaman masukan kata sandi baru yang memuat token rahasia dari URL,
 * memvalidasi token terhadap tabel `password_reset_tokens`, mengenkripsi kata sandi baru
 * menggunakan Bcrypt, memperbarui baris pengguna, serta menghapus token yang telah terpakai.
 *
 * ALUR SISTEM:
 * 1. GET /reset-password/{token} ➔ create() ➔ Form input sandi baru & konfirmasi
 * 2. POST /reset-password ➔ store() ➔ Validasi input ➔ Password::reset()
 *    ├── Verifikasi kecocokan token, email, dan masa berlaku token (60 menit default)
 *    ├── Hash sandi baru: Hash::make($request->password)
 *    ├── Regenerasi remember_token: Str::random(60)
 *    ├── Simpan perubahan ke database: $user->save()
 *    ├── Picu event: event(new PasswordReset($user))
 *    └── Redirect ke rute 'login' dengan pesan sukses
 * ==============================================================================
 */

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    /**
     * Menampilkan formulir input kata sandi baru bertoken.
     *
     * @param  \Illuminate\Http\Request  $request Objek request berisi token pada route parameter dan email pada query string
     * @return \Illuminate\View\View Tampilan resources/views/auth/reset-password.blade.php
     *
     * CARA KERJA & SYNTAX:
     * Menyuntikkan instance $request ke view Blade auth.reset-password agar token
     * dan email dapat di-bind secara otomatis pada elemen form tersembunyi (hidden input).
     */
    public function create(Request $request): View
    {
        return view('auth.reset-password', ['request' => $request]);
    }

    /**
     * Memproses verifikasi token dan menyimpan kata sandi baru ke basis data.
     *
     * @param  \Illuminate\Http\Request  $request Objek request berisi token, email, password, dan password_confirmation
     * @return \Illuminate\Http\RedirectResponse Redireksi ke login atau kembali dengan pesan kesalahan
     *
     * @throws \Illuminate\Validation\ValidationException Jika validasi gagal
     *
     * CARA KERJA & SYNTAX:
     * 1. $request->validate([...]):
     *    - 'token': Wajib ada (berasal dari tautan email).
     *    - 'email': Format email sah pemohon.
     *    - 'password': Wajib diisi dan harus cocok dengan 'password_confirmation' (`confirmed`).
     * 2. Password::reset(...):
     *    - Memanggil facade Password Laravel untuk memeriksa apakah token valid dan belum kedaluwarsa.
     *    - Callback Closure: Dijalankan hanya jika token sah:
     *      * $user->forceFill([...]): Mengisi atribut 'password' dan 'remember_token' secara langsung
     *        mengabaikan proteksi mass-assignment sementara.
     *      * Hash::make($request->password): Mengenkripsi kata sandi baru menggunakan algoritma hashing satu arah Bcrypt.
     *      * Str::random(60): Meregenerasi remember token acak untuk memutus sesi "remember-me" lama di perangkat lain.
     *      * $user->save(): Menyimpan pembaruan ke tabel `users`.
     *      * event(new PasswordReset($user)): Memancarkan event PasswordReset untuk audit trail.
     * 3. Redirect ke login dengan flash message status (bahasa lokal: passphrases.reset).
     */
    public function store(Request $request): RedirectResponse
    {
        // 1. Validasi form masukan kata sandi baru
        $request->validate([
            'token'    => ['required'],
            'email'    => ['required', 'email'],
            'password' => ['required', 'confirmed'],
        ]);

        // 2. Eksekusi reset kata sandi via Password Broker Laravel
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user) use ($request) {
                // Perbarui kata sandi dan amankan sesi
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                // Picu event PasswordReset
                event(new PasswordReset($user));
            }
        );

        // 3. Evaluasi hasil eksekusi reset kata sandi
        return $status == Password::PASSWORD_RESET
                    ? redirect()->route('login')->with('status', __($status))
                    : back()->withInput($request->only('email'))
                            ->withErrors(['email' => __($status)]);
    }
}
