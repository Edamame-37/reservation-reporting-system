# SRS-USR-02: Dasbor Riwayat & Pelacakan Status Reservasi Pengguna

## 1. Identifikasi Dokumen & Metadata SRS
- **Kode Spesifikasi:** SRS-USR-02
- **Judul Fitur:** Dasbor Riwayat & Pelacakan Status Reservasi Pengguna
- **Aktor Utama:** Pengguna Terautentikasi (Sivitas Akademika: Mahasiswa, Dosen, Tenaga Kependidikan/Staf)
- **Role Sistem:** `pengguna` (Spatie Laravel Permission) dengan status akun aktif (`status = 'active'`)
- **Ekuivalen User Story:** US-5 (*"Sebagai Pengguna, saya bisa melihat riwayat dan status reservasi saya, termasuk detail lengkap reservasi tersebut."*)
- **Modul Sistem:** Modul Manajemen Reservasi (*Sub-modul: Portal Pengguna / Sivitas Akademika*)
- **Tingkat Prioritas:** Fundamental / *Must Have* (Kritis)
- **Status Dokumen:** Versi 2.0 (Spesifikasi Lengkap & Terstandarisasi)

---

## 2. Deskripsi Alur Bisnis & Karakteristik Pengguna

### 2.1. Karakteristik & Kebutuhan Pengguna
Setelah sivitas akademika (Mahasiswa, Dosen, atau Staf) mengirimkan permohonan reservasi melalui fitur USR-01, mereka membutuhkan wadah terpusat dan transparan untuk memantau status pemrosesan tiket reservasi secara berkala:
1. **Mahasiswa:** Memantau apakah ruangan untuk kegiatan organisasi atau praktikum sudah disetujui Petugas Sarpras agar kepanitiaan dapat menyebarkan publikasi acara dengan tenang.
2. **Dosen:** Memeriksa kesiapan ruangan untuk perkuliahan pengganti atau seminar, serta membaca catatan petugas apabila terdapat instruksi khusus terkait peralatan lab.
3. **Staf / Tenaga Kependidikan:** Memverifikasi status ruangan rapat internal universitas dan melihat alasan penolakan jika jadwal bertabrakan dengan agenda pimpinan.

### 2.2. Ringkasan Alur Riwayat Reservasi
1. Pengguna membuka menu *Riwayat Reservasi* melalui tautan navigasi bilah samping (*sidebar*) atau dialihkan secara otomatis pasca-pengajuan tiket di USR-01.
2. Sistem menampilkan daftar seluruh tiket peminjaman milik pengguna aktif secara urut waktu terbalik (*chronological descending* / yang terbaru di atas).
3. Pengguna dapat menyaring (*filter*) tiket berdasarkan status (*Semua, Menunggu, Disetujui, Ditolak, Dibatalkan*) dan mencari tiket berdasarkan kode tiket, nama fasilitas, atau tujuan kegiatan.
4. Pengguna dapat mengklik tombol *"Lihat Detail"* pada salah satu tiket untuk membuka jendela dialog modal yang memuat rincian lengkap: kode tiket unik, spesifikasi ruang dan gedung, waktu mulai dan selesai, jumlah peserta, tujuan kegiatan, identitas petugas sarpras pemeriksa, serta alasan resmi jika tiket ditolak atau dibatalkan.
5. Untuk tiket yang masih berstatus `approved` atau `pending` dan jadwal kegiatannya belum lewat batas **H-1**, sistem menyediakan tautan cepat untuk membatalkan reservasi mandiri (terintegrasi dengan fitur USR-03).

---

## 3. Aturan Bisnis Mutlak (*Business Rules*)

Setiap implementasi *Frontend* dan *Backend* wajib menaati aturan bisnis berikut:

