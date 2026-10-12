<?php

/**
 * ==============================================================================================
 * FORM REQUEST: VALIDASI PEMBARUAN DATA FASILITAS (KONSOL ADMIN - ADM-03)
 * ==============================================================================================
 * Berkas: app/Http/Requests/Admin/UpdateFacilityRequest.php
 * 
 * PERAN & FUNGSI UTAMA:
 * 1. Mengenkapsulasi validasi pembaruan data spesifikasi dan inventaris ruangan yang sudah ada.
 * 2. Mengatasi Dilema Validasi Unik (Unique Constraint Dilemma):
 *    Menggunakan `Rule::unique(...)->ignore($facilityId)` agar sistem tidak menganggap data unik
 *    (seperti `code` atau `name`) bertabrakan dengan record dirinya sendiri saat disimpan ulang.
 * 3. Menangani pembaruan gambar opsional: jika admin tidak mengunggah foto cover baru, berkas lama
 *    tetap dipertahankan tanpa memicu error validasi gambar.
 * 4. Menyelaraskan alias form input lama (`type` -> `category`, `location` -> `building`) pada `prepareForValidation`.
 * ==============================================================================================
 */

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFacilityRequest extends FormRequest
{
    /**
     * Menentukan apakah pengguna saat ini diizinkan memperbarui data fasilitas ini.
     * 
     * CARA KERJA & ALUR:
     * - Mengembalikan boolean `true`.
     * - Otorisasi peran Administrator ditangani secara ketat di level middleware route (`role:admin`).
     * 
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Normalisasi data input sebelum aturan validasi dijalankan.
     * 
     * CARA KERJA & ALUR:
     * - Memastikan kode ruangan selalu dalam format uppercase tanpa spasi samping.
     * - Memastikan kategori ruangan selalu dalam format lowercase seragam.
     * - Menangani kompatibilitas mundur jika input form menggunakan nama 'type' atau 'location'.
     */
    protected function prepareForValidation(): void
    {
        // 1. Kapitalisasi kode ruang:
        if ($this->has('code')) {
            $this->merge([
                'code' => strtoupper(trim((string) $this->code)),
            ]);
        }

        // 2. Huruf kecil seragam pada kategori:
        if ($this->has('category')) {
            $this->merge([
                'category' => strtolower(trim((string) $this->category)),
            ]);
        }

        // 3. Fallback alias 'type' ke 'category':
        if ($this->has('type') && !$this->has('category')) {
            $this->merge([
                'category' => strtolower(trim((string) $this->type)),
            ]);
        }

        // 4. Fallback alias 'location' ke 'building':
        if ($this->has('location') && !$this->has('building')) {
            $this->merge([
                'building' => trim((string) $this->location),
            ]);
        }
    }

    /**
     * Mendefinisikan aturan validasi integritas data pembaruan fasilitas.
     * 
     * CARA KERJA & ALUR:
     * - Mengambil parameter rute `{facility}` (bisa berupa ID integer atau instance model)
     *   atau fallback `input('id')`.
     * - Menambahkan klausa `->ignore($facilityId)` pada aturan unik SQL, menghasilkan kueri:
     *   `WHERE code = '...' AND id != '$facilityId'`
     *   sehingga ruangan dapat mempertahankan kodenya sendiri tanpa ditolak.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Mengambil ID fasilitas yang sedang diedit dari route binding atau form body:
        $facilityId = $this->route('facility') ?? $this->input('id');

        return [
            // 'name': Wajib diisi, unik di tabel facilities kecuali ID fasilitas saat ini:
            'name'           => ['required', 'string', 'max:150', Rule::unique('facilities', 'name')->ignore($facilityId)],

            // 'code': Wajib diisi, unik di tabel facilities kecuali ID fasilitas saat ini:
            'code'           => ['required', 'string', 'max:30', Rule::unique('facilities', 'code')->ignore($facilityId)],

            // 'category': Harus salah satu dari 5 enum baku sistem:
            'category'       => ['required', 'string', 'in:auditorium,lab,kelas,olahraga,rapat'],

            // 'building': Lokasi gedung wajib, maksimal 100 karakter:
            'building'       => ['required', 'string', 'max:100'],

            // 'floor_location': Lokasi lantai opsional, maksimal 50 karakter:
            'floor_location' => ['nullable', 'string', 'max:50'],

            // 'capacity': Kapasitas wajib integer minimal 1 orang:
            'capacity'       => ['required', 'integer', 'min:1'],

            // 'equipment': Daftar perlengkapan ruangan opsional:
            'equipment'      => ['nullable'],

            // 'description': Keterangan naratif opsional hingga 1000 karakter:
            'description'    => ['nullable', 'string', 'max:1000'],

            // 'cover_image': Opsional saat update (jika tidak upload, gambar lama tetap digunakan).
            // Jika diupload, wajib file gambar (jpg, jpeg, png) maksimal 2MB (2048 KB).
            'cover_image'    => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],

            // 'status': Status operasional yang valid:
            'status'         => ['nullable', 'string', 'in:aktif,dalam perbaikan,nonaktif'],
        ];
    }

    /**
     * Pesan kesalahan validasi dalam Bahasa Indonesia yang informatif.
     * 
     * CARA KERJA & ALUR:
     * Menjelaskan secara spesifik alasan kegagalan validasi form admin.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required'        => 'Nama fasilitas wajib diisi.',
            'name.max'             => 'Nama fasilitas maksimal 150 karakter.',
            'name.unique'          => 'Nama fasilitas sudah digunakan oleh ruangan lain.',
            'code.required'        => 'Kode ruang wajib diisi.',
            'code.max'             => 'Kode ruang maksimal 30 karakter.',
            'code.unique'          => 'Kode ruang sudah digunakan oleh ruangan lain.',
            'category.required'    => 'Kategori fasilitas wajib dipilih.',
            'category.in'          => 'Kategori fasilitas yang dipilih tidak valid.',
            'building.required'    => 'Lokasi gedung fasilitas wajib diisi.',
            'building.max'         => 'Nama gedung maksimal 100 karakter.',
            'floor_location.max'   => 'Keterangan lantai maksimal 50 karakter.',
            'capacity.required'    => 'Kapasitas fasilitas wajib diisi.',
            'capacity.integer'     => 'Kapasitas harus berupa angka bulat positif.',
            'capacity.min'         => 'Kapasitas fasilitas minimal 1 orang.',
            'description.max'      => 'Deskripsi fasilitas maksimal 1000 karakter.',
            'cover_image.image'    => 'Berkas foto cover harus berupa gambar.',
            'cover_image.mimes'    => 'Format gambar yang diizinkan hanya JPEG, JPG, atau PNG.',
            'cover_image.max'      => 'Ukuran foto cover tidak boleh melebihi 2MB (2048 KB).',
            'status.in'            => 'Status operasional yang dipilih tidak valid.',
        ];
    }
}

