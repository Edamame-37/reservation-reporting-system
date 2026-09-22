# USR-03: Pembatalan Reservasi Mandiri (Validasi Batas H-1)

## 1. Meta Informasi
- **Aktor:** Pengguna (Mahasiswa/Dosen/Staf)
- **Ekuivalen User Story:** US-4
- **Modul:** Manajemen Reservasi (Frontend/User)

## 2. Deskripsi Alur Bisnis
Jika agenda acara pengguna batal atau berubah, mereka memiliki wewenang untuk membatalkan tiket reservasi mereka sendiri agar fasilitas tersebut "kembali kosong" dan dapat di-*booking* oleh orang lain. Pembatalan mandiri ini **memiliki batasan waktu**, yakni maksimal H-1 sebelum tanggal pelaksanaan.

## 3. Kebutuhan Frontend (UI/UX & Validasi)
- **File Rujukan:** Di dalam daftar `resources/views/user/reservation-history.blade.php`
- **Interaksi:**
  - Sebuah tombol berwarna merah (`danger-button.blade.php`) dengan label "Batalkan".
  - Tombol ini **hanya** dirender (*ditampilkan*) jika `status` reservasi adalah *Pending* atau *Approved* **DAN** waktu sekarang (WIB) masih sebelum (H-1) dari `start_time` reservasi.
  - Membutuhkan dialog konfirmasi (*SweetAlert* atau *Modal*) untuk mencegah salah klik.

## 4. Kebutuhan Backend (Controller & DB)
- **Controller:** `ReservationController.php`
- **Method:** `cancel(Request $request, $id)`
- **Validasi Server Khusus (Batas Waktu H-1):**
  - Cocokkan kepemilikan (`user_id == Auth::id()`).
  - Lakukan pemeriksaan tanggal (gunakan *Carbon*): Jika waktu *request* API lebih lambat daripada `reservasi->start_time` dikurangi 24 jam (H-1), maka tolak proses *cancel* (*HTTP 403 Forbidden* / *HTTP 422*).
- **Proses DB:** Mengubah kolom `status` pada tabel `reservations` menjadi `cancelled_by_user`.

## 5. Aturan Penolakan / Edge Cases
- Pengguna yang mencoba mengirim API POST/DELETE pemalsuan (*curl* langsung) untuk membatalkan pesanan di hari-H akan gagal total karena ada filter validasi server batas H-1.
- Jika pengguna membatalkan pesanan yang berstatus *Approved*, maka di kalender utama (PUB-01), jadwal tersebut otomatis tercabut dan menjadi kosong kembali.
