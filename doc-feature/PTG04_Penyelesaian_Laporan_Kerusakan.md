# PTG-04: Manajemen & Penyelesaian Laporan Kerusakan

## 1. Meta Informasi
- **Aktor:** Petugas Sarpras
- **Ekuivalen User Story:** US-11
- **Modul:** Sistem Pelaporan (Petugas)

## 2. Deskripsi Alur Bisnis
Semua tiket keluhan (kerusakan) dari pengguna masuk ke dasbor ini. Petugas mengecek foto dan deskripsi laporan. Mereka kemudian berhak memindahkan status tiket ke jenjang selanjutnya (contoh: dari `baru` menjadi `diproses`). Saat fasilitas selesai diperbaiki tukang, Petugas mengubah status tiket menjadi `selesai` dan diwajibkan mengetikkan *Catatan Resolusi* (misal: "Proyektor telah diganti dengan yang baru").

## 3. Kebutuhan Frontend (UI/UX & Validasi)
- **File Rujukan:** `resources/views/petugas/report-management.blade.php`
- **Komponen UI Utama:**
  - Tabel Daftar Keluhan.
  - Komponen Dropdown untuk memilih Status Update (Diproses, Selesai, Ditolak).
  - Teks area untuk input "Catatan Perbaikan/Resolusi".
- **Validasi Klien:** Jika dropdown diset ke "Selesai" atau "Ditolak", kotak teks "Catatan Resolusi" otomatis menjadi wajib isi.

## 4. Kebutuhan Backend (Controller & DB)
- **Controller:** `ReportManagementController.php`
- **Method:** `updateStatus(Request $request, $id)`
- **Validasi Server Khusus:**
  - `status`: *in:diproses,selesai,ditolak*.
  - Jika `$request->status == 'selesai'`, maka validasi *rule* untuk `catatan_resolusi` adalah `required`.
- **Proses DB:** Memperbarui tabel `damage_reports` kolom `status_laporan` dan `catatan_resolusi`.

## 5. Aturan Penolakan / Edge Cases
- Pengguna pelapor tidak akan bisa menghapus laporannya sendiri yang sudah berstatus `diproses` (Cegah modifikasi data di tengah penanganan operasional).

## 6. Penjelasan Rinci Cara Kerja (Pseudocode & Logika)

### Routing (`routes/web.php`)
```php
Route::middleware(['auth', 'role:petugas'])->group(function () {
    Route::get('/petugas/reports', [ReportManagementController::class, 'index'])->name('petugas.reports.index');
    Route::patch('/petugas/reports/{id}', [ReportManagementController::class, 'updateStatus'])->name('petugas.reports.update');
});
```

### Logika Eksekusi di Controller (`ReportManagementController@updateStatus`)
1. **Validasi Logika Transisi Status:**
   ```php
   $request->validate([
       'status_laporan' => 'required|in:diproses,selesai,ditolak',
       // Jika status diubah menjadi selesai, maka catatan resolusi WAJIB ADA.
       'catatan_resolusi' => 'required_if:status_laporan,selesai|string|nullable'
   ]);
   ```
2. **Proses Pembaruan DB:**
   ```php
   $report = DamageReport::findOrFail($id);
   $report->status_laporan = $request->status_laporan;
   $report->catatan_resolusi = $request->catatan_resolusi ?? $report->catatan_resolusi; // Jangan timpa null jika sudah ada
   $report->save();
   
   return back()->with('success', 'Status laporan keluhan diperbarui.');
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

## 8. Catatan Penyesuaian Tambahan (Diisi oleh Programmer)
*(Bagian ini wajib diisi jika Anda melakukan penyesuaian/improvisasi yang berbeda dari Blueprint di atas selama proses koding! Kosongkan jika tidak ada).*

- ...
