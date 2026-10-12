<?php

/**
 * ==============================================================================================
 * FORM REQUEST: VALIDASI PENAMBAHAN FASILITAS BARU (KONSOL ADMIN - ADM-03)
 * ==============================================================================================
 * Berkas: app/Http/Requests/Admin/StoreFacilityRequest.php
 * 
 * PERAN & FUNGSI UTAMA:
 * 1. Mengenkapsulasi logika otorisasi dan validasi input data pembuatan fasilitas baru
 *    ke dalam kelas terpisah (mematuhi Single Responsibility Principle).
 * 2. Mencegah data kotor atau berbahaya masuk ke database sebelum mencapai `FacilityController::store`.
 * 3. Menjalankan normalisasi input otomatis pada lifecycle hook `prepareForValidation`
 *    (kapitalisasi kode ruang, pembersihan spasi, dan penyelarasan alias input form).
 * 4. Memastikan keunikan `code` dan `name` di tabel `facilities` guna mencegah konflik integritas data.
 * 5. Menolak berkas gambar cover yang tidak aman atau berukuran lebih dari 2MB (2048 KB).
 * ==============================================================================================
 */

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreFacilityRequest extends FormRequest
{
    /**
     * Menentukan apakah pengguna saat ini diizinkan mengirimkan request ini.
     * 
     * CARA KERJA & ALUR:
     * - Mengembalikan boolean `true` atau `false`.
     * - Karena rute telah diproteksi middleware `role:admin` pada `routes/web.php`,
     *   metode ini mengembalikan `true`. Jika `false`, Laravel melempar HTTP 403 Forbidden.
     * 
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Mempersiapkan dan membersihkan data input sebelum aturan validasi dijalankan.
     * 
     * CARA KERJA & ALUR:
     * - Method ini dipanggil otomatis oleh Laravel sebelum method `rules()`.
     * - Menormalkan kode ruangan menjadi huruf kapital seragam (contoh: 'aud-h01' -> 'AUD-H01').
     * - Menormalkan kategori menjadi huruf kecil ('AUDITORIUM' -> 'auditorium').
     * - Menangani fallback alias input form jika frontend mengirim 'type' atau 'location'.
     */
    protected function prepareForValidation(): void
    {
        // 1. Kapitalisasi kode ruang dan pembersihan whitespace:
        if ($this->has('code')) {
            $this->merge([
                'code' => strtoupper(trim((string) $this->code)),
            ]);
        }

        // 2. Harmonisasi nilai kategori huruf kecil:
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
     * Mendefinisikan aturan validasi yang wajib dipenuhi oleh data request.
     * 
     * CARA KERJA & ALUR:
     * - Jika ada satu saja aturan yang gagal, siklus HTTP langsung dihentikan:
     *   * Untuk request HTML form biasa: Otomatis redirect back dengan flash session errors `$errors`.
     *   * Untuk request AJAX/JSON: Mengembalikan response HTTP 422 Unprocessable Content.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // 'name': Wajib diisi string maksimal 150 karakter, tidak boleh duplikat di tabel facilities.
            'name'           => ['required', 'string', 'max:150', 'unique:facilities,name'],

            // 'code': Wajib diisi string maksimal 30 karakter, unik di kolom 'code' tabel facilities.
            'code'           => ['required', 'string', 'max:30', 'unique:facilities,code'],

            // 'category': Wajib salah satu dari 5 enum resmi sistem CAVA.
            'category'       => ['required', 'string', 'in:auditorium,lab,kelas,olahraga,rapat'],

            // 'building': Nama gedung wajib diisi, maksimal 100 karakter.
            'building'       => ['required', 'string', 'max:100'],

            // 'floor_location': Keterangan lantai bersifat opsional, maksimal 50 karakter.
            'floor_location' => ['nullable', 'string', 'max:50'],

            // 'capacity': Wajib bilangan bulat positif minimal 1 orang.
            'capacity'       => ['required', 'integer', 'min:1'],

            // 'equipment': Boleh null atau berupa array/string perlengkapan.
            'equipment'      => ['nullable'],

            // 'description': Keterangan naratif opsional hingga 1000 karakter.
            'description'    => ['nullable', 'string', 'max:1000'],

            // 'cover_image': Berkas upload opsional, harus file gambar asli (jpg, jpeg, png) maksimal 2048 KB (2MB).
            'cover_image'    => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],

            // 'status': Opsional, jika diisi harus sesuai status operasional yang valid.
            'status'         => ['nullable', 'string', 'in:aktif,dalam perbaikan,nonaktif'],
        ];
    }

    /**
     * Kustomisasi pesan kesalahan validasi dalam Bahasa Indonesia yang ramah pengguna.
     * 
     * CARA KERJA & ALUR:
     * Memetakan pasangan "nama_field.nama_aturan" => "Pesan galat yang ditampilkan ke pengguna".
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required'        => 'Nama fasilitas wajib diisi.',
            'name.max'             => 'Nama fasilitas maksimal 150 karakter.',
            'name.unique'          => 'Nama fasilitas sudah terdaftar, gunakan nama yang berbeda.',
            'code.required'        => 'Kode ruang wajib diisi.',
            'code.max'             => 'Kode ruang maksimal 30 karakter.',
            'code.unique'          => 'Kode ruang sudah digunakan oleh fasilitas lain.',
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

