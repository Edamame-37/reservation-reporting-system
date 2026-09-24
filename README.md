<div align="center">
  <br>
  <h1>CAVA - Sistem Cerdas Reservasi</h1>
  <p>
    <strong>Platform terpadu untuk mengelola peminjaman ruang, fasilitas kampus, dan pelaporan kerusakan sarana prasarana secara cepat dan transparan.</strong>
  </p>
</div>

---

## Tentang Proyek
**CAVA (Campus Venue Access)** adalah sistem web yang dirancang untuk mengatasi masalah tumpang tindih (*double-booking*) pada peminjaman fasilitas kampus serta melacak laporan kerusakan aset secara terpusat. 

Proyek ini memisahkan otorisasi pengguna ke dalam empat lapis akses (Visitor, Pengguna, Petugas, Admin) dan dilengkapi perlindungan ketat di sisi *database* terhadap akses konkuren secara serentak.

## Fitur Utama (Key Features)

### Untuk Pengunjung & Pengguna Sivitas
*   **Katalog Interaktif:** Menampilkan direktori seluruh fasilitas beserta status, gambar, dan detail spesifikasi.
*   **Visualisasi Kalender:** Melihat kalender slot waktu ketersediaan secara langsung (*Real-time*).
*   **Reservasi Ruangan:** Form *booking* yang dibatasi hanya pada jam operasional (07:00 - 20:00) dengan rentang per-30 menit.
*   **Pelaporan Kerusakan (Ticketing):** Mengajukan keluhan (dengan bukti foto) jika fasilitas rusak, kotor, atau bermasalah.

### Untuk Petugas & Biro Sarpras
*   **Validasi Anti-Bentrok Mutlak:** Sistem *auto-reject* persetujuan peminjaman di waktu yang sama (*Race Condition Protection*).
*   **Manajemen Antrean Cerdas:** Dasbor satu atap untuk menyetujui, menolak, atau melakukan *Override* (pembatalan darurat) pada pesanan.
*   **Tindak Lanjut Laporan:** Mencatat perbaikan teknis dan otomatis memblokir jadwal fasilitas ("Dalam Perbaikan") ke Kalender Utama.

### Untuk Administrator
*   **Master Data Management (CRUD):** Tambah, ubah, dan kelola jenis ruangan/aset.
*   **Verifikasi Akun:** Menerima atau menolak pendaftaran akun pengguna baru.
*   **Dashboard Analytics:** Statistik penggunaan dan ekspor (*Export*) laporan otomatis ke dalam format **PDF/Excel**.

---

## Teknologi yang Digunakan (Tech Stack)

| Kategori | Teknologi Utama | Alasan / Deskripsi |
| :--- | :--- | :--- |
| **Backend** | Laravel 11+ (PHP 8.2+) | Kerangka utama (MVC) yang memiliki tingkat keamanan solid dan dokumentasi kaya. |
| **Database** | MySQL / MariaDB | Tangguh untuk skenario transaksional dan fitur *Row-Level Locking* (`lockForUpdate`). |
| **CSS Framework**| Tailwind CSS | Kerangka *Utility-first CSS* modern untuk UI/UX bersih dan estetik (*Minimalist Campus*). |
| **Interaktivitas** | Alpine.js | Eksekusi fungsional ringan (*Dropdown, Modal*) tanpa membebani kinerja Laravel Blade. |
| **Plugin UI** | FullCalendar.js | Visualisasi antarmuka manajemen jadwal dan *booking* waktu di *Front-End*. |

---

## Panduan Instalasi (Getting Started)

Proyek ini membutuhkan **Laragon** (atau XAMPP), **Node.js/NPM**, dan **Composer**. 

1. **Kloning Proyek ke Direktori Server**
   Buka terminal di `C:\laragon\www\` (atau direktori `htdocs`), lalu jalankan:
   ```bash
   git clone https://github.com/Edamame-37/reservation-reporting-system.git
   cd reservation-reporting-system
   ```

2. **Instalasi Pustaka (Dependencies)**
   ```bash
   composer install
   npm install
   ```

3. **Konfigurasi Lingkungan (.env)**
   Salin *file* environment dan bangkitkan *App Key*.
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   *Buka file `.env` dan pastikan pengaturan database sesuai (misal: `DB_DATABASE=db_reservasi_kampus`).*

4. **Migrasi dan Penyemaian Data (Seeding)**
   Jalankan migrasi untuk merakit tabel sekaligus memasukkan data *dummy* pengguna dan fasilitas (Bila sudah disiapkan).
   ```bash
   php artisan migrate:fresh --seed
   ```

5. **Kompilasi CSS/JS dan Jalankan Server Lokal**
   Gunakan dua tab Terminal/CMD terpisah untuk mengeksekusi kedua perintah ini:
   
   **Terminal 1 (Aplikasi PHP):**
   ```bash
   php artisan serve
   ```
   **Terminal 2 (Kompilasi Tailwind):**
   ```bash
   npm run dev
   ```

Aplikasi kini dapat diakses di browser melalui alamat: `http://localhost:8000` (atau `http://reservation-reporting-system.test` jika menggunakan *Auto-Virtual Hosts* Laragon).

---

## Dokumentasi Ekstensif Lengkap

Bagi para *developer*, analis, dan pemeriksa sistem, rujukan dokumen lebih mendalam dapat ditelusuri di file dan direktori berikut:
- 📑 [Detail Rencana Proyek (Roadmap)](PLAN_PROJECT.md)
- 📑 [Analisis Studi Kasus Dasar](CASE_PROJECT.md)
- 📑 [Standard Operasional Pengerjaan (SOP)](PLAN_DEVELOPMENT.md)
- 📁 **`/doc-feature/`**: Spesifikasi lengkap ke-19 modul aplikasi per-*user story*.
- 📁 **`/doc-process-system/`**: Kumpulan diagram (Behavioral, Interaction, Structural) UML. 

---
<p align="center"><i>&copy; 2026 Tim Pengembang CAVA. Didedikasikan untuk Manajemen Fasilitas Kampus.</i></p>
