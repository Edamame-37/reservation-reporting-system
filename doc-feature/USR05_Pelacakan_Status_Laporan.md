# SRS-USR-05: Pelacakan Status Laporan (Tiket) Pengguna

## 1. Identifikasi Dokumen & Metadata SRS
- **Kode Spesifikasi:** SRS-USR-05
- **Judul Fitur:** Dasbor Riwayat & Pelacakan Status Tiket Pengaduan Kerusakan Fasilitas
- **Aktor Utama:** Pengguna Terautentikasi (Sivitas Akademika: Mahasiswa, Dosen, Tenaga Kependidikan/Staf)
- **Role Sistem:** `pengguna` (Spatie Laravel Permission) dengan status akun aktif (`status = 'active'`)
- **Ekuivalen User Story:** US-7 (*"Sebagai Pengguna, saya bisa melihat riwayat dan status tiket laporan kerusakan yang saya ajukan beserta catatan teknisi sarpras."*)
- **Modul Sistem:** Modul Pelaporan Kerusakan (*Sub-modul: Portal Pengguna / Sivitas Akademika*)
- **Tingkat Prioritas:** Fundamental / *Must Have* (Kritis)
- **Status Dokumen:** Versi 2.0 (Spesifikasi Lengkap & Terstandarisasi)

---

## 2. Deskripsi Alur Bisnis & Karakteristik Pengguna

### 2.1. Karakteristik & Kebutuhan Pengguna
Setelah sivitas akademika mengunggah foto bukti dan mengirimkan pengaduan kerusakan melalui fitur USR-04, mereka memerlukan media pemantauan yang transparan dan dapat dipercaya:
1. **Transparansi SLA (Service Level Agreement):** Pengguna ingin mengetahui apakah laporannya sudah dilihat oleh petugas, sedang dalam proses perbaikan fisik oleh teknisi, atau sudah selesai diperbaiki.
2. **Klarifikasi Resolusi Teknisi:** Saat tiket ditutup (*Selesai*) atau jika laporan tidak dapat diproses (*Ditolak*), pengguna berhak membaca catatan keterangan resmi dari petugas sarpras (misal: "Penggantian lampu proyektor telah rampung" atau "Kendala AC merupakan wewenang vendor eksternal, jadwal servis 25 Sep").
3. **Pemeriksaan Foto Bukti Mandiri:** Pengguna dapat meninjau kembali berkas foto bukti asli yang pernah mereka unggah melalui pop-up pratinjau.

### 2.2. Ringkasan Alur Pelacakan Status
1. Pengguna membuka halaman *Riwayat Laporan* (`/user/report-history`) melalui bilah navigasi samping atau dialihkan otomatis pasca-submit USR-04.
2. Sistem mengeksekusi kueri terisolasi (`where('user_id', Auth::id())`) dan menyajikan daftar laporan secara kronologis terbalik (*terbaru di atas*).
3. Pengguna dapat menyaring tiket berdasarkan status (*Semua, Baru, Sedang Diproses, Selesai Ditangani, Ditolak*) dengan indikator hitungan angka (*badge count*).
4. Pengguna dapat mencari laporan menggunakan kata kunci (kode tiket, fasilitas, atau deskripsi).
5. Pada setiap baris tabel, pengguna dapat mengklik tombol *"Lihat Foto"* untuk memunculkan modal pop-up yang merender foto bukti asli dari disk storage.
6. Untuk laporan yang telah ditangani, kolom catatan teknisi memuat catatan resolusi resmi (`resolution_note`), nama petugas penanggung jawab (`handled_by`), dan stempel waktu penyelesaian (`resolved_at`).

---

## 3. Aturan Bisnis Mutlak (*Business Rules*)

