<?php
/**
 * NAMA FILE    : RejectUserRequest.php
 * FUNGSI       : Form Request validasi data penolakan verifikasi akun pengguna baru
 * DESKRIPSI    : Memvalidasi payload alasan dan catatan penolakan pendaftaran mandiri sivitas oleh Super Admin.
 * CARA KERJA   : Memeriksa otorisasi request, memastikan field 'reason' dan 'notes' sesuai batasan yang diizinkan sebelum diteruskan ke Controller.
 */

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class RejectUserRequest extends FormRequest
{
    /**
     * FUNCTION/PROCEDURE : authorize()
     * KEGUNAAN           : Menentukan apakah pengguna saat ini berhak mengeksekusi request ini.
     * CARA KERJA         : Mengembalikan nilai true (dapat diintegrasikan dengan otorisasi Gate/Role Admin).
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * FUNCTION/PROCEDURE : rules()
     * KEGUNAAN           : Mendefinisikan aturan validasi integritas payload penolakan akun.
     * CARA KERJA         : Memeriksa bahwa 'reason' wajib diisi dan sesuai daftar opsi valid, serta 'notes' opsional dengan batas panjang.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'in:invalid_ktm,mismatched_data,invalid_email,other'],
            'notes'  => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * FUNCTION/PROCEDURE : messages()
     * KEGUNAAN           : Menyediakan pesan kesalahan validasi dalam Bahasa Indonesia yang informatif.
     * CARA KERJA         : Mengembalikan pemetaan pesan kegagalan sesuai atribut aturan validasi.
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
