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
