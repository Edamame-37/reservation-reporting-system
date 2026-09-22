# AUTH-02: Login & Otorisasi Hak Akses (Role)

## 1. Meta Informasi
- **Aktor:** Pengguna, Petugas, Admin
- **Ekuivalen User Story:** - (Fundamental berdasarkan PDF)
- **Modul:** Autentikasi & Keamanan

## 2. Deskripsi Alur Bisnis
Sistem memiliki tiga kelompok pengguna (*role*) yakni Pengguna Biasa, Petugas, dan Admin. Saat *user* masuk (login) menggunakan kredensial mereka, sistem harus melakukan pengecekan ganda: Pertama mengecek apakah kata sandi benar, dan Kedua mengecek apakah status akun mereka aktif/sudah diverifikasi (bukan berstatus `pending` atau `ditolak`). Setelah berhasil login, mereka akan diarahkan ke *dashboard* masing-masing sesuai hak aksesnya.

## 3. Kebutuhan Frontend (UI/UX & Validasi)
- **File Rujukan:** `resources/views/auth/login.blade.php`
- **Form Input:** Email & Password.
- **Validasi Klien:** Keduanya wajib diisi (`required`).
- **State Halaman:** Jika kredensial salah, tampilkan pesan *error* merah di atas inputan. Jika akun masih "Pending", kembalikan ke halaman login dengan keterangan "Akun Anda belum diverifikasi Admin".

## 4. Kebutuhan Backend (Controller & DB)
- **Controller:** `app/Http/Controllers/Auth/AuthenticatedSessionController.php`
- **Middleware:** `guest` untuk halaman form, `auth` dan peran Spatie (contoh: `role:admin`) untuk otorisasi halaman rute.
- **Validasi Server & Bisnis:**
  - `email` dan `password` cocok di database.
  - Cek kolom `status` pengguna (hanya yang berstatus `verified` / aktif yang boleh login).
  - Tentukan rute *redirect* berdasarkan peran (Gunakan `Spatie\Permission\Traits\HasRoles`). Jika `hasRole('admin')`, maka diarahkan ke `/admin/dashboard`, dsb.

## 5. Aturan Penolakan / Edge Cases
- **Upaya Login Akun Tertahan:** Jika akun `pending` atau di-`banned` oleh Admin mencoba login, sesi tidak akan dibuat dan sistem mengembalikan *HTTP 401* (Unauthorized) atau *HTTP 302* (Redirect back) lengkap dengan pesan kesalahan status akun.

## 6. Penjelasan Rinci Cara Kerja (Pseudocode & Logika)

### Routing (`routes/web.php` atau `routes/auth.php`)
```php
Route::get('/login', [AuthenticatedSessionController::class, 'create'])->middleware('guest')->name('login');
Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('guest');
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->middleware('auth')->name('logout');
```

### Logika Eksekusi di Controller (`AuthenticatedSessionController@store`)
1. **Validasi Kredensial:** Sistem mengecek kecocokan *email* dan *password* melalui `Auth::attempt()`.
2. **Validasi Status Pending (Penting):**
   ```php
   $user = Auth::user();
   if ($user->status !== 'verified') {
       Auth::logout(); // Putuskan sesi
       return back()->withErrors(['email' => 'Akun Anda belum disetujui Admin.']);
   }
   ```
3. **Pengecekan Otorisasi (Redirect):**
   ```php
   if ($user->hasRole('admin')) {
       return redirect()->intended('/admin/dashboard');
   } elseif ($user->hasRole('petugas')) {
       return redirect()->intended('/petugas/dashboard');
   }
   return redirect()->intended('/user/dashboard');
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
