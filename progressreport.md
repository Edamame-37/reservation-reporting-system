# LAPORAN PROGRESS REPORT FITUR SISTEM RESERVASI & PELAPORAN FASILITAS KAMPUS
**Fokus Modul:** Konsol Administrasi & Tata Kelola Sistem (ADM-01, ADM-02, ADM-03, ADM-04)  
**Dokumen Rujukan:** `doc-feature/ADM01_Verifikasi_Akun_Pending.md`, `doc-feature/ADM02_Pembuatan_Akun_Internal.md`, `doc-feature/ADM03_Master_Data_Fasilitas.md`, `doc-feature/ADM04_Statistik_Export_Laporan.md`  
**Target Audiens:** Dosen Pembimbing / Dosen Penguji / Tim Reviewer Proyek  
**Tanggal Presentasi:** 25 September 2026  
**Status Implementasi:** **100% Selesai & Terintegrasi Penuh (Production-Ready)**  

---

## 🎓 Panduan Cepat Pembuka Presentasi (Opening Speech untuk Dosen)

> *"Selamat pagi/siang Bapak/Ibu Dosen Pembimbing dan Penguji. Pada kesempatan kali ini, saya ingin mempresentasikan capaian progres pengembangan Sistem Reservasi dan Pelaporan Kerusakan Fasilitas Kampus, khususnya pada **Modul Konsol Administrator (Biro Sarana & Prasarana)**.*
>
> *Di sistem ini, Admin bertindak sebagai **pusat kendali otorisasi, tata kelola aset, dan pelaporan eksekutif**. Ada 4 fitur inti yang telah selesai saya bangun dan integrasikan dari sisi User Story, arsitektur basis data, hingga antarmukanya:*
> 1. *Penyaringan dan Verifikasi Akun Pendaftar Mandiri (ADM-01 / US-15)*
> 2. *Pembuatan Akun Internal Petugas & Pengguna Khusus (ADM-02 / US-13 & US-14)*
> 3. *Pengelolaan Master Data Fasilitas dengan Proteksi Relasi Data (ADM-03 / US-16)*
> 4. *Dasbor Statistik Okupansi & Ekspor Laporan Statuter PDF/Excel (ADM-04 / US-17)*
>
> *Berikut adalah penjabaran detail dari latar belakang masalah, kebutuhan user story, implementasi teknis, hingga skenario pengujiannya."*

---

## 1. PENJELASAN INTI 4 FITUR YANG TELAH DIKERJAKAN

---

### FITUR 1: ADM-01 (US-15) — Verifikasi & Otorisasi Akun Pendaftar Mandiri (Status Pending)

#### A. Identitas Fitur & User Story
* **Kode Fitur:** ADM-01 | **User Story ID:** **US-15**
* **Aktor:** Super Admin (Biro Sarana dan Prasarana)
* **Rumusan User Story:**
  > **Sebagai** Administrator Sistem (Biro Sarpras),  
  > **Saya ingin** memeriksa data pemohon registrasi dan menyetujui (*approve*) atau menolak (*reject*) pendaftaran akun baru berstatus *pending*,  
  > **Agar** hanya sivitas akademika resmi (mahasiswa, dosen, dan staf beridentitas valid) yang dapat mengakses sistem dan meminjam fasilitas, serta mencegah penyusupan akun fiktif atau bot.

#### B. Mengapa Fitur Ini Dibuat? (*Problem Statement & Urgensi*)
* Kampus membuka formulir registrasi mandiri di web publik agar mahasiswa dan dosen dapat mendaftar sendiri tanpa harus antre di loket TU.
* **Risiko fatal:** Siapa saja di internet bisa mendaftar dengan identitas fiktif. Jika akun langsung aktif tanpa filter, mereka bisa memblokir jadwal ruangan kuliah secara ugal-ugalan (*denial of service/fake reservation*).
* **Solusi:** Mekanisme **Gatekeeper**. Akun yang mendaftar mandiri otomatis terkunci dengan status `pending` dan wajib diverifikasi berkasnya oleh Admin sebelum diizinkan membuat sesi login.

