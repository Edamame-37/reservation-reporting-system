# Rencana Implementasi: Unifikasi Halaman Reservasi & Pelaporan Kerusakan (Bento UI Layout)

**Branch Target**: `refactor/refine-layout`  
**Fitur & Modul**:
1. **Unifikasi Reservasi Ruangan (USR-01, USR-02, USR-03)**: Integrasi form pengajuan ke dalam halaman riwayat sebagai basis utama via Modal / Slide-over Drawer.
2. **Unifikasi Pelaporan Kerusakan (USR-04, USR-05)**: Integrasi form pelaporan ke dalam halaman riwayat sebagai basis utama via Modal / Slide-over Drawer.
3. **Penyederhanaan Elemen Terfragmentasi**: Mengganti pill button bertumpuk/berdempetan (filter status dan kategori pelaporan) menjadi elemen Dropdown (`<select>`) yang rapi dan ergonomis.
4. **Transformasi Tabel Padat Menjadi Bento List**: Mengubah format tabel lebar yang ramai menjadi daftar kartu interaktif modern dilengkapi tombol *"Lihat Detail"*.
5. **Penerapan Bento UI Layout**: Merestrukturisasi antarmuka pengguna ke grid Bento modern (Kartu Ringkasan Metrik, Kartu Quick Action, dan Kartu Konten Utama).
6. **Perampingan Navigasi Sidebar**: Meringkas menu portal mahasiswa/dosen dari 4 tautan terpisah menjadi 2 modul terpadu.

**Dokumen Terkait**: `RULE_FRONTEND.md`, `RULE_BACKEND.md`, `RULE_PROJECT.md`, `TECHSTACK.md`, `GUIDE_GITHUB.md`, `CASE_PROJECT.md`

---

## 1. Analisis Kebutuhan & Permasalahan Saat Ini

Berdasarkan instruksi perbaikan antarmuka dari pengguna dan evaluasi visual pada tangkapan layar `report-history.blade.php`:

1. **Fragmentasi Halaman (Form vs Riwayat Terpisah)**:
   - *Kondisi Saat Ini*: Mahasiswa/dosen memiliki halaman terpisah untuk "Ajukan Reservasi" (`/user/reservation-form`) dan "Riwayat Reservasi" (`/user/reservation-history`), serta "Lapor Kerusakan" (`/user/report-form`) dan "Status Laporan" (`/user/report-history`). Hal ini membuat navigasi terkesan berulang dan tidak efisien.
   - *Solusi*: Jadikan halaman Riwayat sebagai halaman basis tunggal per modul. Aksi "Ajukan Reservasi" atau "Buat Laporan Baru" memunculkan Form Pengisian langsung di tempat via Modal / Slide-over Drawer terintegrasi (dengan dukungan pembukaan otomatis saat terjadi error validasi atau URL `?action=create`).

2. **Elemen Kecil Berdempetan (*Cluttered UI*)**:
   - *Kondisi Saat Ini*: Terdapat tombol-tombol pill filter status (`[Semua 1] [Baru 0] [Diproses 0] [Selesai 1] [Ditolak 0]`) yang berdempetan dengan input pencarian. Pada form laporan kerusakan, terdapat 8 pill tombol kategori yang tersebar berjejeran.
   - *Solusi*: Ganti pemilihan berdempetan tersebut menjadi komponen Dropdown (`<select>`) standar yang bersih, lapang, dan mudah digunakan sesuai kaidah *Ponytail Methodology*.

3. **Tabel Data Terlalu Ramai & Penuh Kolom**:
   - *Kondisi Saat Ini*: Tabel laporan kerusakan memuat 6 kolom lebar (ID Tiket & Waktu, Fasilitas & Kategori, Deskripsi Kerusakan panjang, Status Penanganan, Catatan Resolusi Teknisi, Foto Bukti) yang membuat tabel tampak penuh sesak dan kurang estetis.
   - *Solusi*: Rombak tabel menjadi **Daftar List Item / Card List Modern**. Tampilkan informasi esensial (Kode Tiket, Nama Fasilitas, Tanggal/Waktu, Status) secara rapi dengan tipografi berjenjang, dan pindahkan rincian teknis mendalam (catatan teknisi, foto resolusi tinggi, deskripsi komprehensif) ke dalam modal **"Lihat Detail"**.

