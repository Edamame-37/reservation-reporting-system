# Panduan Pembelajaran Kode: Fitur USR-04
*(Form Pelaporan Kerusakan & Malfungsi Fasilitas - CAVA)*

Dokumen ini disusun khusus sebagai **buku saku dan panduan belajar mendalam** untuk memahami 100% alur kerja, logika bisnis, keamanan unggah berkas, dan keputusan arsitektur di balik kode program fitur:
**USR-04: Form Pelaporan Kerusakan Fasilitas (Ticketing)**.

---

## 🗺️ Peta Konsep: Alur Pengaduan Tiket Kerusakan (*Request Lifecycle*)

Ketika seorang mahasiswa/dosen/staf mengisi formulir keluhan dan mengunggah foto bukti kerusakan, berikut adalah perjalanan data di balik layar:

```mermaid
sequenceDiagram
    autonumber
    actor User as Pengguna (Browser)
    participant Client as Alpine.js (Client Check)
    participant Route as routes/web.php
    participant Request as StoreDamageReportRequest
    participant Controller as ReportController
    participant Storage as File Storage (public/reports)
    participant DB as MySQL (damage_reports)
    participant View as Blade (report-history)

    User->>Client: Pilih berkas foto (JPG/PNG)
    alt Ukuran Berkas > 2 MB di Browser
        Client-->>User: Peringatan seketika "Ukuran melebihi 2 MB" (Batal Upload)
    else Ukuran Berkas Valid (<= 2 MB)
        Client->>User: Render pratinjau thumbnail (FileReader API)
        User->>Route: POST /user/reports (multipart/form-data)
        Route->>Request: Validasi MIME gambar, max:2048 KB, exists:facilities
        alt Validasi Gagal (Bukan gambar / file korup / deskripsi pendek)
            Request-->>User: Redirect Back + Error Messages + Old Input
        else Validasi Lolos
            Request->>Controller: Panggil method store(request)
            Controller->>Controller: Buat kode tiket unik (RPT-YYYYMMDD-XXXX)
            Controller->>Storage: Simpan berkas foto ke disk publik (reports/)
            Storage-->>Controller: Dapatkan path penyimpanan (reports/hash.jpg)
            Controller->>DB: INSERT into damage_reports (Status = 'baru')
            DB-->>Controller: Record tersimpan sukses
            Controller-->>View: Redirect ke /user/report-history + Flash Sukses
            View-->>User: Tampilkan Riwayat Tiket + Notifikasi Hijau
        end
    end
```

---

## 📂 Bagian 1: Bedah Validasi Input & Keamanan Unggah Berkas

### 1.1. FormRequest: `app/Http/Requests/StoreDamageReportRequest.php`

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

#### Mengapa Validasi Berkas Sangat Kritis?
Celah keamanan pada fitur unggah berkas (*File Upload Vulnerability*) adalah salah satu dari celah paling berbahaya (OWASP Top 10):
1. **`image`**:
   - Laravel membaca *magic bytes* (header biner) berkas untuk membuktikan berkas tersebut benar-benar sebuah gambar, bukan skrip PHP jahat yang sengaja diganti namanya menjadi `virus.php.jpg`.
2. **`mimes:jpeg,png,jpg`**:
   - Membatasi tipe MIME hanya pada format foto standar web.
3. **`max:2048`**:
   - Satuan aturan `max` untuk berkas pada Laravel adalah **Kilobyte (KB)**.
   - $2.048\text{ KB} = 2\text{ MB}$.
   - Mencegah serangan *Denial of Service* (DoS) di mana peretas mencoba menghabiskan ruang penyimpanan server lokal (*Disk Exhaustion*).
4. **`exists:facilities,id`**:
   - Memastikan bahwa ruangan yang dilaporkan benar-benar ada di tabel `facilities` kampus.
5. **`min:10`**:
   - Mencegah laporan asal-asalan seperti hanya mengetik kata *"rusak"* atau *"jelek"*. Pelapor diwajibkan menuliskan detail yang bermakna.

---

## 📂 Bagian 2: Bedah Antarmuka Dinamis (`report-form.blade.php`)

