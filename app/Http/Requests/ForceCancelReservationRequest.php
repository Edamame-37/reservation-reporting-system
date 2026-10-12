<?php
/**
 * ====================================================================================================
 * SISTEM INFORMASI RESERVASI & PELAPORAN FASILITAS KAMPUS (CAVA)
 * MODUL 15: OPERASIONAL PETUGAS SARPRAS: PERSETUJUAN, PENOLAKAN, & PEMBATALAN DARURAT (PTG-02 & PTG-03)
 * BERKAS: app/Http/Requests/ForceCancelReservationRequest.php
 * ====================================================================================================
 *
 * DESKRIPSI BERKAS:
 * Kelas Form Request Validation untuk memvalidasi alasan pembatalan sepihak / darurat (Override Privilege)
 * atas reservasi yang sebelumnya telah disetujui (Approved) oleh pihak Petugas Sarpras Kampus (PTG-03 / US-10).
 *
 * PERAN ARSITEKTURAL DALAM SISTEM CAVA:
 * 1. Otorisasi Hak Override Khusus:
 *    Hanya staf operasional dengan peran 'petugas' yang diizinkan membatalkan reservasi yang sudah disetujui.
 * 2. Transparansi & Auditabilitas Pembatalan Darurat:
 *    Mewajibkan pencatatan alasan darurat (`alasan_batal`) dengan panjang minimal 10 karakter agar ada bukti
 *    justifikasi resmi (misalnya: pipa air bocor, pemadaman listrik darurat, instruksi pimpinan universitas).
 * 3. Notifikasi Kesalahan Berbahasa Indonesia:
 *    Menyediakan pesan peringatan kustom jika petugas tidak mengisi alasan dengan memadai.
 * ====================================================================================================
 */

namespace App\Http\Requests;

// Import Base FormRequest dari framework Laravel
use Illuminate\Foundation\Http\FormRequest;

/**
 * Class ForceCancelReservationRequest
 *
 * Menangani otorisasi dan validasi input alasan pembatalan darurat reservasi yang telah disetujui.
 */
class ForceCancelReservationRequest extends FormRequest
{
    /**
     * METHOD: authorize()
     * ------------------------------------------------------------------------------------------------
     * FUNGSI / KEGUNAAN:
     * Memverifikasi apakah pengguna memiliki hak override darurat sebagai Petugas Sarpras.
     *
     * RETURN VALUE:
     * bool : `true` jika pengguna sah dan berhak membatalkan darurat, `false` jika tidak berwenang.
     *
     * CARA KERJA:
     * 1. `auth()->check()`: Memastikan status login pengguna.
     * 2. `auth()->user()->hasRole('petugas') || auth()->user()->role === 'petugas'`: Mengonfirmasi peran petugas sarpras.
     */
    public function authorize(): bool
    {
        return auth()->check() && (auth()->user()->hasRole('petugas') || auth()->user()->role === 'petugas');
    }

    /**
     * METHOD: rules()
     * ------------------------------------------------------------------------------------------------
     * FUNGSI / KEGUNAAN:
     * Menetapkan aturan validasi untuk kolom alasan pembatalan darurat sepihak.
     *
     * RETURN VALUE:
     * array : Daftar aturan validasi:
     * - 'required' : Alasan pembatalan darurat wajib disertakan dalam payload request.
     * - 'string'   : Nilai harus berupa tipe string teks.
     * - 'min:10'   : Minimal 10 karakter untuk menjamin kejelasan kronologi darurat.
     * - 'max:1000' : Maksimal 1000 karakter untuk mencegah input berlebihan.
     */
    public function rules(): array
    {
        return [
            'alasan_batal' => ['required', 'string', 'min:10', 'max:1000'],
        ];
    }

    /**
     * METHOD: messages()
     * ------------------------------------------------------------------------------------------------
     * FUNGSI / KEGUNAAN:
     * Pesan kesalahan validasi dalam Bahasa Indonesia yang formal dan mudah dipahami.
     *
     * RETURN VALUE:
     * array : Pemetaan pesan kegagalan validasi.
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
