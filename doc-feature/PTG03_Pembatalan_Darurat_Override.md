# PTG-03: Pembatalan Darurat (Override) oleh Petugas

## 1. Meta Informasi
- **Aktor:** Petugas Sarpras
- **Ekuivalen User Story:** US-10
- **Modul:** Manajemen Reservasi (Petugas)

## 2. Deskripsi Alur Bisnis
Ada kondisi tertentu (misalnya atap bocor atau perintah mendadak Rektor) di mana fasilitas yang **sudah disetujui** (*Approved*) terpaksa harus dibatalkan sepihak oleh sistem. Petugas memiliki wewenang (*override privilege*) untuk membatalkan status yang telah disetujui tersebut kapan saja. Pembatalan ini memaksa petugas untuk menuliskan alasan resmi pembatalan.

## 3. Kebutuhan Frontend (UI/UX & Validasi)
- **File Rujukan:** `resources/views/petugas/reservation-management.blade.php`
- **Interaksi:** Pada tab/filter daftar peminjaman yang "Disetujui" (*Approved*), sediakan tombol `Batalkan Paksa`.
- **Validasi Klien:** Memicu munculnya kotak dialog Modal. Input teks untuk "Alasan Pembatalan" diset wajib isi (`required`).

## 4. Kebutuhan Backend (Controller & DB)
- **Controller:** `ReservationManagementController.php`
- **Method:** `forceCancel(Request $request, $id)`
- **Validasi Server:**
  - `alasan_batal`: text, required, minimal 10 karakter (agar jelas).
- **Proses DB:** Mengubah kolom `status` reservasi yang tadinya `approved` menjadi `cancelled_by_admin` atau `rejected`, lalu menyimpan string alasan ke kolom `alasan_batal`.

## 5. Aturan Penolakan / Edge Cases
- Jika petugas tidak memasukkan alasan, server mengembalikan error validasi dan tidak mengeksekusi pembatalan.
- Setelah dibatalkan, slot waktu terkait di Kalender Publik (PUB-01) harus langsung kembali bersih (Tersedia) kecuali ruangannya juga ditandai rusak.
