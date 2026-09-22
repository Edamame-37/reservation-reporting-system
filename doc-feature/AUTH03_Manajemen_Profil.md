# AUTH-03: Manajemen Profil Pengguna

## 1. Meta Informasi
- **Aktor:** Pengguna, Petugas, Admin
- **Ekuivalen User Story:** - (Fundamental berdasarkan PDF)
- **Modul:** Autentikasi & Keamanan

## 2. Deskripsi Alur Bisnis
Pengguna yang telah berhasil masuk (login) dapat membuka halaman profil mereka untuk mengubah informasi dasar (seperti Nama) dan memperbarui kata sandi lama menjadi yang baru demi alasan keamanan. Pengguna juga dapat menghapus akun mereka secara mandiri jika diinginkan (opsional bawaan Breeze).

## 3. Kebutuhan Frontend (UI/UX & Validasi)
- **File Rujukan:** `resources/views/profile/edit.blade.php`
- **Form Input:**
  - Form Pembaruan Data (Nama). Email sebaiknya dikunci (`readonly`) karena digunakan sebagai identitas validasi utama.
  - Form Pembaruan Password (Password Saat Ini, Password Baru, Konfirmasi Password Baru).
- **Validasi Klien:** Pengecekan standar minimum panjang sandi. Pesan toast/sukses berwarna hijau akan muncul jika penyimpanan profil berhasil.

## 4. Kebutuhan Backend (Controller & DB)
- **Controller:** `app/Http/Controllers/ProfileController.php` dan `PasswordController.php`
- **Method:** `update(ProfileUpdateRequest $request)`
- **Validasi Server:**
  - Untuk ubah password, harus divalidasi apakah "Password Saat Ini" benar (cocok dengan *Hash* di database).
  - Untuk ubah profil, nama wajib diisi.

## 5. Aturan Penolakan / Edge Cases
- **Konfirmasi Salah:** Jika sandi lama yang diketikkan pengguna keliru, kembalikan dengan pesan *error* validasi khusus tanpa menyentuh *database*.

## 6. Penjelasan Rinci Cara Kerja (Pseudocode & Logika)

### Routing (`routes/web.php`)
```php
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
```

### Logika Eksekusi di Controller (`ProfileController@update`)
1. **Validasi Form:** Pastikan `$request->name` diisi.
2. **Sinkronisasi Data:** ` $request->user()->fill($request->validated());`
3. **Penyimpanan:** `$request->user()->save();`
4. **Respon Visual:** Mengembalikan ke halaman profil (`Redirect::route('profile.edit')`) dan menyematkan parameter sesi `->with('status', 'profile-updated');`. Di file Blade, variabel sesi tersebut akan ditangkap oleh Alpine.js `x-data="{ show: true }"` untuk memunculkan lencana *toast* "Saved." warna hijau yang menghilang setelah 2 detik.

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
