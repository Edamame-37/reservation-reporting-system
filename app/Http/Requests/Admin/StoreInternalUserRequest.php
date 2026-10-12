<?php

/**
 * ====================================================================================================
 * SISTEM INFORMASI RESERVASI & PELAPORAN FASILITAS KAMPUS (CAVA)
 * MODUL 6: KONSOL ADMIN - VERIFIKASI AKUN PENDING SIVITAS & MANAJEMEN PENGGUNA INTERNAL (ADM-01 & ADM-02)
 * BERKAS: app/Http/Requests/Admin/StoreInternalUserRequest.php
 * ====================================================================================================
 * 
 * DESKRIPSI BERKAS:
 * Berkas Form Request ini bertugas memvalidasi data saat Administrator membuat akun pengguna baru
 * secara internal (Direct Account Creation) untuk Petugas Sarpras maupun akun Sivitas Akademika
 * (Mahasiswa, Dosen, Staf) tanpa melalui mekanisme registrasi mandiri.
 * 
 * PERAN & ARSITEKTUR DALAM SISTEM CAVA:
 * 1. Kebijakan Zero Trust & Anti-Privilege Escalation:
 *    Mencegah eskalasi hak akses berbahaya dengan menerapkan larangan mutlak ('not_in:admin,Admin')
 *    pada pemilihan peran. Hal ini menjamin bahwa peran Administrator Super tidak dapat diduplikasi
 *    atau disusupkan melalui antarmuka web biasa.
 * 2. Integritas Identitas & Keunikan Akun:
 *    Memvalidasi keunikan email ('unique:users,email') untuk menghindari duplikasi akun serta
 *    memverifikasi format data identitas (NIM/NIP, zona penugasan teknisi, dan departemen).
 * 3. Pembuatan Akun Langsung Aktif:
 *    Akun yang dibuat melalui form request ini oleh Admin memiliki validasi ketat sehingga aman untuk
 *    langsung berstatus 'active' tanpa perlu melewati antrean verifikasi 'pending'.
 * ====================================================================================================
 */

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class StoreInternalUserRequest
 * 
 * Mengatur otorisasi dan mendefinisikan aturan validasi saat Administrator menambahkan akun internal.
 */
class StoreInternalUserRequest extends FormRequest
{
    /**
     * Menentukan apakah pengguna saat ini berhak mengeksekusi request pendaftaran internal ini.
     * 
     * CARA KERJA & LOGIKA:
     * Mengembalikan nilai boolean 'true' karena rute pemanggilan telah dilindungi di tingkat
     * routing middleware dengan pembatasan peran `['auth', 'role:admin']`.
     * 
     * SYNTAX:
     * - return true; : Mengizinkan request melanjutkan proses ke tahap pemeriksaan rules().
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Mendefinisikan aturan validasi ketat untuk pembuatan akun internal.
     * 
     * CARA KERJA & LOGIKA:
     * 1. 'name': Wajib diisi ('required'), berupa string, maksimal 150 karakter.
     * 2. 'email': Wajib diisi, format email valid, unik di tabel `users` ('unique:users,email').
     * 3. 'password': Bersifat opsional ('nullable') saat form submit; jika diisi, minimal 8 karakter.
     *    Bila dikosongkan oleh Admin, Controller akan menetapkan kata sandi bawaan yang aman.
     * 4. 'role' & 'role_type': Memastikan peran terbatas pada 'petugas', 'pengguna', 'mahasiswa', 'dosen',
     *    atau 'staf', dan MENOLAK SECARA MUTLAK input 'admin' atau 'Admin' demi integritas keamanan RBAC.
     * 5. 'nip' / 'identifier' / 'identity_number': Opsional, string max 50 karakter untuk NIP petugas atau NIM sivitas.
     * 6. 'zone' / 'assignment_zone': Opsional, string max 100 karakter untuk mencatat zona tanggung jawab operasional sarpras.
     * 7. 'department': Opsional, string max 100 karakter untuk fakultas/unit kerja sivitas.
     * 
     * SYNTAX:
     * - 'required'                 : Input tidak boleh kosong.
     * - 'email'                    : Harus berformat alamat email RFC standar.
     * - 'unique:users,email'       : Memeriksa langsung ke database agar tidak terjadi duplikasi email.
     * - 'not_in:admin,Admin'       : Filter keamanan eksplisit yang melarang keras nilai 'admin'.
     * - 'in:petugas,pengguna,...'  : Membatasi nilai input hanya pada daftar peran yang terotorisasi.
     * - 'nullable'                 : Input boleh dikosongkan jika tidak relevan dengan tipe akun.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Identitas nama lengkap pengguna/petugas
            'name'            => ['required', 'string', 'max:150'],

            // Alamat email unik untuk kredensial login dan korespondensi
            'email'           => ['required', 'string', 'email', 'max:150', 'unique:users,email'],

            // Kata sandi awal (opsional, minimal 8 karakter jika disediakan)
            'password'        => ['nullable', 'string', 'min:8'],

            // Validasi peran utama dengan isolasi keamanan anti-eskalasi hak akses
            'role'            => ['nullable', 'string', 'not_in:admin,Admin', 'in:petugas,pengguna,mahasiswa,dosen,staf'],

            // Kategori peran turunan sivitas akademika
            'role_type'       => ['nullable', 'string', 'not_in:admin,Admin', 'in:mahasiswa,dosen,staf'],

            // Nomor Induk Pegawai / Petugas (NIP)
            'nip'             => ['nullable', 'string', 'max:50'],

            // Pengenal alternatif atau NIM
            'identifier'      => ['nullable', 'string', 'max:50'],

            // Nomor kartu identitas resmi
            'identity_number' => ['nullable', 'string', 'max:50'],

            // Zona penugasan kerja teknisi sarpras (misal: Kampus A, Laboratorium Teknik)
            'zone'            => ['nullable', 'string', 'max:100'],
            'assignment_zone' => ['nullable', 'string', 'max:100'],

            // Departemen, Program Studi, atau Unit Kerja
            'department'      => ['nullable', 'string', 'max:100'],
        ];
    }

    /**
     * Menyediakan pesan kesalahan validasi kustom dalam Bahasa Indonesia yang informatif.
     * 
     * CARA KERJA & LOGIKA:
     * Menyajikan notifikasi kegagalan yang jelas jika Admin salah memasukkan data,
     * seperti email yang telah terdaftar atau upaya yang melanggar kebijakan keamanan peran.
     * 
     * SYNTAX:
     * - Mengembalikan array pemetaan pesan error untuk setiap pelanggaran aturan validasi.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required'      => 'Nama lengkap wajib diisi.',
            'name.max'           => 'Nama lengkap tidak boleh melebihi 150 karakter.',
            'email.required'     => 'Alamat email wajib diisi.',
            'email.email'        => 'Format alamat email tidak valid.',
            'email.max'          => 'Alamat email tidak boleh melebihi 150 karakter.',
            'email.unique'       => 'Alamat email ini sudah terdaftar di dalam sistem.',
            'password.min'       => 'Password minimal harus 8 karakter.',
            'role.not_in'        => 'Pembuatan akun dengan peran Admin dilarang demi keamanan sistem.',
            'role.in'            => 'Peran yang dipilih tidak valid.',
            'role_type.not_in'   => 'Pembuatan akun dengan peran Admin dilarang demi keamanan sistem.',
            'role_type.in'       => 'Kategori peran sivitas yang dipilih tidak valid.',
        ];
    }
}
