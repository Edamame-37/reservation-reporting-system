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

## 6. Penjelasan Rinci Cara Kerja (Pseudocode & Logika)

### Routing (`routes/web.php`)
```php
Route::middleware(['auth', 'role:pengguna'])->group(function () {
    Route::delete('/reservations/{id}/cancel', [ReservationController::class, 'cancel'])->name('reservations.cancel');
});
```

### Logika Eksekusi di Controller (`ReservationController@cancel`)
1. **Otorisasi Kepemilikan:**
   ```php
   $reservation = Reservation::findOrFail($id);
   if ($reservation->user_id !== Auth::id()) {
       abort(403, 'Akses ditolak.');
   }
   ```
2. **Validasi Batas H-1 (Wajib Sesuai PDF):**
   ```php
   // Gabungkan tanggal dan jam mulai menjadi objek Carbon
   $startDateTime = Carbon::parse($reservation->date . ' ' . $reservation->start_time);
   
   // Cek selisih dengan waktu sekarang
   if (now()->diffInHours($startDateTime, false) <= 24) {
       return back()->withErrors('Pembatalan maksimal dilakukan H-1 (24 jam) sebelum acara.');
   }
   ```
3. **Penyimpanan Status Baru:**
   ```php
   $reservation->status = 'cancelled_by_user';
   $reservation->save();
   return back()->with('success', 'Reservasi berhasil dibatalkan.');
   ```

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

11. **Alur Branching & Pull Request:** Sesuai `GUIDE_GITHUB.md`, sebelum mulai koding, WAJIB membuat *branch* baru (contoh: `feature/nama-fitur`). Setelah selesai dan di-*push*, wajib membuat **Pull Request (PR)** ke *branch* `develop`.

## 8. Catatan Penyesuaian Tambahan (Diisi oleh Programmer)
*(Bagian ini wajib diisi jika Anda melakukan penyesuaian/improvisasi yang berbeda dari Blueprint di atas selama proses koding! Kosongkan jika tidak ada).*

- ...
