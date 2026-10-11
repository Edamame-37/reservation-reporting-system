<?php
/**
 * NAMA FILE    : PasswordResetLinkController.php
 * FUNGSI       : Controller pengiriman permintaan tautan reset kata sandi
 * DESKRIPSI    : Menangani formulir lupa kata sandi dan pengiriman tautan token pemulihan akun ke email terdaftar.
 * CARA KERJA   : Memvalidasi alamat email dan memicu Password::sendResetLink() untuk mencatat token ke tabel password_reset_tokens serta mengirim notifikasi.
 */

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * FUNCTION/PROCEDURE : create()
     * KEGUNAAN           : Menampilkan formulir permintaan tautan reset password.
     * CARA KERJA         : Mengembalikan tampilan Blade auth.forgot-password.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * FUNCTION/PROCEDURE : store()
     * KEGUNAAN           : Mengirimkan tautan reset kata sandi ke email pemohon.
     * CARA KERJA         : Memvalidasi format email dan memanggil Password broker Laravel untuk menerbitkan token reset.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status == Password::RESET_LINK_SENT
                    ? back()->with('status', __($status))
                    : back()->withInput($request->only('email'))
                            ->withErrors(['email' => __($status)]);
    }
}

