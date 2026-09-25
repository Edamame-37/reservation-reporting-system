<?php
/**
 * NAMA FILE    : AuthenticatedSessionController.php
 * FUNGSI       : Controller autentikasi sesi masuk dan keluar pengguna
 * DESKRIPSI    : Menangani tampilan login, verifikasi kredensial (dalam mode mockup dialihkan ke sesi statis), dan logout.
 * CARA KERJA   : Menerima request login, menyimpan session simulasi mockup tanpa menyentuh kueri basis data MySQL, dan mengarahkan ke dashboard.
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
     * FUNCTION/PROCEDURE : create()
     * KEGUNAAN           : Menampilkan formulir login CAVA SSO.
     * CARA KERJA         : Mengembalikan tampilan Blade auth.login.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * FUNCTION/PROCEDURE : store()
     * KEGUNAAN           : Memproses autentikasi pengguna ke dalam sistem.
     * CARA KERJA         : [MODE MOCKUP] Kueri database Auth::attempt() dikomentari. Menggunakan data sesi statis agar alur login mockup dapat dicoba langsung tanpa koneksi MySQL.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        // 1. Eksekusi Autentikasi Kredensial Database
        $request->authenticate();

        $user = Auth::user();

        // 2. Validasi status akun (ADM-01)
        if ($user->status !== 'active') {
            Auth::guard('web')->logout();
            return back()->withErrors(['email' => 'Akun Anda belum aktif atau belum disetujui Admin.']);
        }

        $request->session()->regenerate();

        // 3. Pengalihan cerdas sesuai Role
        if ($user->hasRole('admin') || $user->role === 'admin') {
            return redirect()->intended(route('admin.dashboard', absolute: false));
        } elseif ($user->hasRole('petugas') || $user->role === 'petugas') {
            return redirect()->intended(route('petugas.dashboard', absolute: false));
        }

        return redirect()->intended(route('user.dashboard', absolute: false));
    }

    /**
     * FUNCTION/PROCEDURE : destroy()
     * KEGUNAAN           : Mengakhiri sesi aktif pengguna dan logout.
     * CARA KERJA         : Menghapus data sesi aktif dan mengarahkan kembali ke beranda.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}

