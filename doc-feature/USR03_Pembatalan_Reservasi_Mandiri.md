# SRS-USR-03: Pembatalan Reservasi Mandiri (Validasi Batas H-1)

## 1. Identifikasi Dokumen & Metadata SRS
- **Kode Spesifikasi:** SRS-USR-03
- **Judul Fitur:** Pembatalan Reservasi Mandiri Pengguna dengan Validasi Batas H-1
- **Aktor Utama:** Pengguna Terautentikasi (Sivitas Akademika: Mahasiswa, Dosen, Tenaga Kependidikan/Staf)
- **Role Sistem:** `pengguna` (Spatie Laravel Permission) dengan status akun aktif (`status = 'active'`)
- **Ekuivalen User Story:** US-4 (*"Sebagai Pengguna, saya bisa membatalkan reservasi saya sendiri maksimal H-1 agar ruangan bisa digunakan orang lain."*)
- **Modul Sistem:** Modul Manajemen Reservasi (*Sub-modul: Portal Pengguna / Sivitas Akademika*)
- **Tingkat Prioritas:** Fundamental / *Must Have* (Kritis)
- **Status Dokumen:** Versi 2.0 (Spesifikasi Lengkap & Terstandarisasi)

---

## 2. Deskripsi Alur Bisnis & Karakteristik Pengguna

### 2.1. Karakteristik & Kebutuhan Pengguna
Jika agenda acara civitas akademika mengalami perubahan tanggal, pembatalan kegiatan dari pimpinan, atau kendala tak terduga, pengguna memiliki wewenang untuk membatalkan tiket reservasi yang telah mereka ajukan secara mandiri:
1. **Pelepasan Ruang Cepat:** Fasilitas yang sebelumnya terisi/dipesan langsung kembali berstatus kosong (tersedia) di kalender ketersediaan sehingga dapat dipinjam oleh civitas akademika lain.
2. **Kemandirian Tanpa Birokrasi:** Pengguna tidak perlu menghubungi petugas sarpras atau admin secara manual hanya untuk membatalkan pengajuan mereka.
3. **Disiplin Jadwal Kampus:** Penerapan batas waktu H-1 mendidik civitas akademika untuk tidak membatalkan pemesanan secara mendadak pada hari pelaksanaan acara.

### 2.2. Ringkasan Alur Pembatalan Mandiri
1. Pengguna membuka halaman *Riwayat Reservasi* (`/user/reservation-history`).
2. Sistem mengevaluasi setiap tiket: Tombol merah *"Batalkan"* hanya akan ditampilkan pada tiket yang berstatus `pending` atau `approved` dan waktu mulai kegiatan (`reservation_date + start_time`) masih berjarak **minimal 24 jam (H-1)** dari waktu sekarang (`now()`).
3. Pengguna menekan tombol *"Batalkan"*, memicu munculnya dialog modal konfirmasi untuk mencegah salah klik (*accidental click*).
4. Pengguna dapat menuliskan alasan pembatalan (opsional) dan menekan tombol konfirmasi *"Ya, Batalkan Reservasi"*.
5. Sistem memvalidasi ulang kepemilikan dan batas H-1 di sisi server. Jika valid, status tiket diubah menjadi `cancelled`, alasan pembatalan disimpan pada kolom `cancellation_reason`, dan pengguna dialihkan kembali ke riwayat dengan pesan notifikasi sukses.

---

## 3. Aturan Bisnis Mutlak (*Business Rules*)

| Kode Aturan | Nama Aturan | Deskripsi & Batasan Ketat | Tingkat Penegakan |
|---|---|---|---|
| **BR-USR03-01** | **Isolasi Kepemilikan (*Strict Ownership*)** | Pengguna **hanya dapat membatalkan reservasi miliknya sendiri** (`reservation->user_id === Auth::id()`). Percobaan membatalkan tiket milik orang lain melalui manipulasi URL/ID akan ditolak tegas dengan *HTTP 403 Forbidden*. | Server-side (Controller) |
| **BR-USR03-02** | **Batas Waktu Mutlak H-1 (24 Jam)** | Pembatalan mandiri **hanya diizinkan jika selisih waktu antara saat permohonan pembatalan diajukan dengan waktu mulai acara (`reservation_date + start_time`) adalah $\ge 24$ jam**. Permohonan pembatalan di hari-H atau kurang dari 24 jam ditolak dengan pesan kesalahan (*HTTP 422*). | Client-side & Server-side |
| **BR-USR03-03** | **Kelayakan Status Tiket (*Eligible Status*)** | Hanya reservasi berstatus `pending` atau `approved` yang dapat dibatalkan. Tiket yang sudah berstatus `cancelled`, `rejected`, atau `completed` tidak dapat dibatalkan kembali (*Idempotent Action*). | Server-side (Controller) |
| **BR-USR03-04** | **Pelepasan Slot Fasilitas Otomatis** | Ketika tiket berstatus `approved` dibatalkan, statusnya berubah menjadi `cancelled`. Karena kueri anti-bentrok (USR-01) hanya memeriksa status `approved`, maka secara otomatis slot jadwal tersebut menjadi kosong dan dapat dipesan pengguna lain tanpa perlu intervensi admin. | Basis Data Relasional |
| **BR-USR03-05** | **Pencatatan Alasan Pembatalan** | Sistem menyimpan riwayat alasan pembatalan pada kolom `cancellation_reason` tabel `reservations` untuk transparansi dan audit operasional sarpras. | Basis Data (MySQL) |

