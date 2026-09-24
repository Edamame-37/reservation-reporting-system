<?php
/**
 * NAMA FILE    : ProfileController.php
 * FUNGSI       : Controller pengelolaan data profil pengguna
 * DESKRIPSI    : Menyajikan formulir pengubahan informasi profil dan penghapusan akun (dalam mode mockup dialihkan tanpa kueri database MySQL).
 * CARA KERJA   : Menyediakan mock user object jika tidak ada session auth aktif, serta menonaktifkan operasi update() dan destroy() ke tabel users.
 */

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * FUNCTION/PROCEDURE : edit()
     * KEGUNAAN           : Menampilkan formulir profil pengguna.
     * CARA KERJA         : [MODE MOCKUP] Jika belum ada autentikasi database, menyediakan data objek User tiruan (dummy user) agar view profil dapat dibuka dengan sempurna.
     */
    public function edit(Request $request): View
    {
        // [MOCKUP MODE] Sediakan dummy user jika request->user() null
        $user = $request->user();
        if (!$user) {
            $mockSession = $request->session()->get('mock_user', []);
            $user = new User([
                'name'  => $mockSession['name'] ?? 'Sivitas Pengguna CAVA',
                'email' => $mockSession['email'] ?? 'pengguna@kampus.ac.id',
            ]);
        }

        return view('profile.edit', [
            'user' => $user,
        ]);
    }

    /**
     * FUNCTION/PROCEDURE : update()
     * KEGUNAAN           : Memperbarui informasi profil pengguna.
     * CARA KERJA         : [MODE MOCKUP] Operasi $user->save() ke database dinonaktifkan sementara dan dialihkan ke pembaruan session mock.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * FUNCTION/PROCEDURE : destroy()
     * KEGUNAAN           : Menghapus akun pengguna dari sistem.
     * CARA KERJA         : [MODE MOCKUP] Operasi $user->delete() dinonaktifkan sementara dan hanya membersihkan session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();
        if ($user) {
            Auth::logout();
            $user->delete();
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}

