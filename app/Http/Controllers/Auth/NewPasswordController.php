<?php
/**
 * NAMA FILE    : NewPasswordController.php
 * FUNGSI       : Controller penetapan kata sandi baru
 * DESKRIPSI    : Menangani formulir reset kata sandi baru (dalam mode mockup dialihkan tanpa kueri database MySQL).
 * CARA KERJA   : Menerima token dan password baru, menonaktifkan kueri broker password database, dan mengarahkan ke halaman login.
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
     * FUNCTION/PROCEDURE : create()
     * KEGUNAAN           : Menampilkan formulir input password baru.
     * CARA KERJA         : Mengembalikan tampilan Blade auth.reset-password.
     */
    public function create(Request $request): View
    {
        return view('auth.reset-password', ['request' => $request]);
    }

    /**
     * FUNCTION/PROCEDURE : store()
     * KEGUNAAN           : Memperbarui kata sandi pengguna.
     * CARA KERJA         : [MODE MOCKUP] Kueri reset password ke database dinonaktifkan sementara dan mengarahkan langsung ke halaman login.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token'    => ['required'],
            'email'    => ['required', 'email'],
            'password' => ['required', 'confirmed'],
        ]);

        /*
        // [MOCKUP MODE] Kueri database dinonaktifkan sementara:
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );
        */

        return redirect()->route('login')->with('status', 'Kata sandi berhasil diperbarui (Mode Mockup). Silakan masuk.');
    }
}