#### C. Cara Kerja Fitur di Sistem (*How It Works*)
1. Mahasiswa mendaftar mandiri melalui portal publik ➔ akun otomatis tercatat di basis data dengan kolom `status = 'pending'`.
2. Admin masuk ke halaman `/admin/user-management` pada tab **"Antrean Verifikasi (UR15)"**.
3. Admin memeriksa kesesuaian Nama, NIM/NIP, email institusi, dan berkas identitas (KTM / SK).
4. **Jika Valid:** Admin menekan tombol **"Setujui"** ➔ `status` diperbarui menjadi `active`, pesan sukses dimunculkan, dan pengguna langsung dapat login.
5. **Jika Tidak Valid:** Admin menekan tombol **"Tolak"** ➔ Membuka modal dialog resmi untuk memilih alasan penolakan (misal: *Foto KTM buram*, *NIM tidak terdaftar di PD-DIKTI*, atau *Bukan email resmi*) + catatan opsional ➔ `status` diubah menjadi `rejected` beserta alasan `rejection_reason`.

#### D. Poin Teknis yang Menarik untuk Dijelaskan ke Dosen
* **Strict Session Blocker:** Pada modul autentikasi, controller login mengecek kolom `status`. Pengguna `pending` atau `rejected` dicegat secara absolut oleh sistem dan diberi umpan balik yang informatif.
* **Idempotensi Tindakan:** Controller memvalidasi kondisi `$user->status !== 'pending'`. Jika sudah pernah diproses, aksi tidak dapat dieksekusi ganda untuk menjaga integritas audit trail.
* **Komponen Teknis:** `AdminUserManagementController@verifyUser`, `AdminUserManagementController@rejectUser`, FormRequest `RejectUserRequest`, dan Blade View `admin/user-management.blade.php`.

---

### FITUR 2: ADM-02 (US-13 & US-14) — Pembuatan Akun Internal (Petugas & Pengguna Khusus)

#### A. Identitas Fitur & User Story
* **Kode Fitur:** ADM-02 | **User Story ID:** **US-13 & US-14**
* **Aktor:** Super Admin (Biro Sarpras)
* **Rumusan User Story:**
  > **(US-13 - Petugas Sarpras):**  
  > **Sebagai** Super Admin,  
  > **Saya ingin** mendaftarkan akun Petugas Sarpras secara langsung beserta penetapan zona operasionalnya,  
  > **Agar** petugas lapangan dapat langsung mengelola antrean persetujuan ruangan dan penanganan kerusakan aset tanpa melalui registrasi publik.
  >
  > **(US-14 - Bypass Pengguna Langsung):**  
  > **Sebagai** Super Admin,  
  > **Saya ingin** mendaftarkan akun sivitas akademika (dosen tamu, pimpinan rektorat, staf BEM) secara manual,  
  > **Agar** akun langsung berstatus aktif (*bypass verification*) untuk kebutuhan peminjaman darurat tanpa menunggu antrean verifikasi berkas.

#### B. Mengapa Fitur Ini Dibuat? (*Problem Statement & Urgensi*)
* **Petugas Sarpras** memiliki hak istimewa operasional (*privilege* tinggi)—bisa menyetujui jadwal, membatalkan secara darurat (*override*), dan memblokir ruangan (*maintenance mode*). Oleh karena itu, pendaftaran akun Petugas **sama sekali tidak boleh dibuka untuk umum**.
* Dalam birokrasi kampus, ada kebutuhan registrasi mendesak (*VIP/Urgent*) bagi pimpinan atau panitia kegiatan resmi universitas yang membutuhkan akun siap pakai dalam hitungan menit tanpa harus melalui birokrasi unggah berkas.

#### C. Cara Kerja Fitur di Sistem (*How It Works*)
1. **Pendaftaran Petugas (US-13):**
   * Admin menekan tombol `+ Akun Petugas (UR13)` ➔ Modal form terbuka.
   * Admin mengisi Nama, NIP, Email resmi, dan memilih **Zona Penugasan** (*Zona 1 - Rektorat/Auditorium, Zona 2 - Gedung Kuliah, Zona 3 - Lab Terpadu*).
   * Sistem otomatis menetapkan Role `petugas` via paket otorisasi Spatie dan menandai status langsung `active`.
