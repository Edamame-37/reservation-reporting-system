# Aturan & Panduan Pengembangan Frontend (*Frontend Rules*)

Dokumen ini memuat panduan komprehensif, dari tingkat fundamental hingga teknikal, yang **wajib ditaati** oleh seluruh pihak yang berkontribusi pada *Frontend*, baik Programmer (Manusia) maupun Agen AI. Tujuannya adalah memastikan antarmuka (UI/UX) dibangun secara konsisten, aman, minim hambatan, dan mudah dikelola (*maintainable*).

---

## BAGIAN 1: ATURAN KHUSUS UNTUK PROGRAMMER (MANUSIA)

Aturan di bawah ini khusus ditekankan bagi *programmer* manusia untuk menjaga etika kerja, pemahaman terhadap *codebase*, dan kelancaran kolaborasi tim.

### 1. Pemahaman Kode Mutlak (*No Blind Copy-Paste*)
Programmer **WAJIB** memahami 100% alur logika dan cara kerja dari setiap baris kode yang ditulis atau disalin (baik dari referensi luar, StackOverflow, maupun di-*generate* oleh AI). Dilarang keras menyisipkan skrip atau elemen HTML ajaib (*magic code*) ke dalam antarmuka tanpa mengetahui persis bagaimana dampaknya terhadap performa (DOM) dan keamanan (XSS) sistem.

### 2. Alur Koordinasi Komunikasi dengan Tim Backend
- **Batas Teritorial Wilayah Kerja:** *Frontend* hanya diizinkan memodifikasi *file* yang berada di dalam folder `resources/views/`, `public/`, direktori *asset*, atau mengonfigurasi `tailwind.config.js`. Anda DILARANG mengubah file `Controllers`, `Models`, dan `Routes` web utama kepunyaan tim *Backend*!
- **Komunikasi Suplai Data (API/Variabel):** Jika hasil desain Anda menuntut adanya data spesifik (misalnya, butuh nama Petugas yang menyetujui), **JANGAN** membuat fungsi penarik data SQL sendiri di file Blade. Laporkan ke *Backend* (*"Halo tim Backend, tolong sertakan variabel nama_petugas di fungsi index"*) lalu siapkan *Mock Data* (teks tiruan sementara) sembari menunggu *Backend* rampung.

### 3. Tata Tertib Umum Git & Eksekusi Perintah AI (Seluruh Tim Manusia)
- **Eksekusi & Evaluasi Perintah AI:** Saat Agen AI memberikan teks perintah `git add` dan `git commit` di akhir percakapan, Anda **WAJIB** mengecek dan memastikan kebenarannya terlebih dahulu. Pastikan pesan *commit* dan daftar *file* sudah relevan dengan apa yang dikerjakan sebelum Anda menjalankannya di terminal.
- **Pantangan `git add .`:** Wajib *commit* secara spesifik per *file*.
- **Bahasa Commit:** Sesuai panduan di `GUIDE_GITHUB.md`, gunakan pola (seperti `feat:`, `fix:`, `docs:`, `ui:`) dengan penjelasan **Bahasa Indonesia**. Bahasa Inggris eksklusif hanya untuk penamaan teknis.
- **Prosedur GitHub:** Dilarang mendorong (*push*) langsung ke *branch* `main`. Selalu kerjakan melalui *branch* fitur turunan (`feature/...`) dan serahkan integrasi akhir melalui *Pull Request*.

### 4. Prosedur Wajib Interaksi dengan Agen AI
- **Penyertaan Konteks Absolut:** Setiap kali Anda memulai percakapan (*chat*) dengan AI untuk membuat/mengubah kode atau melakukan apa pun, Anda **WAJIB** melampirkan seluruh dokumen arsitektur (`.md`). Berikut daftar *file* yang wajib disertakan beserta fungsinya:
  1. `CASE_PROJECT.md` (Aturan dan logika bisnis inti).
  2. `PLAN_PROJECT.md` & `PLAN_DEVELOPMENT.md` (Fase dan pembagian tugas).
  3. `TECHSTACK.md` (Panduan spesifikasi standar teknologi).
  4. `GUIDE_GITHUB.md` (Panduan kolaborasi dan komit Git).
  5. `RULE_FRONTEND.md` (Hukum dan panduan mutlak koding).
