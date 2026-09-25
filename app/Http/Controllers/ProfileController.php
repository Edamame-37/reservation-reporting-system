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
     * CARA KERJA         : Mengirimkan objek user terotentikasi ke view profile.edit.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * FUNCTION/PROCEDURE : update()
     * KEGUNAAN           : Memperbarui informasi profil pengguna.
     * CARA KERJA         : Menerima data tervalidasi, mengosongkan status verifikasi email jika email berubah, dan menyimpannya.
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
     * KEGUNAAN           : Menghapus akun pengguna dari sistem secara permanen.
     * CARA KERJA         : Memvalidasi kata sandi, menghapus objek pengguna, melogoutkan dari session, dan meredirect ke halaman utama.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();
        
        Auth::logout();
        
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}

