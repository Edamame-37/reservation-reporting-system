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

## 6. Penjelasan Rinci Cara Kerja (Pseudocode & Logika)

### Routing (`routes/web.php`)
```php
Route::middleware(['auth', 'role:petugas'])->group(function () {
    Route::delete('/petugas/reservations/{id}/force-cancel', [ReservationManagementController::class, 'forceCancel'])->name('petugas.reservations.force-cancel');
});
```

### Logika Eksekusi di Controller (`ReservationManagementController@forceCancel`)
1. **Validasi Alasan:**
   ```php
   $request->validate([
       'alasan_batal' => 'required|string|min:10' // Petugas wajib memberi alasan panjang
   ]);
   ```
2. **Proses Eksekusi (Pembatalan Sepihak):**
   ```php
   $reservation = Reservation::findOrFail($id);
   // Pastikan hanya membatalkan yang sudah approved (jika tidak, ini bisa error salah sasaran)
   if ($reservation->status !== 'approved') {
       return back()->withErrors('Hanya reservasi yang telah disetujui yang dapat dibatalkan paksa.');
   }

   $reservation->status = 'cancelled_by_admin'; // Atau rejected
   $reservation->alasan_batal = $request->alasan_batal;
   $reservation->save();
   
   return back()->with('success', 'Pembatalan darurat berhasil dieksekusi.');
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

## 8. Catatan Penyesuaian Tambahan (Diisi oleh Programmer)
*(Bagian ini wajib diisi jika Anda melakukan penyesuaian/improvisasi yang berbeda dari Blueprint di atas selama proses koding! Kosongkan jika tidak ada).*

- ...