| Kode Aturan | Nama Aturan | Deskripsi & Batasan Ketat | Tingkat Penegakan |
|---|---|---|---|
| **BR-USR02-01** | **Isolasi Data Pribadi (*Data Scoping*)** | Pengguna **mutlak hanya dapat melihat riwayat reservasi miliknya sendiri** (`where('user_id', Auth::id())`). Pengguna dilarang keras dapat melihat, mengintip, atau mengakses tiket milik sivitas akademika lain. | Server-side (Kueri DB) |
| **BR-USR02-02** | **Transparansi Catatan & Alasan** | Apabila tiket berstatus `rejected` (*Ditolak*) atau `cancelled` (*Dibatalkan*), sistem **wajib menampilkan kolom catatan/alasan** (`rejection_reason` / `cancellation_reason`) secara terbuka kepada pemohon. | Client-side & Server-side |
| **BR-USR02-03** | **Standarisasi Warna Lencana Status** | Penanda visual status tiket wajib mematuhi palet warna standar CAVA: <br>- `pending`: Kuning / *Amber* ("Menunggu Konfirmasi")<br>- `approved`: Hijau / *Emerald* ("Disetujui Petugas")<br>- `rejected`: Merah / *Rose* ("Ditolak")<br>- `cancelled`: Abu-abu / Merah Muda ("Dibatalkan")<br>- `completed`: Biru / *Sky* ("Selesai Digunakan") | Frontend (CSS/Blade) |
| **BR-USR02-04** | **Ketetapan Data (*Immutability*)** | Data peminjaman (fasilitas, tanggal, dan rentang jam) yang sudah tersimpan **tidak dapat diedit/diubah** oleh pengguna. Jika terjadi kesalahan input, pengguna harus membatalkan tiketnya (maksimal H-1) dan mengajukan permohonan baru. | Logika Bisnis Sistem |
| **BR-USR02-05** | **Paginasi & Efisiensi Memori** | Tampilan riwayat tiket wajib menggunakan sistem paginasi maksimal **10 baris data per halaman** untuk mencegah *memory bloat* dan perlambatan waktu muat halaman (*load time*). | Server-side (Eloquent) |
| **BR-USR02-06** | **Pencegahan Masalah N+1 (*Eager Loading*)** | Kueri penarikan data reservasi wajib memuat relasi relasional `facility` dan `reviewer` secara serentak (`Reservation::with(['facility', 'reviewer'])`) demi efisiensi panggilan SQL. | Backend Controller |

---

## 4. Kebutuhan Fungsional (*Functional Requirements*)

| ID Kebutuhan | Nama Kebutuhan | Deskripsi Spesifikasi Fungsional |
|---|---|---|
| **FR-USR02-001** | Pemuatan Daftar Riwayat | Sistem harus menampilkan daftar permohonan reservasi milik pengguna aktif di URL `/user/reservation-history` yang diurutkan dari yang terbaru (`created_at DESC`). |
| **FR-USR02-002** | Tab Penyaringan Status | Sistem harus menyediakan tab filter status (*Semua, Menunggu, Disetujui, Ditolak, Dibatalkan*) lengkap dengan indikator angka hitungan (*badge count*) jumlah tiket pada masing-masing kategori. |
| **FR-USR02-003** | Pencarian Kata Kunci | Sistem harus menyediakan kotak pencarian yang mampu menyaring tiket berdasarkan kecocokan kode tiket (`ticket_code`), nama fasilitas (`facilities.name`), atau deskripsi tujuan (`purpose`). |
| **FR-USR02-004** | Visualisasi Lencana Status | Setiap baris atau kartu reservasi harus menampilkan lencana warna status yang mencerminkan tahapan verifikasi tiket secara intuitif. |
| **FR-USR02-005** | Modal Informasi Rinci | Sistem harus menyediakan jendela dialog pop-up (*Detail Modal*) saat pengguna menekan tombol "Lihat Detail" yang menyajikan data menyeluruh (ID tiket, ruangan, tanggal, rentang jam operasional, durasi slot, kapasitas, deskripsi acara, PIC, nama petugas peninjau, dan alasan penolakan/pembatalan jika ada). |
| **FR-USR02-006** | Navigasi Paginasi Dinamis | Sistem harus menyajikan tautan paginasi Laravel (`{{ $reservations->links() }}`) yang mempertahankan parameter filter pencarian dan status (`withQueryString()`). |
| **FR-USR02-007** | Penanganan Status Kosong (*Empty State*) | Apabila pengguna belum memiliki riwayat reservasi (atau pencarian tidak menghasilkan data), sistem harus menampilkan ilustrasi dan pesan informatif yang ramah disertai tombol ajakan bertindak (*Call-to-Action*) menuju form reservasi baru. |
| **FR-USR02-008** | Integrasi Aksi Pembatalan (H-1) | Untuk tiket yang memenuhi syarat batas waktu H-1, sistem harus menampilkan opsi tombol pembatalan mandiri yang terhubung dengan modul USR-03. |

---

## 5. Kebutuhan Non-Fungsional (*Non-Functional Requirements*)

### 5.1. Keamanan & Privasi (*Security & Privacy*)
- **Otorisasi Data Ketat:** Sistem mengeksekusi penyaringan berbasis `Auth::id()` di sisi *Controller*. Memanipulasi parameter di URL atau *payload* tidak akan pernah mengekspos data reservasi pengguna lain.
- **Pembersihan XSS:** Seluruh data teks (seperti catatan pemohon dan catatan petugas) wajib dirender menggunakan Blade escaping aman `{{ $data }}`.

### 5.2. Kinerja & Optimasi Kueri (*Performance*)
- **Eager Loading Relasi:** Mengeliminasi celah performa N+1 kueri dengan memanggil relasi `with(['facility', 'reviewer'])`.
- **Pemanfaatan Indeks Basis Data:** Kueri memanfaatkan indeks komposit `idx_res_facility_date_status` dan indeks `user_id` untuk memastikan pencarian data tetap di bawah 200ms meski terdapat ribuan baris di tabel `reservations`.

