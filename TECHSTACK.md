# Dokumentasi Pemilihan Tech-Stack & Komparasi Ekosistem

Dokumen ini memuat daftar teknologi yang digunakan pada platform Sistem Reservasi & Pelaporan Kerusakan Fasilitas, beserta alasan pemilihan dan perbandingannya terhadap setidaknya 5 (lima) alternatif kompetitor serupa di industri perangkat lunak.

---

## 1. Framework Backend & Core Logic
**Teknologi Terpilih: Laravel (PHP)** - *(Kategori: Web Framework / Kerangka Kerja)*
*   **Alasan Pemilihan:** Laravel memiliki ekosistem bawaan yang sangat kaya (*Authentication, Middleware, Eloquent ORM, FormRequests*). Struktur foldernya (MVC) sangat rapi dan langsung memenuhi spesifikasi dokumen proyek. Sangat andal dalam menangani keamanan dan manajemen rute (*routing*).
*   **Cara Instalasi:** Buka terminal/CMD, pastikan *Composer* telah terinstal di komputer, lalu jalankan perintah: `composer create-project laravel/laravel reservasi-app`
*   **Cara Penggunaan (Basic):** 
    - Menyalakan server aplikasi lokal: `php artisan serve`
    - Membuat *Controller* baru secara otomatis: `php artisan make:controller NamaController`
    - Mengakses *website* aplikasi Anda: Buka *browser* ke alamat `http://127.0.0.1:8000`

### Tabel Perbandingan Backend Framework
| Alternatif Pembanding | Mengapa Tidak Dipilih? |
|---|---|
| **1. CodeIgniter 4 (PHP)** | Terlalu minimalis. Harus membangun fitur proteksi otentikasi (4 *Role*) dan sistem penguncian *Database* (*lockForUpdate*) dari nol secara manual. |
| **2. Express.js (Node.js)** | Tidak memiliki struktur *folder* baku (MVC). Memerlukan konfigurasi *routing*, keamanan, dan ORM pihak ketiga secara terpisah yang memakan waktu lama. |
| **3. Django (Python)** | Sangat tangguh, namun kurva pembelajarannya (*learning curve*) lebih curam untuk tim yang mayoritas memiliki dasar pemrograman web PHP. |
| **4. Spring Boot (Java)** | Terlalu berat (*Overkill*) untuk lingkup proyek perkuliahan dan membutuhkan spesifikasi *server* (*RAM/CPU*) yang tinggi untuk proses *hosting*. |
| **5. Ruby on Rails (Ruby)** | Kecepatan produksinya sangat baik, namun popularitas dan ketersediaan dokumentasi, *tutorial, serta troubleshooting* berbahasa Indonesia jauh lebih sedikit dibanding Laravel. |

---

## 2. Relational Database Management System (RDBMS)
**Teknologi Terpilih: MySQL / MariaDB** - *(Kategori: Relational Database / Basis Data)*
*   **Alasan Pemilihan:** Memberikan rasio optimal antara keandalan transaksional (mampu menahan lonjakan klik 100 *request* serentak tanpa *double-booking* menggunakan *Row-Level Locking*) dengan kemudahan instalasi. Dijamin 100% kompatibel dengan penyedia *hosting* web termurah mana pun.
*   **Cara Instalasi:** Terinstal secara serentak (otomatis) saat Anda memasang aplikasi server Laragon atau XAMPP di Windows Anda.
*   **Cara Penggunaan (Basic):** 
    - Buka Laragon/XAMPP, lalu klik tombol **Start** pada modul MySQL.
    - Buka pengelola *database* HeidiSQL (bawaan Laragon) atau akses phpMyAdmin di `http://localhost/phpmyadmin`.
    - Klik *Create Database*, beri nama (misal: `db_reservasi_kampus`), lalu masukkan nama tersebut ke dalam *file* `.env` proyek Laravel Anda.

