# PUB-02: Pencarian & Penyaringan Katalog Fasilitas

## 1. Meta Informasi
- **Aktor:** Pengunjung (Tanpa Login) & Semua Pengguna
- **Ekuivalen User Story:** US-2
- **Modul:** Area Publik

## 2. Deskripsi Alur Bisnis
Di halaman Katalog, pengunjung yang mencari ruang tertentu dapat menggunakan kotak pencarian (untuk nama) atau fitur saringan (*filter*) untuk menyempitkan daftar fasilitas berdasarkan kriteria: Tipe (misal: Laboratorium, Kelas), Lokasi Gedung, atau Kapasitas Minimum orang. Daftar fasilitas akan termuat ulang sesuai kriteria.

## 3. Kebutuhan Frontend (UI/UX & Validasi)
- **File Rujukan:** `resources/views/public/catalog.blade.php`
- **Form Input:** 
  - Input Teks (Pencarian Nama)
  - Select / Dropdown (Tipe Fasilitas)
  - Input Angka (Kapasitas Min/Max)
- **Interaksi (Opsional namun disarankan):** Menggunakan *Alpine.js* atau sekadar *form GET submit* biasa untuk me-*refresh* halaman saat filter diubah.

## 4. Kebutuhan Backend (Controller & DB)
- **Controller:** (Sama dengan PUB-01) `PublicFacilityController.php`
- **Method:** `index(Request $request)`
- **Kueri Data:** Menambahkan kondisi `where` yang fleksibel (*dynamic querying*).
  - Jika ada `$request->type`, tambahkan `where('type', $request->type)`.
  - Jika ada `$request->capacity`, tambahkan `where('capacity', '>=', $request->capacity)`.
  - Jika ada `$request->search`, gunakan kueri SQL `LIKE '%...%'` pada kolom nama/deskripsi.

## 5. Aturan Penolakan / Edge Cases
- **Hasil Kosong:** Jika kriteria *filter* terlalu spesifik sehingga tidak ada satupun fasilitas yang cocok, antarmuka harus memunculkan ilustrasi/teks cantik "Fasilitas tidak ditemukan".

## 6. Penjelasan Rinci Cara Kerja (Pseudocode & Logika)

### Logika Eksekusi di Controller (`PublicFacilityController@index`)
Pencarian berjalan di rute `GET /catalog` yang sama, namun dengan tambahan penangkapan *Query String*.

1. **Pembuatan Objek Query (Eloquent):**
   ```php
   $query = Facility::where('status_aktif', 'aktif');
   ```
2. **Filter Dinamis (Tipe & Kapasitas):**
   ```php
   if ($request->has('type') && $request->type != '') {
       $query->where('type', $request->type);
   }
   if ($request->has('min_capacity') && $request->min_capacity != '') {
       $query->where('capacity', '>=', $request->min_capacity);
   }
   ```
3. **Pencarian Kata Kunci (Nama):**
   ```php
   if ($request->has('search') && $request->search != '') {
       $query->where('name', 'LIKE', '%' . $request->search . '%');
   }
   ```
4. **Paginasi & Tampilan:**
   ```php
   $facilities = $query->paginate(9); // Maksimal 9 kartu per halaman
   return view('public.catalog', compact('facilities'));
   ```
5. **UI Pengunjung:** Pastikan form pencarian menggunakan `GET` metode sehingga ketika URL di-*copy*, filternya tetap terbawa. Inputan lama (`old('search')`) harus tetap bertengger di kotak pencarian.

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

## 8. Catatan Penyesuaian Tambahan (Diisi oleh Programmer)
*(Bagian ini wajib diisi jika Anda melakukan penyesuaian/improvisasi yang berbeda dari Blueprint di atas selama proses koding! Kosongkan jika tidak ada).*

- ...
