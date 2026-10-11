<?php
/**
 * NAMA FILE    : ConfirmablePasswordController.php
 * FUNGSI       : Controller konfirmasi kata sandi sebelum aksi krusial
 * DESKRIPSI    : Menangani formulir konfirmasi kata sandi sebelum pengguna menjalankan operasi berisiko tinggi.
 * CARA KERJA   : Memvalidasi kecocokan kata sandi aktif pengguna via Auth guard, mencatat timestamp konfirmasi ke sesi, lalu mengarahkan ke rute tujuan.
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
     * FUNCTION/PROCEDURE : show()
     * KEGUNAAN           : Menampilkan formulir konfirmasi kata sandi.
     * CARA KERJA         : Mengembalikan tampilan Blade auth.confirm-password.
     */
    public function show(): View
    {
        return view('auth.confirm-password');
    }

    /**
     * FUNCTION/PROCEDURE : store()
     * KEGUNAAN           : Memvalidasi kata sandi pengguna sebelum melanjutkan aksi sensitif.
     * CARA KERJA         : Memvalidasi kredensial via Auth::guard('web')->validate(), menyimpan waktu konfirmasi ke sesi, dan melanjutkan request.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required'],
        ]);

        if (! Auth::guard('web')->validate([
            'email' => $request->user()->email,
            'password' => $request->password,
        ])) {
            throw ValidationException::withMessages([
                'password' => __('auth.password'),
            ]);
        }

        $request->session()->put('auth.password_confirmed_at', time());

        return redirect()->intended(route('dashboard', absolute: false));
    }
}