### 2.1. Atribut Wajib Formulir Unggah Berkas:
```blade
<form action="{{ route('user.reports.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
```
- **`enctype="multipart/form-data"`**:
  - Secara default, form HTML mengirim data dalam format `application/x-www-form-urlencoded` yang hanya bisa membawa teks string.
  - Untuk mengirimkan aliran biner berkas foto (*binary stream*), atribut `enctype="multipart/form-data"` **mutlak wajib disertakan**. Jika lupa, `$request->file('attachment_photo')` di backend akan bernilai `null`!

### 2.2. Validasi Klien Cepat via Alpine.js (*Client-side Defense*):
```javascript
handleFile(e) {
    const file = e.target.files[0];
    if (file) {
        if (file.size > 2 * 1024 * 1024) {
            this.fileError = 'Ukuran berkas melebihi batas maksimal 2 MB...';
            e.target.value = '';
            this.imagePreview = null;
            return;
        }
        const reader = new FileReader();
        reader.onload = (event) => {
            this.imagePreview = event.target.result;
        };
        reader.readAsDataURL(file);
    }
}
```
- Menggunakan `FileReader` API peramban untuk membaca berkas lokal dan menampilkannya sebagai *data URL* (*Base64*) pada elemen `<img>` tanpa harus mengunggahnya terlebih dahulu ke server (*Zero Network Cost*).
- Pengecekan `file.size > 2 * 1024 * 1024` langsung di peramban menghemat kuota internet pengguna dan mengurangi beban lalu lintas jaringan (*bandwidth efficiency*).

### 2.3. Retensi Data Masukan Pasca-Error:
- Kolom deskripsi menggunakan `{{ old('description') }}` agar ketikan panjang pelapor tidak hilang jika validasi server menolak foto.
- Dropdown fasilitas mengingat pilihan pengguna dengan: `{{ old('facility_id', $selectedFacilityId) == $facility->id ? 'selected' : '' }}`.

---

## 📂 Bagian 3: Bedah Logika Kontroler (`ReportController.php`)

```php
public function store(StoreDamageReportRequest $request): RedirectResponse
{
    $validated = $request->validated();
    $userId = Auth::id() ?? 1;

    // 1. Generate Kode Tiket Laporan Unik (RPT-YYYYMMDD-XXXX)
    $datePrefix = Carbon::now()->format('Ymd');
    do {
        $randomSeq  = strtoupper(substr(bin2hex(random_bytes(2)), 0, 4));
        $reportCode = sprintf('RPT-%s-%s', $datePrefix, $randomSeq);
    } while (DamageReport::where('report_code', $reportCode)->exists());

    // 2. Unggah Foto Bukti ke Storage Public Disk
    $photoPath = null;
    if ($request->hasFile('attachment_photo')) {
        $photoPath = $request->file('attachment_photo')->store('reports', 'public');
    }

    // 3. Simpan Data Tiket Pengaduan ke Database
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
        ->with('success', "Laporan kerusakan berhasil dikirim dengan kode tiket {$reportCode}...");
}
```

### Mengapa Menggunakan `$request->file(...)->store('reports', 'public')`?
1. **Penamaan Berkas Acak yang Aman (*Hashed Filename*):**
   - Laravel secara otomatis membuat nama acak unik (seperti `a8f9c2d1b5...jpg`).
   - Mencegah penimpaan file jika dua mahasiswa mengunggah foto dengan nama yang sama persis (misal: `foto.jpg`).
2. **Penyimpanan di Disk Publik:**
   - Disimpan di `storage/app/public/reports`, yang ditautkan ke `public/storage/reports` via `php artisan storage:link`.
   - Foto dapat ditampilkan secara publik kepada teknisi dan admin di dasbor mereka melalui `asset('storage/' . $report->attachment_photo)`.

---

## 🧪 Bagian 4: Bedah Pengujian Otomatis (`ReportFeatureTest.php`)

```php
test('USR-04: Pengguna berhasil mengirim laporan kerusakan dengan foto bukti valid (TC-USR04-02)', function () {
    $fakeImage = UploadedFile::fake()->create('bukti_kerusakan.jpg', 500, 'image/jpeg');

    $payload = [
        'facility_id'      => $this->facility->id,
        'category'         => 'AC & Pendingin',
        'description'      => 'Unit AC di baris depan mati total dan mengeluarkan bau hangus.',
        'attachment_photo' => $fakeImage,
    ];

    $response = $this->actingAs($this->user)->post(route('user.reports.store'), $payload);

    $response->assertRedirect(route('user.report-history'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('damage_reports', [
        'user_id'     => $this->user->id,
        'facility_id' => $this->facility->id,
        'status'      => 'baru',
    ]);
});
```