4. **Kebutuhan Layout Bento UI**:
   - *Solusi*: Implementasikan layout Bento Box Grid yang membagi ruang halaman secara hierarkis:
     - **Bento Top Card 1 (Statistik & Metrik Cepat)**: Menampilkan rekapitulasi status tiket dalam kartu-kartu metrik yang bersih.
     - **Bento Top Card 2 (Quick Action & SLA Info)**: Kartu ringkas dengan tombol aksi utama (*Call to Action*) dan informasi operasional/SLA kampus.
     - **Bento Main Card (Daftar Riwayat & Kontrol Pencarian/Filter)**: Kartu utama berisi kontrol filter dropdown, kolom pencarian, dan daftar riwayat interaktif.

---

## 2. Rencana Arsitektur & Perubahan Teknis

### A. Backend (`app/Http/Controllers/`)

1. **`ReservationController.php`**:
   - Pada method `history(Request $request)`:
     - Tambahkan pemuatan data fasilitas aktif:
       ```php
       $facilities = Facility::where('status', 'aktif')->orderBy('name')->get();
       ```
     - Sertakan `$facilities` ke dalam `compact('reservations', 'counts', 'activeStatus', 'keyword', 'facilities')`.
     - Ini memungkinkan formulir modal pengajuan reservasi di `reservation-history.blade.php` memiliki data gedung, lantai, dan ruangan secara langsung tanpa perlu fetch AJAX terpisah.
   - Pada method `create(Request $request)`:
     - Pertahankan agar tetap mengembalikan `view('user.reservation-form', ...)` untuk menjaga kompatibilitas rute lama dan kelulusan pengujian otomatis pest (`ReservationFeatureTest.php`).

2. **`ReportController.php`**:
   - Pada method `history(Request $request)`:
     - Tambahkan pemuatan fasilitas aktif:
       ```php
       $facilities = Facility::where('status', 'aktif')->orderBy('name')->get();
       ```
     - Sertakan `$facilities` ke dalam `compact('reports', 'counts', 'activeStatus', 'facilities')`.
     - Ini memungkinkan modal formulir pelaporan kerusakan di `report-history.blade.php` memuat dropdown fasilitas secara langsung.
   - Pada method `create(Request $request)`:
     - Pertahankan agar tetap merender `view('user.report-form', ...)` demi kompatibilitas pengujian otomatis pest (`ReportFeatureTest.php`).

---

### B. Frontend (`resources/views/`)

1. **Halaman Terpadu Status & Riwayat Pelaporan Kerusakan (`user/report-history.blade.php`)**:
   - **Struktur Bento UI**:
     - **Hero / Header**: Judul dan subjudul yang elegan dengan ikon modul.
     - **Bento Grid Atas**:
       - *Card Metrik Status*: Ringkasan statistik (Total Tiket, Selesai Ditangani, Sedang Diproses, Menunggu).
       - *Card Aksi Cepat*: Tombol utama *"Buat Laporan Baru"* yang membuka modal formulir, disertai info SLA respon teknisi sarpras (&lt; 24 Jam).
     - **Bento Grid Utama (Daftar Laporan)**:
       - *Bar Kontrol*: Input pencarian kata kunci dan **Dropdown Filter Status** (menggantikan pill bertumpuk).
       - *Modern List View*: Menggantikan tabel HTML ramai dengan baris kartu ringkas:
         - Badge Tiket & Tanggal Laporan.
         - Nama Fasilitas & Kategori Sarana.
         - Ringkasan singkat deskripsi masalah (1-2 baris bersih).
         - Indikator Status (Baru, Diproses, Selesai, Ditolak).
         - Tombol *"Lihat Detail"*.
       - *Paginasi Laravel*: Tetap terintegrasi di bagian bawah.
   - **Modal Form Laporan Baru (Terintegrasi)**:
     - Dikelola dengan Alpine.js (`showCreateModal = false`).
     - Otomatis terbuka jika terdapat error validasi (`{{ $errors->any() ? 'true' : 'false' }}`) atau parameter URL `?action=create`.
     - Berisi input fasilitas, **Dropdown Kategori Kerusakan** (menggantikan 8 pill berdempetan), textarea deskripsi, dan upload foto bukti dengan pratinjau instan.
   - **Modal Lihat Detail Tiket**:
     - Menampilkan rincian penuh kendala, foto bukti kerusakan resolusi penuh, nama petugas penangan, tanggal selesai, dan catatan resolusi teknisi.

