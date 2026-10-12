<?php

/**
 * ====================================================================================================
 * NAMA FILE    : StoreReservationRequest.php
 * LOKASI       : app/Http/Requests/StoreReservationRequest.php
 * FUNGSI       : Form Request Validation untuk Permohonan Reservasi Ruangan Mandiri (USR-01)
 * MODUL CAVA   : 10. Portal Pengguna: Formulir Pengajuan Reservasi Ruangan Mandiri (USR-01)
 * ====================================================================================================
 * 
 * ARSITEKTUR & CARA KERJA SISTEM:
 * 1. Pemisahan Logika Validasi (Separation of Concerns):
 *    - Class ini bertindak sebagai gerbang filter validasi payload HTTP POST sebelum data mencapai
 *      controller (`ReservationController@store`).
 *    - Jika payload melanggar salah satu aturan validasi, Laravel secara otomatis membatalkan eksekusi
 *      controller dan mengembalikan HTTP Redirect 302 ke halaman sebelumnya dengan membawa:
 *      * `$errors`: Menampung pesan kegagalan spesifik berbahasa Indonesia.
 *      * Input lama (`withInput()`): Menjaga nilai input form agar pengguna tidak perlu mengetik ulang.
 * 
 * 2. Aturan Jam Operasional & Batasan Slot 30 Menit:
 *    - Jam operasional kampus ditetapkan pada pukul **07:00 – 20:00 WIB**.
 *    - Menggunakan ekspresi reguler (Regex) `^(0[7-9]|1[0-9]|20):(00|30)$`:
 *      * `(0[7-9]|1[0-9]|20)`: Membatasi jam antara 07 hingga 20.
 *      * `(00|30)`           : Memastikan menit hanya bernilai :00 atau :30 (standar slot 30 menit kampus).
 *    - Aturan `after:start_time` memastikan jam selesai tidak sama atau lebih awal dari jam mulai.
 * 
 * ====================================================================================================
 */

namespace App\Http\Requests;

// Mengimpor Base FormRequest dari framework Laravel
use Illuminate\Foundation\Http\FormRequest;

/**
 * Class StoreReservationRequest
 * 
 * Menangani validasi dan sanitasi input form pengajuan peminjaman ruangan oleh sivitas.
 */
class StoreReservationRequest extends FormRequest
{
    /**
     * Menentukan apakah pengguna yang sedang login diizinkan melakukan permintaan ini.
     * 
     * CARA KERJA:
     * - Mengembalikan `true` karena otorisasi peran pengguna telah difilter lebih awal
     *   oleh route middleware `['auth', 'role:pengguna']`.
     * 
     * @return bool Nilai true jika request diizinkan diproses
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Mendefinisikan kumpulan aturan validasi yang diterapkan pada payload pengajuan reservasi.
     * 
     * CARA KERJA:
     * - Setiap field dipetakan ke array aturan validasi Laravel.
     * - Evaluasi dieksekusi secara berurutan; jika salah satu gagal, validasi berhenti untuk field tersebut.
     * 
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // facility_id: Wajib diisi, berupa angka integer, dan harus eksis pada kolom 'id' di tabel 'facilities'
            'facility_id' => [
                'required',
                'integer',
                'exists:facilities,id',
            ],

            // reservation_date: Wajib diisi, berformat tanggal valid, dan tidak boleh tanggal masa lalu (minimal hari ini)
            'reservation_date' => [
                'required',
                'date',
                'after_or_equal:today',
            ],

            // start_time: Wajib diisi, format waktu JJ:MM, rentang 07:00-20:00 WIB, kelipatan 30 menit
            'start_time' => [
                'required',
                'date_format:H:i',
                'regex:/^(0[7-9]|1[0-9]|20):(00|30)$/',
            ],

            // end_time: Wajib diisi, format JJ:MM, harus setelah start_time, rentang 07:00-20:00 WIB, kelipatan 30 menit
            'end_time' => [
                'required',
                'date_format:H:i',
                'after:start_time',
                'regex:/^(0[7-9]|1[0-9]|20):(00|30)$/',
            ],

            // purpose: Wajib diisi berupa teks string, minimal 10 karakter (agar informatif bagi petugas), maksimal 500 karakter
            'purpose' => [
                'required',
                'string',
                'min:10',
                'max:500',
            ],

            // participants_count: Opsional (nullable), berupa angka bulat antara 1 hingga 2000 orang
            'participants_count' => [
                'nullable',
                'integer',
                'min:1',
                'max:2000',
            ],
        ];
    }

    /**
     * Kamus pesan kesalahan kustom berbahasa Indonesia untuk menyajikan pengalaman pengguna yang jelas dan bersahabat.
     * 
     * CARA KERJA:
     * - Memetakan kombinasi nama field dan nama rule (misalnya 'reservation_date.after_or_equal')
     *   ke teks pesan yang ditampilkan pada alert dan helper error di tampilan formulir.
     * 
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            // Pesan validasi pemilihan fasilitas
            'facility_id.required'            => 'Fasilitas atau ruangan wajib dipilih.',
            'facility_id.integer'             => 'Identitas fasilitas harus berupa format numerik valid.',
            'facility_id.exists'              => 'Fasilitas yang dipilih tidak terdaftar pada sistem atau telah dinonaktifkan.',

            // Pesan validasi tanggal permohonan
            'reservation_date.required'       => 'Tanggal pelaksanaan kegiatan wajib diisi.',
            'reservation_date.date'           => 'Format tanggal pelaksanaan tidak valid.',
            'reservation_date.after_or_equal' => 'Tanggal pelaksanaan kegiatan tidak boleh di masa lampau.',

            // Pesan validasi jam mulai
            'start_time.required'             => 'Jam mulai peminjaman wajib ditentukan.',
            'start_time.date_format'          => 'Format jam mulai harus berformat JJ:MM (contoh: 08:00).',
            'start_time.regex'                => 'Jam mulai harus berada di rentang 07:00 - 20:00 WIB dengan interval 30 menit (menit :00 atau :30).',

            // Pesan validasi jam selesai
            'end_time.required'               => 'Jam selesai peminjaman wajib ditentukan.',
            'end_time.date_format'            => 'Format jam selesai harus berformat JJ:MM (contoh: 10:00).',
            'end_time.after'                  => 'Jam selesai harus lebih akhir dari jam mulai kegiatan.',
            'end_time.regex'                  => 'Jam selesai harus berada di rentang 07:00 - 20:00 WIB dengan interval 30 menit (menit :00 atau :30).',

            // Pesan validasi tujuan kegiatan
            'purpose.required'                => 'Tujuan penggunaan fasilitas wajib diisi.',
            'purpose.string'                  => 'Tujuan penggunaan harus berupa format teks yang valid.',
            'purpose.min'                     => 'Deskripsi tujuan minimal berisi 10 karakter agar dapat dievaluasi petugas.',
            'purpose.max'                     => 'Deskripsi tujuan maksimal 500 karakter.',

            // Pesan validasi jumlah peserta
            'participants_count.integer'      => 'Estimasi jumlah peserta harus berupa angka bulat.',
            'participants_count.min'          => 'Estimasi jumlah peserta minimal 1 orang.',
            'participants_count.max'          => 'Estimasi jumlah peserta maksimal 2000 orang.',
        ];
    }
}