### Catatan Teknis Pengujian Pest:
- **`UploadedFile::fake()->create('bukti.jpg', 500, 'image/jpeg')`**:
  - Digunakan untuk mensimulasikan file unggahan berukuran 500 KB tanpa bergantung pada ekstensi PHP `gd` di mesin pengembang.
- **`Storage::fake('public')`**:
  - Menyediakan direktori virtual di memori saat pengujian berlangsung, sehingga folder `storage` lokal asli tidak akan tercemar oleh foto-foto sampah uji coba.

---

## 🎓 Tanya Jawab Kunci (Persiapan Sidang / Review Dosen)

| Pertanyaan Penguji | Jawaban Teknis Terbaik Anda |
|---|---|
| *"Mengapa foto bukti tidak disimpan langsung sebagai biner (BLOB) di tabel MySQL?"* | "Menyimpan gambar langsung di database MySQL (tipe BLOB) akan menyebabkan ukuran basis data membengkak drastis (*Database Bloat*), memperlambat proses *backup/restore*, serta menghabiskan memori RAM server database. Praktik standar industri terbaik adalah menyimpan file fisik di sistem penyimpanan berkas (*File Storage Disk*) dan hanya menyimpan alamat jalurnya (*file path*) pada kolom basis data." |
| *"Bagaimana Anda mencegah celah keamanan unggah berkas (misal ada yang mencoba mengunggah shell PHP)?"* | "Pertama, kami menerapkan validasi aturan `image` dan `mimes:jpeg,png,jpg` pada `StoreDamageReportRequest` yang memeriksa header biner (*Magic Bytes*) berkas, bukan sekadar melihat ekstensi nama file. Kedua, method `store()` Laravel mengacak nama file menjadi string *hash* unik di direktori terisolasi, sehingga file tidak dapat dieksekusi langsung oleh penyerang." |
| *"Mengapa validasi ukuran maksimal 2 MB dilakukan di dua tempat (JavaScript dan PHP Laravel)?"* | "Ini adalah penerapan prinsip *Defense in Depth*. Validasi di sisi peramban (JavaScript) ditujukan untuk *User Experience* agar pengguna langsung tahu bahwa file-nya kebesaran tanpa harus menunggu proses upload yang lama. Sedangkan validasi di sisi server (Laravel) adalah benteng pertahanan mutlak (*Zero Trust*) yang tidak bisa di-bypass meskipun pengguna mematikan JavaScript atau mengirim request via cURL/Postman." |
| *"Apa arti status awal 'baru' pada tiket laporan kerusakan?"* | "Status `'baru'` adalah status awal (*Initial State*) pada alur kerja *ticketing*. Status ini menandakan bahwa laporan telah berhasil dicatat oleh sistem namun belum diinspeksi atau dialokasikan oleh Petugas Sarpras ke teknisi lapangan." |

---

# Panduan Pembelajaran Kode: Fitur USR-05
*(Pelacakan Status & Riwayat Laporan Kerusakan - CAVA)*

Dokumen ini melengkapi bab sebelumnya untuk memahami secara tuntas alur kerja, logika kueri efisien, dan rendering dinamis dari fitur:
**USR-05: Pelacakan Status Laporan (Tiket) Pengguna**.

---

## 🗺️ Peta Konsep: Alur Pelacakan Tiket Kerusakan (*Query Lifecycle*)

```mermaid
sequenceDiagram
    autonumber
    actor User as Pengguna (Mahasiswa/Dosen)
    participant Route as routes/web.php
    participant Controller as ReportController@history
    participant DB as MySQL Database
    participant View as Blade (report-history)

    User->>Route: GET /user/report-history?status=diproses&search=AC
    Route->>Controller: Panggil history(request)
    Controller->>Controller: Ambil user_id dari sesi login (Auth::id())
    
    rect rgb(240, 248, 255)
        Note over Controller,DB: 1. Hitung Rekapitulasi Badge (1 Kali Kueri Agregasi)
        Controller->>DB: SELECT COUNT(*) as total, COUNT(CASE WHEN status='baru'...) FROM damage_reports WHERE user_id = :id
        DB-->>Controller: Return counts [all, baru, diproses, selesai, ditolak]
    end

    rect rgb(245, 255, 250)
        Note over Controller,DB: 2. Kueri Tiket Terisolasi & Eager Loading
        Controller->>DB: SELECT * FROM damage_reports WHERE user_id = :id AND status = 'diproses' AND (deskripsi LIKE '%AC%' OR facility.name LIKE '%AC%')
        Controller->>DB: Eager load facilities & users (handlers)
        DB-->>Controller: Return data terpaginasi (10 baris)
    end

    Controller->>View: Render view('user.report-history', compact('reports', 'counts', 'activeStatus'))
    View-->>User: Tampilkan tabel riwayat tiket dinamis, tab counter, modal foto & catatan resolusi
```

