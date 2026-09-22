# Journey & Panduan Navigasi Fitur Sistem

Dokumen ini menjelaskan alur perjalanan (*journey*) sistem, daftar fitur utama, lokasi berkas (*file*) yang mengatur fitur tersebut, serta penjelasan ringkas mengenai cara kerja masing-masing bagian. Saat ini, sistem berada dalam fase *mockup/prototype* antarmuka.

---

## 1. Modul Autentikasi & Akun
Berfungsi sebagai gerbang masuk bagi pengguna dan manajemen sesi.

- **Lokasi File:** 
  - `resources/views/auth/login.blade.php` (Halaman Login Mockup)
  - `resources/views/auth/register.blade.php` (Halaman Pendaftaran)
- **Cara Kerja:** Pada fase *mockup* ini, halaman *login* diubah menjadi halaman pemilihan prapilih (kepingan kartu) yang memungkinkan Anda langsung melompat ke Dasbor spesifik sesuai _role_ pengguna (Pengunjung, Pengguna, Petugas, Admin) tanpa perlu verifikasi kredensial asli ke basis data.

## 2. Modul Katalog & Publik (Pengunjung)
Area interaksi publik di mana pengunjung anonim dapat melihat ketersediaan fasilitas tanpa harus memiliki akun.

- **Lokasi File:**
  - `resources/views/public/home.blade.php` (Beranda/Halaman Depan)
  - `resources/views/public/catalog.blade.php` (Katalog Pencarian Fasilitas)
  - `resources/views/public/availability.blade.php` (Kalender Ketersediaan Publik)
- **Cara Kerja:** Menampilkan direktori ruang atau fasilitas yang bisa dipinjam. Terdapat fitur pencarian (*search & filter*) dan penampil visualisasi jadwal yang terintegrasi di masa mendatang dengan _FullCalendar.js_.

## 3. Modul Manajemen Reservasi
Siklus permohonan peminjaman ruangan dari sivitas dan persetujuannya oleh petugas sarpras.

- **Lokasi File (Sisi Pengguna):**
  - `resources/views/user/dashboard.blade.php` (Dasbor Sivitas)
  - `resources/views/user/reservation-form.blade.php` (Form Pengajuan)
  - `resources/views/user/reservation-history.blade.php` (Riwayat Peminjaman)
- **Lokasi File (Sisi Petugas):**
  - `resources/views/petugas/dashboard.blade.php` (Dasbor Operasional)
  - `resources/views/petugas/reservation-management.blade.php` (Antrean Persetujuan)
- **Cara Kerja:** 
  - **Sivitas:** Mengisi form pengajuan yang dibatasi pada kelipatan waktu 30 menit (07.00 - 20.00). Mereka juga bisa melacak apakah reservasinya masih berstatus Menunggu, Disetujui, atau Ditolak.
  - **Petugas:** Memiliki wewenang absolut untuk menyetujui, menolak, atau bahkan membatalkan paksa reservasi yang bentrok (*double-booking*).

## 4. Modul Pelaporan Kerusakan (Ticketing)
Fitur interaktif di mana pengguna dapat melaporkan kerusakan aset di area kampus, dan petugas menindaklanjutinya.

- **Lokasi File (Sisi Pengguna):**
  - `resources/views/user/report-form.blade.php` (Form Lapor Kerusakan & Unggah Bukti)
  - `resources/views/user/report-history.blade.php` (Log Status Laporan)
- **Lokasi File (Sisi Petugas):**
  - `resources/views/petugas/report-management.blade.php` (Daftar Antrean Tiket)
- **Cara Kerja:** Pengguna mengunggah gambar kerusakan dari ponsel atau desktop beserta deskripsi masalah. Tiket kemudian masuk ke dalam antrean petugas. Begitu Petugas merespons, mereka memperbarui tahapan status (Baru -> Diproses -> Selesai). Jika fasilitas ditandai "Dalam Perbaikan", fasilitas tersebut akan otomatis terkunci agar tidak bisa disewa oleh pengunjung lain.

## 5. Modul Master Data & Analytics (Administrator)
Level tertinggi pada sistem, diperuntukkan untuk konfigurasi data, registrasi pengguna secara manual, dan perekapan (unduh data).

- **Lokasi File:**
  - `resources/views/admin/dashboard.blade.php` (Dasbor Sentral Eksekutif)
  - `resources/views/admin/facility-master.blade.php` (CRUD Fasilitas Kampus)
  - `resources/views/admin/user-management.blade.php` (Manajemen Akun & Otorisasi)
  - `resources/views/admin/export-report.blade.php` (Modul Unduh Rekap Data)
- **Cara Kerja:** Admin mengontrol hidup-matinya sebuah fasilitas. Mereka juga melakukan otorisasi (menyetujui akun mahasiswa yang baru mendaftar agar aktif), serta menarik data agregasi (*Export* PDF/CSV) terkait frekuensi kerusakan fasilitas dan tingkat okupansi peminjaman ruangan pada kurun waktu tertentu.

## 6. Fondasi UI & Routing (Penggerak Tampilan)
Kerangka dasar di balik layar yang membungkus komponen antarmuka agar selalu konsisten.

- **Lokasi File:**
  - `routes/web.php` (Pusat Lalu Lintas Rute URL Mockup)
  - `resources/views/layouts/` (Berisi `admin.blade.php`, `petugas.blade.php`, `public.blade.php`, `app.blade.php`)
  - `app/View/Components/` (Berisi class `AdminLayout.php`, `PetugasLayout.php`, `PublicLayout.php`)
- **Cara Kerja:** 
  - **Routing:** Seluruh akses navigasi dikendalikan di `web.php` (sementara ini diarahkan langsung merender file statis _blade_ untuk keperluan purwarupa).
  - **Layouting:** File _views_ setiap halaman di-*extend* (dibungkus) menggunakan tag HTML kustom seperti `<x-admin-layout>`. Tag kustom ini diterjemahkan oleh *Class Component* di Laravel yang menyambungkannya ke desain *master container* sehingga *navbar*, *sidebar*, dan jenis _font_ (Inter/Material Symbols) dimuat dengan rapi pada semua halaman secara modular (DRY - *Don't Repeat Yourself*).
