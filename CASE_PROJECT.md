# Analisis Platform Sistem Reservasi & Pelaporan Fasilitas Kampus

## 1. Jenis-Jenis User (Actors)
Platform ini melibatkan beberapa peran pengguna dengan hak akses yang berbeda-beda:

| Role | Hak Akses & Deskripsi |
|---|---|
| **Pengunjung (Visitor)** | Pengguna yang tidak melakukan login. Hanya dapat melihat daftar fasilitas, mencari fasilitas, dan mengecek ketersediaan jadwal (tanpa melihat detail pemohon/tujuan). |
| **Pengguna (Mahasiswa/Dosen/Staf)** | Pengguna terautentikasi (login) yang dapat mengajukan reservasi, melaporkan kerusakan fasilitas, membatalkan reservasinya sendiri, serta melihat riwayat dan status reservasi/laporan mereka. |
| **Petugas** | Aktor operasional yang memproses antrian (menyetujui, menolak, membatalkan reservasi) dan menindaklanjuti laporan kerusakan. Petugas berhak mengubah status fasilitas (misal: "dalam perbaikan") dan memperbaruinya setelah masalah selesai. |
| **Admin** | Pengelola tingkat atas yang mengatur *master data* fasilitas, mendaftarkan/memverifikasi akun petugas dan pengguna, serta melihat dan mengekspor rekapitulasi data (okupansi dan laporan kerusakan). |

## 2. User Story
Berdasarkan dokumen, berikut adalah *user stories* yang menjadi acuan pengembangan:

| No | Aktor | Deskripsi Cerita / Kebutuhan |
|---|---|---|
| 1 | Pengunjung / Pengguna | Saya bisa melihat daftar fasilitas beserta status ketersediaannya per slot waktu (tersedia/tidak tersedia), tanpa melihat detail pemohon atau tujuan penggunaan. |
| 2 | Pengunjung / Pengguna | Saya bisa mencari fasilitas berdasarkan tipe/lokasi/kapasitas. |
| 3 | Pengguna | Saya bisa mengajukan reservasi pada rentang waktu tertentu dengan menyebutkan tujuan penggunaan. |
| 4 | Pengguna | Saya bisa membatalkan reservasi saya sendiri sebelum batas waktu tertentu. |
| 5 | Pengguna | Saya bisa melihat riwayat dan status reservasi saya, termasuk detail lengkap reservasi tersebut. |
| 6 | Pengguna | Saya bisa melaporkan kerusakan/masalah pada fasilitas tertentu (kategori, deskripsi, foto). |
| 7 | Pengguna | Saya bisa melihat status laporan saya. |
| 8 | Petugas | Saya bisa melihat dashboard/antrian reservasi dan laporan yang masih menunggu diproses, agar tidak ada yang terlewat. |
| 9 | Petugas | Saya bisa menyetujui/menolak reservasi yang masuk secara manual; sistem mencegah persetujuan reservasi yang bentrok jadwal pada fasilitas yang sama. |
| 10 | Petugas | Saya bisa membatalkan reservasi yang sudah disetujui dalam kondisi mendesak (mis. fasilitas mendadak tidak bisa dipakai), dengan mencantumkan alasan pembatalan. |
| 11 | Petugas | Saya bisa mengubah status laporan (baru/diproses/selesai/ditolak) beserta catatan resolusi saat laporan ditutup. |
| 12 | Petugas | Saya bisa menandai fasilitas berstatus 'dalam perbaikan' terkait laporan kerusakan yang sedang ditangani, dan mengembalikannya ke status aktif setelah selesai diperbaiki. |
| 13 | Admin | Saya bisa mendaftarkan akun petugas secara langsung (petugas tidak melakukan registrasi mandiri dalam kondisi apa pun). |
| 14 | Admin | Saya bisa mendaftarkan akun pengguna (mahasiswa/dosen/staf) secara langsung tanpa melalui form registrasi mandiri. |
| 15 | Admin | Saya bisa memverifikasi atau menolak akun pengguna hasil registrasi mandiri (jika diimplementasikan) sebelum akun tersebut dapat digunakan untuk login. |
| 16 | Admin | Saya bisa mengelola data fasilitas (tambah/edit/nonaktifkan). |
| 17 | Admin | Saya bisa melihat dan mengekspor (CSV/Excel/PDF) rekap okupansi fasilitas dan frekuensi kerusakan per fasilitas/lokasi. |

