<?php
/**
 * NAMA FILE    : ForceCancelReservationRequest.php
 * FUNGSI       : FormRequest validasi pembatalan darurat (override) reservasi oleh Petugas Sarpras
 * DESKRIPSI    : Memvalidasi ketersediaan dan ketuntasan alasan darurat pembatalan sepihak (alasan_batal) sebelum diolah oleh controller (PTG-03 / US-10).
 * CARA KERJA   : Mencegat HTTP Request, memeriksa otorisasi petugas, memvalidasi input alasan pembatalan minimal 10 karakter agar jelas dan transparan bagi pemohon.
 */

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ForceCancelReservationRequest extends FormRequest
{
    /**
     * FUNCTION/PROCEDURE : authorize()
     * KEGUNAAN           : Menentukan apakah pengguna saat ini berwenang mengeksekusi aksi pembatalan darurat ini.
     * CARA KERJA         : Memeriksa apakah pengguna telah terautentikasi dan memiliki peran petugas.
     */
    public function authorize(): bool
    {
        return auth()->check() && (auth()->user()->hasRole('petugas') || auth()->user()->role === 'petugas');
    }

    /**
     * FUNCTION/PROCEDURE : rules()
     * KEGUNAAN           : Menetapkan aturan validasi untuk payload pembatalan darurat sepihak.
     * CARA KERJA         : Memastikan alasan_batal (atau cancellation_reason) wajib diisi berupa teks terperinci (10 - 1000 karakter).
     */
    public function rules(): array
    {
        return [
            'alasan_batal' => ['required', 'string', 'min:10', 'max:1000'],
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
            'alasan_batal.required' => 'Alasan pembatalan darurat wajib diisi secara resmi.',
            'alasan_batal.min'      => 'Alasan pembatalan darurat minimal 10 karakter agar informatif bagi pemohon.',
            'alasan_batal.max'      => 'Alasan pembatalan darurat maksimal 1000 karakter.',
        ];
    }
}
