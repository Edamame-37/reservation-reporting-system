# Rencana Implementasi: Refinement Bento UI Dasbor User, Pembersihan Breadcrumb, dan Penyesuaian Respon Petugas (< 24 Jam)

**Branch Target**: `refactor/refine-layout`  
**Fitur & Modul**:
1. **Bento UI List Dasbor User (Riwayat Reservasi Terkini)**: Mengganti tabel padat di dasbor pengguna menjadi Bento List Card (maksimal 3 item) dengan micro-animation pop-up saat hover (`hover:scale-[1.015] hover:shadow-md`) dan navigasi langsung terarah ke `#daftar-reservasi` pada halaman riwayat.
2. **Pembersihan Kartu Tiket Kerusakan (*Ultra Clean List*)**: Menghilangkan teks deskripsi masalah dan kotak resolusi dari kartu baris daftar tiket agar antarmuka bersih dan minimalis; rincian tetap tersimpan dan dapat dilihat melalui modal dialog *"Lihat Detail"*.
3. **Standardisasi Respon Petugas (< 24 Jam) Pengganti SLA**: Menghapus istilah teknis "SLA Respon Teknisi" pada pelaporan kerusakan dan menggantinya dengan penjelasan manusiawi bahwa laporan akan ditinjau dan direspon oleh petugas dalam waktu kurang dari 24 jam. Menyesuaikan teks terkait pada antarmuka Petugas dan Admin.
4. **Pembersihan Navigasi Atas (*Breadcrumb Removal*)**: Menghapus elemen breadcrumb ikon home dan tulisan *"Dasbor Saya > ..."* di bagian atas halaman pengguna agar tampilan lebih modern dan langsung berfokus pada konten utama.
5. **Pembersihan Kode Tiket & Penyeragaman Font Tanggal/Jam**: Menghilangkan tampilan kode tiket dari kartu reservasi pengguna dan menyamakan font jam kegiatan dengan font tanggal (Inter/Sans bold, bukan monospace).

**Dokumen Terkait**: `RULE_FRONTEND.md`, `RULE_BACKEND.md`, `GUIDE_GITHUB.md`, `TECHSTACK.md`, `CASE_PROJECT.md`

---

## 1. Analisis Kebutuhan & Permasalahan

1. **Dasbor User (`user/dashboard.blade.php`)**:
   - *Masalah*: Seksi "Riwayat Reservasi Terkini" masih menggunakan tabel HTML 6 kolom tradisional yang kaku dan memenuhi ruang.
   - *Solusi*: Ubah menjadi **Bento UI List View** (maksimal 3 item teratas via `$recentReservations->take(3)`). Berikan efek animasi pop-up mikro yang halus saat kursor diarahkan (`transition-all duration-300 transform hover:scale-[1.015] hover:-translate-y-0.5 hover:shadow-md cursor-pointer`). Tautan *"Lihat Semua Riwayat Reservasi"* diarahkan ke `url('/user/reservation-history#daftar-reservasi')` sehingga halaman riwayat langsung fokus ke daftar reservasi.

2. **Daftar Laporan Kerusakan (`user/report-history.blade.php`)**:
   - *Masalah*: Kartu baris tiket saat ini menampilkan cuplikan teks deskripsi dan kotak hijau catatan resolusi teknisi yang membuat daftar terlihat ramai (*cluttered*).
   - *Solusi*: Bersihkan kartu baris tiket sehingga hanya menampilkan identitas penting: Kode Tiket, Tanggal & Waktu, Kategori Sarana, Nama Fasilitas, Badge Status, dan tombol *"Lihat Detail"*. Rincian deskripsi kerusakan dan catatan resolusi teknisi tetap dapat dibaca secara lengkap pada modal pop-up ketika pengguna mengeklik *"Lihat Detail"*.

3. **Penyesuaian Istilah SLA Menjadi Respon Petugas (< 24 Jam)**:
   - *Masalah*: Frasa "SLA Respon Teknisi" merupakan istilah teknis yang kurang ramah pengguna umum, dan pengguna meminta diganti dengan penjelasan bahwa laporan direspon petugas sarpras kurang dari 24 jam, serta disesuaikan di sisi Petugas dan Admin.
   - *Solusi*: 
     - Di `user/report-history.blade.php`: Hapus badge pill SLA, ubah deskripsi kartu aksi menjadi: *"Laporan kendala fasilitas akan ditinjau dan direspon oleh petugas sarpras dalam waktu kurang dari 24 jam."*
     - Di `user/report-form.blade.php`: Sesuaikan badge header menjadi *"Respon Petugas: < 24 Jam"*.
     - Di `petugas/dashboard.blade.php`: Sesuaikan metrik footer menjadi *"Respon Cepat Petugas < 2 Jam"* dan *"Target Penanganan < 24 Jam"*.
     - Di `admin/dashboard.blade.php`: Sesuaikan metrik footer menjadi *"Verifikasi Petugas < 24 Jam"* dan *"Terselesaikan Petugas X%"*.

