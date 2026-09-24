# SRS-USR-01: Form Pengajuan Reservasi Ruangan Kampus

## 1. Identifikasi Dokumen & Metadata SRS
- **Kode Spesifikasi:** SRS-USR-01
- **Judul Fitur:** Formulir Pengajuan Reservasi Fasilitas & Ruangan Kampus
- **Aktor Utama:** Pengguna Terautentikasi (Sivitas Akademika: Mahasiswa, Dosen, Tenaga Kependidikan/Staf)
- **Role Sistem:** `pengguna` (Spatie Laravel Permission) dengan status akun aktif (`status = 'verified'`)
- **Ekuivalen User Story:** US-3 (*"Sebagai Pengguna, saya bisa mengajukan reservasi pada rentang waktu tertentu dengan menyebutkan tujuan penggunaan."*)
- **Modul Sistem:** Modul Manajemen Reservasi (*Sub-modul: Portal Pengguna / Sivitas Akademika*)
- **Tingkat Prioritas:** Fundamental / *Must Have* (Kritis)
- **Status Dokumen:** Versi 2.0 (Spesifikasi Lengkap & Terstandarisasi)

---

## 2. Deskripsi Alur Bisnis & Karakteristik Pengguna

### 2.1. Karakteristik & Segmentasi Aktor Pengguna
Sistem melayani tiga segmen pengguna utama dalam sivitas akademika dengan hak dan karakteristik kebutuhan pengajuan yang setara di tingkat otorisasi, namun memiliki variasi konteks penggunaan:
1. **Mahasiswa:** Mengajukan reservasi untuk keperluan kegiatan organisasi kemahasiswaan (BEM/DPM/HIMA/UKM), diskusi kelompok, latihan minat bakat, atau kegiatan akademik terstruktur. Teridentifikasi melalui Nomor Induk Mahasiswa (NIM).
2. **Dosen:** Mengajukan reservasi untuk perkuliahan pengganti, seminar/kuliah tamu, penelitian di laboratorium khusus, atau sidang tugas akhir. Teridentifikasi melalui Nomor Induk Dosen Nasional (NIDN) atau NIP.
3. **Tenaga Kependidikan / Staf:** Mengajukan reservasi untuk rapat koordinasi unit kerja, sosialisasi administratif, pelatihan internal, atau kegiatan kelembagaan universitas. Teridentifikasi melalui Nomor Induk Pegawai (NIP).

