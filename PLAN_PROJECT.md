# Blueprint & Pembagian Tugas Pengembangan (*Project Plan*)

Dokumen ini merupakan kerangka cetak biru (*blueprint*) aksi teknis yang menjabarkan **apa saja yang harus dikerjakan** oleh *programmer* langkah demi langkah untuk membangun Sistem Reservasi & Pelaporan Kerusakan Fasilitas.

Gunakan dokumen ini sebagai daftar periksa (*checklist*) progres pengerjaan sistem.

---

## Tahap 1: Setup & Inisiasi Dasar (Fondasi Proyek)
*Tahap ini memastikan seluruh alat dan kerangka kerja siap sebelum mulai mengetik logika.*

- [ ] `[Project Manager]` **Instalasi Framework:** Menjalankan `composer create-project laravel/laravel reservasi-app`.
- [ ] `[Backend]` **Setup Database:** Membuat *database* di MySQL (Laragon/XAMPP) dan menyambungkannya ke file `.env`.
- [ ] `[Project Manager]` **Instalasi Pustaka (Library):**
  - [ ] `[Frontend]` Pasang *Tailwind CSS* & *Alpine.js* (untuk UI & animasi antarmuka).
  - [ ] `[Backend]` Pasang *Laravel Breeze* (untuk kerangka Login/Register bawaan).
  - [ ] `[Backend]` Pasang *Spatie Laravel Permission* (untuk mengatur 4 lapis *Role* pengguna).
  - [ ] `[Backend]` Pasang *Laravel Excel* & *DomPDF* (untuk fitur unduh laporan Admin).
  - [ ] `[Frontend]` Unduh *FullCalendar.js* via NPM (untuk komponen visualisasi jadwal).

---

## Tahap 2: Perancangan Basis Data (Migrasi & Model)
*Pekerjaan murni Backend. Membangun struktur tabel MySQL agar sesuai dengan analisis ERD.*

- [ ] `[Backend]` **Tabel `users` (Bawaan Breeze):** Tambahkan kolom tambahan jika perlu (misal: NIK/NIM, no_hp).
- [ ] `[Backend]` **Tabel `facilities`:** Buat *migration* (nama, tipe, lokasi, kapasitas, deskripsi, foto_fasilitas, status_aktif).
- [ ] `[Backend]` **Tabel `reservations`:** Buat *migration* (user_id, facility_id, tujuan_penggunaan, start_time, end_time, status_reservasi, alasan_batal).
- [ ] `[Backend]` **Tabel `reports`:** Buat *migration* (user_id, facility_id, kategori_kerusakan, deskripsi_masalah, foto_bukti, status_laporan, catatan_resolusi).
- [ ] `[Backend]` **Relasi Eloquent (Models):** Menghubungkan logika di dalam *Model* (misal: `User hasMany Reservation`, `Facility hasMany Report`).
- [ ] `[Backend]` **Data Palsu (Seeder):** Membuat data *dummy* fasilitas (misal: Lab Komputer, Aula) dan membuat 1 akun Admin bawaan via *DatabaseSeeder* agar sistem langsung bisa di-uji.

---

## Tahap 3: Sistem Keamanan & Otorisasi (*Security*)
*Mengunci akses halaman berdasarkan hak wewenang pengguna.*

- [ ] `[Backend]` **Konfigurasi Role & Permission:** Menjalankan seeder untuk membuat *Role*: `Admin`, `Petugas`, `Pengguna`.
- [ ] `[Backend]` **Pembuatan Middleware:** Merakit pelindung rute URL (`Route::middleware`) agar Pengguna biasa tidak bisa mengetik URL Dasbor Admin.
- [ ] `[Backend]` **Penyesuaian Registrasi (Pending):** Memodifikasi logika pendaftaran *Breeze* agar akun baru berstatus *Pending* dan butuh klik validasi (*Approve*) oleh Admin sebelum bisa *login*.

---

