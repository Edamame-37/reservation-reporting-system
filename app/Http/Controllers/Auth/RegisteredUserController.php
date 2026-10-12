<?php
/**
 * ==============================================================================
 * NAMA FILE    : RegisteredUserController.php
 * MODUL        : Modul 5 - Alur Autentikasi Pengguna & Registrasi Mandiri Sivitas (Laravel Breeze Core)
 * KODE FITUR   : AUTH-02 (Registrasi Mandiri Sivitas, Unggah Dokumen, & Status Kunci Pending)
 * ==============================================================================
 * DESKRIPSI:
 * Controller yang menangani alur pendaftaran mandiri (self-registration) untuk sivitas akademika
 * (Mahasiswa, Dosen, dan Staf Kampus).
 *
 * PROSES BISNIS & ATURAN SPESIFIK:
 * 1. Setiap calon peminjam wajib melampirkan berkas identitas resmi (KTM/Kartu Pegawai)
 *    berformat PDF/JPG/PNG maksimal 2MB.
 * 2. Akun yang baru didaftarkan secara default DIKUNCI pada status `'pending'`.
 * 3. Menugaskan peran RBAC `'pengguna'` via Spatie Permission `assignRole('pengguna')`.
 * 4. KEPUTUSAN ARSITEKTUR KRUSIAL: Berbeda dari Laravel Breeze bawaan yang langsung mengeksekusi
 *    `Auth::login($user)`, pada sistem CAVA pengguna **SECARA SENGAJA TIDAK DILOGINKAN LANGSUNG**
 *    (No Auto-Login), melainkan dialihkan ke halaman login dengan flash message edukatif bahwa
 *    akun sedang menunggu verifikasi dokumen oleh Administrator Sarpras (ADM-01).
 * ==============================================================================
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
     * Menampilkan formulir registrasi mandiri sivitas akademika (AUTH-02).
     *
     * @return \Illuminate\View\View Tampilan resources/views/auth/register.blade.php
     *
     * CARA KERJA & SYNTAX:
     * Merender template Blade register yang dibungkus oleh komponen <x-guest-layout>.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Memproses data masukan formulir pendaftaran, upload berkas KTM, dan persistensi ke basis data.
     *
     * @param  \Illuminate\Http\Request  $request Objek request berisi data form dan berkas file
     * @return \Illuminate\Http\RedirectResponse Redireksi ke rute login dengan flash status
     *
     * @throws \Illuminate\Validation\ValidationException Jika input melanggar aturan validasi
     *
     * CARA KERJA & SYNTAX:
     * 1. $request->merge(['email' => strtolower($request->email)]): Menormalkan alamat email
     *    menjadi huruf kecil sebelum divalidasi ke basis data untuk mencegah duplikasi case-sensitive.
     * 2. $request->validate([...]):
     *    - 'name': Wajib diisi, string, maksimal 255 karakter.
     *    - 'identifier': Nomor Induk Mahasiswa (NIM) atau NIP pegawai (maksimal 50 karakter).
     *    - 'role_type': Memastikan jenis sivitas bernilai salah satu dari: mahasiswa, dosen, atau staf.
     *    - 'email': Wajib format email valid dan unik pada tabel `users`.
     *    - 'identity_proof': Berkas kartu identitas wajib diunggah, ekstensi PDF/JPG/PNG, ukuran maksimal 2048 KB (2MB).
     *    - 'password': Menggunakan aturan default Laravel Breeze (minimal 8 karakter) dengan konfirmasi (`confirmed`).
     * 3. $request->file('identity_proof')->store('id_cards', 'public'): Menyimpan file ke storage/app/public/id_cards
     *    dengan nama file hash acak untuk mencegah penimpaan nama file dan serangan path traversal.
     * 4. User::create([...]): Menyimpan record pengguna baru dengan status `'pending'`.
     * 5. $user->assignRole('pengguna'): Menyisipkan relasi peran Spatie RBAC ke tabel `model_has_roles`.
     * 6. event(new Registered($user)): Memicu event sistem Laravel untuk penanganan pendukung jika ada listener.
     * 7. redirect()->route('login')->with(...): Mengarahkan pengguna ke form login tanpa auto-login.
     */
    public function store(Request $request): RedirectResponse
    {
        // 1. Normalisasi email ke format huruf kecil (lowercase)
        if ($request->has('email')) {
            $request->merge(['email' => strtolower($request->email)]);
        }

        // 2. Validasi integritas input form registrasi
        $request->validate([
            'name'           => ['required', 'string', 'max:255'],
            'identifier'     => ['required', 'string', 'max:50'],
            'role_type'      => ['required', 'in:mahasiswa,dosen,staf'],
            'email'          => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'identity_proof' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
            'password'       => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // 3. Simpan file berkas identitas ke direktori storage publik (storage/app/public/id_cards)
        $idCardPath = $request->file('identity_proof')->store('id_cards', 'public');

        // 4. Buat rekaman pengguna baru di database dengan status terkunci ('pending')
        $user = User::create([
            'name'            => $request->name,
            'email'           => $request->email,
            'password'        => Hash::make($request->password), // Enkripsi hash satu arah dengan algoritma Bcrypt
            'identity_number' => $request->identifier,
            'role'            => $request->role_type,
            'id_card_path'    => $idCardPath,
            'status'          => 'pending',                      // Akun terkunci hingga diverifikasi admin
        ]);

        // 5. Berikan hak peran Spatie RBAC sebagai 'pengguna' (sivitas peminjam)
        $user->assignRole('pengguna');

        // 6. Picu event Registered untuk audit trail registrasi
        event(new Registered($user));

        // 7. Alihkan pengguna kembali ke formulir login disertai pesan notifikasi
        // CATATAN: Sistem sengaja TIDAK mengeksekusi Auth::login($user) demi integritas verifikasi admin
        return redirect()->route('login')->with('success', 'Akun terdaftar, menunggu persetujuan Admin.');
    }
}
