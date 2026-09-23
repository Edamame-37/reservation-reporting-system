# USR-01: Form Pengajuan Reservasi Ruangan

## 1. Meta Informasi
- **Aktor:** Pengguna (Sivitas Akademika yang sudah login)
- **Ekuivalen User Story:** US-3
- **Modul:** Manajemen Reservasi (Frontend/User)

## 2. Deskripsi Alur Bisnis
Pengguna yang telah login dapat memilih fasilitas dari katalog dan mengisi formulir pengajuan reservasi. Mereka harus menentukan tanggal, jam mulai (*start time*), jam selesai (*end time*), serta menuliskan tujuan kegiatan. Setelah dikirim, status reservasi akan otomatis menjadi `menunggu persetujuan` (*pending*) dan masuk ke dalam antrean Petugas.

## 3. Kebutuhan Frontend (UI/UX & Validasi)
- **File Rujukan:** `resources/views/user/reservation-form.blade.php`
- **Form Input:**
  - `facility_id` (Dropdown/Hidden bergantung cara masuknya).
  - `date` (Date picker, minimal tanggal hari ini).
  - `start_time` & `end_time` (Time picker, step 30 menit).
  - `purpose` (Textarea tujuan penggunaan).
- **Validasi Klien:** Waktu mulai tidak boleh lebih dari waktu selesai. Waktu tidak boleh di luar jam 07:00 - 20:00.

## 4. Kebutuhan Backend (Controller & DB)
- **Controller:** `ReservationController.php`
- **Method:** `store(Request $request)`
- **Validasi Server Khusus (Sesuai PDF Aturan Bisnis):**
  - **Aturan Jam Operasional:** `start_time` >= 07:00 dan `end_time` <= 20:00.
  - **Aturan Durasi:** Menit pada `start_time` dan `end_time` HANYA boleh bernilai `00` atau `30`. (Kelipatan 30 menit).
  - **Aturan Konkurensi / Bentrok (Penting!):** Sistem menggunakan kueri basis data untuk memeriksa tabel `reservations`. Jika fasilitas yang sama pada rentang waktu yang saling bersinggungan (*overlap*) sudah memiliki reservasi dengan status `approved`, maka sistem harus menolak *request* ini!
- **Data Tersimpan:** Menyimpan ID pengguna (`user_id` dari sesi `Auth::id()`) dan menetapkan kolom `status` ke `pending`.

## 5. Aturan Penolakan / Edge Cases
- Jika jam yang diminta bertabrakan dengan pesanan orang lain yang sudah disetujui, tolak (*HTTP 422*).
- Jika fasilitas yang hendak dipesan kebetulan berstatus 'Dalam Perbaikan' (Rusak), tolak pendaftaran.

## 6. Penjelasan Rinci Cara Kerja (Pseudocode & Logika)

### Routing (`routes/web.php`)
```php
Route::middleware(['auth', 'role:pengguna'])->group(function () {
    Route::post('/reservations', [ReservationController::class, 'store'])->name('reservations.store');
});
```

### Logika Eksekusi di Controller (`ReservationController@store`)
1. **Validasi Kustom FormRequest:**
   ```php
   $request->validate([
       'facility_id' => 'required|exists:facilities,id',
       'date' => 'required|date|after_or_equal:today',
       'start_time' => 'required|date_format:H:i',
       'end_time' => 'required|date_format:H:i|after:start_time',
       'purpose' => 'required|string'
   ]);
   ```
2. **Validasi Menit & Jam Operasional (07.00 - 20.00):**
   ```php
   // Ekstrak menit dan jam pakai Carbon atau explode
   // Jika menit != '00' && menit != '30', lempar error 422.
   // Jika jam < 07 || jam >= 20, lempar error 422.
   ```
3. **Validasi Anti-Bentrok & Fasilitas Rusak:**
   ```php
   $facility = Facility::find($request->facility_id);
   if ($facility->status_aktif == 'maintenance') {
       return back()->withErrors('Fasilitas sedang diperbaiki.');
   }
   
   $overlap = Reservation::where('facility_id', $request->facility_id)
       ->where('date', $request->date)
       ->where('status', 'approved')
       ->where(function($q) use ($request) {
           $q->where('start_time', '<', $request->end_time)
             ->where('end_time', '>', $request->start_time);
       })->exists();

   if ($overlap) {
       return back()->withErrors('Jadwal bentrok dengan pengguna lain.');
   }
   ```
4. **Penyimpanan:**
   ```php
   Reservation::create([... $request->all(), 'user_id' => Auth::id(), 'status' => 'pending']);
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