2. **Pendaftaran Pengguna Langsung (US-14):**
   * Admin menekan tombol `+ Akun Pengguna (UR14)`.
   * Admin mengisi Nama, Peran (Mahasiswa/Dosen/Staf), NIM/NIP, dan Email Kampus.
   * Akun disimpan dengan flag langsung aktif tanpa melalui antrean pending.

#### D. Poin Teknis yang Menarik untuk Dijelaskan ke Dosen
* **Proteksi Eskalasi Wewenang (*Anti-Escalation Safeguard*):** Dosen biasanya menanyakan: *"Bisakah admin membuat admin baru dari form ini?"* Jawab: **Tidak bisa**. Sistem membatasi nilai role hanya untuk `petugas` dan `pengguna` (mahasiswa/dosen/staf). Hal ini mencegah timbulnya akun admin bayangan (*shadow admin*).
* **Integrasi RBAC (*Role-Based Access Control*):** Menggunakan pustaka *Spatie Laravel Permission*, menjamin pemisahan hak akses antara penyewa (pengguna) dan petugas lapangan berjalan aman di level middleware.

---

### FITUR 3: ADM-03 (US-16) — Tata Kelola Master Data Inventaris Fasilitas (CRUD & Soft Deletes)

#### A. Identitas Fitur & User Story
* **Kode Fitur:** ADM-03 | **User Story ID:** **US-16**
* **Aktor:** Super Admin (Biro Sarpras)
* **Rumusan User Story:**
  > **Sebagai** Administrator Sarpras,  
  > **Saya ingin** mengelola master data ruangan dan fasilitas (menambah, mengubah data/kapasitas, mengunggah foto cover, dan menonaktifkan ruangan),  
  > **Agar** katalog publik dan kalender peminjaman selalu menampilkan inventaris kampus yang akurat, serta ruangan yang sedang rusak atau dialihfungsikan tidak dapat dipesan.

#### B. Mengapa Fitur Ini Dibuat? (*Problem Statement & Urgensi*)
* Ruang kampus sangat dinamis: penambahan lab komputer baru, penggabungan kelas, perubahan kapasitas kursi, peremajaan AC/proyektor, hingga renovasi gedung.
* Jika master data inventaris tidak terkelola, terjadi kekacauan: mahasiswa memesan aula yang kapasitasnya sudah berubah, atau memesan laboratorium yang sedang dibongkar.

#### C. Cara Kerja Fitur di Sistem (*How It Works*)
1. **Katalog Inventaris Terpusat:** Admin mengakses `/admin/facility-master` yang menampilkan tabel komprehensif seluruh ruangan, filter kategori (*Auditorium, Ruang Kelas, Lab, Olahraga*), dan pencarian instan.
2. **Tambah Fasilitas Baru (*Create*):** Mengisi formulir modal: Nama Fasilitas (wajib unik), Kode Ruangan, Kategori, Gedung, Kapasitas Kursi (angka positif), Jam Operasional (07:00 - 20:00 WIB), Daftar Sarana Penunjang (AC, Proyektor), dan unggah Foto Cover.
3. **Pembaruan Data (*Update*):** Admin dapat menyunting kapasitas atau spesifikasi. Jika foto diperbarui, sistem menghapus file fisik lama di storage lokal `/public/storage/facilities/` untuk menghemat ruang disk.
4. **Penghapusan / Non-aktif (*Soft Delete*):** Mengubah status fasilitas menjadi non-aktif atau mencabut fasilitas dari katalog publik.

#### D. Poin Teknis yang Menarik untuk Dijelaskan ke Dosen
* **Proteksi Integritas Relasional (*SoftDeletes*):** Dosen database sangat menyukai ini. Jika fasilitas dihapus dengan `DELETE` SQL mentah, seluruh riwayat transaksi di tabel `reservations` dan tiket perbaikan di tabel `reports` yang merujuk pada `facility_id` tersebut akan error atau menjadi *orphan records*.
* **Solusinya:** Menggunakan trait `SoftDeletes` di Laravel (`deleted_at`). Fasilitas hilang dari katalog publik, namun seluruh histori peminjaman tahun lalu tetap utuh dan dapat dipertanggungjawabkan saat audit.

