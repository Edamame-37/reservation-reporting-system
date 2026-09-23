# ADM-04: Dasbor Statistik & Unduh (Export) Laporan

## 1. Meta Informasi
- **Aktor:** Admin
- **Ekuivalen User Story:** US-17
- **Modul:** Reporting & Analytics (Konsol Admin)

## 2. Deskripsi Alur Bisnis
Tugas puncak Biro Sarpras/Admin di akhir bulan adalah melaporkan performa aset kepada Rektorat. Halaman Dasbor Admin dipenuhi oleh grafik dan statistik. Di halaman lainnya, Admin dapat menekan tombol untuk mencetak (*export*) rekap peminjaman ruang (okupansi) dan rekap jumlah kerusakan (tiket perbaikan) ke dalam format dokumen PDF atau hamparan *spreadsheet* Excel.

## 3. Kebutuhan Frontend (UI/UX & Validasi)
- **File Rujukan:** `resources/views/admin/dashboard.blade.php` & `admin/export-report.blade.php`
- **Komponen UI Utama:**
  - Grafik visual (opsional: *Chart.js*).
  - Dua tombol *Export* besar: "Unduh Excel (CSV)" dan "Cetak PDF Laporan".
  - Filter Rentang Tanggal (*Date Range Picker*) untuk menyaring data yang ingin dicetak (misal: "Data Bulan Ini").

## 4. Kebutuhan Backend (Controller & DB)
- **Controller:** `AdminDashboardController.php` & `ExportController.php`
- **Method:** `exportReservations(Request $request)` dan `exportReports(Request $request)`.
- **Integrasi Pustaka (Library):**
  - Menggunakan paket `maatwebsite/excel` (di `composer.json`) untuk merakit *query* database menjadi susunan `.xlsx` atau `.csv`.
  - Menggunakan paket `barryvdh/laravel-dompdf` untuk mengekstrak tampilan khusus *Blade HTML* yang memuat logo dan tabel laporan, kemudian mencetaknya sebagai file biner `.pdf`.
- **Kueri Agregasi (Analytics):** Menghitung frekuensi (*count/group by facility_id*) fasilitas mana yang paling sering digunakan, dan mana yang paling sering dilaporkan rusak.

## 5. Aturan Penolakan / Edge Cases
- Data ekspor memakan banyak memori (RAM). Server disetel untuk melakukan paginasi atau proses *chunking* (pecah data per 1000 baris) jika rentang laporan yang diunduh mencakup waktu puluhan tahun ke belakang, untuk mencegah *Memory Limit Exceeded*.

## 6. Penjelasan Rinci Cara Kerja (Pseudocode & Logika)

### Routing (`routes/web.php`)
```php
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/admin/export/reservations/pdf', [ExportController::class, 'exportReservationsPDF'])->name('admin.export.reservations.pdf');
    Route::get('/admin/export/reservations/excel', [ExportController::class, 'exportReservationsExcel'])->name('admin.export.reservations.excel');
});
```

### Logika Eksekusi di Controller (`ExportController@exportReservationsPDF`)
1. **Query Data dengan Filter:**
   ```php
   $start_date = $request->query('start_date', Carbon::now()->startOfMonth());
   $end_date = $request->query('end_date', Carbon::now()->endOfMonth());
   
   $data = Reservation::with('facility', 'user')
               ->whereBetween('date', [$start_date, $end_date])
               ->where('status', 'approved')
               ->get();
   ```
2. **Injeksi ke DomPDF:**
   ```php
   // Load view khusus cetak, jangan pakai komponen navigasi/header interaktif
   $pdf = Pdf::loadView('admin.exports.reservations-pdf', ['data' => $data]);
   
   // Set ukuran kertas
   $pdf->setPaper('A4', 'landscape');
   
   // Paksa pengguna mengunduh (*download*) file PDF tersebut
   return $pdf->download('Laporan_Reservasi_'.$start_date.'_sampai_'.$end_date.'.pdf');
   ```
3. **Untuk Ekspor Excel (Menggunakan `maatwebsite/excel`):**
   ```php
   return Excel::download(new ReservationsExport($start_date, $end_date), 'Reservations.xlsx');
   // Class ReservationsExport ini harus dibuat terpisah pakai perintah `php artisan make:export`
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
