<?php
/**
 * NAMA FILE    : ConfirmablePasswordController.php
 * FUNGSI       : Controller konfirmasi kata sandi sebelum aksi krusial
 * DESKRIPSI    : Menangani formulir konfirmasi password (dalam mode mockup dialihkan tanpa kueri database MySQL).
 * CARA KERJA   : Memvalidasi keberadaan input password tanpa kueri tabel users, lalu mengarahkan ke intended route.
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
     * KEGUNAAN           : Memvalidasi kata sandi terkonfirmasi.
     * CARA KERJA         : [MODE MOCKUP] Pengecekan database dinonaktifkan sementara dan langsung menandai sesi password telah terkonfirmasi.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required'],
        ]);

        /*
        // [MOCKUP MODE] Pengecekan database dinonaktifkan sementara:
        if (! Auth::guard('web')->validate([
            'email' => $request->user()->email,
            'password' => $request->password,
        ])) {
            throw ValidationException::withMessages([
                'password' => __('auth.password'),
            ]);
        }
        */

        $request->session()->put('auth.password_confirmed_at', time());

        return redirect()->intended(route('dashboard', absolute: false));
    }
}