- **Verifikasi Kritis Dokumen Perencanaan:** Jangan pernah menerima secara buta (*asal accept*) terhadap artefak `implementation_plan.md` buatan AI. Anda **WAJIB** mengecek apakah rencananya logis dan relevan. Jika di dalamnya terdapat permintaan instruksi perintah (*run command terminal*), periksa dengan teliti perintah apa itu sebelum Anda mengizinkannya.
- **Verifikasi Pasca-Eksekusi:** Anda **WAJIB** membaca artefak `walkthrough.md` buatan AI setiap kali AI selesai memodifikasi kode, guna memastikan apa yang telah diselesaikan.

### 5. Prosedur Pengujian (*Testing*) & Eskalasi Bug
- **Mandatori Pengujian:** Anda **WAJIB** menguji (*test*) UI/UX secara manual setiap kali selesai melakukan penambahan atau modifikasi komponen apa pun.
- **Isolasi Kegagalan (Anti-Push Develop):** JANGAN PERNAH melakukan *push* ke *branch* `develop` jika tampilan/kodingan masih rusak (*error*)! Jika terjadi masalah, cobalah untuk memperbaikinya secara mandiri terlebih dahulu. Jika sudah buntu (mentok), segera alihkan atau buat cabang baru khusus perbaikan (`bugfix/...`), *push* kondisi *error* tersebut ke *branch* `bugfix` itu, lalu segera kabari *Project Manager* agar dicarikan solusi silang.

---

## BAGIAN 2: ATURAN KHUSUS UNTUK AGEN AI (*ASSISTANT*)

Aturan di bawah ini mutlak harus diikuti oleh AI (Agen *coding*) yang membantu proyek ini agar tidak merender HTML raksasa yang tidak efisien (*DOM Bloat*).