## SOP Standar Siklus Kerja Pembuatan Antarmuka (Wajib Dipatuhi)
*Sebelum masuk ke pengerjaan modul-modul di bawah (Tahap 4 hingga 7), tim wajib memahami urutan kerja baku ini untuk membangun setiap halaman web agar tidak berantakan:*

1. [ ] `[UI/UX / Frontend]` **Perancangan Visual:** Membuat dan mematangkan desain halaman (*page*) menggunakan *software prototyping* (seperti Figma atau stich.ai).
2. [ ] `[Frontend]` **Konversi Desain (Slicing):** Mengubah desain *mockup* tersebut ke dalam bahasa pemrograman visual murni (HTML Statis & Tailwind CSS).
3. [ ] `[Frontend]` **Integrasi Struktur File (Blade):** Memindahkan HTML hasil *slicing* ke dalam format dan struktur *folder* yang telah disediakan oleh Laravel (yaitu di direktori `resources/views/`).
4. [ ] `[Frontend]` **Pemecahan Komponen Terisolasi:** Memecah objek-objek UI dan logika dari masing-masing halaman menjadi komponen kecil yang bisa didaur ulang (seperti `<x-button>`, `<x-navbar>`) menggunakan fitur *Blade Components* agar kodingan utama tidak menumpuk dan rapi.
5. [ ] `[Backend]` **Penyiapan Suplai Data (API/Controller):** Menyiapkan *Endpoint API* atau variabel dari *Controller* yang berisi data matang (*query database*) untuk ditampilkan oleh Frontend.
6. [ ] `[Frontend]` **Injeksi Data Dinamis:** Berkomunikasi dengan tim Backend terkait alamat *endpoint* / variabelnya, lalu mengganti seluruh data palsu (*mock data*) pada HTML desain menjadi data riil dari sistem.
7. [ ] `[Project Manager]` **Quality Control (QC) & UAT:** Menganalisis dan memvalidasi apakah hasil kode Frontend sudah presisi (*pixel-perfect*) dengan desain asli dan memastikan integrasi data dari Backend berjalan sempurna tanpa *bug*.

---

## Tahap 4: Modul Katalog & Master Data Fasilitas
*Fokus pada halaman yang mengelola dan menampilkan daftar fasilitas.*

- [ ] `[Frontend & Backend]` **Dasbor Admin (CRUD Fasilitas):**
  - [ ] `[Frontend]` Membuat tampilan formulir *Tambah/Edit* fasilitas.
  - [ ] `[Backend]` Logika `FacilityController` untuk memproses *Upload* foto sampul fasilitas.
  - [ ] `[Backend]` Logika penonaktifan fasilitas (agar tidak bisa dipinjam lagi).
- [ ] `[Frontend & Backend]` **Halaman Katalog Publik (Pengunjung):**
  - [ ] `[Frontend]` Tampilan antarmuka (*Grid/Card*) daftar fasilitas dengan *Tailwind*.
  - [ ] `[Frontend]` Fitur kolom pencarian (*Search*) dan penyaringan (*Filter*) berdasarkan Tipe/Lokasi (dihubungkan ke query *Backend*).

---

## Tahap 5: Modul Kalender & Transaksi Reservasi
*Fitur inti dan paling krusial. Memerlukan ketelitian tinggi pada validasi waktu.*

- [ ] `[Frontend & Backend]` **Integrasi API Kalender:**
  - [ ] `[Backend]` Merakit *Endpoint API* (`/api/fasilitas/{id}/jadwal`) untuk menyuplai data JSON ketersediaan ke *Frontend*.
  - [ ] `[Frontend]` Merender komponen *FullCalendar.js* pada halaman detail fasilitas.
- [ ] `[Frontend & Backend]` **Form Pengajuan Reservasi:**
  - [ ] `[Frontend]` Tampilan formulir pengajuan dengan pembatasan jam operasional (07.00 - 20.00).
  - [ ] `[Backend]` Logika Validasi (*FormRequest*) untuk menolak input selain kelipatan 30 menit.