### Tabel Perbandingan Database
| Alternatif Pembanding | Mengapa Tidak Dipilih? |
|---|---|
| **1. PostgreSQL** | Sedikit *Over-engineering* (berlebihan). Instalasi awalnya lebih kompleks (butuh konfigurasi *pgAdmin*) dibandingkan MySQL yang langsung *plug-and-play* dari bawaan Laragon/XAMPP. |
| **2. SQLite** | Berisiko sangat fatal. Berpotensi tinggi mengalami error *"Database Locked"* sistem akan mati/down jika aplikasi ditekan oleh beberapa orang secara bersamaan (*Concurrency* sangat rendah). |
| **3. SQL Server (Microsoft)** | Membutuhkan *server* lingkungan Windows khusus dan akan sangat mahal karena menuntut sistem lisensi berbayar untuk mengaktifkan fitur penuhnya. |
| **4. Oracle Database** | Merupakan *database* level *Enterprise* raksasa tingkat korporat. Ekstrem *overkill* untuk skala proyek tugas kampus dan butuh *Database Administrator* khusus untuk merawatnya. |
| **5. MongoDB (NoSQL)** | Sama sekali tidak cocok. Sistem reservasi menuntut Relasi Tabel yang kaku (Mahasiswa -> Reservasi -> Fasilitas). MongoDB (yang berbasis dokumen) tidak memiliki dukungan integrasi tabel SQL yang solid. |

---

## 3. Framework CSS (Desain Antarmuka)
**Teknologi Terpilih: Tailwind CSS** - *(Kategori: CSS Framework / Kerangka Desain)*
*   **Alasan Pemilihan:** Pendekatan *Utility-First CSS* memungkinkan kustomisasi desain antarmuka tanpa batas langsung di dalam kerangka HTML (Blade). Menghasilkan tampilan web yang dinamis, modern, unik (tidak kaku/pasaran), dan ukuran file kompresi akhirnya (*Production*) yang sangat kecil.
*   **Cara Instalasi:** Melalui terminal (NPM) di dalam folder Laravel Anda:
    ```bash
    npm install -D tailwindcss postcss autoprefixer
    npx tailwindcss init -p
    ```
*   **Cara Penggunaan (Basic):** 
    - Tambahkan *class* bawaan Tailwind langsung ke elemen HTML Blade Anda. Contoh membuat tombol merah: `<button class="bg-red-500 text-white px-4 py-2 rounded">Klik</button>`
    - Anda **wajib** menyalakan `npm run dev` di terminal agar setiap perubahan warna atau letak CSS seketika otomatis diterjemahkan (*compile*) oleh sistem.

### Tabel Perbandingan Framework CSS
| Alternatif Pembanding | Mengapa Tidak Dipilih? |
|---|---|
| **1. Bootstrap 5** | Desain bawaannya terlalu kaku dan mudah ditebak ("khas template"). Sangat pasaran sehingga aplikasi kita akan terlihat kurang eksklusif di mata dosen/penguji. |
| **2. Foundation** | Sangat canggih, namun kurva belajarnya lebih rumit dan popularitasnya (dukungan komunitasnya) semakin menurun tiap tahun dibanding Tailwind atau Bootstrap. |
| **3. Bulma** | Cukup bersih dan murni berbasis *Flexbox*, namun kustomisasi lanjutannya tidak segesit dan sedinamis fitur *JIT (Just-in-Time)* compiler milik Tailwind CSS. |
| **4. Materialize CSS** | Terlalu terpaku pada aturan tata letak *Material Design* buatan Google yang saat ini terasa kuno untuk gaya *web modern* masa kini (kalah estetik dibanding Glassmorphism dll). |
| **5. Chakra UI** | Spesifik diciptakan murni untuk ekosistem aplikasi berbasis *React.js*. Sangat repot dan merusak kerangka arsitektur jika dipaksakan masuk ke ekosistem Laravel Blade (HTML). |

---

