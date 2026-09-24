# SRS-USR-04: Form Pelaporan Kerusakan Fasilitas (Ticketing)

## 1. Identifikasi Dokumen & Metadata SRS
- **Kode Spesifikasi:** SRS-USR-04
- **Judul Fitur:** Formulir Pelaporan Malfungsi & Kerusakan Fasilitas Kampus (Ticketing)
- **Aktor Utama:** Pengguna Terautentikasi (Sivitas Akademika: Mahasiswa, Dosen, Tenaga Kependidikan/Staf)
- **Role Sistem:** `pengguna` (Spatie Laravel Permission) dengan status akun aktif (`status = 'active'`)
- **Ekuivalen User Story:** US-6 (*"Sebagai Pengguna, saya bisa melaporkan kerusakan fasilitas dengan mengunggah foto dan deskripsi agar segera diperbaiki."*)
- **Modul Sistem:** Modul Pelaporan Kerusakan (*Sub-modul: Portal Pengguna / Sivitas Akademika*)
- **Tingkat Prioritas:** Fundamental / *Must Have* (Kritis)
- **Status Dokumen:** Versi 2.0 (Spesifikasi Lengkap & Terstandarisasi)

---

## 2. Deskripsi Alur Bisnis & Karakteristik Pengguna

### 2.1. Karakteristik & Kebutuhan Pengguna
Ketika sivitas akademika sedang beraktivitas di lingkungan kampus (perkuliahan di ruang kelas, praktikum di laboratorium komputer, atau acara di gedung aula/auditorium) dan mendapati fasilitas sarpras mengalami gangguan atau kerusakan fisik:
1. **Laporan Cepat Tanpa Birokrasi:** Pengguna membutuhkan formulir terpadu yang dapat diakses langsung dari peramban ponsel atau laptop mereka tanpa harus mendatangi kantor biro sarpras secara fisik.
2. **Kategori yang Terarah:** Pilihan kategori malfungsi (misal: AC & Pendingin, Kelistrikan, Proyektor & Audio, Mebel/Kursi, dll.) mempermudah petugas mendisposisikan teknisi yang tepat.
3. **Bukti Visual Autentik:** Lampiran foto kerusakan mempercepat estimasi tingkat keparahan kendala sebelum petugas meluncur ke lokasi.

### 2.2. Ringkasan Alur Pelaporan
1. Pengguna membuka formulir pelaporan di URL `/user/report-form` melalui menu samping (*sidebar*) atau tombol pintasan.
2. Form memuat daftar fasilitas kampus yang berstatus aktif dari basis data. Jika pengguna datang dari tautan ruangan tertentu (`?facility_id=2`), ruangan tersebut otomatis terpilih.
3. Pengguna memilih kategori kerusakan, menuliskan deskripsi masalah dan lokasi spesifik secara detail ($\ge 10$ karakter), serta memilih berkas foto bukti nyata (JPG/PNG $\le 2\text{ MB}$).
4. Pengguna melihat pratinjau (*preview*) foto secara instan sebelum mengirimkan laporan.
5. Saat formulir disubmit, sistem memvalidasi kelayakan berkas di sisi klien dan server. Sistem membuat kode tiket unik `RPT-YYYYMMDD-XXXX`, menyimpan foto ke direktori publik, dan mencatat laporan ke tabel `damage_reports` dengan status default `baru`.
6. Pengguna dialihkan ke halaman riwayat pelaporan dengan pesan banner notifikasi hijau sukses.

---

## 3. Aturan Bisnis Mutlak (*Business Rules*)