| Kode Aturan | Nama Aturan | Deskripsi & Batasan Ketat | Tingkat Penegakan |
|---|---|---|---|
| **BR-USR05-01** | **Isolasi Data Pelapor (*Strict Data Scoping*)** | Kueri pengambilan riwayat tiket laporan **mutlak hanya menampilkan data milik pengguna aktif** (`where('user_id', Auth::id())`). Pengguna dilarang keras dapat melihat daftar pengaduan milik mahasiswa/dosen lain. | Server-side (Kueri DB) |
| **BR-USR05-02** | **Ketetapan Data Laporan (*Immutability*)** | Antarmuka riwayat bersifat **murni baca-saja (*Read-Only*)**. Pengguna tidak diizinkan mengubah deskripsi atau mengganti foto setelah laporan terkirim untuk menjaga integritas bukti sarpras. | Arsitektur Sistem |
| **BR-USR05-03** | **Standarisasi Warna Status Laporan** | Indikator visual tahapan penanganan tiket wajib mematuhi palet warna standar CAVA: <br>- `baru`: Amber/Kuning ("Laporan Baru")<br>- `diproses`: Sky/Biru ("Sedang Diproses")<br>- `selesai`: Emerald/Hijau ("Selesai Ditangani")<br>- `ditolak`: Rose/Merah ("Ditolak") | Frontend (Tailwind/Blade) |
| **BR-USR05-04** | **Transparansi Catatan Resolusi Petugas** | Sistem **wajib menyajikan isi kolom `resolution_note`** kepada pengguna jika tiket berstatus `selesai` atau `ditolak` beserta identitas teknisi penangan. | Frontend & Backend |
| **BR-USR05-05** | **Pencegahan Kueri N+1 (*Eager Loading*)** | Penarikan data riwayat wajib memuat relasi relasional fasilitas (`facility`) dan petugas penangan (`handler`) secara serentak via `DamageReport::with(['facility', 'handler'])`. | Backend Controller |

---

## 4. Kebutuhan Fungsional (*Functional Requirements*)

| ID Kebutuhan | Nama Kebutuhan | Deskripsi Spesifikasi Fungsional |
|---|---|---|
| **FR-USR05-001** | Tampilan Daftar Riwayat Dinamis | Sistem harus menampilkan daftar tiket pengaduan kerusakan milik pengguna aktif di URL `/user/report-history` secara dinamis dari tabel `damage_reports`. |
| **FR-USR05-002** | Tab Penyaringan Status & Badge Count | Sistem harus menyediakan tab filter status (*Semua, Baru, Diproses, Selesai, Ditolak*) dengan angka hitungan riil jumlah tiket pada masing-masing status. |
| **FR-USR05-003** | Pencarian Kata Kunci | Sistem harus menyediakan kolom pencarian yang mampu menyaring tiket berdasarkan kode tiket (`report_code`), deskripsi keluhan (`description`), atau nama fasilitas (`facility.name`). |
| **FR-USR05-004** | Dialog Modal Foto Bukti Riil | Sistem harus menyediakan jendela dialog pop-up yang memuat berkas foto asli dari storage (`asset('storage/' . $report->attachment_photo)`) saat tombol "Lihat" diklik. |
| **FR-USR05-005** | Informasi Resolusi Teknisi | Sistem harus menampilkan catatan perbaikan resmi dari teknisi/sarpras, nama penangan, dan tanggal penyelesaian. |
| **FR-USR05-006** | Navigasi Paginasi Presisten | Sistem harus menyediakan navigasi paginasi Laravel (10 baris per halaman) yang mempertahankan parameter query string (`withQueryString()`). |
| **FR-USR05-007** | Penanganan Riwayat Kosong (*Empty State*) | Apabila pengguna belum memiliki riwayat pengaduan, sistem harus menyajikan ilustrasi ramah disertai tautan ajakan bertindak (*CTA*) menuju form pelaporan. |

---

## 5. Kebutuhan Non-Fungsional (*Non-Functional Requirements*)

