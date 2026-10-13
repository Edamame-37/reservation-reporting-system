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

// ====================================================================================================
// 1. DEKLARASI NAMESPACE (PHP NATIVE)
// ====================================================================================================
// SYNTAX     : namespace App\Http\Requests;
// UNTUK APA  : Mengelompokkan class ini ke dalam namespace HTTP Requests sesuai konvensi PSR-4.
// CARA KERJA : Memungkinkan resolusi pemanggilan otomatis oleh framework via Dependency Injection Controller.
namespace App\Http\Requests;

// ====================================================================================================
// 2. IMPORT NAMESPACE & DEPENDENCIES (PHP NATIVE 'use')
// ====================================================================================================
// SYNTAX     : use <Vendor>\<Package>\<ClassName>;
// UNTUK APA  : Mengimpor class model User, base FormRequest, dan Rule builder Laravel ke dalam berkas ini.
// CARA KERJA : Aliasing simbol class pada waktu kompilasi PHP script.
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// ====================================================================================================
// 3. DEKLARASI CLASS & EXTENDS (PHP OOP INHERITANCE)
// ====================================================================================================
// SYNTAX     : class ProfileUpdateRequest extends FormRequest
// UNTUK APA  : Mewarisi fitur HTTP Request Validation bawaan Laravel (intersepsi otomatis sebelum controller).
// CARA KERJA : Jika aturan pada `rules()` gagal, framework langsung membatalkan eksekusi controller dan
//              mengembalikan respon redirect/JSON dengan kumpulan pesan error validation ($errors).
class ProfileUpdateRequest extends FormRequest
{
    // ================================================================================================
    // 4. ATURAN VALIDASI DATA FORMULIR (LARAVEL FORM REQUEST RULES)
    // ================================================================================================
    // SYNTAX     : public function rules(): array
    // UNTUK APA  : Mendefinisikan array aturan validasi untuk payload nama dan email pengguna.
    // CARA KERJA :
    // - 'name'  : Wajib diisi ('required'), bertipe string, maksimal 255 karakter.
    // - 'email' : Wajib diisi, string, huruf kecil ('lowercase'), format email RFC valid ('email'),
    //             maksimal 255 karakter, dan unik pada tabel users kecuali ID user saat ini
    //             (`Rule::unique(User::class)->ignore($this->user()->id)`).
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
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
