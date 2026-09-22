<?php
/**
 * NAMA FILE    : PasswordResetLinkController.php
 * FUNGSI       : Controller pengiriman permintaan tautan reset kata sandi
 * DESKRIPSI    : Menangani formulir lupa kata sandi (dalam mode mockup dialihkan tanpa kueri database MySQL).
 * CARA KERJA   : Menerima email tujuan, melewati kueri tabel password_reset_tokens, dan mengembalikan pesan status tiruan.
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
     * CARA KERJA         : [MODE MOCKUP] Kueri broker password dinonaktifkan sementara dan mengembalikan pesan sukses simulasi.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        /*
        // [MOCKUP MODE] Kueri database dinonaktifkan sementara:
        $status = Password::sendResetLink(
            $request->only('email')
        );
        */

        return back()->with('status', 'Tautan simulasi reset password berhasil dikirim ke email Anda (Mode Mockup).');
    }
}

