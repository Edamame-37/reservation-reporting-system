<?php
/**
 * ==============================================================================
 * NAMA FILE    : PublicFacilityController.php
 * MODUL        : Modul 4 - Portal Publik: Beranda, Katalog Fasilitas, Filter & Matriks Ketersediaan
 * KODE FITUR   : PUB-01 (Katalog & Kalender Ketersediaan) & PUB-02 (Pencarian & Autocomplete)
 * ==============================================================================
 * DESKRIPSI:
 * Controller ini menangani seluruh interaksi portal publik tanpa memerlukan autentikasi login.
 * Menyediakan logika penyajian landing page, penyaringan katalog multi-dimensi (kategori, fasilitas,
 * kapasitas, kata kunci), persistensi preferensi filter menggunakan HTTP cookie browser,
 * kalkulasi matriks 26 slot waktu 30-menitan (07:00 - 20:00 WIB), serta endpoint API JSON
 * yang menerapkan penyembunyian privasi data pemohon (anonymized schedule view).
 *
 * ALUR ARSITEKTUR:
 * HTTP Request (Guest / User) ➔ routes/web.php ➔ PublicFacilityController
 *   ├── applyFilters() ➔ Eloquent Query Builder (Facility::class)
 *   ├── Cookie Management ➔ Simpan & Baca 'cava_pref_category'
 *   ├── Matrix Calculator ➔ Reservation::where('status', 'approved') ➔ Slot 30 Menit
 *   └── Response Dispatch ➔ View (Blade + Alpine.js) ATAU JSON API (Autocomplete/Matrix)
 * ==============================================================================
 */

namespace App\Http\Controllers;

