<?php
/**
 * NAMA FILE    : PasswordController.php
 * FUNGSI       : Controller pengubahan kata sandi akun oleh pengguna
 * DESKRIPSI    : Menangani pembaruan password dari profil pengguna (dalam mode mockup dialihkan tanpa kueri database MySQL).
 * CARA KERJA   : Memvalidasi isian tanpa kueri hash database, menonaktifkan $user->update(), dan mengembalikan status password-updated.
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
     * CARA KERJA         : [MODE MOCKUP] Operasi update ke database dinonaktifkan sementara dan langsung mengembalikan notifikasi berhasil.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required'],
            'password'         => ['required', 'confirmed'],
        ]);

        /*
        // [MOCKUP MODE] Operasi update database dinonaktifkan sementara:
        if ($request->user()) {
            $request->user()->update([
                'password' => Hash::make($validated['password']),
            ]);
        }
        */

        return back()->with('status', 'password-updated');
    }
}