---

## 📂 Bagian 1: Bedah Logika Backend & Optimalisasi Kueri (`ReportController.php`)

### 1.1. Agregasi Tunggal (*Single-Query Conditional Aggregation*)
Menghitung jumlah tiket pada masing-masing tab status (`Semua`, `Baru`, `Diproses`, `Selesai`, `Ditolak`) sering kali dilakukan dengan 5 kali pemanggilan `count()`. Hal ini memicu 5 kali round-trip ke database:

```php
// ❌ KURANG OPTIMAL: Memicu 5 kali kueri kueri ke MySQL
$all      = DamageReport::where('user_id', $userId)->count();
$baru     = DamageReport::where('user_id', $userId)->where('status', 'baru')->count();
$diproses = DamageReport::where('user_id', $userId)->where('status', 'diproses')->count();
$selesai  = DamageReport::where('user_id', $userId)->where('status', 'selesai')->count();
$ditolak  = DamageReport::where('user_id', $userId)->where('status', 'ditolak')->count();
```

Di CAVA, kita mengoptimalkannya menjadi **1 kueri tunggal yang sangat cepat**:
```php
// ✅ SANGAT OPTIMAL: Hanya 1 kali kueri dengan CASE WHEN
$rawCounts = DamageReport::where('user_id', $userId)
    ->selectRaw("
        COUNT(*) as total,
        COUNT(CASE WHEN status = 'baru' THEN 1 END) as baru,
        COUNT(CASE WHEN status = 'diproses' THEN 1 END) as diproses,
        COUNT(CASE WHEN status = 'selesai' THEN 1 END) as selesai,
        COUNT(CASE WHEN status = 'ditolak' THEN 1 END) as ditolak
    ")->first();
```

### 1.2. Pencegahan Kebocoran Data (*Security: IDOR Prevention*)
Klausul `where('user_id', $userId)` dipasang secara mutlak pada kueri utama. Pengguna hanya dapat memantau tiket yang diajukan oleh akunnya sendiri. Hal ini mencegah kerentanan **IDOR (*Insecure Direct Object Reference*)**.

### 1.3. Eliminasi *N+1 Query Problem* via Eager Loading
Setiap baris laporan memerlukan nama fasilitas (`$report->facility->name`) dan nama petugas penangan (`$report->handler->name`).
- Jika tanpa Eager Loading (Lazy Loading), menampilkan 10 baris laporan akan menghasilkan **1 + 10 + 10 = 21 kueri database**!
- Dengan Eager Loading:
  ```php
  $query = DamageReport::with(['facility', 'handler'])->where('user_id', $userId);
  ```
  MySQL hanya mengeksekusi **3 kueri**: 1 kueri utama laporan, 1 kueri untuk mengambil fasilitas terkait (`WHERE id IN (...)`), dan 1 kueri untuk mengambil data teknisi.

### 1.4. Pencarian Lintas Tabel Menggunakan `whereHas`
Ketika pengguna mencari fasilitas (misal: "Lab Jaringan"), kata kunci tidak berada di tabel `damage_reports`, melainkan di tabel `facilities`. Eloquent `whereHas` menyatukan kueri secara elegan:
```php
$query->where(function ($q) use ($keyword) {
    $q->where('report_code', 'like', "%{$keyword}%")
      ->orWhere('description', 'like', "%{$keyword}%")
      ->orWhere('category', 'like', "%{$keyword}%")
      ->orWhereHas('facility', function ($fq) use ($keyword) {
          $fq->where('name', 'like', "%{$keyword}%");
      });
});
```

---

## 📂 Bagian 2: Bedah Antarmuka Dinamis (`report-history.blade.php`)

