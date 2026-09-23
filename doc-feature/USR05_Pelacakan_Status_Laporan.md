# USR-05: Pelacakan Status Laporan (Tiket) Pengguna

## 1. Meta Informasi
- **Aktor:** Pengguna (Mahasiswa/Dosen/Staf)
- **Ekuivalen User Story:** US-7
- **Modul:** Sistem Pelaporan (Frontend/User)

## 2. Deskripsi Alur Bisnis
Setelah memotret dan melapor, Pengguna membutuhkan transparansi (*SLA*) terkait laporannya. Mereka dapat membuka halaman riwayat laporan (*ticketing*) dan melihat progresnya. Apakah tiket baru saja masuk (*Baru*), sedang ditinjau/dikerjakan Petugas (*Diproses*), sudah ditutup (*Selesai*), atau diabaikan karena informasi kurang (*Ditolak*). Saat statusnya berubah menjadi *Selesai* atau *Ditolak*, pengguna juga dapat melihat Catatan Resolusi dari Petugas.

## 3. Kebutuhan Frontend (UI/UX & Validasi)
- **File Rujukan:** `resources/views/user/report-history.blade.php`
- **Komponen UI Utama:**
  - Tabel Daftar Keluhan.
  - Penampil thumbnail / gambar (*modal popup*) untuk foto yang diunggah.
  - Komponen lencana warna: Putih (Baru), Kuning (Diproses), Hijau (Selesai), Merah (Ditolak).
- **Interaksi:** Murni baca-saja (*Read-Only*). Pengguna tidak dapat mengedit laporan yang sudah terkirim (US-6).

## 4. Kebutuhan Backend (Controller & DB)
- **Controller:** `ReportController.php` (Bisa juga disatukan di Dashboard).
- **Method:** `index()` atau `history()`.
- **Kueri Data:** Mengambil data laporan yang berelasi dengan tabel `facilities` tempat kolom `user_id` cocok dengan akun yang sedang login.

## 5. Aturan Penolakan / Edge Cases
- Sangat dilarang keras sebuah kueri menampilkan laporan kerusakan dari mahasiswa A kepada mahasiswa B demi menjaga privasi pelapor (hanya Petugas dan Admin yang berhak melihat semua laporan lintas akun).

## 6. Penjelasan Rinci Cara Kerja (Pseudocode & Logika)

### Routing (`routes/web.php`)
```php
Route::middleware(['auth', 'role:pengguna'])->group(function () {
    Route::get('/reports/history', [ReportController::class, 'index'])->name('reports.index');
});
```

### Logika Eksekusi di Controller (`ReportController@index`)
1. **Pencarian Data Eksklusif (Hanya Milik Sendiri):**
   ```php
   // Cegah N+1 dengan `with('facility')`
   $reports = DamageReport::with('facility')
               ->where('user_id', Auth::id())
               ->orderBy('created_at', 'desc')
               ->paginate(10);
               
   return view('user.report-history', compact('reports'));
   ```

### Logika Frontend (`report-history.blade.php`)
1. **Menampilkan Gambar dari Storage:**
   Karena direktori yang tersimpan di DB adalah `public/reports/...`, pastikan *programmer* menggunakan fungsi `Storage::url()` untuk mencetak tautan foto di HTML.
   ```blade
   <img src="{{ Storage::url($report->photo_path) }}" alt="Bukti Rusak" class="w-32">
   ```
   *(Ingat: Programmers wajib menjalankan `php artisan storage:link` terlebih dahulu agar folder public terhubung).*
2. **Cetak Catatan Petugas:** Jika kolom `catatan_resolusi` pada tiket tersebut tidak kosong, tampilkan *alert box* kecil berisi catatan itu (SLA transparansi).

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
