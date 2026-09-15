# Aturan dan Tata Tertib Programming Proyek (*Project Rules*)

Dokumen ini adalah hukum dasar pengembangan yang **wajib ditaati oleh seluruh anggota tim programmer**. Setiap pelanggaran pada aturan ini berisiko besar menciptakan konflik kode (*merge conflict*) yang sulit diselesaikan, atau bahkan merusak keseluruhan sistem.

---

## 1. Aturan Batas Wilayah Kerja (Frontend vs Backend)
Setiap pengembang harus disiplin dan sadar akan batasan wilayah *file* yang boleh mereka sentuh.
(Lihat peta struktur folder pada file `development_plan.md`).

*   **Aturan Frontend:**
    *   Hanya diizinkan menyentuh *file* yang berada di dalam folder `resources/views/` (File `.blade.php`), `public/`, serta mengelola logika interaktif di *client-side* (Alpine.js / Tailwind CSS).
    *   DILARANG KERAS mengutak-atik logika *database* di dalam *Controller*, *Model*, atau *Route* utama.

*   **Aturan Backend:**
    *   Hanya diizinkan mengedit *file* di dalam direktori `app/Http/Controllers/`, `app/Models/`, `routes/web.php`, `database/migrations/`, dan logika inti server lainnya.
    *   DILARANG KERAS merombak kode HTML, *Class* warna Tailwind, atau *layout* tampilan di dalam *file views*.

*   **Larangan Saling Silang (*Cross-Editing*):**
    *   **Jangan pernah** secara sepihak mengedit file di luar *role* Anda! Jika Anda adalah *Frontend* dan merasa ada yang kurang di logika *Backend*, **Anda tidak boleh** langsung memodifikasi *Controller*-nya.
    *   **Solusi:** Komunikasikan! (Contoh: *"Bro (Backend), tolong tambahkan field nomor HP di endpoint API registrasi ya, form-ku butuh datanya."*)
    *   Masing-masing pihak mempersiapkan persyaratannya di jalurnya sendiri tanpa menabrak kodingan orang lain.

## 2. Kepatuhan Resolusi Data (API & Endpoint)
*   **Koordinasi Endpoint:** Jika halaman *Frontend* membutuhkan data baru dari *database*, diskusikan format data (JSON/variabel) yang dibutuhkan kepada *Backend*. 
*   **Penggunaan Mock Data:** Selagi menunggu tim *Backend* menyelesaikan kueri API, tim *Frontend* wajib menggunakan data statis/palsu (*mock data*) terlebih dahulu di HTML-nya untuk menguji tampilan.

## 3. Aturan Emas Penggunaan Git & GitHub
Wajib mengacu dan tunduk sepenuhnya pada dokumen `github_guide.md`.
*   **Larangan `git add .`:** JANGAN PERNAH MENGETIKKAN `git add .` di terminal Anda. Tambahkan *file* secara spesifik (satu per satu) untuk mencegah masuknya *file* sampah (seperti `.env`) atau *file* yang tidak disengaja.
*   **Aturan Branch:** Dilarang menaruh kode kerjaan di cabang `main`. Setiap pekerjaan fitur mutlak dilakukan di *branch* masing-masing sesuai hierarki (misal: `feature/...`).
*   **Aturan Bahasa Commit:** Biasakan menulis *commit message* menggunakan pola standar (`feat:`, `fix:`, dsb.) dengan **Bahasa Indonesia** sebagai bahasa dasar penjelasannya. Gunakan bahasa Inggris **hanya untuk istilah asing spesifik** yang memang tidak bisa diterjemahkan (contoh: *API endpoint*, *dropdown*, *lockForUpdate*).

## 4. Kepatuhan Pada Prosedur Kerja & Blueprint
*   **SOP Pembuatan Antarmuka:** Setiap pembuatan UI atau perancangan halaman baru, wajib mengikuti urutan siklus dari `project_plan.md` (Tahap Perancangan ➔ *Slicing* HTML ➔ Pemecahan *Blade Component* ➔ Integrasi API Backend ➔ Penggantian *Mock Data* ➔ QC).
*   **Gunakan Template (D.R.Y - *Don't Repeat Yourself*):** Jika ada elemen visual yang sering diulang (seperti Tombol, *Navbar*, *Card*), Frontend WAJIB memisahkannya menjadi *Blade Components* (`<x-nama-komponen>`). Jangan membuang waktu melakukan *Copy-Paste* elemen HTML ribuan baris.

## 5. Kebersihan Kode (*Clean Code*)
*   **Hapus Komentar Sampah:** Jangan meninggalkan kodingan "eksperimen" yang dimatikan (*commented-out*) berserakan di dalam proyek sebelum didorong (*push*) ke GitHub. Hapus kodingan mati tersebut!
*   **Indentasi & Kerapian:** Format *file* Anda (gunakan pintasan *Shift+Alt+F* di VS Code) sebelum menyimpannya agar tulisan *tag* HTML dan PHP tidak rata kiri semua dan menyiksa mata *programmer* selanjutnya yang membaca.
