<?php
/**
 * NAMA FILE    : PasswordController.php
 * FUNGSI       : Controller pengubahan kata sandi akun oleh pengguna
 * DESKRIPSI    : Menangani pembaruan kata sandi dari profil pengguna dengan validasi kata sandi lama dan enkripsi sandi baru.
 * CARA KERJA   : Memvalidasi kecocokan sandi saat ini via aturan 'current_password', mengenkripsi kata sandi baru dengan Hash::make(), dan memperbarui record pengguna di tabel users.
 */

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    /**
     * FUNCTION/PROCEDURE : update()
     * KEGUNAAN           : Memperbarui kata sandi akun pengguna yang sedang login.
     * CARA KERJA         : Memvalidasi kata sandi saat ini dan aturan password baru, mengenkripsi dengan Hash::make(), dan menyimpan ke database.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', Password::defaults(), 'confirmed'],
        ]);

        if ($request->user()) {
            $request->user()->update([
                'password' => Hash::make($validated['password']),
            ]);
        }

        return back()->with('status', 'password-updated');
    }
}

