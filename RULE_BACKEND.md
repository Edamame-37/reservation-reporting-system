# Aturan & Panduan Pengembangan Backend (*Backend Rules*)

Dokumen ini memuat panduan komprehensif, dari tingkat fundamental hingga teknikal, yang **wajib ditaati** oleh seluruh pihak yang berkontribusi pada *Backend*, baik Programmer (Manusia) maupun Agen AI. Integritas data, keamanan sistem, dan efisiensi memori (*memory footprint*) adalah hal yang mutlak dan tidak bisa ditawar.

---

## BAGIAN 1: ATURAN KHUSUS UNTUK PROGRAMMER (MANUSIA)

Aturan di bawah ini khusus ditekankan bagi *programmer* manusia untuk menjaga etika kerja, pemahaman terhadap *codebase*, dan kelancaran kolaborasi tim.

### 1. Pemahaman Kode Mutlak (*No Blind Copy-Paste*)
Programmer **WAJIB** memahami 100% alur logika dan cara kerja dari setiap baris kode yang ditulis atau disalin (baik dari referensi luar, StackOverflow, maupun di-*generate* oleh AI). Dilarang keras menyisipkan kode ajaib (*magic code*) ke dalam sistem tanpa mengetahui persis bagaimana kueri tersebut beroperasi dan apa dampaknya.

### 2. Alur Koordinasi Komunikasi dengan Tim Frontend
- **Batas Teritorial Wilayah Kerja:** *Backend* diizinkan mengeksekusi perombakan di ranah direktori `app/`, `routes/`, `database/`, dan `config/`. **DILARANG KERAS** menyentuh estetika kode visual `.blade.php` atau algoritma komponen *Alpine/Tailwind* kepunyaan *Frontend* jika tidak diminta.
- **Prinsip Melayani (*Data Provider*):** Tugas utama *Backend* adalah *"melayani"* kebutuhan informasi *Frontend*. Desain variabel `compact()` atau *Endpoint API* dengan format struktur data (seperti format *Array/JSON Object*) yang paling logis, konsisten, dan mudah di-*looping* agar pekerjaan kawan *Frontend* tidak menyiksa.
- **Kolaborasi Respons Kegagalan:** Jika penahanan *FormRequest* digagalkan oleh sistem *Backend* Anda, sepakati terlebih dahulu bagaimana format *response* yang akan dikembalikan (`return back()->withErrors()`) agar *Frontend* siap mencegatnya dan mewarnai kotak isian *user* dengan warna merah.

### 3. Tata Tertib Umum Git (Seluruh Tim Manusia)
- **Eksekusi & Evaluasi Perintah AI:** Saat Agen AI memberikan teks perintah `git add` dan `git commit` di akhir percakapan, Anda **WAJIB** mengecek dan memastikan kebenarannya terlebih dahulu. Pastikan pesan *commit* dan daftar *file* sudah relevan dengan apa yang dikerjakan sebelum Anda menjalankannya di terminal.
- **Pantangan `git add .`:** Wajib *commit* secara spesifik per *file*.
- **Bahasa Commit:** Sesuai panduan di `GUIDE_GITHUB.md`, gunakan pola (seperti `feat:`, `fix:`, `docs:`, `refactor:`) dengan penjelasan **Bahasa Indonesia**. Bahasa Inggris eksklusif hanya untuk penamaan teknis.
- **Prosedur GitHub:** Dilarang mendorong (*push*) langsung ke *branch* `main`. Selalu kerjakan melalui *branch* turunan terkait (`feature/..` atau `bugfix/..`) dan gabungkan melalui *Pull Request*.

### 4. Prosedur Wajib Interaksi dengan Agen AI
- **Penyertaan Konteks Absolut:** Setiap kali Anda memulai percakapan (*chat*) dengan AI untuk membuat/mengubah kode atau melakukan apa pun, Anda **WAJIB** melampirkan seluruh dokumen arsitektur (`.md`). Berikut daftar *file* yang wajib disertakan beserta fungsinya:
  1. `CASE_PROJECT.md` (Aturan dan logika bisnis inti).
  2. `PLAN_PROJECT.md` & `PLAN_DEVELOPMENT.md` (Fase dan pembagian tugas).
  3. `TECHSTACK.md` (Panduan spesifikasi standar teknologi).
  4. `GUIDE_GITHUB.md` (Panduan kolaborasi dan komit Git).
  5. `RULE_BACKEND.md` (Hukum dan panduan mutlak koding).