---

### FITUR 4: ADM-04 (US-17) — Dasbor Analitik & Ekspor Laporan Statuter (PDF & Excel/CSV)

#### A. Identitas Fitur & User Story
* **Kode Fitur:** ADM-04 | **User Story ID:** **US-17**
* **Aktor:** Super Admin (Biro Sarpras)
* **Rumusan User Story:**
  > **Sebagai** Administrator Sarpras,  
  > **Saya ingin** melihat dasbor metrik okupansi, frekuensi kerusakan aset, dan mengunduh laporan resmi dalam format PDF siap cetak serta spreadsheet Excel/CSV,  
  > **Agar** Biro Sarpras dapat menyusun laporan pertanggungjawaban bulanan kepada Rektorat secara transparan berbasis data (*data-driven decision*).

#### B. Mengapa Fitur Ini Dibuat? (*Problem Statement & Urgensi*)
* Di akhir semester atau akhir tahun anggaran, pimpinan universitas (Rektorat) membutuhkan data valid:
  * Ruangan mana yang paling sering digunakan sivitas?
  * Fasilitas mana yang paling boros biaya pemeliharaan karena sering rusak?
  * Berapa rata-rata SLA waktu perbaikan teknisi?
* Tanpa fitur ini, staf TU harus merekap ratusan berkas peminjaman manual di buku log kertas yang memakan waktu berhari-hari dan rawan manipulasi.

#### C. Cara Kerja Fitur di Sistem (*How It Works*)
1. **Analitik Dasbor:** Admin membuka `/admin/export-report` atau `/admin/dashboard` dan memilih filter rentang tanggal (*Date Range Picker*).
2. **Kalkulasi Metrik Otomatis:** Sistem menjalankan kueri agregasi:
   * **Okupansi Ruang:** Persentase reservasi disetujui terhadap total permohonan.
   * **Total Jam Pemakaian:** Akumulasi durasi peminjaman terpakai (dihitung dari slot waktu 30 menit).
   * **Indeks SLA Resolusi Kerusakan:** Rasio keluhan yang berhasil dituntaskan teknisi.
   * **Utilisasi per Fasilitas:** Peringkat ruangan terfavorit.
   * **Frekuensi Kerusakan per Kategori:** Statistik AC bocor, proyektor mati, kursi patah, dll.
3. **Ekspor PDF Resmi (DomPDF):** Admin mengklik tombol "Cetak PDF" ➔ Sistem merender Blade template khusus cetak lengkap dengan Kop Surat Universitas, tanda tangan penanggung jawab, tabel bergaris rapi, dalam format kertas **A4 Landscape**.
4. **Ekspor Excel / CSV (Streamed Export):** Admin mengklik tombol "Unduh Excel (CSV)" ➔ Sistem mengekspor data mentah siap olah ke aplikasi spreadsheet.

#### D. Poin Teknis yang Menarik untuk Dijelaskan ke Dosen
* **Optimasi Memori (*Streamed Chunking*):** Jika data transaksi mencapai puluhan ribu baris, pemanggilan `Model::all()` akan menyebabkan *Memory Limit Exceeded (Fatal Error 500)*. Sistem ini menggunakan **StreamedResponse** yang mengalirkan baris demi baris langsung ke output buffer peramban tanpa membebani RAM server.
* **Pemisahan Tampilan Cetak:** Berkas PDF tidak mengambil screenshot halaman web, melainkan menggunakan template khusus (`admin/exports/reservations-pdf.blade.php` dan `damage-reports-pdf.blade.php`) yang bebas dari navigasi sidebar dan CSS interaktif.

---

## 2. MATRIKS PERBANDINGAN FITUR, USER STORY & VALUE BISNIS

Tabel ini sangat ideal untuk ditayangkan langsung pada layar slide presentasi Anda:

| Modul | No. US | Fitur Utama | Aktor Utama | Solusi & Manfaat Nyata Kampus |
|---|---|---|---|---|
| **ADM-01** | **US-15** | Verifikasi Akun Pendaftar Mandiri (*Pending*) | Super Admin | **Proteksi Sistem:** Menyaring pengguna liar, memvalidasi identitas sivitas (NIM/NIP/KTM), dan mencegah spam reservasi. |
| **ADM-02** | **US-13** | Pembuatan Akun Petugas Sarpras | Super Admin | **Tata Kelola Wewenang:** Mengamankan akun operasional berizin tinggi agar terdistribusi per zona kerja dan tidak bisa dibuat publik. |
| **ADM-02** | **US-14** | Registrasi Pengguna Langsung (*Bypass*) | Super Admin | **Efisiensi Layanan:** Mengakomodasi kebutuhan darurat peminjaman ruangan oleh dosen tamu atau panitia dies natalis. |
| **ADM-03** | **US-16** | Master Data Fasilitas (*CRUD & Soft Delete*) | Super Admin | **Akurasi Inventaris & Keamanan Database:** Memastikan data kapasitas/sarana selalu update dan menjaga keutuhan relasi data reservasi masa lalu. |
| **ADM-04** | **US-17** | Dasbor Analitik & Ekspor Statuter (PDF & Excel) | Super Admin | **Akuntabilitas & Audit:** Membantu Biro Sarpras menyajikan data okupansi dan KPI kerusakan ke Rektorat secara otomatis dan cepat. |

---

## 3. ARSITEKTUR TEKNIS & KEUNGGULAN KODE (Yang Disukai Dosen)

Jelaskan aspek arsitektur perangkat lunak ini untuk menunjukkan bahwa Anda tidak sekadar membuat UI, melainkan memikirkan rekayasa perangkat lunak yang matang:

```
                  ┌────────────────────────────────────────┐
                  │          KONSOL ADMINISTRATOR          │
                  └───────────────────┬────────────────────┘
                                      │
        ┌─────────────────────────────┼─────────────────────────────┐
        ▼                             ▼                             ▼
┌──────────────┐              ┌──────────────┐              ┌──────────────┐
│    ADM-01    │              │    ADM-02    │              │    ADM-03    │
│  Verifikasi  │              │ Akun Internal│              │Master Ruangan│
│ Akun Pending │              │ (Petugas/User│              │(SoftDeletes) │
└───────┬──────┘              └───────┬──────┘              └───────┬──────┘
        │                             │                             │
        └──────────────────────┬──────┴─────────────────────────────┘
                               ▼
        ┌────────────────────────────────────────────┐
        │       DATABASE (MySQL + Eloquent ORM)      │
        │ - users (roles, status, rejection_reason)  │
        │ - facilities (deleted_at, status_aktif)    │
        │ - reservations & damage_reports            │
        └──────────────────────┬─────────────────────┘
                               ▼
                      ┌────────────────┐
                      │     ADM-04     │
                      │Analitik & Export│
                      │  (PDF & Excel) │
                      └────────────────┘
```

1. **Separation of Concerns (SoC):**
   * Logika bisnis terisolasi rapi di controller masing-masing: `AdminUserManagementController`, `FacilityController`, dan `ExportController`.
   * Validasi payload form dipisahkan ke kelas *Form Request* (`RejectUserRequest`).
2. **Role-Based Access Control (RBAC):**
   * Menggunakan pustaka standar industri (*Spatie Laravel Permission*) dengan 3 tingkatan peran utama: `Admin`, `Petugas`, dan `Pengguna` (Mahasiswa, Dosen, Staf).
3. **Database Relational Integrity:**
   * Menerapkan trait `SoftDeletes` pada tabel `facilities`. Menghindari eror *foreign key constraint* pada histori reservasi dan laporan kerusakan sebelumnya.
4. **Memory-Friendly Architecture:**
   * Menghindari *memory leak* pada proses unduh data masal dengan memanfaatkan fitur *Streamed Chunking* pada ekspor CSV/Excel.

---

## 4. PANDUAN SKENARIO DEMO LANGSUNG (Live Demo Script)

Ikuti urutan langkah berikut saat Anda mendemokan aplikasi di depan dosen:

### Skenario 1: Demo ADM-01 (Verifikasi Akun Pending)
1. **Buka Browser:** Arahkan ke URL `/admin/user-management`.
2. **Tunjukkan Stat Card:** Tunjukkan kartu statistik di atas: *"Bapak/Ibu, ini antrean pemohon pending yang baru saja mendaftar mandiri lewat web."*
3. **Persetujuan Akun:** Pilih salah satu nama mahasiswa ➔ Klik tombol **"Setujui"** ➔ Tunjukkan notifikasi flash hijau muncul dan status akun berpindah ke tab *Sivitas Terdaftar* (berstatus aktif).
4. **Penolakan Akun:** Pilih pemohon lain ➔ Klik tombol **"Tolak"** ➔ Tunjukkan modal dialog ➔ Pilih opsi alasan: *"Foto KTM buram atau tidak terbaca"* ➔ Klik *Tolak Pendaftaran* ➔ Tunjukkan statusnya terblokir dan alasan penolakan tersimpan di database.

### Skenario 2: Demo ADM-02 (Pembuatan Akun Internal)
1. **Tambah Petugas:** Klik tombol hijau `+ Akun Petugas (UR13)` di pojok kanan atas.
2. **Pengisian Form:** Masukkan nama petugas baru, NIP, email, dan pilih zona (misal: *Zona 2 - Gedung Kuliah Terpadu*).
3. **Simpan:** Klik simpan ➔ Buka tab **"Petugas Sarpras (UR13)"** ➔ Tunjukkan akun langsung aktif tanpa antrean verifikasi.
4. **Jelaskan ke Dosen:** *"Fitur ini membatasi agar publik tidak bisa mendaftar sebagai petugas lapangan, dan admin pun dicegah membuat admin bayangan."*

### Skenario 3: Demo ADM-03 (Master Data Fasilitas & Soft Delete)
1. **Navigasi:** Pindah ke menu `/admin/facility-master`.
2. **Filter Interaktif:** Klik tab kategori *"Auditorium"*, *"Ruang Kelas"*, dan *"Laboratorium"* untuk menunjukkan responsivitas tabel inventaris.
3. **Tambah/Edit Ruangan:** Klik tombol `+ Tambah Fasilitas (UR16)` ➔ Tunjukkan form validasi kelengkapan data (kapasitas kursi, peralatan penunjang, unggah cover foto).
4. **Tekankan ke Dosen:** *"Saat fasilitas dinonaktifkan, kami menerapkan Soft Deletes. Tujuannya agar data riwayat peminjaman mahasiswa di bulan-bulan lalu tidak error saat di-audit."*

### Skenario 4: Demo ADM-04 (Statistik & Ekspor PDF/Excel)
1. **Navigasi:** Buka menu `/admin/export-report`.
2. **Tunjukkan KPI:** Sorot 4 kartu indikator: Tingkat Okupansi, Total Jam Terpakai, Indeks Penyelesaian SLA Kerusakan, dan Ruangan Terfavorit.
3. **Filter Periode:** Ubah tanggal filter (*start_date* dan *end_date*).
4. **Cetak PDF:** Klik tombol merah **"Cetak PDF Laporan"** ➔ Buka file PDF hasil unduhan ➔ Tunjukkan tata letak A4 Landscape resmi lengkap dengan Kop Surat Universitas dan kolom tanda tangan pejabat.
5. **Unduh Excel:** Klik tombol hijau **"Unduh Excel (CSV)"** ➔ Tunjukkan berkas spreadsheet terunduh secara instan.

---

## 5. SIMULASI TANYA-JAWAB KRITIS DENGAN DOSEN (Q&A Defense Guide)

Berikut adalah daftar pertanyaan tajam yang paling sering dilontarkan dosen beserta jawaban taktis yang tepat:

#### ❓ Pertanyaan 1:
> *"Mengapa Anda repot-repot membuat proses approval verifikasi akun di ADM-01? Kenapa tidak dibuat otomatis aktif begitu pengguna klik link verifikasi di email?"*

