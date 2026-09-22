# Panduan Lengkap Pengembangan Proyek (Buku Saku Mahasiswa)

Dokumen ini adalah **Pedoman Komprehensif** yang dirancang khusus untuk mahasiswa agar memahami keseluruhan alur, pembagian file, cara kerja, hingga prosedur kolaborasi kerja kelompok menggunakan GitHub. Baca secara berurutan sebelum Anda mulai mengetikkan baris kode pertama.

---

## 1. Ruang Lingkup Fitur Sistem (Berdasarkan project_case.md)
Berikut adalah daftar modul dan fitur yang wajib dibangun dan dipastikan berfungsi dalam proyek ini:

1. **Autentikasi & Akun:** Registrasi mandiri bagi pendaftar baru, Login/Logout, Verifikasi status akun oleh Admin, Pembuatan akun secara manual oleh Admin.
2. **Katalog & Pencarian:** Halaman publik daftar fasilitas, fitur *Search & Filter* (berdasarkan tipe, lokasi, dan kapasitas), serta penampil kalender ketersediaan interaktif per slot 30 menit.
3. **Manajemen Reservasi:**
   - **Frontend:** Form pengajuan reservasi, form pembatalan reservasi oleh pengguna, riwayat & detail peminjaman.
   - **Backend:** Dashboard antrean persetujuan (*approval*), pembatalan paksa oleh petugas dengan menyertakan alasan, dan validasi mutlak anti-bentrok jadwal (*double-booking*).