- **Verifikasi Kritis Dokumen Perencanaan:** Jangan pernah menerima secara buta (*asal accept*) terhadap artefak `implementation_plan.md` buatan AI. Anda **WAJIB** mengecek apakah rencananya logis dan relevan. Jika di dalamnya terdapat permintaan instruksi perintah (*run command terminal*), periksa dengan teliti perintah apa itu sebelum Anda mengizinkannya.
- **Verifikasi Pasca-Eksekusi:** Anda **WAJIB** membaca artefak `walkthrough.md` buatan AI setiap kali AI selesai memodifikasi kode, guna memastikan apa yang telah diselesaikan.

### 5. Prosedur Pengujian (*Testing*) & Eskalasi Bug
- **Mandatori Pengujian:** Anda **WAJIB** menguji (*test*) sistem secara manual setiap kali selesai melakukan penambahan atau modifikasi kode apa pun.
- **Isolasi Kegagalan (Anti-Push Develop):** JANGAN PERNAH melakukan *push* ke *branch* `develop` jika kodingan masih *error* atau rusak! Jika terjadi masalah, cobalah untuk memperbaikinya secara mandiri terlebih dahulu. Jika sudah buntu (mentok), segera alihkan atau buat cabang baru khusus perbaikan (`bugfix/...`), *push* kondisi *error* tersebut ke *branch* `bugfix` itu, lalu segera kabari *Project Manager* agar dicarikan solusi silang.

---

## BAGIAN 2: ATURAN KHUSUS UNTUK AGEN AI (*ASSISTANT*)

Aturan di bawah ini mutlak harus diikuti oleh AI (Agen *coding*) yang membantu proyek ini agar tidak menghasilkan kodingan yang *bloatware* atau rawan diretas.