### 5.3. Usabilitas & Aksesibilitas (*Usability & UX*)
- **Responsivitas Antarmuka:** Tampilan tabel otomatis bertransformasi menjadi susunan kartu yang nyaman dibaca pada layar perangkat bergerak (*smartphone*).
- **Interaktivitas Cepat Tanpa Bloatware:** Modal detail diatur menggunakan direktif Alpine.js (`x-data`, `x-show`, `x-cloak`) yang ringan tanpa perlu memuat pustaka JavaScript eksternal yang membebani memori.

---

## 6. Spesifikasi Antarmuka Pengguna (*UI/UX Specification*)

### 6.1. Berkas Rujukan & Komponen Tampilan
- **Berkas Tampilan:** `resources/views/user/reservation-history.blade.php`
- **Layout Induk:** `<x-app-layout title="Riwayat Lengkap Reservasi Saya" active="reservation-history">`
- **Komponen Pendukung:**
  - Banner flash notifikasi sukses (`session('success')`).
  - Baris tab navigasi status (`all`, `pending`, `approved`, `rejected`, `cancelled`).
  - Kotak pencarian dengan ikon kaca pembesar (*Material Symbols*).
  - Tabel data riwayat dengan kolom: Kode Tiket, Ruang & Fasilitas, Tanggal & Waktu, Status Verifikasi, dan Aksi.
  - Komponen pop-up modal detail tiket (`x-show="showDetailModal"`).

### 6.2. Skema Warna Lencana Status (*Status Badges*)
| Nilai Status DB | Label Tampilan | Kelas Warna Tailwind |
|---|---|---|
| `pending` | Menunggu Persetujuan | `bg-amber-50 text-amber-800 border-amber-200` |
| `approved` | Disetujui Petugas | `bg-emerald-50 text-emerald-800 border-emerald-200` |
| `rejected` | Ditolak | `bg-rose-50 text-rose-800 border-rose-200` |
| `cancelled` | Dibatalkan | `bg-slate-100 text-slate-700 border-slate-200` |
| `completed` | Selesai Digunakan | `bg-blue-50 text-blue-800 border-blue-200` |

---

## 7. Arsitektur Backend & Logika Kueri Kontroler

### 7.1. Pemetaan Rute URL (`routes/web.php`)
```php
use App\Http\Controllers\ReservationController;

Route::middleware(['auth', 'role:pengguna'])->prefix('user')->name('user.')->group(function () {
    Route::get('/reservation-history', [ReservationController::class, 'history'])->name('reservation-history');
});
```

### 7.2. Logika Kueri Kontroler (`ReservationController@history`)
```php
public function history(Request $request): View
{
    $userId = Auth::id() ?? 1; // Fallback mock testing

    // 1. Hitung Ringkasan Jumlah Tiket per Status (Untuk Badge Count di Tab)
    $counts = [
        'all'       => Reservation::where('user_id', $userId)->count(),
        'pending'   => Reservation::where('user_id', $userId)->where('status', 'pending')->count(),
        'approved'  => Reservation::where('user_id', $userId)->where('status', 'approved')->count(),
        'rejected'  => Reservation::where('user_id', $userId)->where('status', 'rejected')->count(),
        'cancelled' => Reservation::where('user_id', $userId)->where('status', 'cancelled')->count(),
    ];

    // 2. Bangun Kueri dengan Eager Loading
    $query = Reservation::with(['facility', 'reviewer'])
        ->where('user_id', $userId)
        ->orderBy('created_at', 'desc');

    // 3. Filter Berdasarkan Tab Status
    $activeStatus = $request->query('status', 'all');
    if ($activeStatus !== 'all' && in_array($activeStatus, ['pending', 'approved', 'rejected', 'cancelled', 'completed'])) {
        $query->where('status', $activeStatus);
    }

    // 4. Filter Berdasarkan Pencarian Kata Kunci
    $keyword = $request->query('search');
    if (!empty($keyword)) {
        $query->where(function ($q) use ($keyword) {
            $q->where('ticket_code', 'LIKE', '%' . $keyword . '%')
              ->orWhere('purpose', 'LIKE', '%' . $keyword . '%')
              ->orWhereHas('facility', function ($fQuery) use ($keyword) {
                  $fQuery->where('name', 'LIKE', '%' . $keyword . '%')
                         ->orWhere('building', 'LIKE', '%' . $keyword . '%');
              });
        });
    }

    // 5. Paginasi Hasil
    $reservations = $query->paginate(10)->withQueryString();

    return view('user.reservation-history', compact('reservations', 'counts', 'activeStatus', 'keyword'));
}
```

---

