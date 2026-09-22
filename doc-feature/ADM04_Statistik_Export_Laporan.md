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