## 3. Asumsi Bisnis & Aturan Tambahan
Berdasarkan analisis kebutuhan dan batasan sistem, berikut adalah asumsi dan aturan bisnis yang wajib diterapkan secara ketat:

| Kategori Asumsi / Aturan | Deskripsi Spesifikasi |
|---|---|
| **Batasan Waktu Pembatalan** | Pengguna hanya diizinkan membatalkan reservasi yang telah diajukan paling lambat **H-1** sebelum jadwal penggunaan fasilitas. |
| **Notifikasi Sistem** | Sistem menyediakan mekanisme notifikasi (minimal berupa pemberitahuan di dalam aplikasi atau email) ketika status reservasi atau laporan berubah. |
| **Tipe Kerusakan** | Fasilitas yang ada diasumsikan dapat mengalami kerusakan tidak terduga, baik karena faktor usia, kesalahan manusia, maupun faktor eksternal (misalnya kabel jaringan digigit tikus atau **kucing** yang masuk ke ruang laboratorium). |
| **Standarisasi Media** | Fitur laporan kerusakan dengan unggah foto memiliki pembatasan format (misal JPG/PNG) dan batas maksimal ukuran file (misal 2 MB) untuk menghemat penyimpanan server. |
| **Proses Registrasi** | Jika fitur registrasi mandiri digunakan oleh Pengguna, status akun mereka diatur sebagai "Pending" secara *default* hingga Admin melakukan verifikasi. |
| **Kapasitas Konkurensi Sistem** | Sistem harus dirancang tangkas dan responsif ibarat kucing, sanggup menangani penggunaan maksimal oleh **100 *user* secara bersamaan** (*concurrent users*) untuk melakukan tugas pemesanan di detik yang sama, tanpa menyebabkan *server* tumbang atau tembusnya celah *double-booking*. |
| **Ketentuan Jam Operasional & Slot** | Pemesanan fasilitas mutlak dibatasi pada jam operasional kampus (**07.00 – 20.00**). Rentang waktu wajib menggunakan slot durasi tetap kelipatan **30 menit** (misal 07.30–08.00). Validasi slot waktu ini **wajib diproteksi ketat di sisi *server*** (bukan sekadar di tampilan visual kalender). |
| **Kewenangan Eksekusi Petugas** | Petugas memiliki hak absolut untuk membatalkan reservasi yang sudah disetujui dalam kondisi mendesak/darurat (misalnya fasilitas mendadak bocor atau kotor dimasuki hewan liar liar seperti kucing), dengan syarat wajib mencantumkan alasan pembatalan. |
| **Sinkronisasi Pelaporan & Reservasi** | Saat Petugas merespons laporan kerusakan dan menandai fasilitas "Dalam Perbaikan", fasilitas tersebut otomatis berstatus tidak tersedia di kalender utama. Hal ini secara otomatis mencegah pengguna lain tanpa sengaja memesan fasilitas yang sedang rusak, sehingga mereka tidak merasa seperti membeli kucing dalam karung. |

## 4. Fitur-Fitur (Mengacu pada User Story)

| Modul Utama | Sub-Fitur & Deskripsi |
|---|---|
| **Autentikasi & Akun** | Registrasi mandiri pengguna, Login/Logout, Verifikasi Akun oleh Admin, Pembuatan akun *by Admin*. |
| **Katalog & Pencarian** | Halaman daftar fasilitas, fitur *Search & Filter* (berdasarkan tipe, lokasi, kapasitas), dan penampil kalender ketersediaan per slot 30 menit. |
| **Manajemen Reservasi** | **Frontend:** Form pengajuan reservasi, pembatalan reservasi, riwayat & detail.<br>**Backend:** Dashboard *approval*, pembatalan paksa dengan alasan, validasi anti-bentrok. |
| **Sistem Pelaporan (*Ticketing*)** | **Frontend:** Form pelaporan kerusakan (kategori, deskripsi, upload foto), pelacakan status.<br>**Backend:** Manajemen *progress* laporan, pencatatan *log* resolusi, pengubahan status fasilitas (aktif/dalam perbaikan). |
| **Master Data Management** | Fitur CRUD (Create, Read, Update, Non-aktifkan) untuk fasilitas yang hanya diakses Admin. |
| **Reporting & Analytics** | Dashboard rekap okupansi fasilitas dan statistik kerusakan, serta fitur *Export* data ke CSV/Excel/PDF. |

