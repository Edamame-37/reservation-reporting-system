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

## 6. Penjelasan Rinci Cara Kerja (Pseudocode & Logika)

### Routing (`routes/web.php`)
```php
Route::middleware(['auth', 'role:petugas'])->group(function () {
    Route::get('/petugas/reservations', [ReservationManagementController::class, 'index'])->name('petugas.reservations.index');
    Route::patch('/petugas/reservations/{id}/approve', [ReservationManagementController::class, 'approve'])->name('petugas.reservations.approve');
    Route::patch('/petugas/reservations/{id}/reject', [ReservationManagementController::class, 'reject'])->name('petugas.reservations.reject');
});
```

### Logika Eksekusi di Controller (`ReservationManagementController@approve`)
Metode ini adalah jantung sistem, harus tahan dari celah konkurensi (2 petugas meng-klik secara bersamaan).
```php
public function approve($id) {
    // 1. Awali transaksi database yang mengunci baris (Pessimistic Locking)
    DB::transaction(function () use ($id) {
        $reservation = Reservation::lockForUpdate()->findOrFail($id);
        
        // 2. Jika status bukan lagi pending (mungkin sudah disetujui petugas lain), hentikan.
        if ($reservation->status !== 'pending') {
            abort(422, 'Reservasi ini sudah diproses.');
        }

        // 3. Pengecekan Bentrok Jadwal (Overlap SQL)
        $overlap = Reservation::where('facility_id', $reservation->facility_id)
            ->where('date', $reservation->date)
            ->where('status', 'approved')
            ->where(function($query) use ($reservation) {
                $query->where('start_time', '<', $reservation->end_time)
                      ->where('end_time', '>', $reservation->start_time);
            })->exists();

        if ($overlap) {
            abort(422, 'Fasilitas sudah dibooking pada jam tersebut!');
        }

        // 4. Lolos semua? Lakukan persetujuan.
        $reservation->status = 'approved';
        $reservation->save();
    });
    
    return back()->with('success', 'Reservasi berhasil disetujui.');
}
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
