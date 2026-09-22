# PUB-02: Pencarian & Penyaringan Katalog Fasilitas

## 1. Meta Informasi
- **Aktor:** Pengunjung (Tanpa Login) & Semua Pengguna
- **Ekuivalen User Story:** US-2
- **Modul:** Area Publik

## 2. Deskripsi Alur Bisnis
Di halaman Katalog, pengunjung yang mencari ruang tertentu dapat menggunakan kotak pencarian (untuk nama) atau fitur saringan (*filter*) untuk menyempitkan daftar fasilitas berdasarkan kriteria: Tipe (misal: Laboratorium, Kelas), Lokasi Gedung, atau Kapasitas Minimum orang. Daftar fasilitas akan termuat ulang sesuai kriteria.

## 3. Kebutuhan Frontend (UI/UX & Validasi)
- **File Rujukan:** `resources/views/public/catalog.blade.php`
- **Form Input:** 
  - Input Teks (Pencarian Nama)
  - Select / Dropdown (Tipe Fasilitas)
  - Input Angka (Kapasitas Min/Max)
- **Interaksi (Opsional namun disarankan):** Menggunakan *Alpine.js* atau sekadar *form GET submit* biasa untuk me-*refresh* halaman saat filter diubah.

## 4. Kebutuhan Backend (Controller & DB)
- **Controller:** (Sama dengan PUB-01) `PublicFacilityController.php`
- **Method:** `index(Request $request)`
- **Kueri Data:** Menambahkan kondisi `where` yang fleksibel (*dynamic querying*).
  - Jika ada `$request->type`, tambahkan `where('type', $request->type)`.
  - Jika ada `$request->capacity`, tambahkan `where('capacity', '>=', $request->capacity)`.
  - Jika ada `$request->search`, gunakan kueri SQL `LIKE '%...%'` pada kolom nama/deskripsi.

## 5. Aturan Penolakan / Edge Cases
- **Hasil Kosong:** Jika kriteria *filter* terlalu spesifik sehingga tidak ada satupun fasilitas yang cocok, antarmuka harus memunculkan ilustrasi/teks cantik "Fasilitas tidak ditemukan".
