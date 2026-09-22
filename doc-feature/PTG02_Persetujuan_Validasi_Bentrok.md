# PTG-02: Sistem Persetujuan & Validasi Anti-Bentrok

## 1. Meta Informasi
- **Aktor:** Petugas Sarpras
- **Ekuivalen User Story:** US-9
- **Modul:** Manajemen Reservasi (Petugas)

## 2. Deskripsi Alur Bisnis
Di halaman manajemen reservasi, Petugas menyeleksi seluruh peminjaman berstatus `pending`. Petugas berhak menolak (dengan alasan) atau menyetujuinya. Namun, sistem bertindak sebagai asisten ganda: Jika ada 2 pengguna berbeda yang kebetulan meminta jadwal yang tumpang tindih (*overlap*) pada satu ruang yang sama, sistem **akan secara otomatis mencegah petugas** ketika ia mencoba menekan tombol "Approve" pada permintaan kedua (jika permintaan pertama sudah *Approved*).

## 3. Kebutuhan Frontend (UI/UX & Validasi)
- **File Rujukan:** `resources/views/petugas/reservation-management.blade.php`
- **Komponen UI Utama:**
  - Tombol "Setujui" (Hijau) dan "Tolak" (Merah).
  - Saat menekan "Tolak", muncul modal dialog (pop-up) `cava/modal.blade.php` yang memaksa Petugas mengisi kolom "Alasan Penolakan" sebelum data terkirim.

## 4. Kebutuhan Backend (Controller & DB)
- **Controller:** `ReservationManagementController.php`
- **Method:** `approve($id)` dan `reject(Request $request, $id)`
- **Validasi Server Khusus (Anti-Bentrok & Race Condition):**
  - **Kueri Bentrok (Overlap SQL):** Saat metode `approve` dipanggil, Server HARUS melakukan pengecekan ke DB. Apakah untuk `facility_id` yang sama, terdapat baris dengan status `approved` di mana waktu kegiatannya bersinggungan?
    - Rumus Singgungan: `(req_start < existing_end AND req_end > existing_start)`.
  - Jika kueri mengembalikan baris yang ditemukan, maka sistem *Controller* melempar penolakan (HTTP 422) ke layar Petugas: "Gagal menyetujui! Fasilitas telah dibooking oleh pihak lain di rentang waktu tersebut."
- **Proses DB:** Mengubah kolom `status` menjadi `approved` atau `rejected` dan menyimpan kolom `alasan_batal` (jika menolak).

## 5. Aturan Penolakan / Edge Cases
- **Persetujuan Berganda Secara Konkuren:** Untuk menghindari celah jika dua petugas menyetujui dua antrean bentrok dalam fraksi detik yang sama (berlomba), sistem idealnya menggunakan fitur `DB::transaction()` dengan teknik *pesimistic locking* (`lockForUpdate()`) saat membaca tabel reservasi sebelum melakukan status `update()`. (Sesuai dengan syarat "Sistem handal menangani konkurensi 100 user" di PDF).
