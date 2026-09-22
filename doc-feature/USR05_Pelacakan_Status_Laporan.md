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
