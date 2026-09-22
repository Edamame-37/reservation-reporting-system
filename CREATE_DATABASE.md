# Panduan Pemasangan Basis Data (Database Setup) Lokal

Dokumen ini berisi standar prosedur pemasangan dan konfigurasi basis data MySQL untuk Sistem Reservasi & Pelaporan Kerusakan Fasilitas Kampus di lingkungan lokal (pengembangan). Setiap rekan pengembang (*developer*) diwajibkan mengikuti panduan ini saat pertama kali melakukan *clone* repositori agar integritas dan skema *database* tetap konsisten.

## Prasyarat (*Prerequisites*)
Pastikan hal-hal berikut sudah terpasang dan berjalan di perangkat Anda:
1. **PHP (>= 8.2)** & **Composer**
2. **MySQL Server** (bisa menggunakan XAMPP, Laragon, DBngin, Herd, atau *service* langsung).

---

## Langkah-langkah Konfigurasi

### Langkah 1: Setup Variabel Lingkungan (`.env`)
Laravel membutuhkan file konfigurasi `.env` untuk mengatur koneksi ke *database*.
1. Salin file `.env.example` dan ubah namanya menjadi `.env`:
   ```bash
   cp .env.example .env
   ```
2. Buka file `.env` dan atur bagian koneksi *database*. Sesuaikan kredensial `DB_USERNAME` dan `DB_PASSWORD` dengan MySQL Server lokal Anda (jika menggunakan XAMPP/Laragon, biasanya *username* adalah `root` dan *password* dibiarkan kosong).
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=db
   DB_USERNAME=root
   DB_PASSWORD=rahasia123  # Hapus atau ubah sesuai password MySQL Anda
   ```

### Langkah 2: Pembuatan Basis Data Kosong
Sebelum menjalankan migrasi, Anda **wajib** membuat *database* kosong dengan nama yang sama dengan yang tertulis di variabel `DB_DATABASE` pada file `.env` (dalam contoh ini: `db`).
- **Pilihan A (via GUI):** Buka phpMyAdmin / DBeaver / TablePlus, lalu buat database baru bernama `db`.
- **Pilihan B (via MySQL CLI):**
  ```sql
  CREATE DATABASE db;
  ```

### Langkah 3: Eksekusi Migrasi & Data Awal (*Seeder*)
Setelah koneksi `.env` dikonfigurasi dan *database* `db` terbentuk, jalankan perintah migrasi Laravel untuk merakit struktur tabel sekaligus memasukkan data *dummy* dasar (Fasilitas, Role, dan User).

Buka terminal di direktori proyek, lalu jalankan:
```bash
php artisan migrate:fresh --seed
```
*Catatan: Perintah ini akan menjamin skema tabel dan relasi `foreign key` terbentuk dengan 100% konsisten layaknya rancangan developer lainnya.*

### Langkah 4: Validasi Koneksi
Untuk memastikan bahwa konfigurasi berjalan baik dan tabel berhasil terbentuk, jalankan:
```bash
php artisan tinker
```
Lalu di dalam *Psy Shell*, jalankan perintah:
```php
DB::connection()->getPdo();
```
Jika tidak muncul pesan *error* "Access denied" atau "Unknown database", maka setup telah berhasil dan aplikasi sudah siap digunakan untuk koding.

---

> **Troubleshooting:**
> Jika muncul error `SQLSTATE[HY000] [1045] Access denied for user`, periksa kembali bagian `DB_USERNAME` dan `DB_PASSWORD` di `.env`, pastikan sesuai dengan konfigurasi *root* atau *user* MySQL di perangkat Anda.