### 2.1. Mempertahankan Parameter URL Saat Pindah Tab atau Paging
Ketika pengguna memfilter status ke `"diproses"` lalu melakukan pencarian, URL yang terbentuk adalah:
`/user/report-history?status=diproses&search=proyektor`

Di Blade, link tab status dibuat menggunakan helper pintar:
```blade
$url = request()->fullUrlWithQuery(['status' => $key, 'page' => 1]);
```
Dan pada controller, paginasi dilengkapi dengan:
```php
$reports = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();
```
`withQueryString()` memastikan bahwa saat pengguna mengklik tombol halaman 2 atau 3, parameter `status` dan `search` **tidak hilang**.

### 2.2. Modal Pratinjau Foto Riil (Alpine.js)
Foto yang tersimpan di disk publik dipanggil dengan helper `asset('storage/' . $report->attachment_photo)`. Objek data dikirimkan ke fungsi Alpine.js:
```blade
@php
    $photoData = [
        'id'       => $report->report_code,
        'venue'    => $report->facility->name ?? 'Fasilitas Kampus',
        'desc'     => $report->description,
        'photoUrl' => $report->attachment_photo ? asset('storage/' . $report->attachment_photo) : null,
    ];
@endphp
<button @click="openPhoto({{ json_encode($photoData) }})">Lihat</button>
```
Sehingga tidak ada lagi foto palsu dummy Unsplash di dalam kode!

---

## 🧪 Bagian 3: Bedah Pengujian Otomatis (`ReportFeatureTest.php`)

10 pengujian otomatis dijalankan secara terisolasi menggunakan transaksi basis data:
1. **TC-USR05-01:** Verifikasi bahwa tiket pelapor tampil lengkap dengan kode tiket, nama fasilitas, deskripsi, dan badge status.
2. **TC-USR05-02:** Verifikasi isolasi keamanan (`assertDontSee`), memastikan laporan mahasiswa lain tidak bocor.
3. **TC-USR05-03:** Verifikasi tab filter status (`?status=baru`, `?status=selesai`).
4. **TC-USR05-04:** Verifikasi pencarian kata kunci (`?search=...`).

---

## 🎓 Tanya Jawab Kunci USR-05 (Persiapan Sidang / Evaluasi)

| Pertanyaan Penguji | Jawaban Teknis Terbaik Anda |
|---|---|
| *"Bagaimana Anda menjamin mahasiswa tidak bisa mengintip laporan kerusakan yang diajukan oleh pengguna lain?"* | "Pada method `history()` di `ReportController`, kueri database secara absolut dibatasi dengan `where('user_id', Auth::id())`. Dengan begitu, meskipun pengguna mencoba memanipulasi parameter URL atau request query, kueri SQL di tingkat server hanya akan mengambil data milik pengguna yang sedang terautentikasi." |
| *"Apa itu problem N+1 kueri dan bagaimana Anda mengatasinya di halaman riwayat ini?"* | "Problem N+1 terjadi jika kita me-looping relasi (seperti `$report->facility->name`) di dalam Blade tanpa memuat relasi terlebih dahulu, sehingga database dipaksa melakukan 1 kueri tambahan untuk setiap baris data yang ditampilkan. Kami mengatasinya menggunakan fitur Eager Loading Eloquent: `DamageReport::with(['facility', 'handler'])`, sehingga puluhan baris data hanya membutuhkan 3 kueri SQL." |
| *"Mengapa Anda menggunakan conditional aggregation (CASE WHEN) untuk badge counter?"* | "Untuk menghindari *round-trip overhead* ke database server. Jika menggunakan 5 pemanggilan `count()` terpisah, aplikasi membuka dan menunggu 5 koneksi kueri ke MySQL. Dengan agregasi berkondisi tunggal `COUNT(CASE WHEN status = ... THEN 1 END)`, seluruh total rekapitulasi status selesai dihitung dalam satu kali eksekusi kueri berkecepatan tinggi." |
| *"Bagaimana cara mempertahankan kata kunci pencarian ketika pengguna mengklik pagination ke halaman berikutnya?"* | "Kami menambahkan method berantai `->withQueryString()` pada pemanggilan `paginate(10)` di Controller. Method ini secara otomatis menyalin seluruh *query string parameters* yang ada di URL saat ini ke dalam tautan link tombol pagination yang dihasilkan oleh Laravel." |

