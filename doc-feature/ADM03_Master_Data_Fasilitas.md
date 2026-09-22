# ADM-03: Master Data Fasilitas (CRUD)

## 1. Meta Informasi
- **Aktor:** Admin
- **Ekuivalen User Story:** US-16
- **Modul:** Master Data Management (Konsol Admin)

## 2. Deskripsi Alur Bisnis
Sebagai otak tata kelola aplikasi, Admin berhak menambah ruangan baru, memperbaiki keterangan/kapasitas ruangan, atau menonaktifkan ruangan yang sudah dialihfungsikan agar lenyap dari Katalog Publik (PUB-01). Ini adalah siklus lengkap *Create, Read, Update, Delete* (CRUD).

## 3. Kebutuhan Frontend (UI/UX & Validasi)
- **File Rujukan:** `resources/views/admin/facility-master.blade.php`
- **Komponen UI Utama:**
  - Tabel Daftar Master Fasilitas (Bukan sekadar galeri, tapi format baris kaku).
  - Form (Modal) Tambah/Edit.
- **Form Input (Fasilitas):**
  - Nama Fasilitas.
  - Tipe Fasilitas (Dropdown: Kelas, Laboratorium, Aula, Lapangan).
  - Lokasi/Gedung.
  - Kapasitas (Input Angka).
  - Deskripsi Fasilitas.
  - Upload Foto Cover (Gambar).

## 4. Kebutuhan Backend (Controller & DB)
- **Controller:** `FacilityController.php` (Domain Admin).
- **Method:** `index()`, `store()`, `update()`, `destroy()`.
- **Manajemen Gambar (File System):** Upload file gambar fasilitas ke penyimpanan server lokal di direktori `/public/storage/facilities/`. Jika gambar diedit, hapus file gambar fisik lama untuk menghemat kapasitas (*disk space*).
- **Penghapusan Lunak (Soft Delete):** Jangan menggunakan SQL `DELETE` asli (*Hard delete*), tapi gunakan fitur *SoftDeletes* Laravel. Jika dihapus asli, riwayat reservasi yang menautkan `facility_id` tersebut di masa lalu (tabel `reservations`) akan hancur/error.

## 5. Aturan Penolakan / Edge Cases
- Jika nama fasilitas yang sama (contoh: "Lab Komputer A") diketikkan lagi di formulir tambah, validasi `unique:facilities,name` akan melempar pesan *error* agar tidak terjadi data ganda.

## 6. Penjelasan Rinci Cara Kerja (Pseudocode & Logika)

### Routing (`routes/web.php`)
```php
Route::middleware(['auth', 'role:admin'])->group(function () {
    // Resource route meng-generate index, create, store, edit, update, destroy
    Route::resource('admin/facilities', FacilityController::class)->except(['show']);
});
```

### Logika Eksekusi di Controller (`FacilityController@store`)
1. **Validasi File:**
   ```php
   $request->validate([
       'name' => 'required|string|unique:facilities,name',
       'type' => 'required|in:Kelas,Laboratorium,Aula,Lapangan',
       'capacity' => 'required|integer|min:1',
       'cover_image' => 'required|image|mimes:jpeg,png,jpg|max:2048'
   ]);
   ```
2. **Unggah Foto:**
   ```php
   $path = $request->file('cover_image')->store('public/facilities');
   ```
3. **Simpan DB:**
   ```php
   Facility::create([...$request->all(), 'cover_image' => $path, 'status_aktif' => 'aktif']);
   return back()->with('success', 'Fasilitas baru ditambahkan!');
   ```

### Logika Penghapusan Lunak (`FacilityController@destroy`)
```php
// Pastikan model Facility menggunakan trait `SoftDeletes` di app/Models/Facility.php
$facility = Facility::findOrFail($id);
$facility->status_aktif = 'dihapus'; // Flag kustom untuk aplikasi
$facility->delete(); // Memicu SoftDeletes Laravel (isi deleted_at)
return back()->with('success', 'Fasilitas dicabut dari peredaran.');
```
