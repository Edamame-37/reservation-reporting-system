# USR-02: Riwayat & Status Reservasi Pengguna

## 1. Meta Informasi
- **Aktor:** Pengguna (Mahasiswa/Dosen/Staf)
- **Ekuivalen User Story:** US-5
- **Modul:** Manajemen Reservasi (Frontend/User)

## 2. Deskripsi Alur Bisnis
Setelah mengajukan permintaan peminjaman ruangan, Pengguna membutuhkan wadah untuk melacak sejauh mana permohonannya diproses. Halaman ini adalah sebuah tabel atau daftar kartu riwayat peminjaman. Pengguna dapat melihat secara persis apakah laporannya berstatus *Pending*, *Approved*, *Rejected*, atau *Cancelled*. Jika statusnya "Ditolak/Dibatalkan", sistem harus menampilkan "Alasan Penolakan/Pembatalan" dari Petugas.

## 3. Kebutuhan Frontend (UI/UX & Validasi)
- **File Rujukan:** `resources/views/user/reservation-history.blade.php`
- **Komponen UI Utama:**
  - Tabel (*DataTables* atau Grid) riwayat reservasi.
  - Komponen lencana warna kustom (`cava/status-badge.blade.php`).
    - Kuning: Menunggu Persetujuan.
    - Hijau: Disetujui.
    - Merah: Ditolak/Dibatalkan.
  - *Tooltip* atau kolom tabel yang menampilkan "Tujuan" dan "Alasan Petugas".

## 4. Kebutuhan Backend (Controller & DB)
- **Controller:** `ReservationController.php` (Bisa juga `UserDashboardController.php`)
- **Method:** `history()` atau `index()` untuk pengguna.
- **Kueri Data:** `Reservation::with('facility')->where('user_id', Auth::id())->orderBy('created_at', 'desc')->get();`
- **Logika:** Mengambil seluruh reservasi milik pengguna aktif secara eksklusif. Pengguna sama sekali tidak boleh melihat riwayat pengguna lain.

## 5. Aturan Penolakan / Edge Cases
- Sistem tidak melayani aksi edit (Ubah) peminjaman. Jika Pengguna melakukan kesalahan ketik tujuan atau jam, mereka harus membatalkan pesanannya dan membuat pesanan yang baru.