### 5.1. Keamanan & Akses Media (*Security*)
- **Otorisasi Data Ketat:** Parameter URL tidak dapat dimanipulasi untuk menampilkan laporan milik orang lain karena dibatasi langsung oleh `Auth::id()` di sisi server.
- **Penyajian Berkas Aman:** Tautan foto bukti disajikan melalui symlink storage publik Laravel yang terlindungi dari penulisan langsung.

### 5.2. Kinerja & Efisiensi (*Performance*)
- **Paginasi Efisien:** Pembatasan 10 baris per halaman mencegah pembengkakan pemakaian memori saat riwayat mencapai ratusan tiket.
- **Bebas N+1 Query:** Eager Loading memastikan pengambilan data fasilitas dan penangan berjalan hanya dalam 2 kueri SQL.

---

## 6. Spesifikasi Antarmuka Pengguna (*UI/UX Specification*)

- **Berkas Tampilan:** `resources/views/user/report-history.blade.php`
- **Layout Induk:** `<x-app-layout title="Riwayat Lengkap Laporan Kerusakan" active="report-history">`
- **Komponen Utama:**
  - Header & Breadcrumb (*Dasbor Saya > Status Laporan Kerusakan*).
  - Tombol aksi *"Buat Laporan Baru"*.
  - Tab navigasi status (`all`, `baru`, `diproses`, `selesai`, `ditolak`) dengan badge angka dinamis.
  - Kotak pencarian kata kunci dengan tombol hapus pencarian (*clear*).
  - Tabel data pengaduan: ID Tiket & Waktu, Fasilitas & Kategori, Deskripsi Kerusakan, Status Penanganan, Catatan Resolusi, dan Foto Bukti.
  - Modal dialog Alpine.js untuk menampilkan foto bukti beresolusi penuh.
  - Komponen paginasi Laravel di bagian bawah tabel.

---

## 7. Arsitektur Backend & Rute Rujukan

### 7.1. Pemetaan Rute URL (`routes/web.php`)
```php
use App\Http\Controllers\ReportController;

Route::middleware(['auth', 'role:pengguna'])->prefix('user')->name('user.')->group(function () {
    Route::get('/report-history', [ReportController::class, 'history'])->name('report-history');
});
```

### 7.2. Logika Kontroler (`ReportController@history`)
```php
public function history(Request $request): View
{
    $userId = Auth::id() ?? 1;

    // 1. Hitung Ringkasan Badge per Status
    $counts = [
        'all'      => DamageReport::where('user_id', $userId)->count(),
        'baru'     => DamageReport::where('user_id', $userId)->where('status', 'baru')->count(),
        'diproses' => DamageReport::where('user_id', $userId)->where('status', 'diproses')->count(),
        'selesai'  => DamageReport::where('user_id', $userId)->where('status', 'selesai')->count(),
        'ditolak'  => DamageReport::where('user_id', $userId)->where('status', 'ditolak')->count(),
    ];

    // 2. Kueri Eager Loading Bebas N+1
    $query = DamageReport::with(['facility', 'handler'])
        ->where('user_id', $userId)
        ->orderBy('created_at', 'desc');

    // 3. Filter Status Tab
    $activeStatus = $request->query('status', 'all');
    if ($activeStatus !== 'all' && in_array($activeStatus, ['baru', 'diproses', 'selesai', 'ditolak'])) {
        $query->where('status', $activeStatus);
    }

    // 4. Pencarian Kata Kunci
    $keyword = trim($request->query('search', ''));
    if (!empty($keyword)) {
        $query->where(function ($q) use ($keyword) {
            $q->where('report_code', 'LIKE', '%' . $keyword . '%')
              ->orWhere('description', 'LIKE', '%' . $keyword . '%')
              ->orWhere('category', 'LIKE', '%' . $keyword . '%')
              ->orWhereHas('facility', function ($fQuery) use ($keyword) {
                  $fQuery->where('name', 'LIKE', '%' . $keyword . '%')
                         ->orWhere('building', 'LIKE', '%' . $keyword . '%');
              });
        });
    }

    // 5. Paginasi Hasil Kueri
    $reports = $query->paginate(10)->withQueryString();

    return view('user.report-history', compact('reports', 'counts', 'activeStatus', 'keyword'));
}
```