2. **Halaman Terpadu Reservasi Ruangan (`user/reservation-history.blade.php`)**:
   - **Struktur Bento UI**:
     - **Bento Grid Atas**:
       - *Card Metrik Reservasi*: Rekap status permohonan (Total, Disetujui, Menunggu, Ditolak/Batal).
       - *Card Aksi Cepat*: Tombol utama *"Ajukan Reservasi Baru"* yang membuka modal form, disertai ringkasan aturan batas pembatalan H-1.
     - **Bento Grid Utama (Daftar Reservasi)**:
       - *Bar Kontrol*: Input pencarian dan **Dropdown Filter Status** yang rapi.
       - *Modern List View*: Menggantikan tabel ramai dengan kartu list informatif:
         - Kode Tiket & Waktu Pelaksanaan (Tanggal, Jam Mulai - Selesai, Durasi Slot).
         - Nama Ruangan & Gedung.
         - Ringkasan tujuan acara.
         - Status verifikasi sarpras.
         - Tombol *"Lihat Detail"* & tombol cepat *"Batalkan"* (jika memenuhi batas minimal H-1).
       - *Paginasi Laravel*: Bersih di bawah kartu utama.
   - **Modal Form Reservasi Baru (Terintegrasi)**:
     - Dikelola via Alpine.js (`showCreateModal = false`).
     - Otomatis terbuka jika `$errors->any()` atau parameter `action=create`.
     - Berisi pemilihan 3 tingkat hierarki (Gedung, Lantai, Ruang), tanggal kegiatan (min hari ini), rentang jam operasional (07:00 - 20:00 WIB slot 30 menit), estimasi peserta, dan tujuan acara.
   - **Modal Lihat Detail Reservasi**:
     - Menampilkan data lengkap tiket: kapasitas ruang, PIC acara, catatan petugas, reviewer, dan tombol pembatalan mandiri.

3. **Bilah Samping Navigasi (`resources/views/components/cava/sidebar.blade.php`)**:
   - Ringkas navigasi peran Pengguna (`$role === 'user'`) menjadi:
     1. **Dasbor Saya** (`/user/dashboard`) - Ikon `home`
     2. **Reservasi Ruangan** (`/user/reservation-history`) - Ikon `calendar_month` (menyatukan form & riwayat)
     3. **Pelaporan Kerusakan** (`/user/report-history`) - Ikon `handyman` (menyatukan form & riwayat)
   - Tautan `reservation-form` dan `report-form` di dasbor atau pintasan diarahkan ke halaman terpadu dengan opsi pembukaan modal instan.

---

## 3. Tahapan Pengerjaan (Step-by-Step Execution)

1. **Langkah 1**: Perbarui `app/Http/Controllers/ReservationController.php` & `app/Http/Controllers/ReportController.php` untuk memuat data `$facilities` pada method `history()`.
2. **Langkah 2**: Bangun ulang `resources/views/user/report-history.blade.php` dengan Bento UI, Dropdown Filter, Dropdown Kategori Form, Bento List View, Modal Detail, dan Modal Form Laporan Baru.
3. **Langkah 3**: Bangun ulang `resources/views/user/reservation-history.blade.php` dengan Bento UI, Dropdown Filter, Bento List View, Modal Detail, dan Modal Form Reservasi Baru.
4. **Langkah 4**: Perbarui `resources/views/components/cava/sidebar.blade.php` untuk merampingkan navigasi menu pengguna menjadi ringkas dan terpadu.
5. **Langkah 5**: Lakukan verifikasi visual pada antarmuka peramban untuk memastikan responsivitas, interaksi modal, fungsionalitas submit, dan ketiadaan elemen berdempetan.
6. **Langkah 6**: Buat laporan pasca-eksekusi `walkthrough.md` serta cetak instruksi Git commit terstandarisasi untuk dieksekusi oleh programmer manusia.