use App\Models\Facility;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PublicFacilityController extends Controller
{
    /**
     * Helper privat: Menerapkan filter kueri multi-dimensi secara dinamis pada query builder Facility.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query   Instance query builder model Facility
     * @param  \Illuminate\Http\Request               $request Objek request berisi parameter kueri
     * @return \Illuminate\Database\Eloquent\Builder           Query builder yang telah disuntik klausul WHERE
     *
     * CARA KERJA & SYNTAX:
     * 1. $request->filled('search'): Mengecek apakah input pencarian tidak null dan tidak string kosong.
     * 2. $query->where(function($q) ...): Membungkus kondisi OR dalam tanda kurung SQL (parameter grouping)
     *    agar tidak merusak filter kategori atau status di luar kurung.
     * 3. $request->cookie('cava_pref_category', 'semua'): Mengambil preferensi pengguna dari cookie jika
     *    pengguna tidak mengirimkan parameter kategori pada URL saat ini.
     * 4. $query->whereIn('category', $request->categories): Menangani multi-checkbox kategori (array).
     * 5. foreach ($request->equipments as $eq): Menelusuri array filter peralatan dan menyaring kolom
     *    JSON 'equipment' dengan pencarian teks LIKE %...% untuk memastikan semua alat terpenuhi (AND logic).
     * 6. $query->where('capacity', '>=', $request->capacity): Memastikan daya tampung ruangan mencukupi.
     */
    private function applyFilters($query, Request $request)
    {
        // 1. FILTER SEARCH GLOBAL (Kata Kunci Bebas)
        // Mengecek kecocokan pada nama ruang, kode ruangan, deskripsi, nama gedung, atau peralatan.
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('code', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('description', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('building', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('equipment', 'LIKE', "%{$searchTerm}%");
            });
        }

        // 2. FILTER KATEGORI MAJEMUK ATAU TUNGGAL (Dengan Fallback Cookie Browser)
        // Ambil nilai cookie preferensi browser jika ada (default: 'semua')
        $cookieCategory = $request->cookie('cava_pref_category', 'semua');

        if ($request->filled('categories') && is_array($request->categories)) {
            // Prioritas 1: Jika form baru mengirim array checkbox kategori
            $query->whereIn('category', $request->categories);
        } elseif ($request->filled('category') && $request->category !== 'semua') {
            // Prioritas 2: Jika form mengirimkan single dropdown / query string 'category'
            $query->where('category', $request->category);
        } elseif (!$request->has('category') && $cookieCategory !== 'semua') {
            // Prioritas 3: Pengunjung datang kembali tanpa query string, muat preferensi dari Cookie
            $query->where('category', $cookieCategory);
        }

        // 3. FILTER MULTI-EQUIPMENT (Peralatan Pendukung)
        // Memastikan fasilitas memiliki seluruh inventaris yang dicentang oleh pengguna
        if ($request->filled('equipments') && is_array($request->equipments)) {
            foreach ($request->equipments as $eq) {
                // Kolom 'equipment' tersimpan sebagai JSON string di MySQL
                $query->where('equipment', 'LIKE', "%{$eq}%");
            }
        }

        // 4. FILTER MINIMUM KAPASITAS RUANGAN
        // Menyaring kapasitas kursi minimum (misal: hanya ruangan dengan daya tampung >= 50 orang)
        if ($request->filled('capacity') && is_numeric($request->capacity)) {
            $query->where('capacity', '>=', (int) $request->capacity);
        }

        return $query;
    }

    /**
     * Helper privat: Mengambil daftar opsi kategori dan peralatan unik dari database untuk formulir filter.
     *
     * @return array Array asosiatif berisi koleksi 'categories' dan 'equipments'
     *
     * CARA KERJA & SYNTAX:
     * 1. Facility::where('status', '!=', 'nonaktif'): Hanya mengambil fasilitas yang tidak diarsip.
     * 2. select('category')->distinct()->pluck('category'): Menarik daftar nama kategori tanpa duplikasi.
     * 3. pluck('equipment')->map(...)->flatten()->unique(): Mengekstrak seluruh item peralatan dari
     *    kolom JSON setiap ruangan, meleburkannya menjadi 1 dimensi koleksi datar, dan membuang duplikat.
     * 4. compact('categories', 'equipments'): Membungkus kedua variabel ke dalam array asosiatif.
     */
    private function getFilterOptions()
    {
        // Ambil seluruh kategori unik yang terdaftar pada fasilitas aktif
        $categories = Facility::where('status', '!=', 'nonaktif')
            ->select('category')
            ->distinct()
            ->pluck('category')
            ->filter()
            ->values();

        // Ambil seluruh daftar alat unik dari seluruh fasilitas aktif
        $equipments = Facility::where('status', '!=', 'nonaktif')
            ->select('equipment')
            ->get()
            ->pluck('equipment')
            ->map(function ($item) {
                // Parsing format array PHP atau JSON string jika belum ter-cast
                return is_array($item) ? $item : (json_decode($item, true) ?? []);
            })
            ->flatten() // Ratakan array bertingkat menjadi satu array flat
            ->unique()  // Hilangkan nama alat yang berulang
            ->filter()  // Bersihkan nilai null atau string kosong
            ->values(); // Reset kunci indeks array menjadi 0, 1, 2, ...

        return compact('categories', 'equipments');
    }

    /**
     * Menampilkan Halaman Muka (Landing Page / Beranda Publik - PUB-01).
     *
     * @return \Illuminate\View\View Tampilan resources/views/public/home.blade.php
     *
     * CARA KERJA & SYNTAX:
     * 1. Facility::where('status', '!=', 'nonaktif')->take(3)->get(): Mengambil 3 venue sampel teratas
     *    untuk featured card showcase pada hero section.
     * 2. $this->getFilterOptions(): Memuat kategori dan alat untuk quick search bar di beranda.
     * 3. view('public.home', ...): Merender template Blade landing page.
     */
    public function home()
    {
        // Ambil 3 sampel fasilitas unggulan untuk ditampilkan di beranda
        $facilities = Facility::where('status', '!=', 'nonaktif')->take(3)->get();
        
        // Ambil data opsi kategori dan alat untuk filter bar interaktif di landing page
        $filterOptions = $this->getFilterOptions();
        $categories = $filterOptions['categories'];
        $equipments = $filterOptions['equipments'];

        return view('public.home', compact('facilities', 'categories', 'equipments'));
    }

    /**
     * Menampilkan Katalog Fasilitas Lengkap dengan Filter Multi-Dimensi & Paginasi (PUB-01).
     *
     * @param  \Illuminate\Http\Request  $request Objek HTTP Request berisi parameter filter
     * @return \Illuminate\Http\Response  Respon HTTP dengan view public.catalog dan cookie preferensi
     *
     * CARA KERJA & SYNTAX:
     * 1. $query = Facility::where('status', '!=', 'nonaktif'): Inisialisasi basis data fasilitas aktif.
     * 2. $this->applyFilters($query, $request): Terapkan penyaringan multi-dimensi.
     * 3. Carbon::today()->toDateString(): Mengambil tanggal hari ini (format 'YYYY-MM-DD').
     * 4. Reservation::where('reservation_date', $today)->where('status', 'approved')->get()->groupBy('facility_id'):
     *    Mengelompokkan peminjaman yang disetujui hari ini berdasarkan ID fasilitas untuk kalkulasi slot.
     * 5. paginate(8)->withQueryString(): Menghasilkan 8 item per halaman dan mempertahankan parameter URL.
     * 6. through(function ($f) ...): Mengubah struktur data model setiap elemen tanpa merusak pagination metadata.
     * 7. Kalkulasi Slot: 13 jam operasional (07:00 - 20:00 WIB) = 26 slot 30 menit.
     *    Sisa slot = total slot (26) dikurangi slot yang telah terpakai ($occupiedSlots).
     * 8. $response->cookie('cava_pref_category', ...): Menyimpan preferensi kategori ke cookie selama 30 hari.
     */
    public function index(Request $request)
    {
        // Inisialisasi kueri fasilitas yang dapat diakses publik
        $query = Facility::where('status', '!=', 'nonaktif');
        $query = $this->applyFilters($query, $request);

        // Ambil reservasi hari ini yang sudah disetujui untuk menghitung ketersediaan real-time
        $today = Carbon::today()->toDateString();
        $todayReservations = Reservation::where('reservation_date', $today)
            ->where('status', 'approved')
            ->get()
            ->groupBy('facility_id');

        // Batasi penampilan maksimal 8 fasilitas per halaman dengan paginasi server-side
        $facilities = $query->orderBy('name', 'asc')
            ->paginate(8)
            ->withQueryString() // Pertahankan query string saat pengguna berpindah halaman
            ->through(function ($f) use ($todayReservations) {
                // Hitung total slot yang terpakai hari ini (1 slot = 30 menit)
                $occupiedSlots = isset($todayReservations[$f->id])
                    ? $todayReservations[$f->id]->sum('total_slots')
                    : 0;

                // Kampus CAVA beroperasi pukul 07:00 - 20:00 WIB (13 jam * 2 slot/jam = 26 slot)
                $totalSlots = 26;
                // Jika fasilitas dalam perbaikan, ketersediaan dipaksa bernilai 0
                $availableSlots = $f->status === 'dalam perbaikan' ? 0 : max(0, $totalSlots - $occupiedSlots);

                // Kembalikan representasi array DTO (Data Transfer Object) bersih ke View
                return [
                    'id' => $f->id,
                    'code' => $f->code,
                    'name' => $f->name,
                    'category' => $f->category,
                    'building' => $f->building . ($f->floor_location ? ' (Lt. ' . $f->floor_location . ')' : ''),
                    'capacity' => $f->capacity,
                    'status' => $f->status === 'dalam perbaikan' ? 'locked' : 'approved',
                    'equipment' => is_array($f->equipment) ? $f->equipment : (json_decode($f->equipment, true) ?? []),
                    'desc' => $f->description,
                    'image' => $f->image_path,
                    'totalSlots' => $totalSlots,
                    'availableSlots' => $availableSlots,
                ];
            });

        // Ambil opsi filter untuk komponen form pencarian samping (drawer)
        $filterOptions = $this->getFilterOptions();
        $categories = $filterOptions['categories'];
        $equipments = $filterOptions['equipments'];

        // Siapkan respon view katalog
        $response = response()->view('public.catalog', compact('facilities', 'categories', 'equipments'));

        // Jika kategori dikirim eksplisit oleh pengguna, simpan ke cookie preferensi selama 30 hari (60 * 24 * 30 menit)
        if ($request->filled('category')) {
            $response->cookie('cava_pref_category', $request->category, 60 * 24 * 30);
        }

        return $response;
    }

    /**
     * Menampilkan Halaman Kalender Matriks Ketersediaan Publik (PUB-01).
     *
     * @param  \Illuminate\Http\Request  $request Objek request filter
     * @return \Illuminate\View\View Tampilan resources/views/public/availability.blade.php
     *
     * CARA KERJA & SYNTAX:
     * 1. Query fasilitas dengan filter pencarian dan paginasi 8 baris per halaman.
     * 2. Setiap baris dipersiapkan dengan slot terisi awal kosong `occupied: []`
     *    yang nantinya diisi secara reaktif oleh Alpine.js via panggilan AJAX ke API.
     * 3. Mengidentifikasi apakah fasilitas berstatus terkunci ('locked') karena dalam perbaikan.
     */
    public function availability(Request $request)
    {
        $query = Facility::where('status', '!=', 'nonaktif');
        $query = $this->applyFilters($query, $request);

        // Batasi penampilan maksimal 8 fasilitas per request dengan paginasi server-side
        $facilities = $query->orderBy('name', 'asc')
            ->paginate(8)
            ->withQueryString()
            ->through(function ($f) {
                return [
                    'id' => $f->id,
                    'name' => $f->name,
                    'building' => str_contains($f->building, 'Lt.') ? $f->building : ($f->building . ($f->floor_location ? ' (' . $f->floor_location . ')' : '')),
                    'capacity' => $f->capacity,
                    'occupied' => [], // Slot waktu terpakai akan diisi dinamis via Fetch API Alpine.js
                    'locked' => $f->status === 'dalam perbaikan',
                ];
            });

        $filterOptions = $this->getFilterOptions();
        $categories = $filterOptions['categories'];
        $equipments = $filterOptions['equipments'];

        return view('public.availability', compact('facilities', 'categories', 'equipments'));
    }

    /**
     * Endpoint API JSON: Menghitung & Memetakan Slot 30 Menit yang Terpakai untuk Seluruh Fasilitas (PUB-01).
     *
     * @param  string  $date Format tanggal 'YYYY-MM-DD'
     * @return \Illuminate\Http\JsonResponse Payload matriks ketersediaan per ID fasilitas
     *
     * KEBIJAKAN PRIVASI & CARA KERJA:
     * 1. Endpoint ini HANYA mengembalikan jam mulai dan selesai ([facility_id, start_time, end_time]).
     * 2. Identitas pemohon (NIM, Nama, Email, Acara/Organisasi) SENGAJA TIDAK DIAMBIL untuk menjaga privasi sivitas.
     * 3. Carbon Loop 30 Menit:
     *    - Jam mulai dikonversi ke Carbon instance.
     *    - Selama $start < $end, tambahkan string 'H:i' (misal '08:00', '08:30') ke array $matrix[$fid].
     *    - Tambahkan 30 menit ke $start ($start->addMinutes(30)).
     * 4. Struktur Response JSON:
     *    {
     *      "1": ["08:00", "08:30", "09:00"], // Fasilitas ID 1 dipesan jam 08:00-09:30
     *      "3": ["13:00", "13:30"]            // Fasilitas ID 3 dipesan jam 13:00-14:00
     *    }
     */
    public function getMatrixAvailability($date)
    {
        // 1. Validasi integritas format tanggal
        try {
            $parsedDate = Carbon::parse($date)->format('Y-m-d');
        } catch (\Exception $e) {
            return response()->json(['error' => 'Format tanggal tidak valid'], 400);
        }

        // 2. Ambil seluruh reservasi yang telah disetujui (approved) pada tanggal tersebut
        // PENTING: Hanya kolom facility_id, start_time, end_time yang ditarik (anonymized data)
        $reservations = Reservation::where('status', 'approved')
            ->whereDate('reservation_date', $parsedDate)
            ->get(['facility_id', 'start_time', 'end_time']);

        $matrix = [];

        // 3. Konversi rentang waktu durasi ke dalam blok-blok slot 30 menit
        foreach ($reservations as $r) {
            $fid = $r->facility_id;
            if (!isset($matrix[$fid])) {
                $matrix[$fid] = [];
            }

            $start = Carbon::parse($r->start_time);
            $end = Carbon::parse($r->end_time);

            // Perulangan untuk menandai setiap blok 30 menit yang terpakai
            while ($start < $end) {
                $timeString = $start->format('H:i');
                // Hindari duplikasi jika ada jadwal bertumpuk
                if (!in_array($timeString, $matrix[$fid])) {
                    $matrix[$fid][] = $timeString;
                }
                $start->addMinutes(30);
            }
        }

        return response()->json($matrix);
    }

    /**
     * Endpoint API JSON: Mengambil Detail Jam Terisi untuk Satu Fasilitas Spesifik (PUB-01).
     *
     * @param  int     $id   ID unik fasilitas
     * @param  string  $date Tanggal yang dicek (format 'YYYY-MM-DD')
     * @return \Illuminate\Http\JsonResponse Status operasional & daftar rentang jam terisi
     *
     * CARA KERJA & SYNTAX:
     * 1. Facility::find($id): Mencari entitas fasilitas; kembalikan 404 jika tidak ditemukan.
     * 2. Pengecekan status: Jika fasilitas 'dalam perbaikan' atau 'nonaktif', kembalikan respon
     *    khusus status 'maintenance' agar formulir pemesanan di frontend otomatis terkunci.
     * 3. Mengambil koleksi rentang jam terpakai (start_time & end_time) untuk fasilitas tersebut.
     */
    public function showAvailability($id, $date)
    {
        $facility = Facility::find($id);
        if (!$facility) {
            return response()->json(['error' => 'Fasilitas tidak ditemukan'], 404);
        }

        // Cek apakah fasilitas sedang dalam perbaikan atau dinonaktifkan oleh Biro Sarpras
        if ($facility->status === 'dalam perbaikan' || $facility->status === 'nonaktif') {
            return response()->json([
                'status' => 'maintenance',
                'message' => 'Fasilitas sedang ditutup atau dalam perbaikan.'
            ]);
        }

        try {
            $parsedDate = Carbon::parse($date)->format('Y-m-d');
        } catch (\Exception $e) {
            return response()->json(['error' => 'Format tanggal tidak valid'], 400);
        }

        // Ambil daftar rentang pemesanan yang disetujui untuk fasilitas terpilih
        $bookedSlots = Reservation::where('facility_id', $id)
            ->where('status', 'approved')
            ->whereDate('reservation_date', $parsedDate)
            ->get(['start_time', 'end_time']);

        return response()->json([
            'status' => 'success',
            'data' => $bookedSlots
        ]);
    }

    /**
     * Endpoint API JSON: Mesin Saran Pencarian Autocomplete Cepat (PUB-02).
     *
     * @param  \Illuminate\Http\Request  $request Objek request berisi parameter 'q' dan filter opsional
     * @return \Illuminate\Http\JsonResponse Rekomendasi kategori, gedung, dan nama fasilitas
     *
     * CARA KERJA & SYNTAX:
     * 1. $q = $request->q: Menangkap kata kunci yang diketikkan pengguna di search bar.
     * 2. Jika input kosong dan tanpa filter, kembalikan 'empty_state' => true untuk menyembunyikan popover.
     * 3. Mengumpulkan saran kategori dan gedung yang cocok (maksimal 3 item teks).
     * 4. Mengambil 3 fasilitas teratas yang cocok dengan nama/kode/gedung beserta lokasi lantainya.
     * 5. JSON Response diformat khusus untuk dibaca oleh dropdown suggestion Alpine.js / AJAX.
     */
    public function searchSuggestions(Request $request)
    {
        $q = $request->q;
        $hasFilters = $request->filled('categories') || $request->filled('equipments') || $request->filled('capacity');

        // Jika tidak ada kata kunci maupun filter aktif, kembalikan state kosong
        if (empty($q) && !$hasFilters) {
            return response()->json(['suggestions' => [], 'facilities' => [], 'empty_state' => true]);
        }

        // 1. REKOMENDASI TEKS (Saran Kategori atau Nama Gedung)
        $textSuggestions = collect();
        if (!empty($q)) {
            // Cari kecocokan kategori
            $categoriesQuery = Facility::where('status', '!=', 'nonaktif')
                ->where('category', 'LIKE', "%{$q}%");
            $categoriesQuery = $this->applyFilters($categoriesQuery, $request);
            $categories = $categoriesQuery->pluck('category')
                ->unique()
                ->values()
                ->take(2);

            // Cari kecocokan gedung
            $buildingsQuery = Facility::where('status', '!=', 'nonaktif')
                ->where('building', 'LIKE', "%{$q}%");
            $buildingsQuery = $this->applyFilters($buildingsQuery, $request);
            $buildings = $buildingsQuery->pluck('building')
                ->unique()
                ->values()
                ->take(2);
                
            // Gabungkan rekomendasi kategori dan gedung
            $textSuggestions = $categories->concat($buildings)->unique()->take(3)->values();
        }

        // 2. REKOMENDASI FASILITAS SPESIFIK (Nama, Kode, & Lokasi Lantai)
        $facilitiesQuery = Facility::where('status', '!=', 'nonaktif');
        if (!empty($q)) {
            $facilitiesQuery->where(function($query) use ($q) {
                $query->where('name', 'LIKE', "%{$q}%")
                      ->orWhere('code', 'LIKE', "%{$q}%")
                      ->orWhere('building', 'LIKE', "%{$q}%");
            });
        }
        $facilitiesQuery = $this->applyFilters($facilitiesQuery, $request);
        $facilities = $facilitiesQuery->take(3)
            ->get()
            ->map(function($f) {
                return [
                    'id' => $f->id,
                    'name' => $f->name,
                    'building' => $f->building . ($f->floor_location ? ' (Lt. ' . $f->floor_location . ')' : ''),
                ];
            });

        // Kembalikan payload JSON untuk dropdown search bar
        return response()->json([
            'suggestions' => $textSuggestions,
            'facilities' => $facilities
        ]);
    }
}
