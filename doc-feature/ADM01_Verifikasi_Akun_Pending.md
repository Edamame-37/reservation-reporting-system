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