| Kode Aturan | Nama Aturan | Deskripsi & Batasan Ketat | Tingkat Penegakan |
|---|---|---|---|
| **BR-USR04-01** | **Batasan Validasi Berkas Foto Bukti** | Lampiran foto bukti **wajib berupa gambar valid dengan format JPEG, JPG, atau PNG** dan ukuran berkas **maksimal 2.048 KB (2 MB)**. Berkas selain gambar (seperti PDF, DOCX, ZIP, EXE) atau berukuran $> 2\text{ MB}$ mutlak ditolak server. | Client-side & Server-side |
| **BR-USR04-02** | **Validitas Fasilitas Terdaftar** | Nilai `facility_id` wajib terdaftar pada kolom `id` tabel `facilities` dan berstatus aktif. Manipulasi ID ruangan yang tidak ada akan digagalkan validasi `exists:facilities,id`. | Server-side (FormRequest) |
| **BR-USR04-03** | **Format Kode Tiket Laporan Unik** | Sistem wajib menghasilkan kode tiket pelaporan acak unik otomatis dengan format: `RPT-YYYYMMDD-XXXX` (contoh: `RPT-20260924-A1B2`). Kode ini dijamin tidak akan pernah duplikat di tabel `damage_reports`. | Controller / Database |
| **BR-USR04-04** | **Status Awal Tiket Pengaduan** | Setiap tiket pengaduan baru yang berhasil dikirimkan secara mutlak memiliki status awal `baru` (belum ditangani oleh petugas/teknisi). | Controller / Database Default |
| **BR-USR04-05** | **Kelayakan Deskripsi Kerusakan** | Kolom deskripsi masalah wajib diisi dengan teks bermakna **minimal 10 karakter** dan maksimal 1.000 karakter untuk mencegah laporan kosong atau tidak jelas (*spam*). | Client-side & Server-side |

---

## 4. Kebutuhan Fungsional (*Functional Requirements*)

| ID Kebutuhan | Nama Kebutuhan | Deskripsi Spesifikasi Fungsional |
|---|---|---|
| **FR-USR04-001** | Pemuatan Formulir Dinamis | Sistem harus menampilkan halaman formulir di `/user/report-form` dengan dropdown fasilitas kampus yang ditarik secara dinamis dari database (`Facility::where('status', 'aktif')`). |
| **FR-USR04-002** | Penanganan Pre-selected Fasilitas | Jika URL memuat parameter `?facility_id=...`, sistem harus otomatis menandai fasilitas tersebut sebagai opsi terpilih pada dropdown. |
| **FR-USR04-003** | Pilihan Kategori Kerusakan Interaktif | Sistem harus menyediakan tombol-tombol pill kategori kerusakan yang terhubung dengan input tersembunyi (*hidden input*) untuk dikirim ke server. |
| **FR-USR04-004** | Pratinjau Foto Bukti (*Client Preview*) | Sistem harus menampilkan pratinjau gambar instan saat pengguna memilih berkas foto, serta menyediakan tombol hapus/ganti foto. |
| **FR-USR04-005** | Validasi Klien Ukuran Berkas | Sistem harus menolak dan memperingatkan pengguna melalui JavaScript jika berkas yang dipilih melebihi ukuran 2 MB sebelum request dikirimkan ke jaringan. |
| **FR-USR04-006** | Penyimpanan Aman & Pembuatan Tiket | Kontroler backend wajib memvalidasi data via `StoreDamageReportRequest`, menyimpan foto ke disk storage publik, menyimpan record ke tabel `damage_reports`, dan mengalihkan pengguna dengan notifikasi sukses. |
| **FR-USR04-007** | Retensi Input Form Pasca-Error | Jika validasi server gagal, input teks deskripsi, fasilitas terpilih, dan kategori yang sudah dipilih tidak boleh hilang (menggunakan `old()`). |

---

## 5. Kebutuhan Non-Fungsional (*Non-Functional Requirements*)

### 5.1. Keamanan & Integritas File (*Security*)
- **Perlindungan Eksekusi Malware:** Berkas foto disimpan di folder penyimpanan yang terisolasi dengan nama berkas acak (*hashed filename*). Ekstensi berkas diverifikasi secara ketat berdasarkan *MIME Type* riil (`image/jpeg`, `image/png`), bukan sekadar ekstensi nama file.
- **Proteksi CSRF:** Formulir mutlak menyertakan token `@csrf` untuk mencegah manipulasi form eksternal.