---

## 4. Kebutuhan Fungsional (*Functional Requirements*)

| ID Kebutuhan | Nama Kebutuhan | Deskripsi Spesifikasi Fungsional |
|---|---|---|
| **FR-USR03-001** | Evaluasi Tampilan Tombol Batal | Sistem harus menampilkan tombol *"Batalkan"* pada tabel riwayat hanya untuk tiket milik pengguna aktif yang berstatus `pending` atau `approved` dan waktu mulai acaranya $\ge 24$ jam ke depan. |
| **FR-USR03-002** | Dialog Modal Konfirmasi Batal | Saat tombol *"Batalkan"* ditekan, sistem harus memunculkan dialog modal konfirmasi interaktif yang menyajikan ringkasan tiket dan textarea input alasan pembatalan. |
| **FR-USR03-003** | Endpoint Pembatalan Reservasi | Sistem harus menyediakan endpoint rute `DELETE /user/reservations/{id}/cancel` yang diamankan oleh middleware autentikasi dan peran `pengguna`. |
| **FR-USR03-004** | Validasi Server Batas Waktu H-1 | Kontroler backend wajib menghitung selisih waktu secara presisi menggunakan *Carbon* dan menolak pembatalan jika waktu pelaksanaan berjarak $\le 24$ jam dari `now()`. |
| **FR-USR03-005** | Pembaruan Status & Alasan di DB | Kontroler harus memperbarui kolom `status` menjadi `'cancelled'` dan mengisi kolom `cancellation_reason` dengan teks alasan pembatalan. |
| **FR-USR03-006** | Umpan Balik Flash Notifikasi | Pasca-pembatalan berhasil, pengguna dialihkan kembali ke dasbor riwayat dengan pesan notifikasi sukses (*success flash message*). |

---

## 5. Kebutuhan Non-Fungsional (*Non-Functional Requirements*)

### 5.1. Keamanan & Integritas (*Security*)
- **Proteksi Otorisasi:** Sistem menerapkan validasi `Auth::id()` ganda di sisi controller. Percobaan eksekusi langsung via cURL atau Postman terhadap ID tiket orang lain mutlak menghasilkan kode status *HTTP 403 Forbidden*.
- **Perlindungan CSRF:** Seluruh formulir pembatalan wajib menyertakan token `@csrf` dan direktif `@method('DELETE')`.

### 5.2. Keandalan & Konkurensi (*Reliability*)
- **Pembaruan Atomik:** Perubahan status tiket dilakukan secara instan sehingga matriks jadwal ketersediaan publik langsung sinkron secara *real-time*.

### 5.3. Pengalaman Pengguna (*UX & Usability*)
- **Pencegahan Human Error:** Dialog modal konfirmasi menghindarkan pengguna dari risiko salah sentuh tombol batal pada perangkat bergerak (*smartphone*).
- **Indikator Informatif:** Tiket yang tidak lagi dapat dibatalkan (sudah masuk masa H-0) diberikan penanda visual bahwa batas waktu pembatalan mandiri telah berakhir.

---

## 6. Arsitektur Backend & Rute Rujukan

### 6.1. Pemetaan Rute URL (`routes/web.php`)
```php
use App\Http\Controllers\ReservationController;

Route::middleware(['auth', 'role:pengguna'])->prefix('user')->name('user.')->group(function () {
    Route::delete('/reservations/{id}/cancel', [ReservationController::class, 'cancel'])->name('reservations.cancel');
});
```

### 6.2. Logika Kontroler (`ReservationController@cancel`)
```php
public function cancel(Request $request, int|string $id): RedirectResponse
{
    $userId = Auth::id() ?? 1;
    $reservation = Reservation::findOrFail($id);

    // 1. Otorisasi Kepemilikan (Strict Ownership)
    if ($reservation->user_id !== $userId) {
        abort(403, 'Akses ditolak. Anda tidak berhak membatalkan reservasi ini.');
    }

    // 2. Validasi Status Tiket yang Layak
    if (!in_array($reservation->status, ['pending', 'approved'])) {
        return back()->withErrors(['error' => 'Reservasi ini tidak dapat dibatalkan karena sudah diproses atau dibatalkan sebelumnya.']);
    }

    // 3. Validasi Batas Waktu H-1 (24 Jam Sebelum Jam Mulai)
    $startDateTime = Carbon::parse($reservation->reservation_date . ' ' . $reservation->start_time);
    $cancellationDeadline = $startDateTime->copy()->subHours(24);

    if (now()->greaterThan($cancellationDeadline)) {
        return back()->withErrors(['error' => 'Pembatalan mandiri tidak diizinkan. Batas waktu pembatalan maksimal adalah H-1 (24 jam) sebelum waktu mulai acara.']);
    }

    // 4. Update Status & Alasan Pembatalan
    $reason = trim($request->input('cancellation_reason', ''));
    $reservation->status = 'cancelled';
    $reservation->cancellation_reason = !empty($reason) ? $reason : 'Dibatalkan mandiri oleh pemohon.';
    $reservation->save();

    return redirect()->route('user.reservation-history')
        ->with('success', "Reservasi dengan kode {$reservation->ticket_code} berhasil dibatalkan.");
}
```

