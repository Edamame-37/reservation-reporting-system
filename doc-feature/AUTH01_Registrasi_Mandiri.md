# AUTH-01: Registrasi Mandiri Pengguna

## 1. Meta Informasi
- **Aktor:** Pengunjung (Calon Pengguna)
- **Ekuivalen User Story:** - (Fundamental berdasarkan PDF)
- **Modul:** Autentikasi & Keamanan

## 2. Deskripsi Alur Bisnis
Pengunjung web yang belum memiliki akun dapat melakukan registrasi secara mandiri. Setelah form dikirimkan, akun akan tercipta di *database* namun tidak dapat langsung digunakan untuk masuk (login). Akun akan berstatus **Pending** hingga diverifikasi oleh Admin.

## 3. Kebutuhan Frontend (UI/UX & Validasi)
- **File Rujukan:** `resources/views/auth/register.blade.php`
- **Form Input:**
  - Nama Lengkap (Teks)
  - Alamat Email (Email)
  - Password (Katasandi)
  - Konfirmasi Password (Katasandi)
- **Validasi Klien:** Pastikan kolom terisi (HTML5 `required`), format email benar, dan panjang password minimal 8 karakter.
- **State Halaman:** Jika berhasil registrasi, arahkan pengguna ke halaman `/login` dengan pesan sukses ("Akun berhasil dibuat. Silakan tunggu verifikasi admin.").

## 4. Kebutuhan Backend (Controller & DB)
- **Controller:** `app/Http/Controllers/Auth/RegisteredUserController.php`
- **Method:** `store(Request $request)`
- **Database / Relasi:** Tabel `users`. Saat baris baru disimpan, peran (*role*) diset secara otomatis sebagai `pengguna` dan kolom status akun (misal: `is_verified` atau `status`) diset menjadi `pending`.
- **Validasi Server:**
  - `name`: string, max 255.
  - `email`: email, unique di tabel users.
  - `password`: confirmed, min 8.

## 5. Aturan Penolakan / Edge Cases
- **Email Sudah Digunakan:** Server melempar *HTTP 422* dengan pesan bahwa email telah terdaftar.
- **Login Langsung Setelah Register:** Secara *default*, Laravel Breeze otomatis melakukan sesi login sesaat setelah daftar. **Hal ini WAJIB DIMATIKAN** dari logika *controller* Breeze, karena akun baru wajib menunggu persetujuan verifikasi Admin.

## 6. Penjelasan Rinci Cara Kerja (Pseudocode & Logika)

### Routing (`routes/web.php` atau `routes/auth.php`)
```php
Route::get('/register', [RegisteredUserController::class, 'create'])->middleware('guest')->name('register');
Route::post('/register', [RegisteredUserController::class, 'store'])->middleware('guest');
```

### Logika Eksekusi di Controller (`RegisteredUserController@store`)
1. **Validasi Input:** Jalankan fungsi `request()->validate()` untuk `name`, `email` (harus `unique`), dan `password`.
2. **Pembuatan Pengguna:**
   ```php
   $user = User::create([
       'name' => $request->name,
       'email' => $request->email,
       'password' => Hash::make($request->password),
       'status' => 'pending' // STATUS AKUN TERKUNCI (US-15)
   ]);
   ```
3. **Pemberian Peran:** Panggil paket Spatie `$user->assignRole('pengguna');`.
4. **Mencegah Auto-Login:** Pastikan BUKAN menggunakan fungsi `Auth::login($user)`.
5. **Pengalihan (Redirect):** Arahkan pengguna kembali dengan `return redirect()->route('login')->with('success', 'Akun terdaftar, menunggu persetujuan Admin.');`

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