## 4. Pustaka Interaktivitas (JavaScript)
**Teknologi Terpilih: Alpine.js** - *(Kategori: JavaScript Library / Pustaka)*
*   **Alasan Pemilihan:** Pustaka *Javascript* yang ukurannya sangat ringan. Dirancang khusus untuk membaur secara harmonis dengan kerangka *HTML (Blade)* dan *Tailwind CSS*. Sangat efektif dan *powerful* untuk mengontrol animasi *Dropdown, Modal, Toggle*, dan validasi interaktif instan.
*   **Cara Instalasi:** Melalui terminal NPM: `npm install alpinejs` (lalu inisialisasi di `resources/js/app.js`). *Atau* cara tercepat, cukup sisipkan link CDN ini di dalam baris tag `<head>` HTML Anda: `<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>`
*   **Cara Penggunaan (Basic):** 
    - Panggil atribut bawaannya (`x-data`, `x-show`, `x-on`) langsung di kerangka HTML Anda. Contoh menyembunyikan/menampilkan teks:
      ```html
      <div x-data="{ buka: false }">
          <button @click="buka = !buka">Tampilkan Info</button>
          <div x-show="buka">Teks ini akan muncul dan hilang saat tombol ditekan!</div>
      </div>
      ```

### Tabel Perbandingan Interaktivitas JS
| Alternatif Pembanding | Mengapa Tidak Dipilih? |
|---|---|
| **1. jQuery** | Sudah dianggap teknologi kuno (*Legacy*). Memanipulasi *DOM Element* secara berat dan sangat berpotensi menghasilkan kode berantakan (*Spaghetti Code*) yang sulit di-*maintain*. |
| **2. React.js** | Terlalu besar. Menggunakan React memaksa tim merombak arsitektur dengan memecah proyek menjadi API *Backend* dan antarmuka *Frontend* terpisah (sangat menyita waktu untuk memikirkan CORS & *State Management*). |
| **3. Vue.js** | Framework luar biasa andal, namun *setup*-nya tetap terasa terlalu berat jika kebutuhan UI aplikasinya hanya sekadar *dropdown* modal, dan alert ringan. |
| **4. Svelte** | Berbasis *compiler* radikal. Membutuhkan perombakan keseluruhan struktur proyek (serta membuang Blade murni), rasio pengerjaannya tidak sepadan dengan singkatnya batas waktu pengumpulan UTS. |
| **5. Angular** | Terlalu kompleks (*Opinionated TypeScript Framework*) dan memiliki kurva belajar sistem paling sulit dibanding yang lain. Jelas tidak cocok untuk digabungkan dengan sistem monolitik Laravel. |

---

## 5. Komponen Kalender Ketersediaan UI
**Teknologi Terpilih: FullCalendar.js** - *(Kategori: JavaScript Library / Pustaka Komponen UI)*
*   **Alasan Pemilihan:** Komponen ini adalah "Standar absolut" industri perangkat lunak untuk merender antarmuka kalender. Mampu menarik ribuan data kueri dari *Backend* MySQL dengan sangat lancar dan memiliki dukungan visualisasi seret-lepas (*drag-and-drop*) rentang waktu yang sempurna.
*   **Cara Instalasi:** Unduh pustaka utamanya via terminal: `npm install fullcalendar`
*   **Cara Penggunaan (Basic):** 
    - Buat sebuah kotak *div* kosong ber-ID di HTML (*Blade*): `<div id="kalender-reservasi"></div>`
    - Inisiasi dan sambungkan kalender ke *Controller* Anda via skrip JavaScript:
      ```javascript
      import { Calendar } from 'fullcalendar';
      let kalenderKu = new Calendar(document.getElementById('kalender-reservasi'), {
          events: '/api/get-jadwal-fasilitas' // Meminta data jadwal dari Laravel Backend
      });
      kalenderKu.render();
      ```

