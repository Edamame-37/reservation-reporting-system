# USR-01: Form Pengajuan Reservasi Ruangan

## 1. Meta Informasi
- **Aktor:** Pengguna (Sivitas Akademika yang sudah login)
- **Ekuivalen User Story:** US-3
- **Modul:** Manajemen Reservasi (Frontend/User)

## 2. Deskripsi Alur Bisnis
Pengguna yang telah login dapat memilih fasilitas dari katalog dan mengisi formulir pengajuan reservasi. Mereka harus menentukan tanggal, jam mulai (*start time*), jam selesai (*end time*), serta menuliskan tujuan kegiatan. Setelah dikirim, status reservasi akan otomatis menjadi `menunggu persetujuan` (*pending*) dan masuk ke dalam antrean Petugas.

## 3. Kebutuhan Frontend (UI/UX & Validasi)
- **File Rujukan:** `resources/views/user/reservation-form.blade.php`
- **Form Input:**
  - `facility_id` (Dropdown/Hidden bergantung cara masuknya).
  - `date` (Date picker, minimal tanggal hari ini).
  - `start_time` & `end_time` (Time picker, step 30 menit).
  - `purpose` (Textarea tujuan penggunaan).
- **Validasi Klien:** Waktu mulai tidak boleh lebih dari waktu selesai. Waktu tidak boleh di luar jam 07:00 - 20:00.

## 4. Kebutuhan Backend (Controller & DB)
- **Controller:** `ReservationController.php`
- **Method:** `store(Request $request)`
- **Validasi Server Khusus (Sesuai PDF Aturan Bisnis):**
  - **Aturan Jam Operasional:** `start_time` >= 07:00 dan `end_time` <= 20:00.
  - **Aturan Durasi:** Menit pada `start_time` dan `end_time` HANYA boleh bernilai `00` atau `30`. (Kelipatan 30 menit).
  - **Aturan Konkurensi / Bentrok (Penting!):** Sistem menggunakan kueri basis data untuk memeriksa tabel `reservations`. Jika fasilitas yang sama pada rentang waktu yang saling bersinggungan (*overlap*) sudah memiliki reservasi dengan status `approved`, maka sistem harus menolak *request* ini!
- **Data Tersimpan:** Menyimpan ID pengguna (`user_id` dari sesi `Auth::id()`) dan menetapkan kolom `status` ke `pending`.

## 5. Aturan Penolakan / Edge Cases
- Jika jam yang diminta bertabrakan dengan pesanan orang lain yang sudah disetujui, tolak (*HTTP 422*).
- Jika fasilitas yang hendak dipesan kebetulan berstatus 'Dalam Perbaikan' (Rusak), tolak pendaftaran.
