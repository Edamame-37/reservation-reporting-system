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
