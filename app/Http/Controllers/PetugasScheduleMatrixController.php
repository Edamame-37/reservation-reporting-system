<?php
/**
 * ====================================================================================================
 * SISTEM INFORMASI RESERVASI & PELAPORAN FASILITAS KAMPUS (CAVA)
 * MODUL 14: OPERASIONAL PETUGAS SARPRAS: MATRIKS JADWAL 30 MENIT OPERASIONAL & DETAIL PEMOHON (SCHEDULE MATRIX)
 * BERKAS: app/Http/Controllers/PetugasScheduleMatrixController.php
 * ====================================================================================================
 *
 * DESKRIPSI BERKAS:
 * Controller ini bertindak sebagai otak pengolah data visualisasi Matriks Jadwal 30 Menit Operasional
 * untuk seluruh fasilitas kampus yang berada di bawah pengawasan UPT Sarana & Prasarana Kampus.
 * Menyediakan dua alur layanan utama:
 * 1. Perenderan antarmuka Bento UI jadwal harian (`index`) dengan metrik okupansi dan preferensi gedung.
 * 2. Endpoint RESTful JSON (`getMatrixData`) yang menguraikan rentang waktu reservasi ke dalam slot interval
 *    30 menit presisi (07:00 - 20:00 WIB) lengkap dengan payload identitas pemohon sivitas, agenda, dan izin.
 *
 * PERAN ARSITEKTURAL DALAM SISTEM CAVA:
 * 1. Inspeksi Visual Granular Slot 30 Menit:
 *    Mengonversi rentang waktu kontinu (contoh: 08:00 - 11:30) menjadi pemetaan slot diskrit 30 menitan
 *    (08:00, 08:30, 09:00, 09:30, 10:00, 10:30, 11:00) untuk mendeteksi ketersediaan secara instan.
 * 2. Privilese Peninjauan Mendalam Petugas (Full Operational Disclosure):
 *    Berbeda dengan matriks publik sivitas yang menyamarkan data pribadi, endpoint petugas ini menyajikan
 *    identitas pemohon secara lengkap (nama, NIM/NIP, prodi/lembaga, email resmi, berkas surat izin,
 *    dan identitas petugas yang telah memverifikasi).
 * 3. Pencegahan N+1 Query Problem & Optimasi Memori:
 *    Memanfaatkan teknik Eager Loading (`with(['user', 'facility', 'reviewer'])`) pada model Reservation
 *    untuk memuat seluruh data relasional dalam satu kueri SQL gabungan yang efisien.
 * 4. Sinkronisasi Preferensi Pengguna (Cookie Persistence):
 *    Mendukung penyimpanan dan pembacaan preferensi gedung terpilih via HTTP cookie `cava_pref_building`
 *    agar petugas tidak perlu memilih ulang gedung saat berpindah halaman atau me-refresh browser.
 * ====================================================================================================
 */

namespace App\Http\Controllers;

// Import Model Eloquent representasi entitas ruang dan transaksi reservasi
use App\Models\Facility;     // Model fasilitas kampus (tabel: facilities)
use App\Models\Reservation;  // Model transaksi peminjaman (tabel: reservations)

// Import pustaka manipulasi waktu Carbon
use Carbon\Carbon;

// Import kontrak HTTP Request dan Response Laravel
use Illuminate\Http\JsonResponse; // Response terformat JSON dengan header application/json
use Illuminate\Http\Request;      // Objek HTTP Request yang merangkum parameter query, header, & cookie
use Illuminate\View\View;         // Objek respons tampilan Blade

/**
 * Class PetugasScheduleMatrixController
 *
 * Mengelola antarmuka dan penyediaan data JSON untuk Matriks Jadwal 30 Menit Petugas Sarpras.
 */