### 5.2. Kinerja & Efisiensi (*Performance*)
- **Batasan Beban Jaringan:** Pengecekan ukuran file di peramban mencegah pengguna membuang-buang kuota mengunggah file raksasa yang pasti akan ditolak server.

---

## 6. Spesifikasi Antarmuka Pengguna (*UI/UX Specification*)

- **Berkas Tampilan:** `resources/views/user/report-form.blade.php`
- **Layout Induk:** `<x-app-layout title="Form Lapor Kerusakan Fasilitas" active="report-form">`
- **Elemen Antarmuka:**
  - Breadcrumb navigasi (*Dasbor Saya > Form Lapor Kerusakan*).
  - Badge info target SLA respon penanganan sarpras (&lt; 24 Jam).
  - Kotak pilihan ruangan (*select dropdown*) dinamis.
  - Pilihan kategori kerusakan (*pill buttons*: AC & Pendingin, Kelistrikan, Proyektor & Audio, Mebel, Jaringan, Fisik Bangunan, Kebersihan, Lainnya).
  - Textarea deskripsi masalah dengan placeholder informatif dan pesan error validasi.
  - Komponen dropzone unggah foto bukti dengan ikon, indikator batas 2 MB, dan thumbnail pratinjau.
  - Tombol batal dan tombol submit beranimasi spinner saat proses pengiriman berlangsung.

---

## 7. Arsitektur Backend & Rute Rujukan

### 7.1. Pemetaan Rute URL (`routes/web.php`)
```php
use App\Http\Controllers\ReportController;

Route::middleware(['auth', 'role:pengguna'])->prefix('user')->name('user.')->group(function () {
    Route::get('/report-form', [ReportController::class, 'create'])->name('report-form');
    Route::post('/reports', [ReportController::class, 'store'])->name('reports.store');
});
```

### 7.2. Validasi FormRequest (`StoreDamageReportRequest.php`)
```php
public function rules(): array
{
    return [
        'facility_id'      => ['required', 'integer', 'exists:facilities,id'],
        'category'         => ['required', 'string', 'max:100'],
        'description'      => ['required', 'string', 'min:10', 'max:1000'],
        'attachment_photo' => ['required', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
    ];
}
```

### 7.3. Logika Kontroler (`ReportController@store`)
```php
public function store(StoreDamageReportRequest $request): RedirectResponse
{
    $validated = $request->validated();
    $userId = Auth::id() ?? 1;

    // 1. Generate Kode Tiket Laporan Unik (RPT-YYYYMMDD-XXXX)
    $datePrefix = Carbon::now()->format('Ymd');
    do {
        $randomCode = strtoupper(substr(bin2hex(random_bytes(2)), 0, 4));
        $reportCode = sprintf('RPT-%s-%s', $datePrefix, $randomCode);
    } while (DamageReport::where('report_code', $reportCode)->exists());

    // 2. Upload Foto Bukti ke Storage Public
    $photoPath = null;
    if ($request->hasFile('attachment_photo')) {
        $photoPath = $request->file('attachment_photo')->store('reports', 'public');
    }

    // 3. Simpan ke Database
    $report = DamageReport::create([
        'report_code'        => $reportCode,
        'user_id'            => $userId,
        'facility_id'        => $validated['facility_id'],
        'category'           => $validated['category'],
        'description'        => $validated['description'],
        'attachment_photo'   => $photoPath,
        'status'             => 'baru',
        'is_facility_locked' => false,
    ]);

    return redirect()->route('user.report-history')
        ->with('success', "Laporan kerusakan berhasil dikirim dengan kode tiket {$reportCode}. Petugas sarpras akan segera menindaklanjuti.");
}
```

---

## 8. Skenario Pengujian & Kriteria Penerimaan (*Acceptance Criteria*)