4. **Sistem Pelaporan Kerusakan (Ticketing):**
   - **Frontend:** Form pelaporan kerusakan (memilih kategori, deskripsi masalah, upload foto bukti/*evidence*), pelacakan status laporan (Baru/Diproses/Selesai).
   - **Backend:** Manajemen pergerakan *progress* laporan, pencatatan log/catatan resolusi perbaikan, dan aksi pengubahan status fasilitas (dari "Aktif" menjadi "Dalam Perbaikan").
5. **Master Data Management:** Fitur CRUD (Create, Read, Update, Non-aktifkan) untuk mengelola data fasilitas yang hak aksesnya eksklusif hanya untuk peran Admin.
6. **Reporting & Analytics:** Halaman *Dashboard* berisi rekapitulasi okupansi fasilitas dan frekuensi kerusakan, serta fitur krusial *Export* (Unduh) data ke dalam format CSV/Excel/PDF.

---

## 2. Arsitektur & Struktur Folder Proyek
Proyek ini menggunakan pola arsitektur **MVC (Model-View-Controller)** yang merupakan bawaan wajib Laravel. Pahami letak *file* kerja Anda agar tidak tersesat (berdasarkan `analytic.md`):

```text
/ (Root Project Laravel)
├── app/                        # 🗄️ [BACKEND] SERVER-SIDE: PUSAT LOGIKA MVC
│   ├── Http/
│   │   ├── Controllers/        # 🗄️ [BACKEND] Pengendali Alur
│   │   │   ├── HomeController.php
│   │   │   ├── ReservationController.php
│   │   │   ├── ReportController.php
│   │   │   └── FacilityController.php
│   │   ├── Middleware/         # 🗄️ [BACKEND] Gerbang Keamanan Akses
│   │   └── Requests/           # 🗄️ [BACKEND] Validasi Input Form API/Server
│   │       ├── StoreReservationRequest.php
│   │       ├── StoreReportRequest.php
│   │       └── StoreFacilityRequest.php
│   └── Models/                 # 🗄️ [BACKEND] Skema Entitas Database 
│       ├── Facility.php
│       ├── Reservation.php
│       └── Report.php
│
├── config/                     # 🗄️ [BACKEND] Pengaturan Aplikasi (Database, Email, dll)
│
├── public/                     # 🎨 [FRONTEND] Tempat menyimpan Foto Upload, CSS, JS hasil kompilasi Tailwind
│
├── resources/
│   ├── css/                    # 🎨 [FRONTEND] File input Tailwind CSS
│   ├── js/                     # 🎨 [FRONTEND] File input Alpine/JavaScript
│   └── views/                  # 🎨 [FRONTEND] TAMPILAN ANTARMUKA (Blade HTML)
│       ├── layouts/            # 🎨 [FRONTEND] Kerangka master
│       │   ├── admin.blade.php
│       │   ├── petugas.blade.php
│       │   └── public.blade.php
│       ├── public/             # 🎨 [FRONTEND] Area Publik / Visitor
│       │   ├── home.blade.php
│       │   ├── catalog.blade.php
│       │   └── availability.blade.php
│       ├── auth/               # 🎨 [FRONTEND] Area Login & Registrasi (Bawaan Breeze)
│       ├── user/               # 🎨 [FRONTEND] Area Pengguna
│       │   ├── dashboard.blade.php
│       │   ├── reservation-form.blade.php
│       │   ├── reservation-history.blade.php
│       │   ├── report-form.blade.php
│       │   └── report-history.blade.php
│       ├── petugas/            # 🎨 [FRONTEND] Area Petugas
│       │   ├── dashboard.blade.php
│       │   ├── reservation-management.blade.php
│       │   └── report-management.blade.php
│       └── admin/              # 🎨 [FRONTEND] Area Admin
│           ├── dashboard.blade.php
│           ├── facility-master.blade.php
│           ├── user-management.blade.php
│           └── export-report.blade.php
│
├── routes/                     # 🗄️ [KEDUANYA] NAVIGASI URL
│   └── web.php                 # 🗄️ [KEDUANYA] Backend membuat route, Frontend mengonsumsi (menyesuaikan URL)
│
└── database/                   # 🗄️ [BACKEND] Skema struktur tabel MySQL
    ├── migrations/             # 🗄️ [BACKEND] File perakit tabel
    └── seeders/                # 🗄️ [BACKEND] Pembuat data awal/dummy
        ├── RolePermissionSeeder.php
        ├── UserSeeder.php
        └── FacilitySeeder.php
```

---

## 3. Desain Relasi Basis Data (ERD)
Bagian ini memuat rancangan visual relasi antar tabel database Anda. 
*(Catatan: Gambaran rancangan ERD yang sebenarnya akan diletakkan di sini. Silakan hapus/ganti placeholder di bawah dengan gambar ERD asli yang telah Anda rancang nanti).*

> **[SPACE UNTUK MENYISIPKAN GAMBAR ERD DATABASE]**
> `<!-- ![ERD Sistem Reservasi Fasilitas](./path/gambar/erd_anda.png) -->`
> 
> *Konsep Relasi Utama yang harus terlukis pada ERD:*
> - 1 `User` (Satu Orang) bisa memiliki Banyak `Reservasi`.
> - 1 `Facility` (Satu Fasilitas) bisa memiliki Banyak `Reservasi`.
> - 1 `Reservasi` terkait dengan 1 `Laporan Kerusakan` (One-to-One / One-to-Many opsional).

---

## 4. Fase 1: Instalasi Keseluruhan Lingkungan (*Environment*)
Berikut adalah daftar perangkat keras dan pustaka (*library*) yang wajib diinstal, beserta fungsinya:

### 4.1. Instalasi Aplikasi Dasar (Prasyarat di Laptop Mahasiswa)
- **Laragon (atau XAMPP):** Berfungsi sebagai *Server* Lokal untuk menyediakan mesin PHP (min versi 8.2) dan aplikasi database MySQL secara instan di dalam laptop Anda tanpa perlu menyewa *hosting* internet.
- **Node.js & NPM:** Berfungsi murni sebagai mesin kompilator (*compiler*) di balik layar untuk memproses, merakit, dan mengecilkan ukuran kode CSS (Tailwind) menjadi ukuran paling minimalis agar *web* tidak lambat.
- **Composer:** "Play Store" atau "Gudang Aplikasi"-nya PHP. Wajib dipasang karena digunakan untuk mendownload dan merakit *framework* Laravel dari internet ke dalam laptop Anda.
- **Git:** Alat pengatur versi. Wajib dipasang agar bisa menyimpan sejarah *coding* dan mempermudah penggabungan tugas kelompok.

### 4.2. Perintah Instalasi Pustaka (Library) Proyek
Buka terminal/CMD di dalam folder server lokal Anda, lalu eksekusi pemasangan pustaka berikut secara berurutan:

1. **Instalasi Framework Induk (Laravel):** 
   *Perintah:* `composer create-project laravel/laravel reservasi-app`
2. **Instalasi Sistem Autentikasi (Laravel Breeze):** 
   *Fungsi:* Membuat fondasi form Login, Registrasi, dan enkripsi *password* seketika.
   *Perintah:* `composer require laravel/breeze --dev` dilanjutkan dengan `php artisan breeze:install blade`
3. **Instalasi Pengatur Hak Akses (Spatie Permission):** 
   *Fungsi:* Mengunci dan membatasi akses URL berdasarkan *Role* (Visitor, Pengguna, Petugas, Admin).
   *Perintah:* `composer require spatie/laravel-permission`
4. **Instalasi Kerangka Desain UI (Tailwind CSS):** 
   *Perintah:* `npm install -D tailwindcss postcss autoprefixer` dilanjutkan `npx tailwindcss init -p`
5. **Instalasi Mesin Ekspor Data (Excel/PDF Reporting):** 
   *Fungsi:* Menerjemahkan data tabel MySQL menjadi wujud *file* nyata (syarat wajib fitur Laporan Admin).
   *Perintah:* `composer require maatwebsite/excel` dan `composer require barryvdh/laravel-dompdf`

---

## 5. Fase 2: Implementasi Fitur Berdasarkan File (Pemetaan MVC)
Jika ada anggota tim yang bingung, *"Fitur ini harus saya koding di file sebelah mana?"*, ikuti pedoman pemetaan ini:

1. **Fitur Autentikasi & Akun:** 
   - *Logic/Controller:* Berada di `app/Http/Controllers/Auth/` (Sudah terbuat otomatis oleh Breeze).
   - *Tampilan/Views:* Berada di `resources/views/auth/`.
2. **Fitur Master Data & Katalog Fasilitas:**
   - *Logic/Controller:* Buat dan letakkan di `app/Http/Controllers/FacilityController.php` (Fungsi `index`, `create`, `store`).
   - *Model:* `app/Models/Facility.php`.
3. **Fitur Manajemen Reservasi & Anti Double-Booking (VITAL!):**
   - *Logic/Controller:* Buat di `ReservationController.php`. Logika `lockForUpdate()` MySQL untuk mencegah bentrok pesanan mutlak ditulis pada fungsi `store()` di dalam file ini.
   - *Tampilan/Views:* Folder `resources/views/user/reservasi/`.
   - *Kalender Ketersediaan:* Integrasikan pustaka `FullCalendar.js` pada *view* halaman katalog, lalu panggil rute data lewat JavaScript.
4. **Fitur Ticketing / Pelaporan Kerusakan:**
   - *Logic/Controller:* Buat di `ReportController.php` (Logika `Storage::put` untuk mengolah *upload file* foto berada di fungsi `store()` di sini).
   - *Tampilan/Views:* Folder `resources/views/user/laporan/`.
5. **Fitur Reporting / Unduh PDF & Excel:**
   - *Logic/Controller:* Buat sebuah `ExportController.php` khusus untuk mengumpulkan kueri pendapatan data dan menyuntikkannya ke perintah ekspor PDF dari DomPDF.

---

## 6. Tata Cara Menjalankan Aplikasi dan Database
Ikuti standar operasional ini setiap kali Anda baru menyalakan laptop untuk melanjutkan *coding*:

1. **Nyalakan Server & Konfigurasi Database:**
   - Buka aplikasi **Laragon** (atau XAMPP).
   - Klik tombol **"Start All"** untuk menghidupkan mesin Apache dan MySQL.
   - Masuk ke pengelola Database (HeidiSQL / phpMyAdmin), lalu *Create New Database* (misal: `db_reservasi_kampus`).
   - Sambungkan `.env` di proyek Anda dengan nama database tersebut.
2. **Nyalakan Aplikasi Laravel (Terminal Pertama):**
   - Buka terminal di dalam *folder* proyek Anda (contoh: di dalam `reservasi-app`).
   - Ketikkan perintah peluncur: `php artisan serve`.
   - Aplikasi Anda kini hidup dan bisa dibuka di browser pada alamat: `http://127.0.0.1:8000`.
3. **Nyalakan Kompilator CSS Tailwind (Terminal Kedua):**
   - Buka jendela Terminal/CMD **baru** (biarkan terminal pertama menyala di *background*).
   - Ketikkan perintah: `npm run dev`. 
   - *Catatan:* Perintah ini wajib dibiarkan menyala selama Anda mengubah tampilan HTML agar warna/desain yang baru ditambahkan langsung berubah di layar *browser* tanpa putus (*hot-reload*).

---

## 7. Prosedur Git & GitHub untuk Tim (Kolaborasi)
Kerja kelompok tanpa bentrok *file* membutuhkan kedisiplinan tingkat tinggi. Patuhi pedoman ini!

### Aturan Emas (*Golden Rules*) Tim
1. **DILARANG KERAS** mengetik *coding* atau mendorong langsung ke cabang utama (`main` atau `master`).
2. Selalu buat cabang (*branch*) baru dengan nama fitur/modul yang sedang Anda kerjakan sendiri.
3. Selalu lakukan komunikasi (*Pull Request*) sebelum menggabungkan kode anggota A dan anggota B.

### Siklus Kerja Harian Programmer (Git Workflow)
1. **Tarik *Update* Terbaru:** Setiap kali mulai kerja, selalu sinkronkan kode dari internet (GitHub) ke laptop Anda agar kodingan terbaru teman Anda masuk.
   ```bash
   git checkout main
   git pull origin main
   ```
2. **Buat Cabang (*Branch*) Pekerjaan Baru:**
   ```bash
   git checkout -b nama-anda/fitur-yang-dibuat 
   # Contoh: git checkout -b bima/fitur-kalender
   ```
3. **Mulai Koding...** (Simpan file, tes fungsi di *browser* laptop Anda).
4. **Simpan Progres secara Lokal (*Commit*):**
   ```bash
   git add .
   git commit -m "feat: menyelesaikan tampilan kalender di dashboard"
   ```
5. **Kirim Pekerjaan ke GitHub (*Push*):**
   ```bash
   git push origin nama-anda/fitur-yang-dibuat
   ```
6. **Lakukan Pull Request (PR):** Buka repositori GitHub di *browser*, klik tombol *New Pull Request*. Minta ketua kelompok/teman Anda yang lain untuk melihat dan menyetujui kode tersebut (Review & Merge) ke *branch* `main`.

---

## 8. Fase 3: Prosedur Pengujian (*Testing*) Keseluruhan
Sebelum sistem ini di- *deploy* dan didemonstrasikan di hadapan dosen/penguji, tim wajib melakukan simulasi uji coba menyeluruh sebagai berikut:

### 8.1. Pengujian Fungsionalitas Umum (*Feature Testing*)
Dilakukan secara manual (klik demi klik) layaknya pengguna asli:
- **Test Autentikasi:** Apakah *user* bisa mendaftar? Apakah *user* bisa *logout*?
- **Test Filter Kalender:** Cobalah memfilter fasilitas berdasarkan tipe (contoh: "Alat Olahraga"). Apakah kalender merespons ketersediaan dengan akurat?
- **Test Pembatasan Validasi Jam:** Cobalah mengetik jam 21.00 (melewati jam tutup) atau menit ke 15 (contoh: 08.15 - harusnya hanya kelipatan 30). Sistem **harus** mengembalikan pesan *error* validasi HTML/FormRequest.
- **Test Cetak (Ekspor):** Tekan tombol ekspor di dasbor Admin. Periksa apakah *file* PDF/Excel benar-benar terunduh dan angkanya sesuai dengan rekap aslinya.

### 8.2. Pengujian Penetrasi & Keamanan Dasar (*Security & Pen-Test*)
Pengujian esensial untuk membuktikan arsitektur *backend* sudah kokoh:
- **Uji Akses Ilegal (*Middleware Bypass Test*):**
  - *Login* sebagai akun Mahasiswa biasa.
  - Secara paksa, ketik dan ubah URL di kolom pencarian *browser* menjadi `http://127.0.0.1:8000/admin/dashboard`.
  - **Ekspektasi Kelulusan:** Sistem melempar pengguna ke layar *Error 403 (Forbidden)* atau kembali ke *Home*. Mahasiswa mutlak tidak bisa membobol area Admin.
- **Uji Manipulasi Data (*SQL Injection Test*):**
  - Pada *form* isian "Tujuan Reservasi", masukkan karakter perusak seperti: `1' OR '1'='1` atau script usil `<script>alert(1)</script>`.
  - **Ekspektasi Kelulusan:** Sistem Laravel otomatis membersihkan karakter ini. Data tersimpan murni sebagai *string* teks biasa, tidak mengubah logika *database* atau merusak layar.
- **Uji Manipulasi Unggahan (*File Upload Vulnerability*):**
  - Saat melaporkan kerusakan, unggah *file* berekstensi jahat (misal: `virus.exe` atau `script.php`), atau unggah foto asli tapi berukuran 10MB.
  - **Ekspektasi Kelulusan:** Server menolak *upload* secara tegas karena validasi (`mimes:jpg,png|max:2048`) bekerja.

### 8.3. Simulasi Ekstrem (*Concurrency / Double-Booking Test*)
Ini adalah atraksi utama keamanan proyek Anda:
1. Buka 2 jendela *browser* yang berbeda (contoh: Chrome biasa dan Firefox/Incognito).
2. Login di Chrome sebagai Akun Mahasiswa A. Login di Firefox sebagai Akun B.
3. Di kedua *browser*, buka *form* reservasi yang sama, untuk Fasilitas yang sama, dan untuk rentang Jam yang persis sama.
4. **Siapkan aba-aba, dan tekan tombol "Submit" di kedua layar secara bersamaan!**
5. **Ekspektasi Kelulusan Tingkat Tinggi:** Mekanisme antrean MySQL (`lockForUpdate` di Controller) akan menjerat eksekusi. Salah satu pesanan (yang masuk duluan beberapa milidetik) akan sukses. Pesanan satunya lagi akan dipaksa menunggu sesaat, lalu otomatis dibatalkan sistem dengan mengeluarkan pesan *error*: *"Mohon maaf, fasilitas ini baru saja diamankan oleh orang lain"*. Tidak boleh ada 2 nama tercatat pada jam yang sama di *database*.
