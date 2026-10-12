<?php

/**
 * ====================================================================================================
 * SISTEM INFORMASI RESERVASI & PELAPORAN FASILITAS KAMPUS (CAVA)
 * MODUL 6: KONSOL ADMIN - VERIFIKASI AKUN PENDING SIVITAS & MANAJEMEN PENGGUNA INTERNAL (ADM-01 & ADM-02)
 * BERKAS: app/Http/Requests/Admin/RejectUserRequest.php
 * ====================================================================================================
 * 
 * DESKRIPSI BERKAS:
 * Berkas Form Request ini bertugas memvalidasi data masukan (payload) saat Administrator menolak
 * pendaftaran mandiri calon pengguna sivitas akademika (Mahasiswa/Dosen/Staf) yang berstatus 'pending'.
 * 
 * PERAN & ARSITEKTUR DALAM SISTEM CAVA:
 * 1. Menjaga Integritas Data Penolakan: Memastikan Administrator selalu menyertakan alasan yang terstandarisasi
 *    (seperti kartu identitas tidak jelas, data tidak cocok, email tidak valid, dll.) sebelum status akun diubah
 *    menjadi 'rejected'.
 * 2. Akuntabilitas & Transparansi: Alasan penolakan dan catatan tambahan ini nantinya disimpan ke dalam kolom
 *    `rejection_reason` pada tabel `users`, sehingga pengguna yang bersangkutan dapat mengetahui alasan penolakan
 *    secara transparan saat mencoba login.
 * 3. Enkapsulasi Validasi: Memisahkan aturan validasi HTTP request dari Controller layer (AdminUserManagementController),
 *    mengikuti prinsip Single Responsibility Principle (SRP).
 * ====================================================================================================
 */

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class RejectUserRequest
 * 
 * Mengatur otorisasi dan mendefinisikan aturan validasi saat mengeksekusi penolakan akun sivitas.
 */
class RejectUserRequest extends FormRequest
{
    /**
     * Menentukan apakah pengguna saat ini berhak mengeksekusi request ini.
     * 
     * CARA KERJA & LOGIKA:
     * Mengembalikan nilai boolean 'true' karena otorisasi level rute telah diproteksi
     * secara ketat oleh middleware `role:admin` pada routes/web.php.
     * 
     * SYNTAX:
     * - return true; : Mengizinkan request melanjutkan ke tahap validasi rules().
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Mendefinisikan aturan validasi integritas payload penolakan akun pengguna.
     * 
     * CARA KERJA & LOGIKA:
     * 1. 'reason': Menjadi field wajib ('required'), berformat teks ('string'), dan nilainya
     *    harus termasuk ke dalam salah satu opsi valid ('in:invalid_ktm,mismatched_data,invalid_email,invalid_data,other').
     *    Hal ini mencegah input liar dan menjaga konsistensi audit penolakan.
     * 2. 'notes': Bersifat opsional ('nullable'), berformat teks ('string'), dengan batas maksimal 500 karakter ('max:500')
     *    untuk menampung rincian/catatan khusus dari Admin bagi pendaftar.
     * 
     * SYNTAX:
     * - 'required' : Field tidak boleh kosong atau null.
     * - 'string'   : Nilai input harus berupa tipe data string.
     * - 'in:...'   : Nilai harus cocok dengan daftar enumerasi yang diizinkan.
     * - 'nullable' : Field diperbolehkan bernilai null (kosong).
     * - 'max:500'  : Panjang string maksimal 500 karakter untuk mencegah eksploitasi buffer/database overflow.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Alasan utama penolakan wajib dipilih dari kategori standar CAVA
            'reason' => ['required', 'string', 'in:invalid_ktm,mismatched_data,invalid_email,invalid_data,other'],

            // Catatan atau instruksi perbaikan tambahan bagi sivitas akademika (opsional)
            'notes'  => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Menyediakan pesan kesalahan validasi kustom dalam Bahasa Indonesia yang ramah pengguna.
     * 
     * CARA KERJA & LOGIKA:
     * Jika payload yang dikirimkan melanggar salah satu aturan di rules(), Laravel akan mengembalikan
     * respon error HTTP 422 (Unprocessable Entity) atau me-redirect kembali ke form dengan membawa
     * pesan kesalahan spesifik yang didefinisikan di bawah ini.
     * 
     * SYNTAX:
     * - Mengembalikan array pasangan key 'atribut.aturan' => 'pesan kesalahan lokal'.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'reason.required' => 'Alasan penolakan verifikasi wajib dipilih.',
            'reason.in'       => 'Pilihan alasan penolakan tidak valid.',
            'notes.max'       => 'Catatan tambahan tidak boleh melebihi 500 karakter.',
        ];
    }
}
