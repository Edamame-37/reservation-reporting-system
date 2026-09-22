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
