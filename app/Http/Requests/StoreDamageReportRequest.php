<?php

/**
 * ====================================================================================================
 * NAMA FILE    : StoreDamageReportRequest.php
 * LOKASI       : app/Http/Requests/StoreDamageReportRequest.php
 * FUNGSI       : Form Request Validation Pengaduan Kerusakan Fasilitas Kampus (USR-04)
 * MODUL CAVA   : 12. Portal Pengguna: Pelaporan Kerusakan Fasilitas & Pelacakan Tiket (USR-04 & USR-05)
 * ====================================================================================================
 * 
 * ARSITEKTUR & CARA KERJA SISTEM:
 * 1. Sanitasi & Validasi Input Pelaporan:
 *    - Memvalidasi seluruh payload formulir pengaduan kerusakan sarpras sebelum mencapai `ReportController@store`.
 *    - Menjamin keamanan upload file: hanya berkas gambar (JPG, JPEG, PNG) dengan batas maksimal 2MB (2048 KB)
 *      yang diizinkan masuk ke server guna mencegah celah keamanan upload berkas berbahaya (RCE prevention).
 * 
 * 2. Aturan Deskripsi Rinci:
 *    - `description`: Wajib diisi minimal 10 karakter dan maksimal 1000 karakter agar kronologi kerusakan
 *      cukup jelas bagi teknisi sarpras untuk melakukan asesmen awal.
 * 
 * ====================================================================================================
 */

namespace App\Http\Requests;

// Mengimpor Base Class FormRequest dari framework Laravel
use Illuminate\Foundation\Http\FormRequest;

/**
 * Class StoreDamageReportRequest
 * 
 * Mengendalikan validasi server-side permohonan pelaporan kerusakan fasilitas fisik kampus.
 */
class StoreDamageReportRequest extends FormRequest
{
    /**
     * Menentukan apakah pengguna berwenang membuat permohonan ini.
     * 
     * @return bool True jika diizinkan (otorisasi role ditangani oleh middleware web.php)
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Mendefinisikan aturan validasi yang diterapkan pada payload pengaduan kerusakan.
     * 
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // facility_id: Wajib diisi, berupa integer, dan harus eksis pada tabel 'facilities'
            'facility_id' => [
                'required',
                'integer',
                'exists:facilities,id',
            ],

            // category: Opsional (nullable), string maksimal 100 karakter (contoh: AC, Kelistrikan, Furnitur)
            'category' => [
                'nullable',
                'string',
                'max:100',
            ],

            // description: Wajib diisi, string minimal 10 karakter, maksimal 1000 karakter
            'description' => [
                'required',
                'string',
                'min:10',
                'max:1000',
            ],

            // attachment_photo: Wajib dilampirkan, berkas gambar, format jpeg/png/jpg, maksimal 2048 KB (2MB)
            'attachment_photo' => [
                'required',
                'image',
                'mimes:jpeg,png,jpg',
                'max:2048',
            ],
        ];
    }

    /**
     * Kamus pesan kesalahan kustom berbahasa Indonesia untuk antarmuka pengguna yang ramah.
     * 
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            // Pesan validasi fasilitas
            'facility_id.required'      => 'Silakan pilih fasilitas atau ruangan yang mengalami kerusakan.',
            'facility_id.integer'       => 'Identitas fasilitas harus berupa format numerik valid.',
            'facility_id.exists'        => 'Fasilitas yang dipilih tidak terdaftar di dalam sistem.',

            // Pesan validasi deskripsi
            'description.required'      => 'Deskripsi masalah kerusakan wajib diisi secara rinci.',
            'description.min'           => 'Deskripsi masalah minimal harus berisi 10 karakter.',
            'description.max'           => 'Deskripsi masalah tidak boleh melebihi 1.000 karakter.',

            // Pesan validasi lampiran foto bukti
            'attachment_photo.required' => 'Foto bukti kerusakan wajib dilampirkan.',
            'attachment_photo.image'    => 'Berkas lampiran harus berupa gambar valid.',
            'attachment_photo.mimes'    => 'Format gambar yang didukung hanya JPG, JPEG, dan PNG.',
            'attachment_photo.max'      => 'Ukuran foto bukti tidak boleh melebihi 2 MB (2.048 KB).',
        ];
    }
}
