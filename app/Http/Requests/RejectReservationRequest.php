<?php
/**
 * ====================================================================================================
 * SISTEM INFORMASI RESERVASI & PELAPORAN FASILITAS KAMPUS (CAVA)
 * MODUL 15: OPERASIONAL PETUGAS SARPRAS: PERSETUJUAN, PENOLAKAN, & PEMBATALAN DARURAT (PTG-02 & PTG-03)
 * BERKAS: app/Http/Requests/RejectReservationRequest.php
 * ====================================================================================================
 *
 * DESKRIPSI BERKAS:
 * Kelas Form Request Validation resmi yang bertindak sebagai penjaga gerbang (Gatekeeper) untuk
 * memvalidasi integritas data alasan penolakan saat Petugas Sarpras menolak permohonan reservasi sivitas (PTG-02).
 *
 * PERAN ARSITEKTURAL DALAM SISTEM CAVA:
 * 1. Otorisasi Hak Akses Berbasis Peran (RBAC):
 *    Memverifikasi bahwa pengguna yang mengirimkan aksi penolakan terautentikasi dan memiliki peran 'petugas'.
 * 2. Transparansi Penolakan Layanan (Accountability):
 *    Mewajibkan petugas mencantumkan alasan penolakan formal (`rejection_reason`) dengan batasan panjang yang
 *    memadai (minimal 5 karakter, maksimal 1000 karakter) agar pemohon menerima penjelasan yang jelas.
 * 3. Lokalisasi Pesan Validasi:
 *    Menyediakan pesan kegagalan dalam Bahasa Indonesia yang ramah pengguna jika kolom tidak diisi atau tidak valid.
 * ====================================================================================================
 */

namespace App\Http\Requests;

// Import Base FormRequest dari framework Laravel
use Illuminate\Foundation\Http\FormRequest;

/**
 * Class RejectReservationRequest
 *
 * Menangani otorisasi dan validasi input alasan penolakan tiket reservasi fasilitas.
 */
class RejectReservationRequest extends FormRequest
{
    /**
     * METHOD: authorize()
     * ------------------------------------------------------------------------------------------------
     * FUNGSI / KEGUNAAN:
     * Menentukan apakah pengguna yang sedang login memiliki wewenang untuk menolak permohonan reservasi.
     *
     * RETURN VALUE:
     * bool : Mengembalikan `true` jika pengguna sah, atau `false` yang memicu respon HTTP 403 Forbidden.
     *
     * CARA KERJA:
     * 1. `auth()->check()`: Memastikan sesi otentikasi pengguna aktif (bukan guest).
     * 2. `auth()->user()->hasRole('petugas') || auth()->user()->role === 'petugas'`: Memeriksa kepemilikan
     *    peran Petugas Sarpras baik melalui sistem Spatie Laravel-Permission maupun kolom role bawaan.
     */
    public function authorize(): bool
    {
        return auth()->check() && (auth()->user()->hasRole('petugas') || auth()->user()->role === 'petugas');
    }

    /**
     * METHOD: rules()
     * ------------------------------------------------------------------------------------------------
     * FUNGSI / KEGUNAAN:
     * Menetapkan aturan validasi deklaratif untuk payload penolakan tiket permohonan.
     *
     * RETURN VALUE:
     * array : Array pemetaan nama kolom ke daftar rule validasi Laravel.
     *
     * CARA KERJA:
     * - 'required' : Nilai kolom `rejection_reason` wajib disertakan dan tidak boleh string kosong atau null.
     * - 'string'   : Nilai harus berupa tipe data teks / untaian karakter.
     * - 'min:5'    : Panjang teks minimal 5 karakter guna mencegah pengisian alasan asal-asalan.
     * - 'max:1000' : Panjang teks maksimal 1000 karakter untuk mencegah eksploitasi beban memori basis data.
     */
    public function rules(): array
    {
        return [
            'rejection_reason' => ['required', 'string', 'min:5', 'max:1000'],
        ];
    }

    /**
     * METHOD: messages()
     * ------------------------------------------------------------------------------------------------
     * FUNGSI / KEGUNAAN:
     * Mengkustomisasi pesan kesalahan validasi agar informatif dan berbahasa Indonesia standar.
     *
     * RETURN VALUE:
     * array : Array pemetaan aturan kesalahan ke kalimat pesan kesalahan.
     */
    public function messages(): array
    {
        return [
            'rejection_reason.required' => 'Alasan penolakan permohonan reservasi wajib diisi.',
            'rejection_reason.min'      => 'Alasan penolakan minimal berisi 5 karakter agar jelas bagi pemohon.',
            'rejection_reason.max'      => 'Alasan penolakan maksimal 1000 karakter.',
        ];
    }
}