## 5. User Requirement
Kebutuhan dari sisi pengguna sistem agar fitur dapat berjalan optimal:

| Aktor / Perspektif | Requirement (Kebutuhan) |
|---|---|
| **Pengunjung** | Memerlukan *interface* penanggalan (kalender) yang interaktif untuk mengecek slot waktu secara instan tanpa harus mendaftar. |
| **Pengguna** | Membutuhkan antarmuka pengisian waktu (start dan end) yang secara otomatis terkunci pada jam operasional (07.00 - 20.00) dan interval 30 menit. |
| **Pengguna** | Memerlukan form pelaporan yang responsif, sehingga saat mereka menemukan masalah di lapangan (contohnya kursi patah atau ada kotoran **kucing** di lapangan), mereka dapat langsung memfoto dan melaporkannya lewat ponsel. |
| **Petugas** | Membutuhkan pandangan terpusat (dashboard) yang menampilkan indikator atau notifikasi *real-time* jika ada antrian reservasi atau laporan baru, sehingga SLA terjaga. |
| **Admin** | Memerlukan halaman *user management* yang terstruktur untuk memudahkan pencarian pengguna *pending* dan pengunduhan laporan secara periodik. |

## 6. System Requirement (Functional & Non-Functional)

| Kategori | Requirement (Persyaratan Sistem) |
|---|---|
| **Functional** | Sistem **harus** memisahkan akses otorisasi berdasarkan 4 *role*: Pengunjung, Pengguna, Petugas, dan Admin. |
| **Functional** | Sistem **harus** memiliki fungsi validasi jam operasional (07.00–20.00) dan kelipatan 30 menit di level *Server* dan *Client*. |
| **Functional** | Sistem **harus** memblokir secara otomatis permintaan persetujuan reservasi oleh petugas jika slot waktu untuk fasilitas tersebut sudah disetujui untuk pihak lain. |
| **Functional** | Sistem **harus** mengunci fasilitas agar tidak dapat dipesan ketika Petugas mengubah status fasilitas menjadi 'Dalam Perbaikan'. |
| **Functional** | Sistem **harus** menyediakan dukungan format *export* file (CSV/Excel/PDF) menggunakan *library* terkait di sisi *backend* atau *frontend*. |
| **Functional** | Sistem **harus** menerapkan pengelolaan file gambar (unggah, simpan di server, dan tampilkan) pada modul laporan kerusakan. |
| **Non-Functional** | **Architecture:** Kode sistem harus dipisahkan menjadi minimal 3 bagian utama (koneksi DB, MVC, dan HTML) dan terorganisir (contoh: `/public`, `/app`, `/views`, `/config`). |
| **Non-Functional** | **Usability (UI/UX):** Antarmuka harus sangat mudah dipahami dengan visualisasi kalender yang jelas, sehingga siapa pun, bahkan petugas yang sedang asyik memberi makan **kucing** di kampus, dapat mengoperasikannya tanpa kebingungan di perangkat *mobile* maupun *desktop*. |
| **Non-Functional** | **Reliability & Data Integrity:** Sistem harus menjamin bahwa status persetujuan yang dieksekusi secara bersamaan (*race condition*) dapat ditangani dengan baik agar tidak terjadi bentrok. |
| **Non-Functional** | **Security:** Perlindungan ganda pada form penting dengan validasi di *client-side* maupun *server-side*. Password di *database* harus di-hash (*bcrypt*). |
| **Non-Functional** | **Collaboration:** Pengembangan sistem wajib menggunakan *version control* (GitHub/GitLab) dengan standar komit yang deskriptif oleh setiap anggota. |


