# ADM-01: Verifikasi Akun Pengguna Baru (Pending)

## 1. Meta Informasi
- **Aktor:** Admin
- **Ekuivalen User Story:** US-15
- **Modul:** Manajemen Pengguna (Konsol Admin)

## 2. Deskripsi Alur Bisnis
Karena siapa saja bisa mendaftar lewat web Publik (AUTH-01), Admin bertugas menjadi penyaring (*filter*). Semua akun hasil registrasi mandiri masuk ke halaman daftar "Akun Pending". Admin berhak memverifikasi (mengubah status menjadi aktif/verified) atau memblokir/menolak pendaftaran tersebut agar tidak masuk ke sistem. 

## 3. Kebutuhan Frontend (UI/UX & Validasi)
- **File Rujukan:** `resources/views/admin/user-management.blade.php`
- **Komponen UI Utama:**
  - Tabel Daftar Akun yang difilter dengan kondisi `status == 'pending'`.
  - Tombol Setujui (`primary-button`) dan Tolak (`danger-button`).

## 4. Kebutuhan Backend (Controller & DB)
- **Controller:** `AdminUserManagementController.php`
- **Method:** `verifyUser($user_id)` dan `rejectUser($user_id)`
- **Validasi Server:** Memastikan kueri hanya menyasar baris pengguna berstatus `pending`.
- **Proses DB:** Mengubah nilai di kolom `status` pada tabel `users`.
  - Jika diverifikasi: `status = 'verified'`.
  - Jika ditolak: `status = 'rejected'` (atau menghapus datanya).

## 5. Aturan Penolakan / Edge Cases
- Akun berstatus `pending` atau `rejected` yang mencoba masuk di halaman Login (AUTH-02) secara paksa tidak akan pernah diizinkan membuat sesi (*session*).

## 6. Penjelasan Rinci Cara Kerja (Pseudocode & Logika)

### Routing (`routes/web.php`)
```php
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/users', [AdminUserManagementController::class, 'index'])->name('admin.users.index');
    Route::patch('/admin/users/{id}/verify', [AdminUserManagementController::class, 'verifyUser'])->name('admin.users.verify');
    Route::patch('/admin/users/{id}/reject', [AdminUserManagementController::class, 'rejectUser'])->name('admin.users.reject');
});
```

### Logika Eksekusi di Controller (`AdminUserManagementController@verifyUser`)
1. **Verifikasi:**
   ```php
   $user = User::findOrFail($id);
   if ($user->status !== 'pending') {
       return back()->withErrors('Akun sudah diproses sebelumnya.');
   }
   
   $user->status = 'verified'; // Akun aktif
   $user->save();
   
   return back()->with('success', 'Akun pengguna disetujui.');
   ```
2. **Penolakan (`rejectUser`):**
   ```php
   $user = User::findOrFail($id);
   $user->status = 'rejected'; // Atau langsung $user->delete() sesuai selera.
   $user->save();
   
   return back()->with('success', 'Pendaftaran akun ditolak.');
   ```
