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
     * CARA KERJA         : Memvalidasi input form, mengunggah file identitas, merekam ke tabel users dengan status 'pending', 
     *                      memberikan peran Spatie 'pengguna', lalu mengarahkan ke halaman login tanpa auto-login.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // Konversi email ke lowercase sebelum divalidasi
        if ($request->has('email')) {
            $request->merge(['email' => strtolower($request->email)]);
        }

        // Validasi form registrasi
        $request->validate([
            'name'           => ['required', 'string', 'max:255'],
            'identifier'     => ['required', 'string', 'max:50'],
            'role_type'      => ['required', 'in:mahasiswa,dosen,staf'],
            'email'          => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'identity_proof' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
            'password'       => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Simpan file bukti identitas ke storage (contoh: storage/app/public/id_cards)
        $idCardPath = $request->file('identity_proof')->store('id_cards', 'public');

        // Buat record pengguna di database
        $user = User::create([
            'name'            => $request->name,
            'email'           => $request->email,
            'password'        => Hash::make($request->password),
            'identity_number' => $request->identifier,
            'role'            => $request->role_type,
            'id_card_path'    => $idCardPath,
            'status'          => 'pending',
        ]);

        // Berikan role Spatie "pengguna" kepada pendaftar
        $user->assignRole('pengguna');

        // Panggil event Registered (Opsional, untuk trigger notifikasi jika ada)
        event(new Registered($user));

        // Redirect ke halaman login dengan flash message
        return redirect()->route('login')->with('success', 'Akun terdaftar, menunggu persetujuan Admin.');
    }
}