---

## 7. Skenario Pengujian & Kriteria Penerimaan (*Acceptance Criteria*)

### 7.1. Kriteria Penerimaan Berbasis Format Gherkin
```gherkin
Fitur: Pembatalan Reservasi Mandiri Pengguna (Batas Waktu H-1)

  Skenario: Pengguna membatalkan tiket reservasi berstatus pending lebih dari 24 jam sebelum acara
    Dengan Saya adalah pemilik tiket reservasi berstatus "pending"
    Dan Jadwal acara dimulai 48 jam dari sekarang (H-2)
    Ketika Saya mengklik tombol "Batalkan" dan mengonfirmasi pada dialog modal
    Maka Status tiket berubah menjadi "cancelled" pada basis data
    Dan Alasan pembatalan tersimpan di kolom "cancellation_reason"
    Dan Sistem menampilkan notifikasi sukses di halaman riwayat

  Skenario: Pengguna mencoba membatalkan tiket reservasi yang berjarak kurang dari 24 jam (H-0)
    Dengan Saya adalah pemilik tiket reservasi
    Dan Jadwal acara dimulai dalam waktu 10 jam dari sekarang
    Ketika Saya mengirim permintaan pembatalan ke endpoint sistem
    Maka Sistem menolak permintaan pembatalan dengan pesan kesalahan validasi
    Dan Status tiket tetap tidak berubah

  Skenario: Pengguna mencoba membatalkan tiket milik pengguna lain
    Dengan Terdapat tiket reservasi milik Pengguna B
    Ketika Saya (Pengguna A) mengirimkan request pembatalan terhadap ID tiket milik Pengguna B
    Maka Sistem menolak akses dengan respons HTTP 403 Forbidden
```

### 7.2. Matriks Kasus Uji (*Test Case Matrix*)

| ID Uji | Skenario Pengujian | Aksi Pengujian | Hasil yang Diharapkan | Status |
|---|---|---|---|---|
| **TC-USR03-01** | Batal Tiket Pending $\ge 24$ Jam | POST /user/reservations/{id}/cancel | Status menjadi `cancelled`, flash success muncul. | Mandatory Pass |
| **TC-USR03-02** | Batal Tiket Approved $\ge 24$ Jam | POST /user/reservations/{id}/cancel | Status menjadi `cancelled`, fasilitas kembali kosong. | Mandatory Pass |
| **TC-USR03-03** | Penolakan Batal $< 24$ Jam (H-0) | Request batal pada tiket besok pagi (<24 jam) | Permintaan gagal, pesan error H-1 muncul. | Mandatory Pass |
| **TC-USR03-04** | Penolakan Batal Tiket User Lain | User A membatalkan tiket User B | Respons HTTP 403 Forbidden. | Mandatory Pass |
| **TC-USR03-05** | Penolakan Batal Tiket Expired/Rejected | Request batal pada tiket `rejected`/`cancelled` | Permintaan ditolak dengan pesan kesalahan. | Mandatory Pass |

---

## 8. Instruksi Khusus untuk Programmer (Mandatori)

1. **Anti-Hardcode:** Menggunakan data dinamis dari atribut Eloquent `$reservation->id` dan `$reservation->ticket_code`.
2. **Pemisahan Pengerjaan (*Separation of Concerns*):** Wajib mengerjakan dan menguji fase **Frontend terlebih dahulu**, melakukan commit git terpisah, baru kemudian melangkah ke fase **Backend** dan commit terpisah.
3. **Kepatuhan Tipe Status Basis Data:** Gunakan nilai enum `'cancelled'` (bukan `cancelled_by_user`) agar selaras dengan skema MySQL migrasi `2026_09_20_000002_create_reservations_table.php`.
4. **Keamanan Otorisasi Ganda:** Selalu verifikasi `$reservation->user_id === Auth::id()`.
5. **Standar Kolaborasi Git:** Gunakan branch `feature/usr03/pembatalan-reservasi` dan pesan commit konvensional berbahasa Indonesia.

---

## 9. Catatan Penyesuaian Tambahan (Diisi oleh Programmer)

- **Penyesuaian Enum Database:** Nilai status pada skema database resmi adalah `cancelled` (disertai kolom teks `cancellation_reason`), sehingga kita menggunakan status `'cancelled'` dan mencatat detail pemohon di kolom alasan.
- **Konfirmasi Modal Alpine.js:** Menambahkan modal pop-up konfirmasi pembatalan mandiri yang bersih dan responsif langsung di `reservation-history.blade.php`.
