<?php
/**
 * NAMA FILE    : RegisteredUserController.php
 * FUNGSI       : Controller pendaftaran akun baru pengguna CAVA
 * DESKRIPSI    : Menangani formulir registrasi dan pencatatan akun (dalam mode mockup dialihkan tanpa kueri database MySQL).
 * CARA KERJA   : Menerima form pendaftaran, memvalidasi input dasar tanpa pengecekan unik database, menyimpan data ke session mockup, dan mengarahkan ke dashboard.
 */

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * FUNCTION/PROCEDURE : create()
     * KEGUNAAN           : Menampilkan halaman formulir registrasi akun.
     * CARA KERJA         : Mengembalikan tampilan Blade auth.register.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * FUNCTION/PROCEDURE : store()
     * KEGUNAAN           : Memproses data registrasi pengguna baru.
     * CARA KERJA         : [MODE MOCKUP] Validasi database unique:users dan User::create() dinonaktifkan sementara. Pengguna diarahkan langsung ke dashboard sebagai mock user.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // Validasi format isian tanpa menyentuh tabel database
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'lowercase', 'email', 'max:255'],
            'password' => ['required', 'confirmed'],
        ]);

        /*
        // [MOCKUP MODE] Kueri insert database dan auth dinonaktifkan sementara:
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));
        Auth::login($user);
        */

        // Simpan sesi mockup pengguna
        $request->session()->put('mock_user', [
            'name'  => $request->name,
            'email' => $request->email,
            'role'  => 'user'
        ]);

        return redirect(route('dashboard', absolute: false));
    }
}

