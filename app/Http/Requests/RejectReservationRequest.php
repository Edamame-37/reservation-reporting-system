<?php
/**
 * NAMA FILE    : RejectReservationRequest.php
 * FUNGSI       : FormRequest validasi penolakan permohonan reservasi oleh Petugas Sarpras
 * DESKRIPSI    : Memvalidasi ketersediaan dan format kolom alasan penolakan (rejection_reason) sebelum diolah oleh controller (PTG-02).
 * CARA KERJA   : Mencegat HTTP Request, memeriksa otorisasi petugas, memvalidasi input teks alasan penolakan minimal 5 karakter, dan mengembalikan pesan kesalahan jika kosong.
 */

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RejectReservationRequest extends FormRequest
{
    /**
     * FUNCTION/PROCEDURE : authorize()
     * KEGUNAAN           : Menentukan apakah pengguna saat ini berwenang mengeksekusi request penolakan ini.
     * CARA KERJA         : Memeriksa apakah pengguna telah terautentikasi dan memiliki peran petugas.
     */
    public function authorize(): bool
    {
        return auth()->check() && (auth()->user()->hasRole('petugas') || auth()->user()->role === 'petugas');
    }

    /**
     * FUNCTION/PROCEDURE : rules()
     * KEGUNAAN           : Menetapkan aturan validasi untuk payload request penolakan.
     * CARA KERJA         : Memastikan rejection_reason wajib diisi berupa teks string yang informatif (5 - 1000 karakter).
     */
    public function rules(): array
    {
        return [
            'rejection_reason' => ['required', 'string', 'min:5', 'max:1000'],
        ];
    }

    /**
     * FUNCTION/PROCEDURE : messages()
     * KEGUNAAN           : Menyediakan pesan umpan balik validasi kustom dalam Bahasa Indonesia.
     * CARA KERJA         : Mengembalikan pemetaan string pesan kesalahan untuk setiap kegagalan rule validasi.
     */
    public function messages(): array
    {
        return [
            'rejection_reason.required' => 'Alasan penolakan permohonan reservasi wajib diisi.',
            'rejection_reason.min'      => 'Alasan penolakan minimal berisi 5 karakter agar jelas bagi pemohon.',
            'rejection_reason.max'      => 'Alasan penolakan maksimal 1000 karakter.',
        ];
    }
}