### 2.2. Ringkasan Alur Pengajuan Reservasi
1. Pengguna yang telah berhasil melakukan *login* dan berstatus akun aktif dapat mengakses formulir pengajuan reservasi baik melalui menu Dasbor Pengguna maupun melalui tombol *"Reservasi Ruang Ini"* pada kartu fasilitas di halaman Katalog Publik.
2. Pengguna menentukan fasilitas yang ingin dipinjam melalui **3 dropdown bertingkat (*Cascading Dropdown*)**: memilih Gedung (Gedung A, B, C, D, E, F, atau Gedung Rektorat/PKM), memilih Lantai (Lantai 1, 2, atau 3), lalu memilih Ruangan spesifik (misal: A104). Pengguna juga menentukan tanggal pelaksanaan kegiatan, rentang waktu pemakaian (*start time* dan *end time*), serta rincian tujuan penggunaan ruangan.
3. Antarmuka formulir secara interaktif menyaring pilihan slot waktu agar selalu berada dalam koridor jam operasional kampus (07:00 – 20:00 WIB) dengan durasi kelipatan 30 menit.
4. Saat formulir dikirimkan (*submit*), sistem memvalidasi integritas data, status kesiapan fasilitas (bukan dalam status pemeliharaan/*maintenance*), serta memastikan tidak ada persinggungan jadwal (*schedule conflict / overlap*) dengan peminjaman lain yang telah berstatus *Approved*.
5. Apabila seluruh validasi terpenuhi, sistem mengunci data secara transaksional, menyimpan entitas reservasi baru ke basis data dengan status awal `pending`, dan memasukkannya ke dalam antrean persetujuan Petugas Sarpras. Pengguna selanjutnya diarahkan ke halaman Riwayat Reservasi disertai notifikasi umpan balik sukses.

---

## 3. Aturan Bisnis Mutlak (*Business Rules*)

Setiap baris kode *Frontend* maupun *Backend* wajib mematuhi aturan bisnis baku berikut tanpa kompromi:

| Kode Aturan | Nama Aturan | Deskripsi & Batasan Ketat | Tingkat Penegakan |
|---|---|---|---|
| **BR-USR01-01** | **Jam Operasional Kampus** | Peminjaman fasilitas hanya diizinkan pada rentang operasional kampus, yaitu pukul **07:00 WIB sampai 20:00 WIB**. Tidak diperkenankan memilih waktu mulai sebelum 07:00 atau waktu selesai setelah 20:00. | Client-side & Server-side |
| **BR-USR01-02** | **Granularitas Slot 30 Menit** | Setiap penetapan waktu mulai (*start_time*) dan waktu selesai (*end_time*) **mutlak bernilai kelipatan 30 menit**. Nilai menit pada jam hanya boleh `:00` atau `:30` (contoh: 08:00, 08:30, 09:00). Nilai sembarang seperti 08:15 atau 08:45 wajib ditolak. | Client-side & Server-side |
| **BR-USR01-03** | **Durasi & Urutan Kronologis** | Waktu selesai (*end_time*) wajib lebih besar daripada waktu mulai (*start_time*). Durasi peminjaman paling singkat adalah 1 slot (30 menit). | Client-side & Server-side |
| **BR-USR01-04** | **Validitas Tanggal** | Tanggal permohonan reservasi tidak boleh tanggal lampau (`date >= today`). Pengguna dapat memesan fasilitas untuk hari ini hingga batas kalender akademik semester aktif. | Client-side & Server-side |
| **BR-USR01-05** | **Pencegahan Bentrok (*Anti Double-Booking*)** | Sistem menolak pengajuan jika pada fasilitas dan tanggal yang sama terdapat reservasi lain yang **telah disetujui (*status = 'approved'*)** dengan rentang waktu yang saling bersinggungan (*overlap*): `(req_start < existing_end) AND (req_end > existing_start)`. | Server-side (Kueri DB) |
| **BR-USR01-06** | **Proteksi Fasilitas Pemeliharaan** | Fasilitas yang sedang berstatus 'Dalam Perbaikan' (*maintenance*) atau non-aktif (`status_aktif != 'aktif'`) dilarang keras untuk diajukan peminjamannya. | Client-side & Server-side |
| **BR-USR01-07** | **Inisiasi Status Awal** | Setiap pengajuan reservasi baru yang berhasil terkirim secara otomatis memiliki status `pending` (*Menunggu Persetujuan Petugas*). Pengguna tidak memiliki wewenang mengubah statusnya sendiri menjadi *approved*. | Server-side (Otomatisasi) |
| **BR-USR01-08** | **Integritas Akun Pengguna** | Hanya akun pengguna yang telah diverifikasi oleh Administrator (`status = 'verified'`) yang diizinkan mengirimkan data reservasi. Akun berstatus `pending` atau `suspended` ditolak aksesnya oleh *middleware*. | Middleware Server-side |
| **BR-USR01-09** | **Hierarki Penomoran Kode Ruang** | Pemilihan fasilitas diorganisasikan dalam hierarki 3 level: Gedung ➔ Lantai ➔ Ruangan. Format kode ruangan baku diawali huruf gedung, angka lantai, dan nomor urut ruangan (contoh: Gedung A Lantai 1 Ruang 4 diberi kode **A104**). | Client-side (UI) & Master DB |

---

## 4. Kebutuhan Fungsional (*Functional Requirements*)

| ID Kebutuhan | Nama Kebutuhan | Deskripsi Spesifikasi Fungsional |
|---|---|---|
| **FR-USR01-001** | Aksesibilitas Formulir | Sistem harus menyediakan halaman formulir pengajuan reservasi yang dapat diakses oleh Pengguna terautentikasi melalui URL `/user/reservation-form`. |
| **FR-USR01-002** | Pre-Seleksi Data Fasilitas | Sistem harus mampu menerima parameter *query string* (misal: `?facility_id=1`) untuk langsung memilihkan gedung, lantai, dan ruangan yang dituju secara otomatis saat pengguna datang dari halaman katalog. |
| **FR-USR01-003** | Pemilihan Fasilitas Bertingkat | Antarmuka formulir menyediakan 3 dropdown bertingkat (*Cascading Dropdown*): (1) Pemilih Gedung (A–F / Rektorat / PKM), (2) Pemilih Lantai (1–3), dan (3) Pemilih Ruangan (A101–A104, B201–B204, dst.) yang secara reaktif mengikat `facility_id` valid ke basis data. |
| **FR-USR01-004** | Pemilihan Tanggal Aman | Sistem harus menyediakan pemilih tanggal (*date picker*) dengan atribut batasan minimal tanggal hari ini (`min="{{ date('Y-m-d') }}"`). |
| **FR-USR01-005** | Pemilihan Slot Waktu Terstruktur | Sistem harus menyediakan antarmuka pemilihan waktu mulai dan waktu selesai yang dibatasi secara ketat hanya pada opsi jam 07:00 hingga 20:00 dengan interval 30 menit. |
| **FR-USR01-006** | Pengisian Tujuan & Kategori | Sistem harus mewajibkan pengguna mengisikan deskripsi tujuan peminjaman (minimal 10 karakter, maksimal 500 karakter) serta opsi kategori kegiatan (Akademik, Organisasi, Rapat Resmi). |
| **FR-USR01-007** | Indikator Live Bentrok (UI Hint) | Antarmuka pengguna harus memberikan indikasi visual atau petunjuk slot waktu apabila pengguna memilih tanggal tertentu (memanfaatkan interaktivitas Alpine.js). |
| **FR-USR01-008** | Validasi Ganda Terpadu | Sistem harus memvalidasi seluruh *payload* formulir pada lapis klien (HTML5/JS) dan lapis server menggunakan *Custom FormRequest* Laravel. |
| **FR-USR01-009** | Transaksional Penyimpanan Data | Sistem harus mencatat peminjaman ke dalam tabel `reservations` secara transaksional (`DB::transaction`) dengan merekam `user_id` dari sesi aktif dan status `pending`. |
| **FR-USR01-010** | Umpan Balik & Pengalihan | Setelah pengajuan sukses, sistem harus menampilkan notifikasi sukses (*flash session*) dan mengalihkan pengguna ke halaman Riwayat Reservasi (`/user/reservation-history`). |
| **FR-USR01-011** | Penanganan Kesalahan Presisi | Jika terjadi kegagalan validasi atau bentrok jadwal, sistem harus mengembalikan formulir dengan mempertahankan isian lama (`old()`) dan menampilkan pesan kesalahan merah spesifik tepat di bawah bidang isian terkait. |

---

## 5. Kebutuhan Non-Fungsional (*Non-Functional Requirements*)

### 5.1. Keamanan (*Security*)
- **Perlindungan CSRF:** Setiap pengiriman formulir wajib menyertakan token `@csrf` valid untuk mencegah serangan *Cross-Site Request Forgery*.
- **Pembersihan Input (Sanitasi & XSS):** Seluruh input teks (khususnya kolom tujuan kegiatan) wajib melalui proses sanitasi dan ditampilkan di *view* menggunakan sintaks pelarian string Blade aman `{{ $data }}`.
- **Pencegahan SQL Injection:** Seluruh operasi kueri basis data mutlak menggunakan metode bawaan Eloquent ORM atau *Parameter Binding* PDO.
- **Otorisasi Ketat:** Rute formulir dan pemrosesan dilindungi middleware `['auth', 'role:pengguna']` untuk memastikan pihak luar (tamu tanpa login) atau peran yang tidak sesuai tidak dapat melakukan *bypass*.

### 5.2. Keandalan & Konkurensi (*Reliability & Concurrency*)
- **Daya Tahan 100 Permintaan Bersamaan (*100 Concurrent Users*):** Sesuai prasyarat `CASE_PROJECT.md`, sistem harus mampu menangani hingga 100 sivitas yang mengakses dan mengirim form pada detik yang sama tanpa menimbulkan *server error* atau tembusnya celah *double-booking*.
- **Pesimistic Locking / Transaksi Basis Data:** Saat memeriksa bentrok ketersediaan dan membuat baris reservasi, sistem wajib membungkus proses dalam `DB::transaction()` dengan isolasi memadai guna menghindari kondisi balapan (*race condition*).

### 5.3. Usabilitas & Aksesibilitas Antarmuka (*Usability & UX*)
- **Filosofi Desain Minimalis & Cepat (Prinsip Ponytail):** Struktur tampilan mengutamakan elemen HTML5 asli yang dipadukan dengan utilitas Tailwind CSS dan direktif ringan Alpine.js (`x-data`, `x-model`, `x-show`). Dilarang memuat dependensi JS pihak ketiga yang membebani DOM.
- **Desain Responsif (*Mobile-First*):** Tata letak antarmuka beradaptasi sempurna pada layar ponsel pintar (layar sempit) hingga monitor *desktop*, memudahkan sivitas yang melakukan peminjaman langsung dari lokasi kampus melalui gawai masing-masing.
- **Kejelasan Pesan Galat:** Setiap teks peringatan harus menggunakan bahasa Indonesia yang ramah, informatif, dan tidak ambigu (misal: *"Waktu mulai tidak boleh lebih dari waktu selesai"* atau *"Slot waktu pada jam tersebut telah disetujui untuk kegiatan lain"*).

---

## 6. Spesifikasi Antarmuka Pengguna (*UI/UX Specification*)

### 6.1. Berkas Rujukan & Struktur Komponen
- **Berkas Tampilan:** `resources/views/user/reservation-form.blade.php`
- **Layout Induk:** Menggunakan layout `<x-app-layout title="Form Pengajuan Reservasi" active="reservation-form">`
- **Komponen Blade Reusable:**
  - `<x-input-label>`: Label untuk setiap kotak isian.
  - `<x-text-input>` / `<x-select-input>`: Elemen input dengan kelas Tailwind yang terstandarisasi.
  - `<x-input-error>`: Kotak peringatan teks merah jika terdapat kegagalan validasi pada bidang terkait.
  - `<x-primary-button>`: Tombol aksi utama submit formulir.

### 6.2. Rincian Elemen Formulir Pengajuan
1. **Navigasi Breadcrumb:**
   - Link: *Dasbor Saya* ➔ *Form Pengajuan Reservasi*.
2. **Kartu Ringkasan Fasilitas Terpilih (*Venue Summary Card*):**
   - Menampilkan nama fasilitas terpilih, kategori, kapasitas maksimum orang, serta lokasi gedung secara dinamis.
3. **Pilihan Fasilitas (`facility_id`):**
   - Tipe: Dropdown `<select>` (atau *hidden input* bila pre-selected dari katalog).
   - Opsi: Memuat seluruh fasilitas aktif dari basis data.
4. **Pemilih Tanggal (`date`):**
   - Tipe: `<input type="date">`.
   - Batasan: `min="{{ date('Y-m-d') }}"`. Wajib terisi (*required*).
5. **Pemilih Rentang Waktu (`start_time` & `end_time`):**
   - Tipe: Dropdown `<select>` opsi tetap slot 30 menit.
   - Pilihan Rentang Waktu:
     - `07:00`, `07:30`, `08:00`, `08:30`, `09:00`, `09:30`, `10:00`, `10:30`, `11:00`, `11:30`, `12:00`, `12:30`, `13:00`, `13:30`, `14:00`, `14:30`, `15:00`, `15:30`, `16:00`, `16:30`, `17:00`, `17:30`, `18:00`, `18:30`, `19:00`, `19:30`, `20:00`.
6. **Kategori Kegiatan (`activity_type` - Opsional/Pendukung):**
   - Pilihan: Akademik / Perkuliahan, Kegiatan Organisasi Mahasiswa, Rapat Dinas / Acara Resmi.
7. **Tujuan Penggunaan Ruangan (`purpose`):**
   - Tipe: `<textarea rows="4">`.
   - Placeholder: *"Jelaskan rincian agenda, nama organisasi/panitia, perkiraan jumlah peserta, dan penanggung jawab kegiatan..."*
8. **Tombol Tindakan (*Action Buttons*):**
   - Tombol Batal: Mengarahkan kembali ke `/user/dashboard`.
   - Tombol Ajukan Reservasi: Tombol utama dengan efek *hover*, *focus ring*, dan *loading state* saat proses pengiriman berlangsung.

---

## 7. Arsitektur Backend, Routing, & Logika Kontroler

### 7.1. Pemetaan Rute URL (`routes/web.php`)
```php
use App\Http\Controllers\ReservationController;

Route::middleware(['auth', 'role:pengguna'])->prefix('user')->name('user.')->group(function () {
    // Menampilkan halaman formulir pengajuan reservasi
    Route::get('/reservation-form', [ReservationController::class, 'create'])->name('reservations.create');
    
    // Memproses data formulir reservasi baru
    Route::post('/reservations', [ReservationController::class, 'store'])->name('reservations.store');
});
```

### 7.2. Validasi Server Khusus (`StoreReservationRequest.php`)
Validasi didelegasikan secara terpisah ke dalam kelas `StoreReservationRequest` untuk memisahkan aturan validasi dari tubuh kontroler (*clean architecture*).

```php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('pengguna');
    }

    public function rules(): array
    {
        return [
            'facility_id' => ['required', 'exists:facilities,id'],
            'date'        => ['required', 'date', 'after_or_equal:today'],
            'start_time'  => ['required', 'date_format:H:i', 'regex:/^(0[7-9]|1[0-9]|20):(00|30)$/'],
            'end_time'    => ['required', 'date_format:H:i', 'after:start_time', 'regex:/^(0[7-9]|1[0-9]|20):(00|30)$/'],
            'purpose'     => ['required', 'string', 'min:10', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'facility_id.required'   => 'Fasilitas wajib dipilih.',
            'facility_id.exists'     => 'Fasilitas yang dipilih tidak terdaftar di sistem.',
            'date.required'          => 'Tanggal kegiatan wajib diisi.',
            'date.after_or_equal'    => 'Tanggal kegiatan tidak boleh berada di masa lampau.',
            'start_time.required'    => 'Jam mulai kegiatan wajib dipilih.',
            'start_time.regex'       => 'Jam mulai harus antara 07:00 hingga 20:00 dengan kelipatan 30 menit (menit 00 atau 30).',
            'end_time.required'      => 'Jam selesai kegiatan wajib dipilih.',
            'end_time.after'         => 'Jam selesai harus lebih akhir dari jam mulai kegiatan.',
            'end_time.regex'         => 'Jam selesai harus antara 07:00 hingga 20:00 dengan kelipatan 30 menit (menit 00 atau 30).',
            'purpose.required'       => 'Tujuan penggunaan fasilitas wajib diisi.',
            'purpose.min'            => 'Deskripsi tujuan minimal berisi 10 karakter agar dapat dievaluasi petugas.',
        ];
    }
}
```

### 7.3. Logika Eksekusi Kontroler (`ReservationController.php`)
```php
namespace App\Http\Controllers;

use App\Models\Facility;
use App\Models\Reservation;
use App\Http\Requests\StoreReservationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReservationController extends Controller
{
    /**
     * Menampilkan form pengajuan reservasi.
     */
    public function create(Request $request)
    {
        $facilities = Facility::where('status_aktif', 'aktif')->orderBy('nama')->get();
        $selectedFacilityId = $request->query('facility_id');

        return view('user.reservation-form', compact('facilities', 'selectedFacilityId'));
    }

    /**
     * Memproses penyimpanan permohonan reservasi baru.
     */
    public function store(StoreReservationRequest $request)
    {
        $validated = $request->validated();

        // 1. Verifikasi Status Kelayakan Fasilitas
        $facility = Facility::findOrFail($validated['facility_id']);
        if ($facility->status_aktif !== 'aktif') {
            return back()
                ->withInput()
                ->withErrors(['facility_id' => 'Fasilitas ini sedang dalam masa perbaikan/non-aktif dan tidak dapat dipesan.']);
        }

        // 2. Eksekusi Pengecekan Bentrok dalam Transaksi Basis Data
        return DB::transaction(function () use ($validated, $facility) {
            // Cek apakah ada jadwal bersinggungan yang SUDAH berstatus 'approved'
            $overlapExists = Reservation::where('facility_id', $validated['facility_id'])
                ->where('date', $validated['date'])
                ->where('status', 'approved')
                ->where(function ($query) use ($validated) {
                    $query->where('start_time', '<', $validated['end_time'])
                          ->where('end_time', '>', $validated['start_time']);
                })
                ->exists();

            if ($overlapExists) {
                return back()
                    ->withInput()
                    ->withErrors(['start_time' => 'Rentang waktu yang Anda pilih telah terisi oleh reservasi lain yang telah disetujui. Silakan pilih slot lain.']);
            }

            // 3. Simpan Entitas Reservasi dengan Status Awal 'pending'
            Reservation::create([
                'user_id'     => Auth::id(),
                'facility_id' => $validated['facility_id'],
                'date'        => $validated['date'],
                'start_time'  => $validated['start_time'],
                'end_time'    => $validated['end_time'],
                'purpose'     => $validated['purpose'],
                'status'      => 'pending',
            ]);

            return redirect()
                ->route('user.reservation-history')
                ->with('success', 'Permohonan reservasi berhasil diajukan dan sedang menunggu verifikasi Petugas Sarpras.');
        });
    }
}
```

---

## 8. Skenario Pengujian & Kriteria Penerimaan (*Acceptance Criteria*)

### 8.1. Kriteria Penerimaan Berbasis Format Gherkin
```gherkin
Fitur: Form Pengajuan Reservasi Ruangan oleh Pengguna (Mahasiswa/Dosen/Staf)

  Skenario: Pengajuan reservasi berhasil dengan data valid dan slot kosong
    Dengan Saya adalah pengguna terautentikasi (mahasiswa/dosen/staf) dengan akun aktif
    Dan Saya berada di halaman formulir pengajuan reservasi ("/user/reservation-form")
    Ketika Saya memilih fasilitas "Laboratorium Komputer Terpadu"
    Dan Saya memilih tanggal 3 hari ke depan
    Dan Saya memilih jam mulai "09:00" dan jam selesai "11:30"
    Dan Saya mengisi tujuan kegiatan "Pelatihan Git dan Web Development UKM Komputer"
    Dan Saya menekan tombol "Ajukan Reservasi"
    Maka Sistem berhasil menyimpan data reservasi dengan status "pending"
    Dan Sistem mengalihkan Saya ke halaman riwayat reservasi ("/user/reservation-history")
    Dan Menampilkan pesan sukses "Permohonan reservasi berhasil diajukan..."

  Skenario: Pengajuan gagal karena slot waktu bertabrakan dengan jadwal yang sudah disetujui
    Dengan Fasilitas "Auditorium Utama" telah memiliki reservasi berstatus "approved" pada pukul 13:00 - 15:00
    Ketika Saya mengajukan fasilitas tersebut pada tanggal yang sama untuk pukul 14:00 - 16:00
    Dan Saya menekan tombol "Ajukan Reservasi"
    Maka Sistem membatalkan penyimpanan data
    Dan Sistem mengembalikan formulir dengan nilai input yang dipertahankan
    Dan Menampilkan pesan kesalahan "Rentang waktu yang Anda pilih telah terisi oleh reservasi lain yang telah disetujui."

  Skenario: Pengajuan gagal karena waktu di luar jam operasional kampus
    Ketika Pengguna mencoba mengirimkan jam mulai "06:30" atau jam selesai "21:00"
    Maka Sistem menolak pengiriman data pada level validasi
    Dan Menampilkan pesan galat bahwa jam operasional dibatasi antara 07:00 hingga 20:00 WIB.

  Skenario: Pengajuan gagal karena menit waktu bukan kelipatan 30 menit
    Ketika Pengguna mencoba mengirimkan jam mulai "08:15"
    Maka Validasi regex server menggagalkan request
    Dan Menampilkan pesan galat bahwa menit wajib bernilai 00 atau 30.

  Skenario: Pengajuan gagal karena fasilitas berstatus dalam perbaikan (maintenance)
    Dengan Fasilitas "Ruang Seminar 1" memiliki status_aktif bernilai "maintenance"
    Ketika Pengguna mencoba mengajukan reservasi untuk fasilitas tersebut
    Maka Sistem menolak pengajuan
    Dan Menampilkan pesan "Fasilitas ini sedang dalam masa perbaikan/non-aktif dan tidak dapat dipesan."
