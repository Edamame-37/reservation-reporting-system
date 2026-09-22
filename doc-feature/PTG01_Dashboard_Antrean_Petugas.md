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