class PetugasScheduleMatrixController extends Controller
{
    /**
     * METHOD: index()
     * ------------------------------------------------------------------------------------------------
     * FUNGSI / KEGUNAAN:
     * Menampilkan lembar kerja matriks jadwal operasional 30 menit petugas dengan desain Bento UI.
     *
     * PARAMETER:
     * @param \Illuminate\Http\Request $request : Mengandung query parameter `date` dan `building`, serta cookie preferensi.
     *
     * RETURN VALUE:
     * \Illuminate\View\View : Mengembalikan view 'petugas.schedule-matrix' dengan binding data compact().
     *
     * CARA KERJA & ALUR LOGIKA:
     * 1. Menentukan tanggal terpilih (`$selectedDate`): mengambil dari parameter `date` atau default hari ini.
     * 2. Menentukan filter gedung terpilih (`$selectedBuilding`): mengambil dari query string, cookie `cava_pref_building`,
     *    atau default bernilai 'semua'.
     * 3. Menarik daftar nama gedung unik (distinct) dari fasilitas yang tidak non-aktif untuk mengisi dropdown filter.
     * 4. Membangun kueri fasilitas aktif dengan filter gedung jika dipilih, diurutkan menurut nama gedung dan nama ruang.
     * 5. Menghitung ringkasan statistik okupansi harian:
     *    - Total reservasi berstatus 'approved' pada tanggal tersebut.
     *    - Total reservasi berstatus 'pending' pada tanggal tersebut.
     *    - Total seluruh fasilitas yang tersedia di kampus.
     *    - Total fasilitas yang sedang dikunci dalam status 'dalam perbaikan'.
     * 6. Merender tampilan Blade `petugas.schedule-matrix` dengan seluruh variabel yang telah diolah.
     */
    public function index(Request $request): View
    {
        // --------------------------------------------------------------------------------------------
        // LANGKAH 1: EKSTRAKSI PARAMETER TEMPORAL & FILTER PREFERENSI GEDUNG
        // --------------------------------------------------------------------------------------------

        /**
         * Syntax: $request->query('date', now()->format('Y-m-d'))
         * - `$request->query('date')` : Membaca nilai parameter URL `?date=YYYY-MM-DD`.
         * - `now()->format('Y-m-d')`  : Nilai fallback tanggal sistem saat ini jika parameter query tidak disertakan.
         */
        $selectedDate = $request->query('date', now()->format('Y-m-d'));

        /**
         * Syntax: $request->query('building', $request->cookie('cava_pref_building', 'semua'))
         * Mengimplementasikan cascading hierarki preferensi filter:
         * 1. Query string URL (`?building=Gedung+A`) memiliki prioritas tertinggi.
         * 2. Cookie peramban `cava_pref_building` dibaca jika query string kosong.
         * 3. Fallback string 'semua' digunakan jika keduanya tidak tersedia.
         */
        $selectedBuilding = $request->query('building', $request->cookie('cava_pref_building', 'semua'));

        // --------------------------------------------------------------------------------------------
        // LANGKAH 2: PENGAMBILAN DAFTAR GEDUNG UNIK UNTUK DROPDOWN FILTER
        // --------------------------------------------------------------------------------------------

        /**
         * Syntax: Facility::where('status', '!=', 'nonaktif')->select('building')->distinct()->orderBy(...)->pluck(...)
         * - `where('status', '!=', 'nonaktif')` : Mengabaikan fasilitas yang telah diarsip / dinonaktifkan permanen.
         * - `select('building')`                : Mengoptimasi kueri dengan hanya mengambil kolom 'building'.
         * - `distinct()`                        : Menghilangkan nama gedung duplikat dalam hasil SQL.
         * - `pluck('building')`                 : Mengonversi koleksi objek menjadi array satu dimensi daftar string gedung.
         */
        $buildings = Facility::where('status', '!=', 'nonaktif')
            ->select('building')
            ->distinct()
            ->orderBy('building')
            ->pluck('building');

        // --------------------------------------------------------------------------------------------
        // LANGKAH 3: KUERI MASTER FASILITAS DENGAN FILTER SPASIAL GEDUNG
        // --------------------------------------------------------------------------------------------

        $facilityQuery = Facility::where('status', '!=', 'nonaktif');

        // Jika filter gedung aktif dan bukan 'semua', tambahkan klausa penyaring SQL WHERE
        if ($selectedBuilding && $selectedBuilding !== 'semua') {
            $facilityQuery->where('building', $selectedBuilding);
        }

        // Eksekusi kueri dengan pengurutan alfabetis gedung dan nama ruangan
        $facilities = $facilityQuery->orderBy('building')->orderBy('name')->get();

        // --------------------------------------------------------------------------------------------
        // LANGKAH 4: KALKULASI METRIK OKUPANSI & BEBAN OPERASIONAL HARIAN (BENTO KPI)
        // --------------------------------------------------------------------------------------------

        /**
         * Mengambil seluruh reservasi pada tanggal yang dipilih untuk agregasi in-memory:
         * Meminimalkan panggilan kueri terpisah ke basis data dengan memfilter koleksi Laravel Collection.
         */
        $dayReservations = Reservation::whereDate('reservation_date', $selectedDate)->get();

        // Menghitung jumlah permohonan yang telah dialokasikan slotnya secara resmi
        $approvedToday = $dayReservations->where('status', 'approved')->count();

        // Menghitung permohonan yang masih menggantung dalam antrean verifikasi
        $pendingToday = $dayReservations->where('status', 'pending')->count();

        // Total kapasitas inventaris ruang aktif universitas
        $totalFacilities = Facility::where('status', '!=', 'nonaktif')->count();

        // Total ruang yang saat ini dinonaktifkan karena kerusakan teknis atau pemeliharaan berkala
        $maintenanceCount = Facility::where('status', 'dalam perbaikan')->count();

        // --------------------------------------------------------------------------------------------
        // LANGKAH 5: PENGEMBALIAN VIEW DENGAN INJEKSI DATA
        // --------------------------------------------------------------------------------------------

        return view('petugas.schedule-matrix', compact(
            'facilities',
            'buildings',
            'selectedDate',
            'selectedBuilding',
            'approvedToday',
            'pendingToday',
            'totalFacilities',
            'maintenanceCount'
        ));
    }

