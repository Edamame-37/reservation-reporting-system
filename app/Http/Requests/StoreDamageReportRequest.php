<?php
/**
 * NAMA FILE    : StoreDamageReportRequest.php
 * FUNGSI       : Form Request Validasi Pengaduan Kerusakan Fasilitas Kampus (USR-04)
 * DESKRIPSI    : Memvalidasi integritas data laporan kerusakan: keberadaan ID fasilitas aktif, kategori, deskripsi detail (min 10 karakter), dan batasan berkas foto bukti (maksimal 2 MB - JPG/PNG).
 * CARA KERJA   : Mencegat request sebelum masuk ke ReportController@store, me-redirect kembali dengan error bag dan old input jika validasi gagal.
 */

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDamageReportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'facility_id'      => ['required', 'integer', 'exists:facilities,id'],
            'category'         => ['required', 'string', 'max:100'],
            'description'      => ['required', 'string', 'min:10', 'max:1000'],
            'attachment_photo' => ['required', 'image', 'mimes:jpeg,png,jpg', 'max:2048'], // 2048 KB = 2 MB
        ];
    }

    /**
     * Get the custom validation error messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'facility_id.required'      => 'Silakan pilih fasilitas atau ruangan yang mengalami kerusakan.',
            'facility_id.exists'        => 'Fasilitas yang dipilih tidak terdaftar di dalam sistem.',
            'category.required'         => 'Kategori kerusakan sarana wajib dipilih.',
            'description.required'      => 'Deskripsi masalah kerusakan wajib diisi secara rinci.',
            'description.min'           => 'Deskripsi masalah minimal harus berisi 10 karakter.',
            'description.max'           => 'Deskripsi masalah tidak boleh melebihi 1.000 karakter.',
            'attachment_photo.required' => 'Foto bukti kerusakan wajib dilampirkan.',
            'attachment_photo.image'    => 'Berkas lampiran harus berupa gambar valid.',
            'attachment_photo.mimes'    => 'Format gambar yang didukung hanya JPG, JPEG, dan PNG.',
            'attachment_photo.max'      => 'Ukuran foto bukti tidak boleh melebihi 2 MB (2.048 KB).',
        ];
    }
}
