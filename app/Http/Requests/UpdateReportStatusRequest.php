<?php
/**
 * NAMA FILE    : UpdateReportStatusRequest.php
 * FUNGSI       : FormRequest validasi pembaruan status dan catatan resolusi tiket laporan kerusakan
 * DESKRIPSI    : Memvalidasi transisi status tiket keluhan (baru/diproses/selesai/ditolak) serta mewajibkan pengisian catatan resolusi jika status diubah menjadi selesai (PTG-04 / US-11).
 * CARA KERJA   : Mencegat HTTP Request, memeriksa otorisasi petugas, menerapkan rule in:baru,diproses,selesai,ditolak dan required_if:status,selesai untuk catatan resolusi.
 */

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReportStatusRequest extends FormRequest
{
    /**
     * FUNCTION/PROCEDURE : authorize()
     * KEGUNAAN           : Menentukan apakah pengguna saat ini berwenang mengeksekusi pembaruan status tiket keluhan ini.
     * CARA KERJA         : Memeriksa apakah pengguna telah terautentikasi dan memiliki peran petugas.
     */
    public function authorize(): bool
    {
        return auth()->check() && (auth()->user()->hasRole('petugas') || auth()->user()->role === 'petugas');
    }

    /**
     * Prepare the data for validation.
     * Mendukung alias status_laporan atau status dari form input.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('status_laporan') && ! $this->has('status')) {
            $this->merge(['status' => $this->input('status_laporan')]);
        }

        if ($this->has('catatan_resolusi') && ! $this->has('resolution_note')) {
            $this->merge(['resolution_note' => $this->input('catatan_resolusi')]);
        }
    }

    /**
     * FUNCTION/PROCEDURE : rules()
     * KEGUNAAN           : Menetapkan aturan validasi untuk status tiket dan catatan penyelesaian perbaikan.
     * CARA KERJA         : Memastikan status berada dalam enum yang valid, serta memastikan catatan resolusi wajib diisi jika status diubah menjadi selesai atau ditolak.
     */
    public function rules(): array
    {
        return [
            'status'             => ['required', 'string', 'in:baru,diproses,selesai,ditolak'],
            'catatan_resolusi'   => ['nullable', 'string', 'max:2000', 'required_if:status,selesai'],
            'resolution_note'    => ['nullable', 'string', 'max:2000'],
            'is_facility_locked' => ['nullable', 'boolean'],
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
            'status.required'             => 'Pilihan status tiket wajib ditentukan.',
            'status.in'                   => 'Status tiket tidak valid. Pilihan yang diizinkan: baru, diproses, selesai, atau ditolak.',
            'catatan_resolusi.required_if' => 'Catatan resolusi/tindakan teknisi wajib diisi saat menandai laporan kerusakan telah selesai ditangani.',
            'catatan_resolusi.max'        => 'Catatan resolusi maksimal 2000 karakter.',
        ];
    }
}
