<?php
/**
 * ====================================================================================================
 * SISTEM INFORMASI RESERVASI & PELAPORAN FASILITAS KAMPUS (CAVA)
 * MODUL 16: OPERASIONAL PETUGAS SARPRAS: MANAJEMEN TIKET KERUSAKAN, RESOLUSI & MODE PERBAIKAN (PTG-04 & PTG-05)
 * BERKAS: app/Http/Requests/UpdateReportStatusRequest.php
 * ====================================================================================================
 *
 * DESKRIPSI BERKAS:
 * Kelas Form Request Validation yang bertindak sebagai filter validasi saat Petugas Sarpras
 * memperbarui siklus penanganan tiket keluhan kerusakan fasilitas fisik kampus (PTG-04 / US-11).
 * Mengelola transisi status tiket ('baru' -> 'diproses' -> 'selesai' / 'ditolak'), memvalidasi catatan
 * resolusi teknisi (wajib diisi jika status selesai/ditolak), serta saklar kunci fasilitas.
 *
 * PERAN ARSITEKTURAL DALAM SISTEM CAVA:
 * 1. Otorisasi Akses Petugas Operasional:
 *    Hanya pengguna yang terautentikasi dengan peran 'petugas' yang diizinkan memperbarui tiket pengaduan.
 * 2. Normalisasi Payload Input (prepareForValidation):
 *    Mendukung alias nama kolom dari form input Blade (misal `status_laporan` dinormalisasi ke `status`,
 *    dan `catatan_resolusi` dinormalisasi ke `resolution_note`) guna mencegah inkonsistensi controller.
 * 3. Validasi Kondisional Ketat (required_if):
 *    Mewajibkan catatan resolusi/tindakan teknisi (`catatan_resolusi`) saat status diubah menjadi 'selesai'
 *    agar setiap perbaikan terdokumentasi dengan pertanggungjawaban yang jelas.
 * ====================================================================================================
 */

namespace App\Http\Requests;

// Import Base FormRequest dari framework Laravel
use Illuminate\Foundation\Http\FormRequest;

/**
 * Class UpdateReportStatusRequest
 *
 * Menangani otorisasi dan validasi pembaruan status tiket keluhan kerusakan dan catatan resolusi perbaikan.
 */
class UpdateReportStatusRequest extends FormRequest
{
    /**
     * METHOD: authorize()
     * ------------------------------------------------------------------------------------------------
     * FUNGSI / KEGUNAAN:
     * Menentukan otorisasi eksekusi permintaan berdasarkan peran pengguna saat ini.
     *
     * RETURN VALUE:
     * bool : `true` jika pengguna sah sebagai petugas, `false` jika tidak berhak (HTTP 403 Forbidden).
     *
     * CARA KERJA:
     * 1. `auth()->check()`: Memastikan sesi otentikasi login aktif.
     * 2. `auth()->user()->hasRole('petugas') || auth()->user()->role === 'petugas'`: Memvalidasi peran petugas sarpras.
     */
    public function authorize(): bool
    {
        return auth()->check() && (auth()->user()->hasRole('petugas') || auth()->user()->role === 'petugas');
    }

    /**
     * METHOD: prepareForValidation()
     * ------------------------------------------------------------------------------------------------
     * FUNGSI / KEGUNAAN:
     * Menjalankan pra-pemrosesan data payload sebelum dievaluasi oleh aturan validasi method rules().
     *
     * CARA KERJA:
     * 1. Jika form mengirimkan input `status_laporan`, maka nilainya disalin ke atribut `status`.
     * 2. Jika form mengirimkan input `catatan_resolusi`, nilainya disalin ke atribut `resolution_note`.
     * Menjamin kompatibilitas antarmuka frontend dengan skema controller basis data.
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
     * METHOD: rules()
     * ------------------------------------------------------------------------------------------------
     * FUNGSI / KEGUNAAN:
     * Mendeklarasikan aturan validasi integritas status dan resolusi perbaikan sarpras.
     *
     * RETURN VALUE:
     * array : Daftar aturan validasi:
     * - 'status'             : Wajib diisi ('required'), berupa string, dan nilainya harus salah satu dari:
     *                          'baru', 'diproses', 'selesai', atau 'ditolak'.
     * - 'catatan_resolusi'   : Bersifat opsional ('nullable'), string maks 2000 karakter, namun wajib diisi
     *                          jika status bernilai 'selesai' (`required_if:status,selesai`).
     * - 'resolution_note'    : String maksimal 2000 karakter.
     * - 'is_facility_locked' : Nilai boolean penanda saklar penguncian fasilitas ke mode perbaikan.
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
     * METHOD: messages()
     * ------------------------------------------------------------------------------------------------
     * FUNGSI / KEGUNAAN:
     * Menyediakan pesan kegagalan validasi kustom dalam Bahasa Indonesia formal.
     *
     * RETURN VALUE:
     * array : Pemetaan pesan kegagalan validasi.
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
