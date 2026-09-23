<?php
/**
 * NAMA FILE    : StoreReservationRequest.php
 * FUNGSI       : Validasi Request Form Pengajuan Reservasi Ruang Kampus
 * DESKRIPSI    : Memvalidasi ketersediaan input facility_id, tanggal minimal hari ini, slot waktu interval 30 menit (07:00-20:00 WIB), dan deskripsi tujuan.
 * CARA KERJA   : Memeriksa payload HTTP POST sebelum diteruskan ke ReservationController@store dan menyajikan pesan kegagalan ramah berbahasa Indonesia jika input tidak valid.
 */

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReservationRequest extends FormRequest
{
    /**
     * Menentukan apakah pengguna memiliki otorisasi untuk melakukan permintaan ini.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi yang diterapkan pada payload pengajuan reservasi.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'facility_id'      => ['required', 'integer', 'exists:facilities,id'],
            'reservation_date' => ['required', 'date', 'after_or_equal:today'],
            'start_time'       => ['required', 'date_format:H:i', 'regex:/^(0[7-9]|1[0-9]|20):(00|30)$/'],
            'end_time'         => ['required', 'date_format:H:i', 'after:start_time', 'regex:/^(0[7-9]|1[0-9]|20):(00|30)$/'],
            'purpose'          => ['required', 'string', 'min:10', 'max:500'],
        ];
    }

    /**
     * Pesan kesalahan khusus berbahasa Indonesia untuk setiap kegagalan validasi.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'facility_id.required'         => 'Fasilitas atau ruangan wajib dipilih.',
            'facility_id.exists'           => 'Fasilitas yang dipilih tidak terdaftar pada sistem.',
            'reservation_date.required'    => 'Tanggal pelaksanaan kegiatan wajib diisi.',
            'reservation_date.date'        => 'Format tanggal pelaksanaan tidak valid.',
            'reservation_date.after_or_equal' => 'Tanggal pelaksanaan kegiatan tidak boleh di masa lampau.',
            'start_time.required'          => 'Jam mulai peminjaman wajib ditentukan.',
            'start_time.date_format'       => 'Format jam mulai harus berformat JJ:MM (contoh: 08:00).',
            'start_time.regex'             => 'Jam mulai harus berada di rentang 07:00 - 20:00 WIB dengan interval 30 menit (menit :00 atau :30).',
            'end_time.required'            => 'Jam selesai peminjaman wajib ditentukan.',
            'end_time.date_format'         => 'Format jam selesai harus berformat JJ:MM (contoh: 10:00).',
            'end_time.after'               => 'Jam selesai harus lebih akhir dari jam mulai kegiatan.',
            'end_time.regex'               => 'Jam selesai harus berada di rentang 07:00 - 20:00 WIB dengan interval 30 menit (menit :00 atau :30).',
            'purpose.required'             => 'Tujuan penggunaan fasilitas wajib diisi.',
            'purpose.min'                  => 'Deskripsi tujuan minimal berisi 10 karakter agar dapat dievaluasi petugas.',
            'purpose.max'                  => 'Deskripsi tujuan maksimal 500 karakter.',
        ];
    }
}
