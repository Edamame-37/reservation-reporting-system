<?php
/**
 * NAMA FILE    : UpdateFacilityRequest.php
 * FUNGSI       : Form Request validasi data pembaruan master fasilitas (Konsol Admin)
 * DESKRIPSI    : Memvalidasi integritas payload perombakan spesifikasi ruang kampus (ADM-03 / US-16) dengan pengecualian ID aktif.
 * CARA KERJA   : Memastikan otorisasi request, memeriksa keunikan nama dan kode ruang dengan mengabaikan ID saat ini, dan mengecek foto cover pengganti jika ada.
 */

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFacilityRequest extends FormRequest
{
    /**
     * FUNCTION/PROCEDURE : authorize()
     * KEGUNAAN           : Menentukan apakah pengguna yang sedang login berhak mengeksekusi request ini.
     * CARA KERJA         : Mengembalikan nilai true (dapat diintegrasikan dengan otorisasi Gate/Role Admin).
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * FUNCTION/PROCEDURE : prepareForValidation()
     * KEGUNAAN           : Menormalkan input data sebelum aturan validasi dievaluasi.
     * CARA KERJA         : Membersihkan spasi pada kode ruang dan memastikan kategori dalam format huruf kecil.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('code')) {
            $this->merge([
                'code' => strtoupper(trim((string) $this->code)),
            ]);
        }

        if ($this->has('category')) {
            $this->merge([
                'category' => strtolower(trim((string) $this->category)),
            ]);
        }

        if ($this->has('type') && !$this->has('category')) {
            $this->merge([
                'category' => strtolower(trim((string) $this->type)),
            ]);
        }

        if ($this->has('location') && !$this->has('building')) {
            $this->merge([
                'building' => trim((string) $this->location),
            ]);
        }
    }

    /**
     * FUNCTION/PROCEDURE : rules()
     * KEGUNAAN           : Mendefinisikan aturan validasi integritas data pembaruan fasilitas.
     * CARA KERJA         : Memeriksa field yang diperbarui dengan Rule::unique()->ignore($facilityId) agar tidak konflik dengan dirinya sendiri.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $facilityId = $this->route('facility') ?? $this->input('id');

        return [
            'name'           => ['required', 'string', 'max:150', Rule::unique('facilities', 'name')->ignore($facilityId)],
            'code'           => ['required', 'string', 'max:30', Rule::unique('facilities', 'code')->ignore($facilityId)],
            'category'       => ['required', 'string', 'in:auditorium,lab,kelas,olahraga,rapat'],
            'building'       => ['required', 'string', 'max:100'],
            'floor_location' => ['nullable', 'string', 'max:50'],
            'capacity'       => ['required', 'integer', 'min:1'],
            'equipment'      => ['nullable'],
            'description'    => ['nullable', 'string', 'max:1000'],
            'cover_image'    => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'status'         => ['nullable', 'string', 'in:aktif,dalam perbaikan,nonaktif'],
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
