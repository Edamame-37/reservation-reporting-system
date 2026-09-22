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
