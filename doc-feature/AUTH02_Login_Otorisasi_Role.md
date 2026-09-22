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