---

## 8. Skenario Pengujian & Kriteria Penerimaan (*Acceptance Criteria*)

### 8.1. Format Gherkin
```gherkin
Fitur: Dasbor Riwayat dan Status Laporan Kerusakan Pengguna

  Skenario: Pengguna melihat daftar tiket pengaduan miliknya
    Dengan Saya adalah pengguna terautentikasi yang memiliki laporan di sistem
    Ketika Saya membuka halaman "/user/report-history"
    Maka Sistem menyajikan seluruh tiket kerusakan milik akun Saya
    Dan Sistem TIDAK menampilkan tiket milik pengguna lain
    Dan Setiap tiket memuat kode tiket, nama fasilitas, tanggal kirim, dan badge status penanganan

  Skenario: Pengguna membuka modal foto bukti yang diunggah
    Dengan Terdapat tiket laporan yang memiliki lampiran foto bukti
    Ketika Saya mengklik tombol "Lihat" pada kolom foto bukti
    Maka Dialog modal pop-up muncul di layar
    Dan Menampilkan foto bukti asli yang diambil dari storage publik

  Skenario: Pengguna menyaring laporan berdasarkan status "selesai"
    Dengan Saya berada di halaman riwayat laporan
    Ketika Saya menekan tab "Selesai"
    Maka Tabel hanya menampilkan tiket yang berstatus "selesai"
    Dan Menampilkan catatan resolusi teknisi secara transparan
```

### 8.2. Matriks Kasus Uji (*Test Case Matrix*)

| ID Uji | Skenario Pengujian | Aksi Pengujian | Hasil yang Diharapkan | Status |
|---|---|---|---|---|
| **TC-USR05-01** | Pemuatan Riwayat Pengguna Aktif | GET /user/report-history | Tiket milik akun pengguna aktif termuat dengan status HTTP 200. | Mandatory Pass |
| **TC-USR05-02** | Isolasi Akun Pengguna (*Data Scoping*) | Periksa daftar tiket di view | Hanya tiket dengan `user_id = Auth::id()` yang muncul. | Mandatory Pass |
| **TC-USR05-03** | Penyaringan Tab Status (*Baru/Diproses/dll*) | GET dengan `?status=selesai` | Tabel hanya menampilkan tiket berstatus `selesai`. | Mandatory Pass |
| **TC-USR05-04** | Pencarian Kata Kunci | GET dengan `?search=AC` | Menampilkan tiket dengan kategori/fasilitas AC. | Mandatory Pass |
| **TC-USR05-05** | Penanganan Riwayat Kosong (*Empty State*) | Akun baru tanpa laporan | Menampilkan ilustrasi dan pesan "Belum Ada Laporan Kerusakan". | Mandatory Pass |

---

## 9. Instruksi Khusus untuk Programmer (Mandatori)

1. **Anti-Hardcode:** Mutlak menghapus array `reports: [...]` statis di Alpine.js dan menggantinya dengan loop `@forelse ($reports as $report)`.
2. **Pemisahan Pengerjaan (*Separation of Concerns*):** Wajib menyelesaikan tahap **Frontend terlebih dahulu** (`report-history.blade.php`), melakukan commit terpisah, lalu melangkah ke tahap **Backend** (`ReportController.php`, `web.php`) dan commit terpisah.
3. **Penyajian Foto Asli:** Gunakan `asset('storage/' . $report->attachment_photo)` pada modal preview.
4. **Standar Kolaborasi Git:** Gunakan branch `feature/usr05/pelacakan-laporan` dan pesan commit konvensional berbahasa Indonesia.
