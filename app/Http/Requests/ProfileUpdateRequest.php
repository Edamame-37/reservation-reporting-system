<?php
/**
 * ====================================================================================================
 * SISTEM INFORMASI RESERVASI & PELAPORAN FASILITAS KAMPUS (CAVA)
 * MODUL 17: MANAJEMEN AKUN PROFIL PENGGUNA (PROFILE SETTINGS & PASSWORD)
 * BERKAS: app/Http/Requests/ProfileUpdateRequest.php
 * ====================================================================================================
 *
 * DESKRIPSI BERKAS:
 * Kelas Form Request Validation resmi yang bertindak sebagai verifikator payload integritas data
 * saat pengguna aktif (Super Admin, Petugas Sarpras, atau Sivitas Akademika) melakukan pembaruan
 * informasi identitas diri pada halaman Pengaturan Profil Akun (`/profile`).
 *
 * PERAN ARSITEKTURAL DALAM SISTEM CAVA:
 * 1. Validasi String Nama Lengkap:
 *    Menjamin nama pengguna tidak kosong, bertipe string, dan memiliki batas panjang maksimum 255 karakter.
 * 2. Validasi Keunikan Email dengan Pengecualian ID Diri Sendiri (Self-Exemption Unique Rule):
 *    Menggunakan `Rule::unique(User::class)->ignore($this->user()->id)` agar sistem tidak menolak
 *    pembaruan jika pengguna mempertahankan alamat email lamanya, namun tetap menolak jika email
 *    tersebut telah dipakai oleh pengguna lain di basis data.
 * 3. Normalisasi Format Alamat Email:
 *    Menerapkan aturan `'lowercase'` dan `'email'` untuk menyeragamkan penyimpanan email institusi.
 * ====================================================================================================
 */

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class ProfileUpdateRequest
 *
 * Menangani validasi pembaruan nama lengkap dan alamat email pengguna aktif CAVA.
 */
class ProfileUpdateRequest extends FormRequest
{
    /**
     * METHOD: rules()
     * ------------------------------------------------------------------------------------------------
     * FUNGSI / KEGUNAAN:
     * Mendefinisikan aturan validasi integritas untuk payload perubahan profil pengguna.
     *
     * RETURN VALUE:
     * array<string, ValidationRule|array<mixed>|string> : Daftar aturan validasi:
     * - 'name'  : Wajib diisi ('required'), bertipe string, maksimal 255 karakter.
     * - 'email' : Wajib diisi ('required'), string, huruf kecil ('lowercase'), format email valid ('email'),
     *             maksimal 255 karakter, dan unik pada tabel users kecuali untuk ID pengguna saat ini
     *             (`Rule::unique(User::class)->ignore($this->user()->id)`).
     *
     * CARA KERJA & ALUR LOGIKA:
     * 1. Laravel mengekstrak nilai input 'name' dan 'email' dari request HTTP PATCH.
     * 2. Evaluasi rule memeriksa tipe data dan panjang string.
     * 3. Aturan `Rule::unique(User::class)->ignore(...)` menghasilkan kueri SQL:
     *    `SELECT count(*) FROM users WHERE email = ? AND id != ?`
     * 4. Jika valid, controller diizinkan memproses pembaruan; jika gagal, dialihkan kembali dengan pesan error.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
        ];
    }
}
