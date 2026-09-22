# ADM-03: Master Data Fasilitas (CRUD)

## 1. Meta Informasi
- **Aktor:** Admin
- **Ekuivalen User Story:** US-16
- **Modul:** Master Data Management (Konsol Admin)

## 2. Deskripsi Alur Bisnis
Sebagai otak tata kelola aplikasi, Admin berhak menambah ruangan baru, memperbaiki keterangan/kapasitas ruangan, atau menonaktifkan ruangan yang sudah dialihfungsikan agar lenyap dari Katalog Publik (PUB-01). Ini adalah siklus lengkap *Create, Read, Update, Delete* (CRUD).

## 3. Kebutuhan Frontend (UI/UX & Validasi)
- **File Rujukan:** `resources/views/admin/facility-master.blade.php`
- **Komponen UI Utama:**
  - Tabel Daftar Master Fasilitas (Bukan sekadar galeri, tapi format baris kaku).
  - Form (Modal) Tambah/Edit.
- **Form Input (Fasilitas):**
  - Nama Fasilitas.
  - Tipe Fasilitas (Dropdown: Kelas, Laboratorium, Aula, Lapangan).
  - Lokasi/Gedung.
  - Kapasitas (Input Angka).
  - Deskripsi Fasilitas.
  - Upload Foto Cover (Gambar).

## 4. Kebutuhan Backend (Controller & DB)
- **Controller:** `FacilityController.php` (Domain Admin).
- **Method:** `index()`, `store()`, `update()`, `destroy()`.
- **Manajemen Gambar (File System):** Upload file gambar fasilitas ke penyimpanan server lokal di direktori `/public/storage/facilities/`. Jika gambar diedit, hapus file gambar fisik lama untuk menghemat kapasitas (*disk space*).
- **Penghapusan Lunak (Soft Delete):** Jangan menggunakan SQL `DELETE` asli (*Hard delete*), tapi gunakan fitur *SoftDeletes* Laravel. Jika dihapus asli, riwayat reservasi yang menautkan `facility_id` tersebut di masa lalu (tabel `reservations`) akan hancur/error.

## 5. Aturan Penolakan / Edge Cases
- Jika nama fasilitas yang sama (contoh: "Lab Komputer A") diketikkan lagi di formulir tambah, validasi `unique:facilities,name` akan melempar pesan *error* agar tidak terjadi data ganda.