**Jawaban Anda:**
> *"Terima kasih pertanyaannya, Pak/Bu. Fitur verifikasi email standar hanya memvalidasi bahwa alamat email tersebut aktif, tetapi tidak dapat memvalidasi **apakah pemilik email benar-benar sivitas akademika kampus yang berhak**. Siapa pun bisa membuat email gratis di internet.  
> Karena aset kampus (seperti Auditorium atau Lab Komputer) bernilai miliaran rupiah dan menyangkut alokasi jadwal kuliah resmi, kami menerapkan kontrol administratif di mana Admin wajib memverifikasi kecocokan NIM/NIP pemohon dengan data PD-DIKTI/KTM fisik sebelum akun diberi izin membuat transaksi peminjaman."*

---

#### ❓ Pertanyaan 2:
> *"Pada ADM-02, apa yang mencegah seorang Admin yang nakal membuat akun Super Admin lain untuk temannya?"*

**Jawaban Anda:**
> *"Kami menerapkan prinsip keamanan **Principle of Least Privilege** dan **Server-Side Validation**. Pada form pembuatan akun internal, opsi role di antarmuka dikunci ketat dan di sisi backend divalidasi dengan aturan `in:petugas,pengguna`. Request yang memanipulasi payload HTTP untuk memasukkan role `admin` akan langsung ditolak dengan eror validasi 422. Akun Super Admin hanya dapat diinisialisasi melalui database seeder resmi oleh administrator server."*

---

#### ❓ Pertanyaan 3:
> *"Di ADM-03, apa bedanya menghapus data biasa (Hard Delete) dengan Soft Delete yang Anda implementasikan? Apa resikonya jika pakai delete biasa?"*

**Jawaban Anda:**
> *"Jika menggunakan Hard Delete (`DELETE FROM facilities WHERE id = ...`), basis data akan mengalami pelanggaran integritas referensial (*foreign key integrity violation*) karena ID fasilitas tersebut sudah tertaut pada puluhan baris di tabel `reservations` dan tabel `damage_reports`.  
> Jika kita memaksa `ON DELETE CASCADE`, seluruh riwayat peminjaman masa lalu akan ikut terhapus, sehingga laporan audit tahunan universitas menjadi cacat dan kehilangan histori.  
> Dengan **Soft Delete (`deleted_at`)**, fasilitas tersebut ditandai non-aktif dan disembunyikan dari katalog publik, tetapi seluruh data relasional transaksinya tetap utuh untuk kebutuhan pelaporan di ADM-04."*

---

#### ❓ Pertanyaan 4:
> *"Di ADM-04, jika data reservasi yang ditarik mencapai 50.000 baris selama 5 tahun, apakah server Anda tidak akan down saat mengekspor Excel atau PDF?"*

**Jawaban Anda:**
> *"Kami telah mengantisipasi hal tersebut dengan dua pendekatan:  
> 1. Fitur ekspor dilengkapi filter parameter rentang tanggal (*Date Range Picker*) wajib, sehingga data yang ditarik terbatasi per periode audit.  
> 2. Khusus untuk ekspor spreadsheet, kami menggunakan teknik **StreamedResponse dengan chunking memory**, di mana data diambil dari basis data per blok kecil dan dialirkan langsung ke output stream browser tanpa ditumpuk sekaligus di memori RAM server, sehingga mencegah terjadinya fatal error `Memory Limit Exceeded`."*

---

## 6. KESIMPULAN & ROADMAP SELANJUTNYA

### Capaian Saat Ini:
* Seluruh 4 fitur Konsol Admin (**ADM-01, ADM-02, ADM-03, ADM-04**) telah selesai dikembangkan 100% dan siap diuji coba secara langsung (*fully functioning*).
* Struktur arsitektur telah memenuhi standar integritas data, keamanan RBAC, dan kesiapan pelaporan statuter universitas.

### Langkah Selanjutnya (*Next Milestone*):
* Integrasi menyeluruh antrean verifikasi akun dengan notifikasi email otomatis ke mahasiswa/dosen (*Mail Notification Event*).
* Finalisasi modul transaksi reservasi sisi pengguna (validasi bentrok jadwal slot 30 menit) dan dashboard operasional petugas lapangan.
