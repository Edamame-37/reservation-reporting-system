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
│   │   │   ├── Auth/           # 🗄️ [BACKEND] Kontroler Autentikasi (Bawaan Breeze)
│   │   │   │   ├── AuthenticatedSessionController.php    # Memproses login dan logout pengguna.
│   │   │   │   ├── ConfirmablePasswordController.php     # Meminta konfirmasi password sebelum aksi krusial.
│   │   │   │   ├── EmailVerificationNotificationController.php # Mengirim ulang email verifikasi.
│   │   │   │   ├── EmailVerificationPromptController.php # Menampilkan form permintaan verifikasi email.
│   │   │   │   ├── NewPasswordController.php             # Memproses penyimpanan password baru.
│   │   │   │   ├── PasswordController.php                # Mengubah password pengguna aktif.
│   │   │   │   ├── PasswordResetLinkController.php       # Mengirim tautan reset password via email.
│   │   │   │   ├── RegisteredUserController.php          # Memproses pendaftaran akun baru.
│   │   │   │   └── VerifyEmailController.php             # Memvalidasi tautan verifikasi email.
│   │   │   ├── Controller.php                        # Induk dasar pengendali (base controller) Laravel.
│   │   │   ├── ProfileController.php                 # Mengendalikan pembaruan data profil pengguna.
│   │   │   ├── PublicFacilityController.php          # [PUB] Mengendalikan Katalog Fasilitas Publik & Kalender.
│   │   │   ├── ReservationController.php             # [USR] Mengolah pengajuan & riwayat reservasi pengguna.
│   │   │   ├── ReportController.php                  # [USR] Mengolah pelaporan kerusakan dari pengguna.
│   │   │   ├── DashboardPetugasController.php        # [PTG] Memuat metrik & antrean di dasbor petugas.
│   │   │   ├── ReservationManagementController.php   # [PTG] Mengelola persetujuan/penolakan dan anti-bentrok.
│   │   │   ├── ReportManagementController.php        # [PTG] Menindaklanjuti keluhan & status blokir fasilitas.
│   │   │   ├── AdminUserManagementController.php     # [ADM] Memverifikasi dan mendaftarkan akun internal.
│   │   │   ├── FacilityController.php                # [ADM] Melakukan fungsi CRUD master data fasilitas.
│   │   │   ├── AdminDashboardController.php          # [ADM] Menampilkan analitik dan rekapitulasi okupansi.
│   │   │   └── ExportController.php                  # [ADM] Mengeksekusi pencetakan laporan ke Excel/PDF.
│   │   └── Requests/           # 🗄️ [BACKEND] Validasi Input Form API/Server
│   │       ├── Auth/           
│   │       │   └── LoginRequest.php                  # Aturan validasi ketika submit form login.
│   │       └── ProfileUpdateRequest.php              # Aturan validasi saat mengedit profil.
│   └── Models/                 # 🗄️ [BACKEND] Skema Entitas Database 
│       ├── DamageReport.php    # Model untuk mengolah tabel damage_reports (kerusakan fasilitas).
│       ├── Facility.php        # Model untuk mengolah tabel facilities (master data aset ruang).
│       ├── Reservation.php     # Model untuk mengolah tabel reservations (data antrean dan jadwal).
│       └── User.php            # Model untuk tabel pengguna, dikaitkan dengan Spatie Permission.
│
├── config/                     # 🗄️ [BACKEND] Pengaturan Aplikasi (Database, Email, dll)
│
├── public/                     # 🎨 [FRONTEND] Tempat menyimpan Foto Upload, CSS, JS hasil kompilasi Tailwind
│
├── resources/
│   ├── css/                    # 🎨 [FRONTEND] File input Tailwind CSS (app.css)
│   ├── js/                     # 🎨 [FRONTEND] File input Alpine/JavaScript (app.js)
│   └── views/                  # 🎨 [FRONTEND] TAMPILAN ANTARMUKA (Blade HTML)
│       ├── admin/              # 🎨 [FRONTEND] Antarmuka Khusus Admin (Biro Sarpras)
│       │   ├── dashboard.blade.php       # Dasbor statistik dan rekapitulasi pelaporan.
│       │   ├── export-report.blade.php   # Antarmuka antrean untuk mencetak PDF/Excel.
│       │   ├── facility-master.blade.php # Halaman CRUD data fasilitas.
│       │   └── user-management.blade.php # Halaman manajemen validasi dan blokir akun.
│       ├── auth/               # 🎨 [FRONTEND] Halaman Login & Registrasi (Bawaan Breeze)
│       │   ├── confirm-password.blade.php # Form minta ketik ulang password keamanan.
│       │   ├── forgot-password.blade.php  # Form lupa password.
│       │   ├── login.blade.php            # Form masuk (login).
│       │   ├── register.blade.php         # Form pendaftaran akun.
│       │   ├── reset-password.blade.php   # Form mengatur ulang password.
│       │   └── verify-email.blade.php     # Tampilan instruksi cek email.
│       ├── components/         # 🎨 [FRONTEND] Komponen UI Reusable (Daur Ulang)
│       │   ├── cava/           # 🎨 [FRONTEND] Komponen Khusus Desain CAVA 
│       │   │   ├── header.blade.php       # Komponen navbar atas.
│       │   │   ├── role-switcher.blade.php# Komponen dropdown untuk pindah dasbor peran.
│       │   │   ├── sidebar.blade.php      # Komponen navigasi menu di samping.
│       │   │   ├── slot-matrix.blade.php  # Komponen petak-petak matriks jadwal ketersediaan.
│       │   │   ├── stat-card.blade.php    # Komponen kartu statistik (angka laporan).
│       │   │   └── status-badge.blade.php # Komponen label lencana warna-warni (pending/approve).
│       │   ├── application-logo.blade.php # Logo aplikasi.
│       │   ├── auth-session-status.blade.php # Pesan status login.
│       │   ├── danger-button.blade.php    # Tombol merah (hapus/bahaya).
│       │   ├── dropdown-link.blade.php    # Isi dari menu dropdown.
│       │   ├── dropdown.blade.php         # Wadah pembungkus dropdown.
│       │   ├── input-error.blade.php      # Teks merah untuk pesan kesalahan input form.
│       │   ├── input-label.blade.php      # Teks label di atas input form.
│       │   ├── modal.blade.php            # Komponen jendela dialog (pop-up).
│       │   ├── nav-link.blade.php         # Link navigasi biasa.
│       │   ├── primary-button.blade.php   # Tombol utama (biru/hitam).
│       │   ├── responsive-nav-link.blade.php # Link navigasi khusus mode mobile.
│       │   ├── secondary-button.blade.php # Tombol sekunder (putih/abu).
│       │   └── text-input.blade.php       # Komponen kotak isian teks (input text).
│       ├── layouts/            # 🎨 [FRONTEND] Kerangka Halaman Master (Template)
│       │   ├── admin.blade.php            # Kerangka tata letak dasbor Admin.
│       │   ├── app.blade.php              # Kerangka tata letak aplikasi utama.
│       │   ├── guest.blade.php            # Kerangka tata letak publik/tanpa login (beranda, dll).
│       │   ├── navigation.blade.php       # Navigasi utama Breeze.
│       │   ├── petugas.blade.php          # Kerangka tata letak dasbor Petugas.
│       │   └── public.blade.php           # Kerangka tata letak khusus area katalog pengunjung.
│       ├── petugas/            # 🎨 [FRONTEND] Antarmuka Khusus Petugas Operasional
│       │   ├── dashboard.blade.php            # Dasbor pemantauan petugas.
│       │   ├── report-management.blade.php    # Daftar keluhan kerusakan dan tindak lanjut.
│       │   └── reservation-management.blade.php # Antrean persetujuan (approval) peminjaman.
│       ├── profile/            # 🎨 [FRONTEND] Antarmuka Profil Akun
│       │   ├── edit.blade.php                 # Halaman utama edit profil.
│       │   └── partials/                      
│       │       ├── delete-user-form.blade.php # Form hapus akun.
│       │       ├── update-password-form.blade.php # Form ubah password.
│       │       └── update-profile-information-form.blade.php # Form ubah nama/email.
│       ├── public/             # 🎨 [FRONTEND] Area Publik (Katalog & Beranda)
│       │   ├── availability.blade.php     # Halaman cek jadwal ketersediaan semua fasilitas.
│       │   ├── catalog.blade.php          # Halaman grid daftar fasilitas.
│       │   └── home.blade.php             # Halaman muka (Landing Page) pencarian rungan.
│       ├── user/               # 🎨 [FRONTEND] Antarmuka Pengguna Sivitas Akademika (Dosen/Mhs)
│       │   ├── dashboard.blade.php        # Dasbor utama pengguna biasa.
│       │   ├── report-form.blade.php      # Halaman form pelaporan fasilitas rusak.
│       │   ├── report-history.blade.php   # Riwayat pelaporan yang pernah di-submit.
│       │   ├── reservation-form.blade.php # Halaman form booking ruangan.
│       │   └── reservation-history.blade.php # Riwayat dan tiket booking yang pernah dilakukan.
│       ├── dashboard.blade.php            # Halaman rute bawaan Breeze.
│       └── welcome.blade.php              # Halaman sambutan bawaan Laravel (opsional).
│
├── routes/                     # 🗄️ [KEDUANYA] NAVIGASI URL
│   └── web.php                 # 🗄️ [KEDUANYA] Pintu gerbang URL menuju controller/views.
│
└── database/                   # 🗄️ [BACKEND] Skema struktur tabel MySQL
    ├── factories/              # 🗄️ [BACKEND] Pembuat pola data palsu untuk testing
    │   └── UserFactory.php     # Pabrik data dummy untuk tabel pengguna.
    ├── migrations/             # 🗄️ [BACKEND] File perakit urutan tabel
    │   ├── 0001_01_01_000000_create_users_table.php          # Migrasi tabel pengguna.
    │   ├── 0001_01_01_000001_create_cache_table.php          # Migrasi tabel cache.
    │   ├── 0001_01_01_000002_create_jobs_table.php           # Migrasi tabel antrean pekerjaan.
    │   ├── 2026_09_20_000001_create_facilities_table.php     # Migrasi tabel master fasilitas.
    │   ├── 2026_09_20_000002_create_reservations_table.php   # Migrasi tabel transaksi peminjaman.
    │   ├── 2026_09_20_000003_create_damage_reports_table.php # Migrasi tabel keluhan kerusakan.
    │   └── 2026_09_20_151512_create_permission_tables.php    # Migrasi tabel role & permission Spatie.
    └── seeders/                # 🗄️ [BACKEND] Pembuat data awal untuk disuntikkan
        ├── DatabaseSeeder.php       # Induk pemanggil seluruh seeder.
        ├── FacilitySeeder.php       # Penyuntik data laboratorium/aula/ruang awal.
        ├── RolePermissionSeeder.php # Pembuat Role Admin/Petugas/User.
        └── UserSeeder.php           # Penyuntik 1 akun sakti (Admin) untuk testing.
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
   - **WAJIB MEMBACA:** Silakan ikuti prosedur lengkap pembuatan database pada file [CREATE_DATABASE.md](CREATE_DATABASE.md) sebelum melanjutkan.
   - Buka aplikasi **Laragon** (atau XAMPP) dan pastikan Apache & MySQL menyala.
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