### 1. Aturan Optimasi & Minimasi Kode (Ponytail Methodology)
Untuk mencegah *over-engineering* dan lambatnya waktu *render* web, Agen AI **WAJIB** menerapkan metodologi optimasi "Ponytail" (*Write one line. It works.*). Terapkan "Tangga Keputusan" berikut sebelum men-generate elemen antarmuka baru:
1. **YAGNI (*You Aren't Gonna Need It*):** Apakah komponen UI atau animasi ekstra ini benar-benar krusial? Jika hanya memperberat *DOM*, abaikan/buang.
2. **Daur Ulang (*Reuse Code*):** Gunakan kembali *Blade Components* (`<x-button>`, `<x-card>`) yang sudah ada. Jangan pernah *copy-paste* blok HTML mentah secara redundan.
3. **Pemanfaatan Fitur *Native* (Sangat Penting):** Sebisa mungkin gunakan tag HTML *native* bawaan *browser* daripada menginstal dependensi JS raksasa. Contoh: Daripada memuat *library Datepicker* JavaScript berat, gunakan tag HTML5 murni `<input type="date">` atau `<input type="time">`.
4. **Gunakan Depedensi yang Ada:** Manfaatkan *Tailwind CSS* dan direktif *Alpine.js* (`x-data`, `x-show`) secara maksimal. Dilarang mengimpor library luar seperti jQuery.
5. **The Minimum That Works:** Tulis struktur *Tailwind* seefisien mungkin (hindari kelas bertumpuk jika tidak ada efeknya).

> **⚠️ CATATAN KEAMANAN BAGI AI:** Meskipun komponen HTML dituntut ramping dan minimalis, AI **TIDAK BOLEH MENGURANGI** aspek vital perlindungan pengguna, seperti: pesan *error* validasi, pelarian string (*string escaping* untuk XSS `{{ }}`), status *loading*, dan standar aksesibilitas dasar (UX/UI). *Lazy on solution, never lazy on user experience!*

### 2. Batas Teritorial Wilayah Kerja AI
Agen AI dilarang menyentuh, mengedit, atau menulis ulang kueri SQL di dalam *Controller* atau *Model* jika tugas/prompt pengguna murni meminta perbaikan atau pembuatan halaman tampilan (*View/Frontend*).

### 3. Tata Tertib Pemberian Perintah Git (AI Dilarang Eksekusi Langsung)
Setiap kali Anda selesai melakukan perubahan pada kode/file, Anda **DILARANG KERAS** menjalankan (*run*) langsung perintah git menggunakan tool *run_command*. Sebagai gantinya, Anda **WAJIB** mencetak/menuliskan teks perintah `git add <nama_file>` dan `git commit -m "pesan commit"` secara terpisah (satu persatu per file) di akhir balasan Anda. Format *commit* harus patuh pada `GUIDE_GITHUB.md`. Berikan teks perintah tersebut agar *Programmer* (Manusia) sendiri yang mengeksekusinya.

### 4. Pola Pikir & Tanggung Jawab Kepemimpinan Teknis (AI sebagai Senior Dev)
- **Pemahaman Sistem Menyeluruh:** AI tidak hanya diwajibkan memuntahkan antarmuka, melainkan harus benar-benar paham secara mendalam terhadap alur kerja sistem secara menyeluruh (*big picture*) dan mengerti laju log keberjalanannya.
- **Persona *Senior Developer*:** AI **WAJIB** selalu bertindak, berpikir, merakit UI, menguji, dan menjelaskan segala sesuatunya selayaknya seorang *Senior Frontend Developer*. Bimbinglah pengguna (Programmer Manusia) layaknya Anda membantu dan menuntun seorang *Junior Developer* atau anak magang (*Intern*). Jelaskan dengan sabar dan arahkan ke praktik terbaik (*best practices*).
- **Kepatuhan Mutlak Pada Dokumen Panduan:** AI **WAJIB** selalu tunduk pada aturan dan *plan* yang telah ditetapkan pada *file-file* `.md`. Jangan pernah keluar jalur. Jika muncul kasus anomali yang menuntut pelanggaran aturan/rencana, atau jika ada kondisi di luar skenario panduan, AI **WAJIB MENGHENTIKAN EKSEKUSI** dan meminta konfirmasi/izin eksplisit dari *Programmer* (Manusia) terlebih dahulu!

### 5. Kewajiban Pembuatan Artefak Perencanaan & Laporan (Wajib!)
- **Pra-Eksekusi (`implementation_plan.md`):** Sebelum Anda mulai membuat atau mengedit *source code* proyek, Anda **WAJIB** menyusun langkah kerja ke dalam artefak `implementation_plan.md` terlebih dahulu. Hal ini wajib dilakukan agar *Programmer* dapat membaca, mengawasi, dan memastikan terkait apa saja yang akan dikerjakan oleh Anda.
- **Pasca-Eksekusi (`walkthrough.md`):** Setiap kali Anda selesai membangun atau memodifikasi tampilan, Anda **WAJIB** membuat atau memperbarui artefak `walkthrough.md`. Dokumen ini bertindak sebagai laporan yang memberi informasi tentang bagian mana yang telah dimodifikasi, cara manusia mengujinya (*testing guide*), serta memberi arahan *"what to do next"* (apa langkah selanjutnya yang perlu dilakukan oleh Programmer jika uji coba modifikasi tersebut telah berjalan lancar).

### 6. Tata Tertib Modifikasi Kode (Manual Edit Tanpa Script Eksternal)
Agen AI **WAJIB** melakukan pengeditan atau pembuatan kode secara langsung dan manual. AI **DILARANG KERAS** menggunakan, membuat, atau memanggil skrip otomatis eksternal (seperti script Python, Bash shell, PowerShell, dsb) untuk mengedit atau merombak kode. Setiap perubahan wajib dilakukan langsung pada file target.

---

## BAGIAN 3: ATURAN BERSAMA (BERLAKU UNTUK MANUSIA & AI)

Aturan konseptual antarmuka ini wajib dipraktikkan baik saat programmer mendesain maupun saat AI membangun struktur *layout*.

### 1. Fokus Pada Pengguna (*User-Centric*)
Antarmuka adalah etalase dan wajah sistem. Prioritaskan kejelasan informasi, kemudahan navigasi (usabilitas), dan responsivitas interaksi. Setiap isian yang berpotensi *error* WAJIB diberikan umpan balik (*feedback*) visual yang jelas.

### 2. Arsitektur Berbasis Komponen (*Component-Driven & D.R.Y*)
Jangan pernah membuang waktu menulis kode HTML yang sama (seperti form input, Tombol, *Card*, atau *Navbar*) berulang kali secara *hardcode*. Pecah elemen visual menjadi **Blade Components** (contoh: `<x-button>`).

### 3. Pemisahan Tanggung Jawab (*Separation of Concerns*)
*Frontend* hanya dan mutlak mengurus presentasi visual (HTML/Tailwind) dan interaktivitas ringan di sisi peramban (Alpine.js). **DILARANG KERAS** menyisipkan operasi kueri *database* mentah atau logika bisnis berat secara langsung di dalam *file* `.blade.php`.

### 4. Aspek Teknikal (Koding & Standarisasi UI)
- **Disiplin Tailwind CSS:** Hindari penggunaan *magic numbers* (nilai *custom* sembarangan seperti `w-[13px]`). Manfaatkan sistem *spacing* bawaan Tailwind atau *palette* di `tailwind.config.js`.
- **Pendekatan *Mobile-First*:** Koding untuk tampilan layar HP/kecil terlebih dahulu secara alami (kolom ke bawah), baru manfaatkan *breakpoint* (`md:`, `lg:`) untuk pelebaran horizontal di PC/*desktop*.
- **Konsistensi Logika Alpine.js:** Untuk animasi *toggle*, *modal*, atau *tabs* sederhana, wajib gunakan Alpine.js. Jangan gunakan *Vanilla Javascript* manual atau *Event Listener* terpisah di bagian `<script>` kecuali untuk manipulasi *library* kompleks (seperti FullCalendar).
- **Validasi Visual (*Feedback*):** Setiap *input form* harus mampu menangkap variabel bawaan Laravel (`$errors`) dari *Backend* dan mencetak *feedback* (misal: tulisan merah peringatan di bawah kotak form).

### 5. Aturan Ketat Dokumentasi Kode (*Strict Commenting Rule*)

Seluruh pengembang (Manusia dan AI) diwajibkan mematuhi format komentar standar HTML/Blade/JS berikut beserta contoh implementasinya.

**A. Kewajiban Komentar Header File**
Setiap file tampilan `.blade.php` atau komponen Vue/JS **WAJIB** diawali dengan *Header Comment* di baris paling atas.
*Format Terstandarisasi:*
```blade
{{-- 
  NAMA FILE    : user-dashboard.blade.php
  FUNGSIONALITAS  : Halaman Antarmuka Dasbor Utama Pengguna Mahasiswa
  DESKRIPSI    : Menampilkan ringkasan pinjaman fasilitas yang aktif, riwayat laporan, dan tombol cepat (Quick Actions).
  CARA KERJA   : Menerima array $activeReservations dan $recentReports dari Controller. Merender UI kalender ringan menggunakan Alpine.js.
--}}
```

**B. Kewajiban Komentar Routing (Form Action / Fetch API)**
Saat HTML/JS melakukan interaksi komunikasi pemanggilan ke Backend (misal `<form action="...">` atau `fetch/axios`), **WAJIB** ada komentar penjelas.
*Format Terstandarisasi:*
```blade
<!-- 
  ROUTE: Mengirimkan form data input via POST (ID Fasilitas dan Rentang Waktu) ke /reservasi.
  FUNGSI: Mengajukan pemesanan baru yang akan dievaluasi oleh sistem Backend.
-->
<form action="/reservasi" method="POST">
```

**C. Kewajiban Komentar Blok Elemen (UI) / Logika Alpine.js**
Setiap memisahkan atau merakit balok *layout* krusial (seperti Modal Konfirmasi, Form, atau Sidebar Alpine.js), berikan penjelas cara kerjanya.
*Format Terstandarisasi:*
```blade
<!-- 
  ELEMEN       : Modal Konfirmasi Pembatalan
  KEGUNAAN     : Memberikan layar konfirmasi akhir sebelum reservasi benar-benar dibuang (Mencegah salah klik).
  CARA KERJA   : Tersembunyi secara default (x-data="isModalOpen = false"). Ketika diklik 'Ya, Batalkan', tombol di dalamnya men-trigger method fetch() pembatalan via AJAX.
-->
<div x-data="{ isModalOpen: false }">
    ...
</div>
```

> **⚠️ PERINGATAN REVISI KOMENTAR (MANUSIA & AI):** Jika bentuk komponen UI atau cara kerjanya diubah, Anda **MUTLAK WAJIB** memperbarui komentar HTML di atasnya agar terus relevan!