    /**
     * METHOD: getMatrixData()
     * ------------------------------------------------------------------------------------------------
     * FUNGSI / KEGUNAAN:
     * Endpoint API asinkron (AJAX) untuk menarik pemetaan slot 30 menit terisi beserta rincian lengkap
     * identitas pemohon, kontak, tujuan kegiatan, surat izin, dan status verifikasi pada tanggal target.
     *
     * PARAMETER:
     * @param string $date : String tanggal berformat 'YYYY-MM-DD' yang dikirim via URL parameter.
     *
     * RETURN VALUE:
     * \Illuminate\Http\JsonResponse : Mengembalikan payload JSON berupa struktur matriks multi-dimensi:
     *                                 `matrix[facility_id][slot_time] = detail_object` dan ringkasan metrik.
     *
     * CARA KERJA & ALUR LOGIKA:
     * 1. Validasi format tanggal menggunakan `Carbon::parse()`; mengembalikan HTTP 400 jika format tidak valid.
     * 2. Menarik seluruh record transaksi reservasi pada tanggal tersebut yang berstatus 'approved' atau 'pending'
     *    dengan Eager Loading relasi `user`, `facility`, dan `reviewer` (petugas pemeriksa).
     * 3. Menginisialisasi array penampung `$matrix = []`, pencatat jumlah slot `$bookedSlotsCount = 0`,
     *    dan daftar ruangan yang memiliki agenda `$activeRooms = []`.
     * 4. Melakukan perulangan foreach pada setiap reservasi:
     *    a. Menghitung waktu mulai (`$start`) dan waktu selesai (`$end`) menggunakan Carbon.
     *    b. Menyusun payload `$detail` yang memuat identitas pemohon, ruangan, jadwal, dan tautan surat izin.
     *    c. Menjalankan loop `while ($start < $end)` untuk mengisi setiap interval 30 menit ke dalam matriks.
     *       Setiap iterasi menambahkan 30 menit (`$start->addMinutes(30)`).
     * 5. Mengembalikan response JSON dengan kode status HTTP 200 berisi data matriks dan ringkasan angka.
     */
    public function getMatrixData(string $date): JsonResponse
    {
        // --------------------------------------------------------------------------------------------
        // LANGKAH 1: VALIDASI & NORMALISASI FORMAT TANGGAL
        // --------------------------------------------------------------------------------------------
        try {
            $parsedDate = Carbon::parse($date)->format('Y-m-d');
        } catch (\Exception $e) {
            // Mengembalikan respon error 400 Bad Request jika format tanggal korup atau tidak terbaca
            return response()->json(['error' => 'Format tanggal tidak valid'], 400);
        }

        // --------------------------------------------------------------------------------------------
        // LANGKAH 2: EKSTRAKSI DATA RESERVASI DENGAN EAGER LOADING ANTI N+1
        // --------------------------------------------------------------------------------------------

        /**
         * Syntax: Reservation::with(['user', 'facility', 'reviewer'])->whereDate(...)->whereIn(...)->get()
         * - `with(['user', 'facility', 'reviewer'])` : Memuat sekaligus model User pemohon, model Facility,
         *                                             dan model User reviewer dalam satu kueri SQL relasional.
         * - `whereDate('reservation_date', $parsedDate)` : Memfilter hanya reservasi yang diselenggarakan pada tanggal ini.
         * - `whereIn('status', ['approved', 'pending'])` : Hanya menyertakan permohonan yang aktif mengunci slot jadwal.
         */
        $reservations = Reservation::with(['user', 'facility', 'reviewer'])
            ->whereDate('reservation_date', $parsedDate)
            ->whereIn('status', ['approved', 'pending'])
            ->get();

        // --------------------------------------------------------------------------------------------
        // LANGKAH 3: PEMETAAN RENTANG WAKTU KE INTERVAL DISKRIT 30 MENIT
        // --------------------------------------------------------------------------------------------

        $matrix = [];           // Array multi-dimensi [facility_id][H:i] => detail_data
        $bookedSlotsCount = 0;  // Akumulator total slot 30 menit yang terpakai
        $activeRooms = [];      // Hash map pelacak ruangan unik yang memiliki reservasi

        foreach ($reservations as $res) {
            $fid = $res->facility_id;
            if (!isset($matrix[$fid])) {
                $matrix[$fid] = [];
            }

            // Tandai fasilitas ini memiliki setidaknya satu agenda reservasi hari ini
            $activeRooms[$fid] = true;

            // Membangun objek waktu absolut mulai dan selesai kegiatan
            $start = Carbon::parse($res->reservation_date->format('Y-m-d') . ' ' . $res->start_time);
            $end = Carbon::parse($res->reservation_date->format('Y-m-d') . ' ' . $res->end_time);

            /**
             * Payload detail operasional lengkap untuk staf sarpras kampus:
             * Menyediakan konteks penuh pemohon tanpa sensor untuk kebutuhan verifikasi dan koordinasi lapangan.
             */
            $detail = [
                'id' => $res->id,
                'ticket_code' => $res->ticket_code,
                'status' => $res->status,
                'applicant_name' => $res->user->name ?? 'Pemohon Sivitas',
                'applicant_identity' => $res->user->identity_number ?? '-',
                'applicant_email' => $res->user->email ?? '-',
                'applicant_department' => $res->user->department ?? '-',
                'applicant_role' => $res->user->role ?? 'mahasiswa',
                'facility_id' => $res->facility_id,
                'facility_name' => $res->facility->name ?? 'Fasilitas',
                'facility_building' => $res->facility->building ?? '-',
                'facility_floor' => $res->facility->floor_location ?? '-',
                'facility_capacity' => $res->facility->capacity ?? '-',
                'reservation_date' => $res->reservation_date->translatedFormat('d F Y'),
                'start_time' => substr($res->start_time, 0, 5),
                'end_time' => substr($res->end_time, 0, 5),
                'total_slots' => $res->total_slots,
                'purpose' => $res->purpose,
                'participants_count' => $res->participants_count,
                'permit_letter_path' => $res->permit_letter_path ? asset('storage/' . $res->permit_letter_path) : null,
                'reviewed_by_name' => $res->reviewer->name ?? null,
                'reviewed_at' => $res->reviewed_at ? $res->reviewed_at->translatedFormat('d M Y H:i') : null,
            ];

            /**
             * ALGORITMA DEKOMPOSISI SLOT 30 MENIT:
             * Melakukan iterasi dari waktu mulai ($start) hingga sebelum waktu selesai ($end),
             * menambahkan setiap slot string jam (contoh: "08:00", "08:30") ke dalam array $matrix.
             */
            while ($start < $end) {
                $timeString = $start->format('H:i');
                // Kaitkan slot waktu dengan objek detail reservasi
                $matrix[$fid][$timeString] = $detail;
                // Tambahkan akumulasi slot terpakai
                $bookedSlotsCount++;
                // Loncat 30 menit ke slot berikutnya
                $start->addMinutes(30);
            }
        }

        // --------------------------------------------------------------------------------------------
        // LANGKAH 4: PENGIRIMAN RESPONS JSON KE FRONTEND CLIENT
        // --------------------------------------------------------------------------------------------

        return response()->json([
            'matrix' => $matrix,
            'summary' => [
                'booked_slots_count' => $bookedSlotsCount,
                'active_rooms_count' => count($activeRooms),
                'total_reservations' => $reservations->count(),
            ]
        ]);
    }
}
