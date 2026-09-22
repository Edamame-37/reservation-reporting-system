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
        // [MOCKUP MODE] Kueri autentikasi database dinonaktifkan sementara
        // $request->authenticate();

        // Menyimpan data identitas pengguna tiruan (dummy user) pada file session
        $email = $request->input('email', 'pengguna@kampus.ac.id');
        $request->session()->put('mock_user', [
            'name'  => 'Sivitas Akademika (Mock)',
            'email' => $email,
            'role'  => 'user'
        ]);

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * FUNCTION/PROCEDURE : destroy()
     * KEGUNAAN           : Mengakhiri sesi aktif pengguna dan logout.
     * CARA KERJA         : Menghapus data sesi aktif dan mengarahkan kembali ke beranda.
     */
    public function destroy(Request $request): RedirectResponse
    {
        // [MOCKUP MODE] Menghapus data sesi pengguna mock
        // Auth::guard('web')->logout();
        $request->session()->forget('mock_user');

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}