### Tabel Perbandingan Komponen Kalender
| Alternatif Pembanding | Mengapa Tidak Dipilih? |
|---|---|
| **1. Day.js / Moment.js** | Ini murni hanyalah pustaka logika manipulasi waktu (*Date parser*), bukan komponen pembuat UI Kalender kotak-kotak (*Grid Calendar*) yang bisa diklik. |
| **2. Toast UI Calendar** | Secara visual bagus, namun sistem koneksi (*AJAX Fetch Backend*)-nya dan pelaporan *error* dokumentasinya tidak sekuat komunitas mapan milik FullCalendar. |
| **3. React Big Calendar** | Terkunci dan spesifik diciptakan khusus untuk lingkungan bahasa basis komponen *React.js*. Tidak bisa dipakai di HTML biasa. |
| **4. V-Calendar** | Terkunci secara spesifik untuk lingkungan proyek *Vue.js*. Kita tidak mungkin membuang waktu menginstal Vue seutuhnya hanya demi memakai satu fitur kalender ini. |
| **5. Syncfusion Scheduler** | Bersifat komersial (Harus membeli lisensi/berbayar untuk menghapus fitur penuhnya). *Watermark* perusahaan asing di produk web kampus sangat dihindari. |

---

## 6. Lingkungan Server Lokal (*Local Development Environment*)
**Teknologi Terpilih: Laragon (OS Windows)** - *(Kategori: Development Tool / Lingkungan Server Lokal)*
*   **Alasan Pemilihan:** Arsitekturnya terisolasi, sangat ringan di RAM, dan secara magis otomatis men-*generate* nama domain virtual khusus (misalnya: web Anda bisa diakses via `http://reservasi.test` tanpa perlu mengetik panjang `localhost:8000`). Mencegah konflik teknis ketika dikerjakan secara tim berbarengan.
*   **Cara Instalasi:** Buka *website* resmi `laragon.org`, unduh *installer Laragon Full Version*, dan instal di dalam *drive* `C:\` laptop Anda (Ikuti langkah *Next* sampai usai).
*   **Cara Penggunaan (Basic):** 
    - Buka aplikasi Laragon, tekan tombol besar bertuliskan **"Start All"**.
    - Pindahkan atau instal *folder* proyek Laravel Anda (misal: `reservasi-app`) persis ke dalam direktori/folder `C:\laragon\www\`.
    - Berkat fitur otomatis *Auto-Virtual Hosts*, proyek Anda bisa langsung diakses di peramban (*browser*) dengan mengetik alamat domain buatan sendiri, yaitu: `http://reservasi-app.test`.

### Tabel Perbandingan Server Lokal
| Alternatif Pembanding | Mengapa Tidak Dipilih? |
|---|---|
| **1. XAMPP** | Sering memicu bencana "*port conflict*" (bentrok dengan aplikasi Skype/VMware di Port 80/3306). Pengaturan ubah versi *PHP engine* sangat rawan *error* bagi mahasiswa pemula. |
| **2. Laravel Sail (Docker)** | Opsi yang paling ideal di kacamata industri modern. Sayangnya, ini sangat rakus memakan RAM (boros *resource laptop*) dan waktu instalasi kontainer (*Docker image*) yang sangat berat untuk *laptop* spesifikasi minimal milik mayoritas mahasiswa. |
| **3. WAMP Server** | UI (*User Interface*) perawatannya dan navigasi cara kerjanya terasa lebih berat di sistem, serta tidak seluwes fungsional *auto-virtual host* milik Laragon. |
| **4. MAMP** | Arsitektur dasarnya lebih diperuntukkan dan dioptimasi bagi ekosistem Apple (*macOS*), sedangkan mayoritas anggota tim proyek perkuliahan adalah pengguna sistem operasi Windows. |
| **5. Laravel Valet** | Secara resmi hanya rilis eksklusif untuk komputer bersistem *macOS*. (Walaupun ada versi replika *Valet for Windows*, cara instalasinya rawan *error* jika salah konfigurasi Nginx manual). |