### 1. Aturan Optimasi & Minimasi Kode (Ponytail Methodology)
Untuk mencegah *over-engineering* dan membengkaknya kode (*bloatware*), Agen AI **WAJIB** menerapkan metodologi optimasi "Ponytail" (*Write one line. It works.*). Terapkan "Tangga Keputusan" berikut sebelum men-generate kode fungsi baru:
1. **YAGNI (*You Aren't Gonna Need It*):** Apakah fitur logika ini benar-benar dituntut oleh dokumen spesifikasi? Jika tidak, lewati dan jangan ditulis.
2. **Daur Ulang (*Reuse Code*):** Panggil ulang fungsi yang sudah ada di dalam *codebase* (DRY - *Don't Repeat Yourself*).
3. **Fungsi Bawaan (*Stdlib/Laravel Helper*):** Gunakan fungsi bawaan Laravel (seperti `collect()`, `Str::`, dsb) daripada merakit struktur *looping* manual yang memakan banyak baris.
4. **Dependensi Tersedia:** Gunakan fitur dari *package* yang sudah diinstal (seperti *Spatie* atau *Breeze*).
5. **The Minimum That Works:** Jika suatu algoritma bisa ditulis rapi, logis, dan terbaca hanya dalam satu baris (misal: *Ternary Operator* sederhana), lakukanlah.

> **⚠️ CATATAN KEAMANAN BAGI AI:** Meskipun baris kode dituntut minimalis ekstrem, **TIDAK BOLEH ADA PENGURANGAN** pada aspek keamanan (seperti `FormRequest` validasi input, proteksi `lockForUpdate()`, atau pengecekan *Role*). *Lazy on solution, never lazy on security!*

### 2. Batas Teritorial Wilayah Kerja AI
Agen AI hanya boleh merombak file di `app/`, `routes/`, `database/`, dan `config/` saat ditugaskan dengan konteks perbaikan *Backend*. AI dilarang mengubah file `.blade.php` milik *Frontend* secara sepihak jika instruksi pengguna murni berkaitan dengan *Backend*.

### 3. Tata Tertib Pemberian Perintah Git (AI Dilarang Eksekusi Langsung)
Setiap kali Anda selesai melakukan perubahan pada kode/file, Anda **DILARANG KERAS** menjalankan (*run*) langsung perintah git menggunakan *tool run_command*. Sebagai gantinya, Anda **WAJIB** mencetak/menuliskan teks perintah `git add <nama_file>` dan `git commit -m "pesan commit"` secara terpisah (satu persatu per file) di akhir balasan Anda. Format *commit* harus patuh pada `GUIDE_GITHUB.md`. Berikan teks perintah tersebut agar *Programmer* (Manusia) sendiri yang mengeksekusinya.

### 4. Pola Pikir & Tanggung Jawab Kepemimpinan Teknis (AI sebagai Senior Dev)
- **Pemahaman Sistem Menyeluruh:** AI tidak hanya diwajibkan memuntahkan kode, melainkan harus benar-benar paham secara mendalam terhadap alur kerja sistem secara menyeluruh (*big picture*) dan mengerti laju log keberjalanannya dari hulu ke hilir.
- **Persona *Senior Developer*:** AI **WAJIB** selalu bertindak, berpikir, menulis kode, menguji, dan menjelaskan segala sesuatunya selayaknya seorang *Senior Backend Developer*. Bimbinglah pengguna (Programmer Manusia) layaknya Anda membantu dan menuntun seorang *Junior Developer* atau anak magang (*Intern*). Jelaskan dengan sabar, arahkan ke praktik terbaik (*best practices*).
- **Kepatuhan Mutlak Pada Dokumen Panduan:** AI **WAJIB** selalu tunduk pada aturan dan *plan* yang telah ditetapkan pada *file-file* `.md`. Jangan pernah keluar jalur. Jika muncul kasus anomali yang menuntut pelanggaran aturan/rencana, atau jika ada kondisi di luar skenario panduan, AI **WAJIB MENGHENTIKAN EKSEKUSI** dan meminta konfirmasi/izin eksplisit dari *Programmer* (Manusia) terlebih dahulu!

### 5. Kewajiban Pembuatan Artefak Perencanaan & Laporan (Wajib!)
- **Pra-Eksekusi (`implementation_plan.md`):** Sebelum Anda mulai membuat atau mengedit *source code* proyek, Anda **WAJIB** menyusun langkah kerja ke dalam artefak `implementation_plan.md` terlebih dahulu. Hal ini wajib dilakukan agar *Programmer* dapat membaca, mengawasi, dan memastikan terkait apa saja yang akan dikerjakan oleh Anda.
- **Pasca-Eksekusi (`walkthrough.md`):** Setiap kali Anda selesai membangun atau memodifikasi kode, Anda **WAJIB** membuat atau memperbarui artefak `walkthrough.md`. Dokumen ini bertindak sebagai laporan yang memberi informasi tentang bagian mana yang telah dimodifikasi, cara manusia mengujinya (*testing guide*), serta memberi arahan *"what to do next"* (apa langkah selanjutnya yang perlu dilakukan oleh Programmer jika uji coba modifikasi tersebut telah berjalan lancar).

### 6. Tata Tertib Modifikasi Kode (Manual Edit Tanpa Script Eksternal)
Agen AI **WAJIB** melakukan pengeditan atau pembuatan kode secara langsung dan manual. AI **DILARANG KERAS** menggunakan, membuat, atau memanggil skrip otomatis eksternal (seperti script Python, Bash shell, PowerShell, dsb) untuk mengedit atau merombak kode. Setiap perubahan wajib dilakukan langsung pada file target.

---

## BAGIAN 3: ATURAN BERSAMA (BERLAKU UNTUK MANUSIA & AI)

Aturan fundamental teknis ini **wajib** diimplementasikan secara teknis baik saat dikoding oleh Programmer maupun saat digenerate oleh AI.

### 1. Integritas & Keamanan Absolut (*Zero Trust Policy*)
Jangan pernah memercayai input apa pun yang dikirimkan oleh sistem *Frontend* atau pengguna secara langsung. Semua data wajib diperiksa, divalidasi ketat, dan dibersihkan (*sanitize*) dari skrip asing sebelum dimasukkan ke database.

### 2. Single Source of Truth
*Backend* memegang hak veto dan kendali penuh atas aturan bisnis (*Business Rules*). Contoh: Aturan batas waktu batal H-1 mutlak dicegat dan digagalkan oleh kode PHP/Server, bukan sekadar memanipulasi dan menyembunyikan tombolnya di file *Frontend HTML*.

### 3. Arsitektur MVC yang Disiplin (*Fat Model, Skinny Controller*)
- *Controller* bertugas murni sebagai polisi pengatur lalu lintas; jangan pernah menjejalkan ratusan baris kueri di dalamnya.
- *Model* bertugas murni sebagai peta relasi (*Eloquent*).
- Jika logika bisnis (seperti kalkulasi bentrok jadwal) mulai membengkak, ekstraksi logika tersebut ke *Service Class* tersendiri agar kode *Controller* bersih.

### 4. Aspek Teknikal (Koding & Optimasi Basis Data)
- **Validasi Mutlak (*FormRequest*):** Seluruh proses *Create/Update/Delete* data WAJIB melewati penjagaan *FormRequest* bawaan Laravel. Jangan letakkan validasi langsung di dalam *Controller*.
- **Optimalisasi Basis Data (Anti N+1 Problem):** Haram hukumnya mengeksekusi kueri `Database` di dalam sebuah perulangan *looping*. Selalu gunakan fitur *Eager Loading* (`with('relasi')`) saat menarik data.
- **Pertahanan Maksimal Kondisi Ekstrem (*Race Condition Handling*):** Fitur pemesanan waktu sangat rentan terhadap *double-booking*. **WAJIB** menerapkan mekanisme gembok di tingkat baris/tabel menggunakan sintaks `lockForUpdate()` di dalam blok `DB::transaction()`.
- **Manajemen Pengecualian (*Graceful Error Handling*):** Gunakan blok `try...catch` pada transaksi krusial. JANGAN PERNAH membocorkan pesan rentan (*SQL Syntax Error* mentah). Kembalikan pesan JSON/Sesi yang aman (*"Mohon maaf, sistem gagal memproses data"*).

### 5. Aturan Ketat Dokumentasi Kode (*Strict Commenting Rule*)

Seluruh pengembang (Manusia dan AI) diwajibkan mematuhi format komentar standar (termasuk *DocBlock*) berikut beserta contoh implementasinya.

**A. Kewajiban Komentar Header File**
Setiap file PHP (`Controller`, `Model`, `Middleware`, dsb) **WAJIB** diawali dengan *Header Comment* di bawah `<?php` yang menjelaskan identitas file.
*Format Terstandarisasi:*
```php
<?php
/**
 * NAMA FILE    : ReservationController.php
 * FUNGSI   : Controller pengelola alur logika transaksi reservasi
 * DESKRIPSI    : Bertanggung jawab menangani CRUD reservasi, validasi kelayakan jam pinjam, dan pembatalan.
 * CARA KERJA   : Menerima HTTP Request, berkomunikasi dengan Model Reservation dan Facility, menggunakan lockForUpdate() untuk mencegah bentrokan.
 */
namespace App\Http\Controllers;
```

**B. Kewajiban Komentar Routing (URL)**
Setiap mendaftarkan rute (contoh di `web.php`), Anda **WAJIB** menjelaskan fungsi dan asupan datanya di atas deklarasi rute.
*Format Terstandarisasi:*
```php
// ROUTE: Menerima POST form data multipart/form-data (lampiran < 2MB).
// FUNGSI: Endpoint penangkap keluhan kerusakan fasilitas, meneruskan payload ke ReportController.
Route::post('/lapor-kerusakan', [ReportController::class, 'store']);
```

**C. Kewajiban Komentar Blok Fungsi (Method/Logika)**
Setiap fungsi/method baru **WAJIB** diberikan *DocBlock* yang menjelaskan 3 elemen kunci: Apa fungsinya, Kegunaannya, dan Cara Kerjanya.
*Format Terstandarisasi:*
```php
/**
 * FUNCTION/PROCEDURE       : overrideCancel()
 * KEGUNAAN     : Tuas darurat khusus Petugas/Admin membatalkan paksa reservasi karena kondisi force-majeure.
 * CARA KERJA   : Menerima param ID Reservasi. Mengecek Gate otoritas. Jika lolos, memulai DB::transaction, mengubah status menjadi 'cancelled', mencatat alasan pembatalan, dan membebaskan antrean di Facility.
 */
public function overrideCancel(Request $request, $id) {
    // kodingan fungsi
}
```

> **⚠️ PERINGATAN REVISI KOMENTAR (MANUSIA & AI):** Jika Anda memodifikasi alur kueri suatu saat nanti, Anda **MUTLAK WAJIB** merevisi tulisan komentar tersebut agar selaras. Komentar usang yang membual/bohong sangat berbahaya bagi sistem!
