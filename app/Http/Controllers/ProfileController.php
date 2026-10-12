<?php
/**
 * ====================================================================================================
 * SISTEM INFORMASI RESERVASI & PELAPORAN FASILITAS KAMPUS (CAVA)
 * MODUL 17: MANAJEMEN AKUN PROFIL PENGGUNA (PROFILE SETTINGS & PASSWORD)
 * BERKAS: app/Http/Controllers/ProfileController.php
 * ====================================================================================================
 *
 * DESKRIPSI BERKAS:
 * Controller inti pengelola identitas akun profil dan preferensi keamanan akun pengguna CAVA.
 * Menyediakan 3 fungsionalitas utama:
 * 1. Menampilkan antarmuka manajemen profil multi-peran (`edit`).
 * 2. Menyimpan pembaruan nama lengkap dan email dengan validasi integritas (`update`).
 * 3. Menangani proses penutupan / penghapusan akun permanen atas inisiatif mandiri pengguna (`destroy`).
 *
 * PERAN ARSITEKTURAL DALAM SISTEM CAVA:
 * 1. Universal Profile Service:
 *    Dapat diakses oleh seluruh peran (Super Admin, Petugas Sarpras, Mahasiswa, Dosen, Tendik).
 * 2. Deteksi Perubahan Email & Invalidasi Verifikasi (isDirty Check):
 *    Jika pengguna mengubah alamat email, sistem secara otomatis mereset `email_verified_at` menjadi null
 *    sehingga mewajibkan proses verifikasi ulang email demi keamanan akun.
 * 3. Mekanisme Penghapusan Akun Aman (Safe Self-Deletion):
 *    Mewajibkan validasi konfirmasi kata sandi saat ini (`current_password`) dalam error bag terisolasi
 *    (`userDeletion`), melogout sesi aktif, menghapus entitas pengguna, serta menginvaliasi session cookie.
 * ====================================================================================================
 */

namespace App\Http\Controllers;

// Import FormRequest untuk validasi pembaruan profil
use App\Http\Requests\ProfileUpdateRequest;

// Import Model Eloquent User
use App\Models\User;

// Import kontrak HTTP Redirect dan Request
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

// Import Façades Auth dan Redirect Laravel
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

// Import kontrak View
use Illuminate\View\View;

/**
 * Class ProfileController
 *
 * Mengelola perenderan view profil, pembaruan atribut akun, dan penghapusan akun mandiri.
 */
class ProfileController extends Controller
{
    /**
     * METHOD: edit()
     * ------------------------------------------------------------------------------------------------
     * FUNGSI / KEGUNAAN:
     * Menampilkan formulir antarmuka pengaturan profil pengguna.
     *
     * PARAMETER:
     * @param \Illuminate\Http\Request $request : Objek HTTP Request yang memuat instance user aktif.
     *
     * RETURN VALUE:
     * \Illuminate\View\View : Mengembalikan view 'profile.edit' dengan variabel 'user'.
     *
     * CARA KERJA & ALUR LOGIKA:
     * 1. Mengambil data entitas model pengguna yang sedang terotentikasi via `$request->user()`.
     * 2. Merender berkas view `resources/views/profile/edit.blade.php`.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * METHOD: update()
     * ------------------------------------------------------------------------------------------------
     * FUNGSI / KEGUNAAN:
     * Memperbarui atribut informasi nama lengkap dan alamat email pengguna aktif.
     *
     * PARAMETER:
     * @param \App\Http\Requests\ProfileUpdateRequest $request : Payload yang telah tervalidasi.
     *
     * RETURN VALUE:
     * \Illuminate\Http\RedirectResponse : Mengalihkan kembali ke route profile.edit dengan flash status.
     *
     * CARA KERJA & ALUR LOGIKA:
     * 1. `$request->user()->fill($request->validated())`: Mengisi atribut model User secara massal
     *    hanya dengan data yang telah lolos aturan validasi.
     * 2. `$request->user()->isDirty('email')`: Memeriksa apakah kolom email mengalami perubahan nilai.
     *    Jika ya, nilai `email_verified_at` direset menjadi null agar pengguna memverifikasi ulang email barunya.
     * 3. `$request->user()->save()`: Menyimpan rekaman perubahan ke tabel `users`.
     * 4. Mengalihkan kembali ke halaman profil dengan membawa session `status = 'profile-updated'`
     *    sehingga antarmuka memunculkan badge hijau "Tersimpan".
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        // Pengisian atribut aman dari data tervalidasi
        $request->user()->fill($request->validated());

        // Reset verifikasi email jika alamat email diubah
        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        // Simpan pembaruan ke basis data MySQL
        $request->user()->save();

        // Redirect dengan status session flash
        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * METHOD: destroy()
     * ------------------------------------------------------------------------------------------------
     * FUNGSI / KEGUNAAN:
     * Menghapus akun pengguna dari sistem secara permanen atas permintaan mandiri pengguna.
     *
     * PARAMETER:
     * @param \Illuminate\Http\Request $request : Mengandung input konfirmasi kata sandi.
     *
     * RETURN VALUE:
     * \Illuminate\Http\RedirectResponse : Mengalihkan ke beranda publik universitas (`/`).
     *
     * CARA KERJA & ALUR LOGIKA:
     * 1. `$request->validateWithBag('userDeletion', [...])`: Memvalidasi kata sandi konfirmasi
     *    menggunakan aturan `current_password`. Pesan kesalahan diisolasi dalam bag 'userDeletion'
     *    agar modal konfirmasi dapat terbuka kembali secara otomatis jika validasi gagal.
     * 2. Mendapatkan referensi model pengguna aktif `$user = $request->user()`.
     * 3. `Auth::logout()`: Mencabut seluruh hak otentikasi sesi pengguna saat ini.
     * 4. `$user->delete()`: Menghapus record pengguna dari tabel `users` di basis data.
     * 5. `$request->session()->invalidate()`: Mengosongkan data sesi PHP dan menghancurkan session ID lama.
     * 6. `$request->session()->regenerateToken()`: Menghasilkan token CSRF baru demi keamanan browser.
     * 7. Mengalihkan pengunjung ke halaman beranda root (`/`).
     */
    public function destroy(Request $request): RedirectResponse
    {
        // 1. Validasi konfirmasi kata sandi saat ini dengan error bag khusus 'userDeletion'
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();
        
        // 2. Cabut sesi login pengguna
        Auth::logout();
        
        // 3. Eksekusi penghapusan akun dari basis data
        $user->delete();

        // 4. Invalidation sesi dan regenerasi token CSRF anti-hijacking
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // 5. Redirect ke halaman beranda publik
        return Redirect::to('/');
    }
}
