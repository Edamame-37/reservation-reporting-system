<?php
/**
 * NAMA FILE    : StoreInternalUserRequest.php
 * FUNGSI       : Form Request validasi pendaftaran akun internal oleh Super Admin (ADM-02 / US-13 & US-14)
 * DESKRIPSI    : Memvalidasi integritas data pembuatan akun petugas dan pengguna langsung serta memproteksi agar tidak ada pembuatan akun role admin sembarangan.
 * CARA KERJA   : Memeriksa field 'name', 'email' (harus unik), 'password', 'role' (dilarang admin), 'identity_number', dan 'assignment_zone' sebelum dialihkan ke Controller.
 */

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreInternalUserRequest extends FormRequest
{
    /**
     * FUNCTION/PROCEDURE : authorize()
     * KEGUNAAN           : Menentukan hak akses eksekusi request pendaftaran internal.
     * CARA KERJA         : Mengembalikan true (otorisasi dikelola via middleware auth & role admin).
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * FUNCTION/PROCEDURE : rules()
     * KEGUNAAN           : Mendefinisikan aturan ketat validasi data akun internal (Zero Trust Policy).
     * CARA KERJA         : Memeriksa kelayakan isian data, keunikan email di tabel users, serta memastikan role admin dilarang mutlak.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'            => ['required', 'string', 'max:150'],
            'email'           => ['required', 'string', 'email', 'max:150', 'unique:users,email'],
            'password'        => ['nullable', 'string', 'min:8'],
            'role'            => ['nullable', 'string', 'not_in:admin,Admin', 'in:petugas,pengguna,mahasiswa,dosen,staf'],
            'role_type'       => ['nullable', 'string', 'not_in:admin,Admin', 'in:mahasiswa,dosen,staf'],
            'nip'             => ['nullable', 'string', 'max:50'],
            'identifier'      => ['nullable', 'string', 'max:50'],
            'identity_number' => ['nullable', 'string', 'max:50'],
            'zone'            => ['nullable', 'string', 'max:100'],
            'assignment_zone' => ['nullable', 'string', 'max:100'],
            'department'      => ['nullable', 'string', 'max:100'],
        ];
    }

    /**
     * FUNCTION/PROCEDURE : messages()
     * KEGUNAAN           : Menyediakan umpan balik pesan validasi dalam Bahasa Indonesia yang informatif.
     * CARA KERJA         : Mengembalikan pemetaan pesan kesalahan sesuai pelanggaran aturan validasi.
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
