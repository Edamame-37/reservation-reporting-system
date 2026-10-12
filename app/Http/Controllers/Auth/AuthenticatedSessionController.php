<?php
/**
 * ==============================================================================
 * NAMA FILE    : AuthenticatedSessionController.php
 * MODUL        : Modul 5 - Alur Autentikasi Pengguna & Registrasi Mandiri Sivitas (Laravel Breeze Core)
 * KODE FITUR   : AUTH-01 (Manajemen Sesi Otentikasi, Login Role-Based Redirection, & Logout)
 * ==============================================================================
 * DESKRIPSI:
 * Controller inti yang mengatur siklus hidup otentikasi sesi (session-based authentication)
 * pengguna CAVA. Menangani penyajian formulir login, pemrosesan verifikasi kredensial
 * berkeamanan tinggi melalui `LoginRequest`, regenerasi ID sesi anti-pembajakan (Session Fixation),
 * pengalihan dinamis ke dasbor masing-masing aktor (Admin, Petugas, Sivitas), serta pembersihan
 * sesi yang aman saat pengguna melakukan logout.
 *
 * ALUR SISTEM:
 * 1. GET /login ➔ create() ➔ View auth/login.blade.php
 * 2. POST /login ➔ store() ➔ $request->authenticate() ➔ Check Status ➔ Regenerate Session ➔ Redirect Role Dashboard
 * 3. POST /logout ➔ destroy() ➔ Guard Logout ➔ Invalidate Session ➔ Regenerate CSRF Token ➔ Redirect '/'
 * ==============================================================================
 */

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Menampilkan antarmuka visual halaman login (AUTH-01).
     *
     * @return \Illuminate\View\View Tampilan resources/views/auth/login.blade.php
     *
     * CARA KERJA & SYNTAX:
     * Mengembalikan view Blade auth.login yang dilengkapi dengan form input,
     * tombol submit, link pemulihan kata sandi, serta kartu kredensial demo untuk evaluasi.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Memproses percobaan autentikasi kredensial pengguna dan mengarahkan ke dasbor yang sesuai.
     *
     * @param  \App\Http\Requests\Auth\LoginRequest  $request Form Request tervalidasi & ter-throttle
     * @return \Illuminate\Http\RedirectResponse     Redireksi ke dasbor aktor terkait
     *
     * CARA KERJA & SYNTAX:
     * 1. $request->authenticate():
     *    - Mengeksekusi verifikasi rate limiting (maksimal 5 kegagalan).
     *    - Menjalankan Auth::attempt() untuk mencocokkan password hash Bcrypt.
     *    - Memeriksa status akun (Pending/Rejected/Active).
     * 2. Pengecekan Sekunder Status Akun:
     *    - Sebagai lapisan pertahanan kedua (Defense in Depth), jika status akun tidak aktif,
     *      pengguna langsung dikeluarkan dan dikembalikan dengan pesan error.
     * 3. $request->session()->regenerate():
     *    - Sangat penting untuk keamanan web: Menerbitkan ID sesi baru setelah pengguna berhasil login
     *      untuk mencegah serangan Session Fixation Attack (OWASP Top 10).
     * 4. Pengalihan Dinamis Berbasis Peran (Multi-Level RBAC):
     *    - redirect()->intended(...): Mengarahkan pengguna ke halaman yang semula ingin diakses,
     *      atau ke rute fallback dasbor perannya:
     *      * Admin   ➔ route('admin.dashboard')
     *      * Petugas ➔ route('petugas.dashboard')
     *      * Pengguna➔ route('user.dashboard')
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        // 1. Eksekusi Autentikasi Kredensial Database & Rate Limiter
        $request->authenticate();

        $user = Auth::user();

        // 2. Lapisan Validasi Tambahan Status Akun (ADM-01)
        if ($user->status !== 'active') {
            Auth::guard('web')->logout();
            return back()->withErrors(['email' => 'Akun Anda belum aktif atau belum disetujui Admin.']);
        }

        // 3. Regenerasi Sesi untuk Mencegah Session Fixation Vulnerability
        $request->session()->regenerate();

        // 4. Pengalihan Cerdas Berdasarkan Hak Peran (Spatie RBAC & Kolom Role)
        if ($user->hasRole('admin') || $user->role === 'admin') {
            return redirect()->intended(route('admin.dashboard', absolute: false));
        } elseif ($user->hasRole('petugas') || $user->role === 'petugas') {
            return redirect()->intended(route('petugas.dashboard', absolute: false));
        }

        // Default untuk peran sivitas umum ('pengguna')
        return redirect()->intended(route('user.dashboard', absolute: false));
    }

    /**
     * Mengakhiri sesi aktif pengguna secara aman (Logout - AUTH-01).
     *
     * @param  \Illuminate\Http\Request  $request Objek HTTP Request sesi saat ini
     * @return \Illuminate\Http\RedirectResponse Redireksi kembali ke halaman beranda publik
     *
     * CARA KERJA & SYNTAX:
     * 1. Auth::guard('web')->logout(): Menghapus status autentikasi dari state Guard Laravel.
     * 2. $request->session()->invalidate(): Mengosongkan seluruh data yang tersimpan di sesi server
     *    dan mematikan ID sesi saat ini agar tidak dapat disalahgunakan lagi.
     * 3. $request->session()->regenerateToken(): Menghasilkan token CSRF baru untuk melindungi
     *    formulir berikutnya dari serangan Cross-Site Request Forgery.
     * 4. redirect('/'): Mengarahkan pengguna kembali ke landing page beranda portal publik.
     */
    public function destroy(Request $request): RedirectResponse
    {
        // 1. Logout dari guard web
        Auth::guard('web')->logout();

        // 2. Hancurkan data sesi aktif
        $request->session()->invalidate();

        // 3. Regenerasi token perlindungan CSRF
        $request->session()->regenerateToken();

        // 4. Arahkan kembali ke halaman muka publik
        return redirect('/');
    }
}