### 8.1. Format Gherkin
```gherkin
Fitur: Formulir Pelaporan Kerusakan Fasilitas Kampus

  Skenario: Pengguna berhasil mengirim laporan kerusakan dengan data lengkap dan foto valid
    Dengan Saya adalah pengguna terautentikasi di halaman "/user/report-form"
    Ketika Saya memilih fasilitas "Lab Komputer A"
    Dan Saya memilih kategori "AC & Pendingin"
    Dan Saya mengisikan deskripsi "AC mengeluarkan bunyi berisik dan tidak dingin sama sekali"
    Dan Saya melampirkan berkas foto bukti "ac_rusak.jpg" berukuran 1 MB
    Dan Saya menekan tombol "Kirim Laporan Kerusakan"
    Maka Sistem menyimpan laporan ke tabel "damage_reports" dengan status "baru"
    Dan Berkas foto tersimpan pada direktori storage "reports"
    Dan Pengguna dialihkan ke halaman riwayat laporan dengan pesan sukses

  Skenario: Pengguna mengunggah berkas foto yang melebihi batas 2 MB
    Dengan Saya berada di form pelaporan kerusakan
    Ketika Saya memilih berkas gambar dengan ukuran 3.5 MB
    Maka Peramban memunculkan peringatan kesalahan ukuran file
    Dan Sistem menggagalkan pengunggahan dengan pesan error validasi ukuran maksimal 2 MB

  Skenario: Pengguna mengirim laporan dengan deskripsi kurang dari 10 karakter
    Dengan Saya berada di form pelaporan kerusakan
    Ketika Saya mengetikkan deskripsi "rusak"
    Dan Saya menekan tombol submit
    Maka Sistem menolak permohonan dengan pesan validasi "Deskripsi minimal 10 karakter"
    Dan Nilai fasilitas dan kategori yang telah dipilih tetap bertahan di form
```

### 8.2. Matriks Kasus Uji (*Test Case Matrix*)

| ID Uji | Skenario Pengujian | Aksi Pengujian | Hasil yang Diharapkan | Status |
|---|---|---|---|---|
| **TC-USR04-01** | Laporan Sukses dengan Foto Valid | POST /user/reports (Payload lengkap + JPG 1MB) | Tiket tersimpan dengan status `baru`, redirect flash success. | Mandatory Pass |
| **TC-USR04-02** | Penolakan Foto $> 2\text{ MB}$ | POST dengan dummy file gambar 3 MB | Error validasi `attachment_photo` (max 2048 KB). | Mandatory Pass |
| **TC-USR04-03** | Penolakan Format Non-Gambar | POST dengan file `.pdf` atau `.txt` | Error validasi format berkas harus berupa gambar. | Mandatory Pass |
| **TC-USR04-04** | Penolakan Deskripsi Pendek ($< 10$) | POST dengan deskripsi "rusak" | Error validasi `description` minimal 10 karakter. | Mandatory Pass |
| **TC-USR04-05** | Penolakan Fasilitas Fiktif | POST dengan `facility_id = 99999` | Error validasi fasilitas tidak valid. | Mandatory Pass |
| **TC-USR04-06** | Integritas Storage Disk Publik | Periksa `Storage::disk('public')->assertExists($path)` | Berkas fisik benar-benar ada di storage. | Mandatory Pass |

---

## 9. Instruksi Khusus untuk Programmer (Mandatori)

1. **Anti-Hardcode:** Mengambil seluruh data fasilitas dari database via `Facility::where('status', 'aktif')`.
2. **Pemisahan Pengerjaan (*Separation of Concerns*):** Kerjakan fase **Frontend terlebih dahulu** (`report-form.blade.php`), lakukan commit terpisah, lalu beralih ke fase **Backend** (`StoreDamageReportRequest.php`, `ReportController.php`, `web.php`) dan commit terpisah.
3. **Penyimpanan Storage Simetris:** Gunakan `Storage::disk('public')->putFile(...)` atau `$request->file('attachment_photo')->store('reports', 'public')`.
4. **Standar Kolaborasi Git:** Gunakan branch `feature/usr04/pelaporan-kerusakan` dan commit konvensional berbahasa Indonesia.