## 8. Skenario Pengujian & Kriteria Penerimaan (*Acceptance Criteria*)

### 8.1. Kriteria Penerimaan Berbasis Format Gherkin
```gherkin
Fitur: Dasbor Riwayat dan Status Reservasi Pengguna

  Skenario: Pengguna melihat daftar riwayat tiket reservasi miliknya
    Dengan Saya adalah pengguna terautentikasi dengan tiket reservasi yang tersimpan
    Ketika Saya mengakses halaman "/user/reservation-history"
    Maka Sistem menampilkan seluruh tiket reservasi milik akun Saya
    Dan Sistem TIDAK menampilkan tiket milik pengguna lain
    Dan Setiap tiket memuat kode tiket, nama fasilitas, tanggal, rentang jam, dan badge status yang sesuai

  Skenario: Pengguna menyaring riwayat berdasarkan status pending
    Dengan Saya berada di halaman riwayat reservasi
    Ketika Saya menekan tab "Menunggu"
    Maka Tabel hanya menampilkan tiket yang memiliki status "pending"
    Dan Menampilkan angka badge hitungan tiket pending yang akurat

  Skenario: Pengguna mencari tiket menggunakan kode tiket
    Dengan Saya memiliki tiket dengan kode "TKT-20260923-0001"
    Ketika Saya mengetikkan "TKT-20260923-0001" pada kolom pencarian dan menekan Enter
    Maka Sistem memuat ulang tabel dan menyajikan tiket yang cocok secara tepat

  Skenario: Pengguna membuka modal rincian tiket yang ditolak
    Dengan Terdapat tiket berstatus "rejected" dengan alasan "Jadwal bentrok dengan acara Dies Natalis"
    Ketika Saya mengklik tombol "Lihat Detail" pada tiket tersebut
    Maka Jendela dialog pop-up muncul di layar
    Dan Menyajikan rincian lengkap beserta teks alasan penolakan petugas secara transparan
```

### 8.2. Matriks Kasus Uji (*Test Case Matrix*)

| ID Uji | Skenario Pengujian | Aksi Pengujian | Hasil yang Diharapkan | Status |
|---|---|---|---|---|
| **TC-USR02-01** | Pemuatan Riwayat Default | Buka `/user/reservation-history` | Seluruh tiket milik user aktif termuat berurut dari yang terbaru. | Mandatory Pass |
| **TC-USR02-02** | Isolasi Akun Pengguna | Cek ID pemohon pada kueri DB | Hanya baris dengan `user_id = Auth::id()` yang ditampilkan. | Mandatory Pass |
| **TC-USR02-03** | Penyaringan Tab Status | Klik tab "Menunggu" / "Disetujui" | URL memuat `?status=...` dan tabel terfilter secara akurat. | Mandatory Pass |
| **TC-USR02-04** | Pencarian Berdasarkan Fasilitas | Cari kata kunci "Auditorium" | Tiket dengan fasilitas Auditorium ditampilkan. | Mandatory Pass |
| **TC-USR02-05** | Buka Modal Detail Tiket | Klik tombol "Lihat Detail" | Modal terbuka menampilkan PIC, reviewer, dan alasan penolakan/batal. | Mandatory Pass |
| **TC-USR02-06** | Penanganan Riwayat Kosong | Filter status yang tidak memiliki tiket | Menampilkan ilustrasi dan teks "Belum Ada Riwayat Reservasi". | Mandatory Pass |

---

## 9. Instruksi Khusus untuk Programmer (Mandatori)

1. **Anti-Hardcode:** Wajib menghapus segala jenis data *hardcode* (*dummy array*) di *frontend* dan menggantinya dengan data dinamis dari variabel *Controller* (`$reservations`).
2. **Pemisahan Pengerjaan (*Separation of Concerns*):** Sesuai permintaan dan pedoman `RULE_FRONTEND.md` & `RULE_BACKEND.md`, kerjakan tahap Frontend terlebih dahulu hingga selesai, lakukan commit terpisah, lalu lanjutkan ke tahap Backend dan commit terpisah.
3. **Kepatuhan Kueri Bebas N+1:** Mutlak menggunakan *Eager Loading* `with(['facility', 'reviewer'])` pada `ReservationController`.
4. **Kepatuhan Desain CAVA:** Gunakan warna penanda status badge CAVA dan komponen tata letak yang konsisten.
5. **Standar Kolaborasi Git:** Sebelum commit, pastikan pesan berbahasa Indonesia dan gunakan branch `feature/usr02/riwayat-reservasi`.

---

## 10. Catatan Penyesuaian Tambahan (Diisi oleh Programmer)
*(Bagian ini wajib diisi jika Anda melakukan penyesuaian/improvisasi yang berbeda dari Blueprint di atas selama proses koding! Kosongkan jika tidak ada).*

- ...
