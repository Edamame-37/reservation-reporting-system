# ADM-02: Pembuatan Akun Internal (Petugas & Pengguna)

## 1. Meta Informasi
- **Aktor:** Admin
- **Ekuivalen User Story:** US-13 & US-14
- **Modul:** Manajemen Pengguna (Konsol Admin)

## 2. Deskripsi Alur Bisnis
Admin memiliki kendali penuh untuk mendaftarkan akun secara langsung tanpa melalui jalur "Registrasi Mandiri". Ini diperuntukkan untuk akun-akun level tinggi seperti **Petugas** (yang memang tidak bisa dibuat lewat registrasi biasa) atau mendaftarkan dosen/pejabat tertentu (Pengguna) secara manual (*bypass*). Akun hasil buatan Admin ini tidak perlu lagi melalui fase verifikasi (*langsung aktif*).

## 3. Kebutuhan Frontend (UI/UX & Validasi)
- **File Rujukan:** `resources/views/admin/user-management.blade.php`
- **Komponen UI Utama:**
  - Tombol "Tambah Akun Baru".
  - Sebuah formulir pop-up Modal (`cava/modal.blade.php`).
- **Form Input:**
  - Nama Lengkap (Teks)
  - Email (Email)
  - Password Sementara (Password)
  - Pilih Role (Dropdown: *Petugas* atau *Pengguna*). Dilarang membuat role Admin lain demi keamanan.

## 4. Kebutuhan Backend (Controller & DB)
- **Controller:** `AdminUserManagementController.php`
- **Method:** `storeUser(Request $request)`
- **Validasi Server Khusus:**
  - Validasi *role*: `in:petugas,pengguna`.
- **Proses DB:** Menyimpan baris ke `users`. Menetapkan *role* via paket Spatie: `$user->assignRole($request->role)`. Kolom `status` langsung diset ke `verified` atau `aktif`.

## 5. Aturan Penolakan / Edge Cases
- Tidak boleh ada antarmuka yang mengizinkan Admin membuat akun dengan role "Admin". Pembatasan ini mencegah admin-admin bayangan diciptakan sembarangan. Murni dibatasi pada dua peran pendukung.

## 6. Penjelasan Rinci Cara Kerja (Pseudocode & Logika)

### Routing (`routes/web.php`)
```php
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::post('/admin/users', [AdminUserManagementController::class, 'storeUser'])->name('admin.users.store');
});
```

### Logika Eksekusi di Controller (`AdminUserManagementController@storeUser`)
1. **Validasi Form Request:**
   ```php
   $request->validate([
       'name' => 'required|string|max:255',
       'email' => 'required|email|unique:users,email',
       'password' => 'required|string|min:8',
       'role' => 'required|in:petugas,pengguna' // SANGAT PENTING: Jangan izinkan 'admin'!
   ]);
   ```
2. **Insert User Langsung Aktif:**
   ```php
   $user = User::create([
       'name' => $request->name,
       'email' => $request->email,
       'password' => Hash::make($request->password),
       'status' => 'verified' // Langsung aktif tanpa antre verifikasi!
   ]);
   ```
3. **Penyematan Peran (Spatie):**
   ```php
   $user->assignRole($request->role);
   return back()->with('success', 'Akun internal berhasil diciptakan.');
   ```
