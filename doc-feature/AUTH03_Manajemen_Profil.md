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
