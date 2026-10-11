<?php
/**
 * NAMA FILE    : NewPasswordController.php
 * FUNGSI       : Controller penetapan kata sandi baru
 * DESKRIPSI    : Menangani formulir reset kata sandi baru, validasi token pemulihan, dan enkripsi kata sandi baru.
 * CARA KERJA   : Menerima token dan kata sandi baru, memvalidasi integritas data, mengeksekusi Password::reset() untuk memperbarui tabel users, serta memicu event PasswordReset.
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
     * KEGUNAAN           : Memperbarui kata sandi pengguna berdasarkan token reset.
     * CARA KERJA         : Memvalidasi token dan kredensial baru, memperbarui hash kata sandi di database via Password::reset(), dan mengarahkan ke login.
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

        return $status == Password::PASSWORD_RESET
                    ? redirect()->route('login')->with('status', __($status))
                    : back()->withInput($request->only('email'))
                            ->withErrors(['email' => __($status)]);
    }
}

