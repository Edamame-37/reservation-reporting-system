# PTG-01: Dasbor Utama & Antrean Petugas Operasional

## 1. Meta Informasi
- **Aktor:** Petugas Sarpras
- **Ekuivalen User Story:** US-8
- **Modul:** Dasbor Kendali Petugas

## 2. Deskripsi Alur Bisnis
Petugas Sarpras adalah garda terdepan operasional. Ketika mereka login ke sistem, mereka harus segera disambut oleh sebuah dasbor komando yang menyoroti angka-angka genting: Berapa reservasi yang menunggu persetujuan? Berapa keluhan fasilitas yang belum disentuh (Baru)? Hal ini berfungsi sebagai pengingat SLA agar tidak ada *request* pengguna yang tertimbun.

## 3. Kebutuhan Frontend (UI/UX & Validasi)
- **File Rujukan:** `resources/views/petugas/dashboard.blade.php`
- **Komponen UI Utama:**
  - Kartu Metrik/Statistik (`cava/stat-card.blade.php`) berisikan jumlah antrean (Angka).
  - Dua buah tabel ringkasan (*mini-table*): 
    - 5 Reservasi *Pending* terbaru.
    - 5 Laporan Kerusakan *Baru* terbaru.
- **Interaksi:** Masing-masing baris tabel (*row*) memiliki tombol navigasi untuk melompat langsung ke halaman detil (Management) persetujuan.

## 4. Kebutuhan Backend (Controller & DB)
- **Controller:** `DashboardPetugasController.php` (Atau disatukan dalam logika `PetugasController`).
- **Method:** `index()`
- **Kueri Data Agregat:**
  - `Reservation::where('status', 'pending')->count()`
  - `DamageReport::whereIn('status', ['baru', 'diproses'])->count()`
  - Meneruskan data (*passing variables*) ringkasan terbaru menggunakan `limit(5)->get()`.

## 5. Aturan Penolakan / Edge Cases
- Tidak berlaku aksi tulis DB di modul ini. Seluruh tampilan murni *dashboard monitoring*. Jika metrik kosong, sistem menampilkan ikon ilustrasi "Semua antrean beres".

## 6. Penjelasan Rinci Cara Kerja (Pseudocode & Logika)

### Routing (`routes/web.php`)
```php
Route::middleware(['auth', 'role:petugas'])->group(function () {
    Route::get('/petugas/dashboard', [DashboardPetugasController::class, 'index'])->name('petugas.dashboard');
});
```

### Logika Eksekusi di Controller (`DashboardPetugasController@index`)
1. **Perhitungan Metrik (Agregasi):**
   ```php
   $pendingReservationsCount = Reservation::where('status', 'pending')->count();
   $newReportsCount = DamageReport::whereIn('status_laporan', ['baru', 'diproses'])->count();
   ```
2. **Pengambilan Data Pratinjau (Mini Table):**
   ```php
   // Ambil 5 data teratas saja untuk ditampilkan di dashboard
   $recentReservations = Reservation::with('facility', 'user')
       ->where('status', 'pending')
       ->orderBy('created_at', 'asc') // First in, first out
       ->limit(5)->get();
       
   $recentReports = DamageReport::with('facility', 'user')
       ->whereIn('status_laporan', ['baru', 'diproses'])
       ->orderBy('created_at', 'asc')
       ->limit(5)->get();
   ```
3. **Pengembalian View:**
   ```php
   return view('petugas.dashboard', compact(
       'pendingReservationsCount', 
       'newReportsCount', 
       'recentReservations', 
       'recentReports'
   ));
   ```

## 7. Instruksi Khusus untuk Programmer (Mandatori)
1. **Anti-Hardcode:** Wajib menghapus segala jenis data *hardcode* (*dummy*) di *frontend* dan langsung menggantinya dengan data dinamis yang terhubung ke *database* via variabel *Controller*.
2. **Fleksibilitas Blueprint:** Ingat bahwa isi dokumen ini adalah *blueprint* dasar. Anda diberikan kebebasan penuh untuk melakukan **improvisasi** dan menyempurnakan struktur atau estetika kodenya selama tidak menyimpang dari tujuan utama.
3. **Pemisahan Pengerjaan (Separation of Concerns):** Walaupun Anda ditugaskan sendirian sebagai *Fullstack* (mengerjakan UI dan Database sekaligus), **DILARANG KERAS** mengerjakannya secara bersamaan dalam satu *commit*. Kerjakan fase *Frontend* hingga selesai, lalu beralih ke fase *Backend* (atau sebaliknya). Ini diwajibkan oleh pedoman standar RULE_FRONTEND.md dan RULE_BACKEND.md.
4. **Patuh pada Aturan Induk:** Sebelum mulai mengetikkan satu baris kode pun, Anda diwajibkan untuk mereview dan mematuhi seluruh *guidelines* yang tercantum di file RULE_FRONTEND.md dan RULE_BACKEND.md.
5. **Kesesuaian Bisnis Inti:** Jangan menulis fungsi yang melenceng! Cek ulang dokumen CASE_PROJECT.md setiap kali Anda ragu mengenai aturan bisnis dari fitur yang sedang dikerjakan.
6. **Kesesuaian Arsitektur:** Pastikan *controller* dan *view* yang Anda buat diletakkan persis pada jalur folder yang sudah diamanatkan oleh peta struktur PLAN_DEVELOPMENT.md.
7. **Standar Teknologi CAVA:** Gunakan aturan *stack* (seperti Tailwind, Spatie, dll) sesuai perintah resmi pada dokumen TECHSTACK.md.
8. **Kepatuhan Mutlak Sistem:** Taati seluruh undang-undang dan aturan *workflow* di dalam RULE_PROJECT.md tanpa terkecuali.
9. **Finalisasi Valid:** Anda HANYA diizinkan mencentang progress penyelesaian fitur ini di PLAN_PROJECT.md SETELAH pengujian (testing) secara manual tuntas dilakukan tanpa celah (bug), sesaat sebelum melakukan integrasi akhir (push).

10. **Standar Kolaborasi Git:** Sebelum melakukan *commit* dan *push*, Anda WAJIB memastikan bahwa tata cara dan penamaan pesannya sesuai dengan aturan di `GUIDE_GITHUB.md`.

11. **Alur Branching & Pull Request:** Sesuai `GUIDE_GITHUB.md`, sebelum mulai koding, WAJIB membuat *branch* baru (contoh: `feature/nama-fitur`). Setelah selesai dan di-*push*, wajib membuat **Pull Request (PR)** ke *branch* `develop`.

## 8. Catatan Penyesuaian Tambahan (Diisi oleh Programmer)
*(Bagian ini wajib diisi jika Anda melakukan penyesuaian/improvisasi yang berbeda dari Blueprint di atas selama proses koding! Kosongkan jika tidak ada).*

- ...
