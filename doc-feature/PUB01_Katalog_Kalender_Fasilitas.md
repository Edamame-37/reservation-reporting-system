# PUB-01: Katalog Fasilitas & Kalender Ketersediaan

## 1. Meta Informasi
- **Aktor:** Pengunjung (Tanpa Login) & Semua Pengguna
- **Ekuivalen User Story:** US-1
- **Modul:** Area Publik

## 2. Deskripsi Alur Bisnis
Pengunjung mengakses beranda aplikasi dan dapat melihat daftar seluruh fasilitas kampus yang disewakan/dipinjamkan. Di setiap fasilitas, terdapat fitur untuk membuka tampilan penanggalan (kalender) harian guna melihat kotak-kotak slot waktu mana yang masih kosong (Tersedia) dan mana yang sudah dipesan (Tidak Tersedia / Dalam Perbaikan). Detail nama peminjam maupun tujuannya akan disembunyikan.

## 3. Kebutuhan Frontend (UI/UX & Validasi)
- **File Rujukan:** `resources/views/public/catalog.blade.php` dan `availability.blade.php`
- **Komponen UI Utama:**
  - Grid daftar fasilitas (Gambar, Nama, Tipe, Kapasitas).
  - *FullCalendar.js* atau komponen *Slot Matrix* kustom (`slot-matrix.blade.php`) untuk visualisasi waktu 07.00 - 20.00.
  - Indikator Warna (Hijau: Tersedia, Merah/Abu-abu: Tidak Tersedia).
- **Interaksi:** Ketika mengklik salah satu fasilitas di katalog, halaman kalender ketersediaan spesifik fasilitas tersebut akan terbuka.

## 4. Kebutuhan Backend (Controller & DB)
- **Controller:** (Misal) `PublicFacilityController.php`
- **Method:** `index()` dan `showAvailability($id, $date)`
- **Kueri Data:** Mengambil tabel `facilities` (hanya yang statusnya aktif).
- **Logika Endpoint Kalender:** *Endpoint* merespons daftar reservasi yang berstatus `disetujui` (approved) pada suatu fasilitas di tanggal tertentu, namun hanya mengembalikan data `start_time` dan `end_time` saja (merahasiakan data `user_id` atau alasan).

## 5. Aturan Penolakan / Edge Cases
- **Fasilitas Non-aktif:** Fasilitas yang sudah dinonaktifkan Admin (dihapus lunak / *soft delete* / status tidak aktif) tidak boleh muncul di katalog pengunjung.
- **Fasilitas Sedang Diperbaiki:** Waktu *maintenance* (US-12) harus diikutkan di dalam *output* ketersediaan agar slot waktunya berwarna merah/tidak tersedia.

## 6. Penjelasan Rinci Cara Kerja (Pseudocode & Logika)

### Routing (`routes/web.php`)
```php
Route::get('/catalog', [PublicFacilityController::class, 'index'])->name('catalog.index');
Route::get('/availability/{id}/{date}', [PublicFacilityController::class, 'showAvailability'])->name('catalog.availability');
```

### Logika Eksekusi di Controller (`PublicFacilityController@showAvailability`)
Fungsi ini dipanggil oleh Ajax/Fetch dari kalender untuk mendapatkan slot yang sudah terisi.
1. **Pemeriksaan Kerusakan (Maintenance):**
   ```php
   $facility = Facility::findOrFail($id);
   if ($facility->status_aktif !== 'aktif') {
       return response()->json(['status' => 'maintenance', 'message' => 'Fasilitas sedang ditutup.']);
   }
   ```
2. **Pencarian Reservasi (Approval Saja):**
   ```php
   $bookedSlots = Reservation::where('facility_id', $id)
       ->where('status', 'approved')
       ->whereDate('start_time', $date)
       ->get(['start_time', 'end_time']); // Hanya waktu, rahasiakan nama pemesan!
   
   return response()->json($bookedSlots);
   ```
3. **Di sisi Frontend (JavaScript Matrix):** Kalender akan mewarnai kotak jam antara `start_time` dan `end_time` menjadi warna merah. Jam operasional yang dirender hanyalah 07.00 hingga 20.00.