```

### 8.2. Matriks Pengujian Kasus Uji (*Test Case Matrix*)

| ID Uji | Kasus Pengujian | Input Data Uji | Hasil yang Diharapkan | Status |
|---|---|---|---|---|
| **TC-USR01-01** | Pengajuan Normal (Positif) | Fasilitas Aktif, Tgl Besok, 08:00 - 10:00, Tujuan 30 karakter | HTTP 302 Redirect ke Riwayat, Record tercatat status `pending`. | Mandatory Pass |
| **TC-USR01-02** | Jam Operasional Sebelum 07:00 | Jam Mulai: 06:30, Jam Selesai: 08:00 | Error validasi `start_time` tertolak (HTTP 422). | Mandatory Pass |
| **TC-USR01-03** | Jam Operasional Sesudah 20:00 | Jam Mulai: 19:00, Jam Selesai: 21:00 | Error validasi `end_time` tertolak (HTTP 422). | Mandatory Pass |
| **TC-USR01-04** | Menit Sembarang (Bukan 00/30) | Jam Mulai: 08:15, Jam Selesai: 09:45 | Error validasi format jam kelipatan 30 menit. | Mandatory Pass |
| **TC-USR01-05** | Waktu Selesai <= Waktu Mulai | Jam Mulai: 10:00, Jam Selesai: 09:30 | Error validasi `end_time` wajib setelah `start_time`. | Mandatory Pass |
| **TC-USR01-06** | Tanggal Masa Lampau | Tanggal: Kemarin | Error validasi `date` minimal hari ini (`after_or_equal`). | Mandatory Pass |
| **TC-USR01-07** | Bentrok Jadwal (*Overlap Approved*) | Slot 09:00 - 11:00 pada ruang yang telah berstatus *approved* jam 10:00 - 12:00 | Gagal simpan, pesan bentrok muncul di bawah waktu. | Mandatory Pass |
| **TC-USR01-08** | Fasilitas Status Maintenance | ID Fasilitas dengan `status_aktif = 'maintenance'` | Gagal simpan, pesan fasilitas dalam perbaikan. | Mandatory Pass |
| **TC-USR01-09** | Tujuan Kegiatan Terlalu Pendek | Input teks tujuan kurang dari 10 karakter | Error validasi `purpose` minimal 10 karakter. | Mandatory Pass |

---

## 9. Instruksi Khusus untuk Programmer (Mandatori)

1. **Anti-Hardcode:** Wajib menghapus segala jenis data *hardcode* (*dummy*) di *frontend* dan langsung menggantinya dengan data dinamis yang terhubung ke *database* via variabel *Controller* (`compact('facilities', 'selectedFacilityId')`).
2. **Fleksibilitas Blueprint:** Ingat bahwa isi dokumen ini adalah *blueprint* dasar spesifikasi. Anda diberikan kebebasan penuh untuk melakukan **improvisasi** dan menyempurnakan struktur atau estetika kodenya selama tidak menyimpang dari tujuan utama bisnis.
3. **Pemisahan Pengerjaan (*Separation of Concerns*):** Walaupun Anda ditugaskan sendirian sebagai *Fullstack* (mengerjakan UI dan Database sekaligus), **DILARANG KERAS** mengerjakannya secara bersamaan dalam satu *commit*. Kerjakan fase *Frontend* hingga selesai, lalu beralih ke fase *Backend* (atau sebaliknya). Ini diwajibkan oleh pedoman standar `RULE_FRONTEND.md` dan `RULE_BACKEND.md`.
4. **Patuh pada Aturan Induk:** Sebelum mulai mengetikkan satu baris kode pun, Anda diwajibkan untuk mereview dan mematuhi seluruh *guidelines* yang tercantum di file `RULE_FRONTEND.md` dan `RULE_BACKEND.md`.
5. **Kesesuaian Bisnis Inti:** Jangan menulis fungsi yang melenceng! Cek ulang dokumen `CASE_PROJECT.md` setiap kali Anda ragu mengenai aturan bisnis dari fitur yang sedang dikerjakan.
6. **Kesesuaian Arsitektur:** Pastikan *controller* dan *view* yang Anda buat diletakkan persis pada jalur folder yang sudah diamanatkan oleh peta struktur `PLAN_DEVELOPMENT.md`.
7. **Standar Teknologi CAVA:** Gunakan aturan *stack* (seperti Tailwind CSS, Alpine.js, Spatie, dll) sesuai perintah resmi pada dokumen `TECHSTACK.md`.
8. **Kepatuhan Mutlak Sistem:** Taati seluruh undang-undang dan aturan *workflow* di dalam `RULE_PROJECT.md` tanpa terkecuali.
9. **Finalisasi Valid:** Anda HANYA diizinkan mencentang progress penyelesaian fitur ini di `PLAN_PROJECT.md` SETELAH pengujian (*testing*) secara manual tuntas dilakukan tanpa celah (*bug*), sesaat sebelum melakukan integrasi akhir (*push*).
10. **Standar Kolaborasi Git:** Sebelum melakukan *commit* dan *push*, Anda WAJIB memastikan bahwa tata cara dan penamaan pesannya sesuai dengan aturan di `GUIDE_GITHUB.md`.
11. **Alur Branching & Pull Request:** Sesuai `GUIDE_GITHUB.md`, sebelum mulai koding, WAJIB membuat *branch* baru (`feature/usr01-pengajuan-reservasi`). Setelah selesai dan di-*push*, wajib membuat **Pull Request (PR)** ke *branch* `develop`.

---

## 10. Catatan Penyesuaian Tambahan (Diisi oleh Programmer)
*(Bagian ini wajib diisi jika Anda melakukan penyesuaian/improvisasi yang berbeda dari Blueprint di atas selama proses koding! Kosongkan jika tidak ada).*

- ...