4. **Pembersihan Navigasi Atas (*Breadcrumb*)**:
   - *Masalah*: Di atas judul halaman terdapat breadcrumb ikon home dan tulisan *"Dasbor Saya > ..."* yang dirasa repetitif karena sidebar sudah menunjukkan posisi aktif.
   - *Solusi*: Hapus blok breadcrumb tersebut dari halaman `reservation-history.blade.php` dan `report-history.blade.php` agar tata letak langsung menampilkan judul halaman yang elegan.

---

## 2. Rencana Arsitektur & Perubahan Teknis

### A. Frontend: `resources/views/user/dashboard.blade.php`
- Pada seksi "Riwayat Reservasi Terkini":
  - Hapus tag `<table>` 6 kolom.
  - Render kartu-kartu Bento List (maksimal 3 item via `$recentReservations->take(3)`):
    - Layout flexbox responsif dengan `transition-all duration-300 transform hover:scale-[1.015] hover:-translate-y-0.5 hover:shadow-md cursor-pointer border border-slate-200/80 hover:border-slate-300 rounded-2xl bg-white p-4 sm:p-5`.
    - Menampilkan Kode Tiket (mono badge), Tanggal & Sesi Jam, Nama Ruangan & Gedung, serta Badge Status.
  - Tautan tombol *"Lihat Semua Riwayat Reservasi"* diperbarui mengarah ke `url('/user/reservation-history#daftar-reservasi')`.

### B. Frontend: `resources/views/user/reservation-history.blade.php`
- Hapus blok breadcrumb navigasi atas (ikon home dan tulisan `Dasbor Saya > Reservasi Ruangan Kampus`).
- Tambahkan atribut `id="daftar-reservasi"` pada elemen container Bento Grid Daftar Reservasi agar tautan anchor dari dasbor langsung mendarat tepat pada daftar riwayat.

### C. Frontend: `resources/views/user/report-history.blade.php`
- Hapus blok breadcrumb navigasi atas (ikon home dan tulisan `Dasbor Saya > Pelaporan Kerusakan Fasilitas`).
- Pada Bento Card 2 (Quick Action):
  - Hapus badge pill `SLA Respon Teknisi: < 24 Jam`.
  - Sesuaikan paragraf penjelasan: *"Kirimkan laporan kerusakan AC, proyektor, kelistrikan, atau fasilitas kampus lainnya. Laporan Anda akan segera ditinjau dan direspon oleh petugas sarpras dalam waktu kurang dari 24 jam."*
  - Sesuaikan footer kartu: *"Respon petugas < 24 jam"*.
- Pada kartu baris daftar tiket pengaduan:
  - Hapus baris deskripsi: `<p class="text-xs text-slate-600 mt-1 line-clamp-1">{{ $report->description }}</p>`.
  - Hapus blok resolusi hijau: `@if ($report->resolution_note) ... @endif`.
  - Kartu kini tampil bersih dan rapi (*clean minimalist*), dengan seluruh deskripsi dan resolusi dapat dilihat melalui modal *"Lihat Detail"*.

### D. Frontend: `resources/views/user/report-form.blade.php`
- Sesuaikan badge informasi header dari `Target Respon SLA: < 24 Jam` menjadi `Respon Petugas: < 24 Jam`.

### E. Frontend: Dasbor Petugas & Admin (`resources/views/petugas/dashboard.blade.php` & `resources/views/admin/dashboard.blade.php`)
- `petugas/dashboard.blade.php`:
  - Ubah `SLA Respon < 2 Jam` menjadi `Respon Cepat Petugas < 2 Jam`.
  - Ubah `Tidak ada permohonan reservasi... SLA operasional dalam kondisi optimal` menjadi `Penanganan operasional dalam kondisi optimal`.
- `admin/dashboard.blade.php`:
  - Ubah `Target SLA < 24 Jam` menjadi `Verifikasi Petugas < 24 Jam`.
  - Ubah `SLA Terselesaikan` menjadi `Terselesaikan Petugas`.

---

## 3. Tahapan Pengerjaan (Step-by-Step Execution)

1. **Langkah 1**: Perbarui `resources/views/user/dashboard.blade.php` untuk merombak seksi riwayat reservasi terkini menjadi Bento List Card (maksimal 3 item) dengan micro-animation pop-up dan anchor `#daftar-reservasi`.
2. **Langkah 2**: Perbarui `resources/views/user/reservation-history.blade.php` untuk menghapus breadcrumb atas dan menambahkan anchor `id="daftar-reservasi"`.
3. **Langkah 3**: Perbarui `resources/views/user/report-history.blade.php` untuk menghapus breadcrumb atas, menghapus SLA badge, memperjelas respon petugas < 24 jam, serta membersihkan teks deskripsi dan kotak resolusi pada kartu tiket.
4. **Langkah 4**: Perbarui `resources/views/user/report-form.blade.php`, `resources/views/petugas/dashboard.blade.php`, dan `resources/views/admin/dashboard.blade.php` untuk menyelaraskan istilah respon petugas < 24 jam.
5. **Langkah 5**: Validasi sintaks blade dan jalankan kompilasi template (`php artisan view:clear; php artisan view:cache`).
6. **Langkah 6**: Buat laporan pasca-eksekusi `walkthrough.md` serta sajikan instruksi Git commit terpisah sesuai panduan tim.
