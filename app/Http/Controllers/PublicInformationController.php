<?php
/**
 * ====================================================================================================
 * SISTEM INFORMASI RESERVASI & PELAPORAN FASILITAS KAMPUS (CAVA)
 * MODUL 18: PUSAT INFORMASI PUBLIK, KEBIJAKAN, SYARAT & BANTUAN SIVITAS
 * BERKAS: app/Http/Controllers/PublicInformationController.php
 * ====================================================================================================
 *
 * DESKRIPSI BERKAS:
 * Controller penampil pusat regulasi, kebijakan privasi, tata tertib peminjaman fasilitas, dan
 * pusat bantuan/helpdesk yang dapat diakses secara terbuka oleh publik maupun sivitas akademika kampus.
 *
 * PERAN ARSITEKTURAL DALAM SISTEM CAVA:
 * 1. Transparansi Regulasi Institusional:
 *    Menyajikan dokumen statuter kampus terkait tata tertib pemakaian aset fisik, kewajiban peminjam,
 *    dan sanksi pelanggaran demi menjaga ketertiban pemanfaatan sarana prasarana universitas.
 * 2. Transparansi Privasi & Penggunaan Cookie (GDPR & Regulasi PDP):
 *    Menjelaskan secara eksplisit fungsi setiap cookie yang ditanam di peramban pengguna (cookie preferensi,
 *    fungsional, sesi esensial, keamanan CSRF, dan persistensi otentikasi).
 * 3. Resolusi Seksi Dinamis (URL Segment & Query Parameter Fallback):
 *    Mendukung penentuan seksi aktif baik melalui parameter jalur URL (`/informasi/{section}`)
 *    maupun query string (`/informasi?tab=...`), dengan validasi whitelist ketat untuk mencegah
 *    input tidak sah.
 * ====================================================================================================
 */

namespace App\Http\Controllers;

// Import objek Request dan antarmuka View dari Laravel
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Class PublicInformationController
 *
 * Mengelola perenderan halaman regulasi, kebijakan privasi, syarat ketentuan, dan panduan bantuan CAVA.
 */
class PublicInformationController extends Controller
{
    /**
     * METHOD: index()
     * ------------------------------------------------------------------------------------------------
     * FUNGSI / KEGUNAAN:
     * Menampilkan halaman pusat informasi publik CAVA dengan seksi konten yang disesuaikan.
     *
     * PARAMETER:
     * @param \Illuminate\Http\Request $request : Objek HTTP Request untuk membaca query string '?tab=...'.
     * @param string|null $section : Parameter opsional dari rute URL segmen kedua ('/informasi/{section?}').
     *
     * RETURN VALUE:
     * \Illuminate\View\View : Mengembalikan view 'public.information' dengan array variabel 'activeSection'.
     *
     * CARA KERJA & ALUR LOGIKA:
     * 1. Menyiapkan daftar whitelist seksi yang valid: `['kebijakan', 'syarat-ketentuan', 'bantuan']`.
     * 2. Menentukan nilai seksi aktif (`$activeSection`):
     *    - Menggunakan nilai `$section` dari URL path jika ada.
     *    - Jika tidak ada, memeriksa query parameter `?tab=...`.
     *    - Jika keduanya tidak ada, menetapkan default bernilai 'kebijakan'.
     * 3. Memverifikasi apakah nilai `$activeSection` terdapat dalam array `$validSections` menggunakan
     *    perbandingan ketat (`in_array(..., true)`). Jika tidak valid, mengembalikan default 'kebijakan'.
     * 4. Merender view Blade `public.information` dan meneruskan variabel `activeSection`.
     */
    public function index(Request $request, ?string $section = null): View
    {
        // 1. Whitelist seksi informasi yang diizinkan untuk mencegah injection/undefined view tab
        $validSections = ['kebijakan', 'syarat-ketentuan', 'bantuan'];

        // 2. Cascading resolusi tab: Path URL -> Query Parameter '?tab' -> Default 'kebijakan'
        $activeSection = $section ?? $request->query('tab', 'kebijakan');

        // 3. Sanitasi nilai: Kembalikan ke 'kebijakan' jika string di luar whitelist
        if (!in_array($activeSection, $validSections, true)) {
            $activeSection = 'kebijakan';
        }

        // 4. Render view publik dengan penanda seksi aktif
        return view('public.information', [
            'activeSection' => $activeSection,
        ]);
    }
}
