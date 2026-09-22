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
