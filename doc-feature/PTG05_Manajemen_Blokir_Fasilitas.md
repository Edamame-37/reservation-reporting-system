# PTG-05: Manajemen Blokir Fasilitas (Maintenance Mode)

## 1. Meta Informasi
- **Aktor:** Petugas Sarpras
- **Ekuivalen User Story:** US-12
- **Modul:** Sistem Pelaporan (Petugas) / Relasi Katalog

## 2. Deskripsi Alur Bisnis
Ini adalah jembatan *sinkronisasi* antara Pelaporan dan Peminjaman. Ketika fasilitas rusak parah, Petugas menekan sebuah tombol ("Tandai Sedang Diperbaiki") pada laporan tersebut. Tombol sakti ini otomatis memblokir fasilitas tersebut di tingkat sistem. Semua orang yang mencoba mem-booking fasilitas ini akan ditolak (diblokir), dan di Kalender Publik (PUB-01), seluruh slot waktunya dicoret merah (Tidak Tersedia). 

## 3. Kebutuhan Frontend (UI/UX & Validasi)
- **File Rujukan:** Terintegrasi pada UI `resources/views/petugas/report-management.blade.php`
- **Komponen UI Utama:** Sebuah tombol *toggle* atau tombol *switch* (Saklar On/Off) bernama "Mode Perbaikan (Lock)".

## 4. Kebutuhan Backend (Controller & DB)
- **Controller:** `FacilityStatusController.php` atau disatukan di `ReportManagementController.php`
- **Method:** `toggleMaintenance($facility_id)`
- **Proses DB:** Mengubah kolom `status_aktif` di tabel `facilities` dari `aktif` (1) menjadi `maintenance` (0 atau 2).
- **Relasi ke Fitur Lain (Sangat Penting):**
  - **Dampak di PUB-01 (Katalog Kalender):** Kueri kalender harus menimpa blok visualnya dengan tanda merah penuh jika `status_aktif` fasilitas == `maintenance`.
  - **Dampak di USR-01 (Pengajuan):** Di metode validasi Reservasi, tambahkan baris validasi: `if ($facility->status_aktif == 'maintenance') return abort(403, 'Fasilitas dalam masa perbaikan');`.

## 5. Aturan Penolakan / Edge Cases
- Jika fasilitas sedang dalam "Mode Perbaikan", lalu di tengah waktu itu ada reservasi yang kebetulan sudah terlanjur *Approved* di hari esok, petugas diharapkan membatal-paksanya (via PTG-03). Blokir perbaikan ini bersifat mengunci *booking* baru.

## 6. Penjelasan Rinci Cara Kerja (Pseudocode & Logika)

### Routing (`routes/web.php`)
```php
Route::middleware(['auth', 'role:petugas'])->group(function () {
    Route::patch('/petugas/facilities/{id}/toggle-maintenance', [ReportManagementController::class, 'toggleMaintenance'])->name('petugas.facilities.toggle-maintenance');
});
```

### Logika Eksekusi di Controller (`ReportManagementController@toggleMaintenance`)
1. **Saklar (Toggle) Status Master Fasilitas:**
   ```php
   $facility = Facility::findOrFail($id);
   
   // Jika sedang aktif, ubah ke maintenance, begitu pula sebaliknya (Toggling).
   if ($facility->status_aktif == 'aktif') {
       $facility->status_aktif = 'maintenance';
       $msg = 'Fasilitas diblokir (Masuk mode maintenance).';
   } else {
       $facility->status_aktif = 'aktif';
       $msg = 'Fasilitas dikembalikan ke status aktif.';
   }
   
   $facility->save();
   return back()->with('success', $msg);
   ```
2. **Catatan Arsitektur:** Ini adalah manipulasi tingkat Dewa dari sisi Petugas, karena ia mengubah master data (yang seharusnya ranah Admin). Namun ini legal secara bisnis, mengingat Petugas adalah ujung tombak di lapangan yang melihat kerusakan, sehingga tak perlu menunggu Admin untuk menutup fasilitas.

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