- [ ] `[Backend]` **Logika Anti-Bentrok (*Concurrency Handling*):**
  - [ ] `[Backend]` Memasukkan sintaks `lockForUpdate()` di dalam transaksi *database* pada `ReservationController` untuk menggagalkan klik ganda (*double-booking*).
- [ ] `[Frontend & Backend]` **Dasbor Petugas (Approval):**
  - [ ] `[Frontend]` Halaman antrean daftar pesanan yang harus disetujui/ditolak Petugas.
  - [ ] `[Backend]` Fitur *Override* (Pembatalan paksa oleh Petugas diiringi alasan).
- [ ] `[Frontend & Backend]` **Dasbor Pengguna:** Riwayat pinjaman (dibuat oleh `[Frontend]`) dan tombol Batalkan Pesanan dengan validasi logika **Batas H-1** (diproteksi oleh `[Backend]`).

---

## Tahap 6: Modul Pelaporan Kerusakan (Ticketing)
*Fitur operasional pemeliharaan aset.*

- [ ] `[Frontend & Backend]` **Formulir Laporan (Pengguna):**
  - [ ] `[Frontend]` Tampilan form lapor dengan batasan unggah *file* gambar (Max 2MB).
  - [ ] `[Backend]` Logika penyimpanan file gambar (menggunakan `Storage` Laravel).
- [ ] `[Frontend & Backend]` **Manajemen Laporan (Petugas):**
  - [ ] `[Frontend]` Antarmuka dasbor petugas untuk melihat dan mengubah pergerakan status laporan (Baru ➔ Diproses ➔ Selesai).
  - [ ] `[Backend]` Input catatan resolusi/perbaikan setelah laporan ditutup.
- [ ] `[Backend]` **Sinkronisasi Kalender (Otomatisasi):**
  - [ ] `[Backend]` Menambahkan *Event/Observer* (Logika otomatis): Jika laporan diubah menjadi "Diproses", ubah status fasilitas tersebut menjadi "Dalam Perbaikan" (sehingga langsung diblokir dan tidak muncul di Kalender Reservasi).

---

## Tahap 7: Modul Pelaporan & Ekspor Data (*Analytics*)
*Fitur untuk kebutuhan dokumen manajemen.*

- [ ] `[Frontend]` **Dasbor Statistik Admin:** Merancang tampilan *chart* atau angka rekapitulasi okupansi (jumlah peminjaman per fasilitas) dan frekuensi kerusakan.
- [ ] `[Backend]` **Fitur Unduh (Export):**
  - [ ] `[Backend]` Logika integrasi *Laravel Excel* untuk mencetak data CSV.
  - [ ] `[Backend]` Logika integrasi *DomPDF* untuk mencetak tampilan tabel HTML menjadi *file* PDF laporan resmi.

---

## Tahap 8: Pengujian & Penyelesaian Akhir (*Testing & Polish*)
*Pembersihan bug sebelum diserahkan ke dosen penguji.*

- [ ] `[Project Manager]` **Uji Akses & Keamanan:** Mencoba meretas URL Dasbor Admin menggunakan akun Mahasiswa biasa (QA Testing).
- [ ] `[Project Manager]` **Uji Konkurensi Ekstrem:** Melakukan *simulasi klik* form pendaftaran secara bersamaan dari 2 *browser* berbeda pada detik yang sama untuk menguji keberhasilan pertahanan `lockForUpdate()`.
- [ ] `[Frontend]` **Uji Responsivitas UI:** Memastikan kalender dan form laporan kerusakan tidak hancur bentuknya ketika dibuka melalui layar *smartphone*.
- [ ] `[Seluruh Tim]` **Perapian Kode & Komit Akhir:** Memastikan tidak ada *comment* kode sampah (*spaghetti code*) yang tertinggal dan melakukan dokumentasi *GitHub* sesuai standar *Branching*.
