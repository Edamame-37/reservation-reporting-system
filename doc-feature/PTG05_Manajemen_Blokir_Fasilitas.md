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
