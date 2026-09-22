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

## 6. Penjelasan Rinci Cara Kerja (Pseudocode & Logika)

### Routing (`routes/web.php`)
```php
Route::middleware(['auth', 'role:pengguna'])->group(function () {
    Route::get('/reservations/history', [ReservationController::class, 'history'])->name('reservations.history');
});
```

### Logika Eksekusi di Controller (`ReservationController@history`)
1. **Pencarian Data (Query):**
   ```php
   // Harus memuat relasi (Eager Loading) agar tidak N+1 Query Problem
   $reservations = Reservation::with('facility')
                   ->where('user_id', Auth::id())
                   ->orderBy('created_at', 'desc')
                   ->paginate(10);
   ```
2. **Pengembalian View:**
   ```php
   return view('user.reservation-history', compact('reservations'));
   ```

### Logika Frontend (`reservation-history.blade.php`)
Di dalam baris tabel (looping `@foreach($reservations as $res)`), buat pengkondisian IF untuk status:
```blade
@if($res->status == 'pending')
    <span class="bg-yellow-100 text-yellow-800">Menunggu</span>
@elseif($res->status == 'approved')
    <span class="bg-green-100 text-green-800">Disetujui</span>
@endif
```
Jika statusnya `rejected` atau `cancelled`, cetak nilai variabel `$res->alasan_batal` di dalam kolom keterangan agar pengguna mengerti mengapa pengajuannya digagalkan.

## 7. Instruksi Khusus untuk Programmer (Mandatori)
1. **Anti-Hardcode:** Wajib menghapus segala jenis data *hardcode* (*dummy*) di *frontend* dan langsung menggantinya dengan data dinamis yang terhubung ke *database* via variabel *Controller*.
2. **Fleksibilitas Blueprint:** Ingat bahwa isi dokumen ini adalah *blueprint* dasar. Anda diberikan kebebasan penuh untuk melakukan **improvisasi** dan menyempurnakan struktur atau estetika kodenya selama tidak menyimpang dari tujuan utama.
3. **Pemisahan Pengerjaan (Separation of Concerns):** Walaupun Anda ditugaskan sendirian sebagai *Fullstack* (mengerjakan UI dan Database sekaligus), **DILARANG KERAS** mengerjakannya secara bersamaan dalam satu *commit*. Kerjakan fase *Frontend* hingga selesai, lalu beralih ke fase *Backend* (atau sebaliknya). Ini diwajibkan oleh pedoman standar RULE_FRONTEND.md dan RULE_BACKEND.md.
4. **Patuh pada Aturan Induk:** Sebelum mulai mengetikkan satu baris kode pun, Anda diwajibkan untuk mereview dan mematuhi seluruh *guidelines* yang tercantum di file RULE_FRONTEND.md dan RULE_BACKEND.md.
5. **Kesesuaian Bisnis Inti:** Jangan menulis fungsi yang melenceng! Cek ulang dokumen CASE_PROJECT.md setiap kali Anda ragu mengenai aturan bisnis dari fitur yang sedang dikerjakan.
6. **Kesesuaian Arsitektur:** Pastikan *controller* dan *view* yang Anda buat diletakkan persis pada jalur folder yang sudah diamanatkan oleh peta struktur PLAN_DEVELOPMENT.md.
7. **Standar Teknologi CAVA:** Gunakan aturan *stack* (seperti Tailwind, Spatie, dll) sesuai perintah resmi pada dokumen TECHSTACK.md.
8. **Kepatuhan Mutlak Sistem:** Taati seluruh undang-undang dan aturan *workflow* di dalam RULE_PROJECT.md tanpa terkecuali.
9. **Finalisasi Valid:** Anda HANYA diizinkan mencentang progress penyelesaian fitur ini di PLAN_PROJECT.md SETELAH pengujian (testing) secara manual tuntas dilakukan tanpa celah (bug), sesaat sebelum melakukan integrasi akhir (push).

10. **Standar Kolaborasi Git:** Sebelum melakukan *commit* dan *push*, Anda WAJIB memastikan bahwa tata cara dan penamaan pesannya sesuai dengan aturan di `GUIDE_GITHUB.md`.

## 8. Catatan Penyesuaian Tambahan (Diisi oleh Programmer)
*(Bagian ini wajib diisi jika Anda melakukan penyesuaian/improvisasi yang berbeda dari Blueprint di atas selama proses koding! Kosongkan jika tidak ada).*

- ...
