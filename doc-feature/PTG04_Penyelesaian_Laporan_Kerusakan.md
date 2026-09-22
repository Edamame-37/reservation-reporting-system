# PTG-04: Manajemen & Penyelesaian Laporan Kerusakan

## 1. Meta Informasi
- **Aktor:** Petugas Sarpras
- **Ekuivalen User Story:** US-11
- **Modul:** Sistem Pelaporan (Petugas)

## 2. Deskripsi Alur Bisnis
Semua tiket keluhan (kerusakan) dari pengguna masuk ke dasbor ini. Petugas mengecek foto dan deskripsi laporan. Mereka kemudian berhak memindahkan status tiket ke jenjang selanjutnya (contoh: dari `baru` menjadi `diproses`). Saat fasilitas selesai diperbaiki tukang, Petugas mengubah status tiket menjadi `selesai` dan diwajibkan mengetikkan *Catatan Resolusi* (misal: "Proyektor telah diganti dengan yang baru").

## 3. Kebutuhan Frontend (UI/UX & Validasi)
- **File Rujukan:** `resources/views/petugas/report-management.blade.php`
- **Komponen UI Utama:**
  - Tabel Daftar Keluhan.
  - Komponen Dropdown untuk memilih Status Update (Diproses, Selesai, Ditolak).
  - Teks area untuk input "Catatan Perbaikan/Resolusi".
- **Validasi Klien:** Jika dropdown diset ke "Selesai" atau "Ditolak", kotak teks "Catatan Resolusi" otomatis menjadi wajib isi.

## 4. Kebutuhan Backend (Controller & DB)
- **Controller:** `ReportManagementController.php`
- **Method:** `updateStatus(Request $request, $id)`
- **Validasi Server Khusus:**
  - `status`: *in:diproses,selesai,ditolak*.
  - Jika `$request->status == 'selesai'`, maka validasi *rule* untuk `catatan_resolusi` adalah `required`.
- **Proses DB:** Memperbarui tabel `damage_reports` kolom `status_laporan` dan `catatan_resolusi`.

## 5. Aturan Penolakan / Edge Cases
- Pengguna pelapor tidak akan bisa menghapus laporannya sendiri yang sudah berstatus `diproses` (Cegah modifikasi data di tengah penanganan operasional).
