# USR-04: Form Pelaporan Kerusakan Fasilitas (Ticketing)

## 1. Meta Informasi
- **Aktor:** Pengguna (Mahasiswa/Dosen/Staf)
- **Ekuivalen User Story:** US-6
- **Modul:** Sistem Pelaporan (Frontend/User)

## 2. Deskripsi Alur Bisnis
Ketika pengguna menggunakan suatu ruangan dan menemukan kerusakan aset (contoh: AC mati, LCD Proyektor pecah, kursi patah, atau kotor dimasuki kucing liar), mereka dapat melapor ke Petugas Sarpras secara *real-time*. Pelaporan ini mewajibkan pengguna memilih kategori masalah, menjabarkan deskripsi, serta mengunggah sebuah foto bukti dari perangkat ponsel/laptop mereka.

## 3. Kebutuhan Frontend (UI/UX & Validasi)
- **File Rujukan:** `resources/views/user/report-form.blade.php`
- **Form Input:**
  - `facility_id` (Dropdown fasilitas).
  - `category` (Dropdown misal: Elektronik, Kebersihan, Fisik Bangunan).
  - `description` (Textarea wajib isi).
  - `photo` (Input File Gambar, diwajibkan format `.jpg, .png`).
- **Validasi Klien:** Membatasi unggahan file dengan tag `accept="image/*"` dan JavaScript cek ukuran file maksimal 2 MB agar server tidak macet.

## 4. Kebutuhan Backend (Controller & DB)
- **Controller:** `ReportController.php`
- **Method:** `store(Request $request)`
- **Validasi Server & Upload File:**
  - `photo`: `mimes:jpeg,png,jpg | max:2048` (Batas 2 MB).
  - File foto harus diunggah dan disimpan ke disk lokal (`storage/app/public/reports`) dan jalurnya (*path*) direkam ke dalam *database*.
  - Menetapkan status laporan awal menjadi `baru` (new).

## 5. Aturan Penolakan / Edge Cases
- Format file selain gambar atau ukuran lebih besar dari 2 MB mutlak ditolak oleh sisi *server*.
- Pesan kesalahan (error) tidak akan menghilangkan data deskripsi yang sudah diketik pengguna agar tidak capek mengetik ulang (gunakan fitur `old()` Laravel).

## 6. Penjelasan Rinci Cara Kerja (Pseudocode & Logika)

### Routing (`routes/web.php`)
```php
Route::middleware(['auth', 'role:pengguna'])->group(function () {
    Route::get('/reports/create', [ReportController::class, 'create'])->name('reports.create');
    Route::post('/reports', [ReportController::class, 'store'])->name('reports.store');
});
```

### Logika Eksekusi di Controller (`ReportController@store`)
1. **Validasi Ketat Foto (Maksimal 2 MB):**
   ```php
   $request->validate([
       'facility_id' => 'required|exists:facilities,id',
       'category' => 'required|string',
       'description' => 'required|string',
       'photo' => 'required|image|mimes:jpeg,png,jpg|max:2048', // 2048 KB = 2MB
   ]);
   ```
2. **Unggah (Upload) File Gambar ke Lokal Storage:**
   ```php
   $path = $request->file('photo')->store('public/reports');
   // Path yang tersimpan akan seperti: "public/reports/xyz123.jpg"
   ```
3. **Penyimpanan Data Keluhan:**
   ```php
   DamageReport::create([
       'user_id' => Auth::id(),
       'facility_id' => $request->facility_id,
       'category' => $request->category,
       'description' => $request->description,
       'photo_path' => $path,
       'status_laporan' => 'baru' // Default tiket baru
   ]);
   ```
4. **Respon:** Kembalikan ke halaman riwayat pelaporan dengan pesan hijau sukses.
