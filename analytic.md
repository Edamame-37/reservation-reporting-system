# Blueprint Pembelajaran Mendalam Sistem CAVA (Campus Venue Access)
> **Dokumen Analitik & Pipeline Pembelajaran Komprehensif**  
> *Target*: Memahami 100% proses bisnis, arsitektur perangkat lunak, relasi basis data, logika alur kerja (*workflows*), logika konkurensi (*anti-race condition*), serta sintaks kode dari hulu ke hilir.

---

## 1. Peta Besar & Arsitektur Sistem (*The Big Picture*)

Sistem **CAVA (Campus Venue Access)** adalah platform terpadu peminjaman fasilitas kampus (auditorium, laboratorium, ruang rapat, kelas) dan sistem pelaporan kerusakan sarana prasarana berbasis tiket (*ticketing*).

```
   ┌─────────────────────────────────────────────────────────────┐
   │                        KLIEN (Browser)                      │
   └───────────────┬─────────────────────────────┬───────────────┘
                   │                             │
    [Tampilan Publik & Sivitas]        [Panel Admin & Petugas]
    (Blade + Tailwind + Alpine.js)    (Bento UI + Master Console)
                   │                             │
                   ▼                             ▼
   ┌─────────────────────────────────────────────────────────────┐
   │                  HTTP KERNEL & ROUTING                      │
   │           (routes/web.php & routes/auth.php)                │
   │  - Middleware: auth, role:admin, role:petugas, role:pengguna│
   └──────────────────────────────┬──────────────────────────────┘
                                  ▼
   ┌─────────────────────────────────────────────────────────────┐
   │              CONTROLLER LAYER (app/Http/Controllers)        │
   │  - ReservationController       - ReservationManagementCtrl  │
   │  - ReportController            - ReportManagementController │
   │  - PublicFacilityController    - AdminUserManagementCtrl    │
   │  - ExportController (PDF/Excel)- FacilityController         │
   └──────────────┬──────────────────────────────┬───────────────┘
                  │ FormRequest Validation       │
                  ▼                              ▼
   ┌──────────────────────────────┐┌─────────────────────────────┐
   │   DATA ACCESS / ELOQUENT     ││     CONCURRENCY ENGINE      │
   │ (app/Models: User, Facility, ││   DB::transaction() with    │
   │  Reservation, DamageReport)  ││       lockForUpdate()       │
   └──────────────┬───────────────┘└─────────────┬───────────────┘
                  │                              │
                  ▼                              ▼
   ┌─────────────────────────────────────────────────────────────┐
   │                     BASIS DATA (MySQL)                      │
   │      users | facilities | reservations | damage_reports     │
   └─────────────────────────────────────────────────────────────┘
```

### Masalah Nyata yang Diselesaikan Sistem:
1. **Bentrokan Jadwal (*Double-Booking*):** Dua orang meminjam ruangan yang sama di jam yang sama pada detik bersamaan (*race condition*).
2. **Keterlambatan Penanganan Aset:** Fasilitas rusak tetap dipinjam orang karena biro sarpras tidak tahu ada kerusakan.
3. **Ketiadaan Audit Trail:** Tidak ada data rekapitulasi jam pemakaian, rasio penolakan, dan riwayat resolusi teknisi.

### 4 Lapis Otorisasi Pengguna (*Multi-Level RBAC*):
| Aktor (*Role*) | Akses & Wewenang | Titik Masuk Utama |
|---|---|---|
| **Visitor (Pengunjung)** | Publik tanpa login; melihat katalog ruangan, slot ketersediaan 30 menit (tanpa data pemohon). | `/` dan `/public/catalog` |
| **Pengguna (Sivitas)** | Mahasiswa/Dosen/Staf terdaftar; mengajukan reservasi, membatalkan mandiri (H-1), melaporkan kerusakan. | `/user/dashboard` |
| **Petugas (Sarpras)** | Memverifikasi antrean permohonan reservasi, validasi bentrok, *override* darurat, menangani tiket kerusakan, mode pemeliharaan. | `/petugas/dashboard` |
| **Admin (Super Admin)**| Verifikasi registrasi sivitas (Pending ➔ Active), membuat akun internal, mengelola master data fasilitas, ekspor laporan resmi (PDF/Excel). | `/admin/dashboard` |

---

## 2. Pipeline Pembelajaran Terstruktur (7 Fase Runtut)

Ikuti urutan langkah di bawah ini untuk menguasai proyek secara bertahap dan teratur:

```
[FASE 0: Mental Model & Setup]
         │
         ▼
[FASE 1: Domain Bisnis & Diagram] ──► Pahami Status & Rules
         │
         ▼
[FASE 2: Database & Model Eloquent] ──► Pahami Skema & Relasi
         │
         ▼
[FASE 3: Keamanan, Autentikasi & RBAC] ──► Pahami Breeze & Spatie
         │
         ▼
[FASE 4: Core Engine Reservasi] ──► Pahami Overlap SQL & Lock
         │
         ▼
[FASE 5: Core Engine Tiketing Kerusakan] ──► Pahami Maintenance Mode
         │
         ▼
[FASE 6: Admin Master & Export Analytics] ──► Pahami DomPDF & Excel
         │
         ▼
[FASE 7: Arsitektur Frontend UI/UX] ──► Pahami Blade, Tailwind, Alpine
```

---

### FASE 0: Mental Model, Arsitektur Folder, & Alur Request
Sebelum membaca kode, pahami bagaimana Laravel 11 mengorganisasi file dan menjalankan request:

1. **Titik Awal Eksekusi HTTP:**
   - Permintaan masuk dari browser ke `public/index.php`.
   - Dimuat oleh kernel Laravel ➔ Diarahkan ke [routes/web.php](file:///c:/my-project/reservation-reporting-system/routes/web.php).
   - Melewati penjagaan **Middleware** (misal: `auth`, `role:admin`).
   - Diteruskan ke **FormRequest** untuk memvalidasi input pengguna.
   - Diproses di dalam **Controller** (memanggil **Model Eloquent**).
   - Hasil dikembalikan dalam bentuk **Blade View** (HTML) atau **JSON Response**.

2. **File & Folder Kunci yang Wajib Dipetakan:**
   - `app/Models/`: Definisi tabel, relasi, tipe data kolom, dan query scopes.
   - `app/Http/Controllers/`: Logika pemrosesan bisnis per modul.
   - `app/Http/Requests/`: Validasi payload request dan pesan error.
   - `database/migrations/`: Cetak biru struktur tabel MySQL.
   - `database/seeders/`: Data awal untuk pengujian lokal.
   - `resources/views/`: Kode antarmuka (Blade template terbagi atas folder `admin/`, `petugas/`, `user/`, `public/`, `components/`).

---

### FASE 1: Membedah Dokumen Bisnis & Diagram Proses
Jangan langsung masuk ke kode PHP sebelum memahami aturan bisnisnya! Buka dan baca dokumen berikut secara berurutan:

1. **[CASE_PROJECT.md](file:///c:/my-project/reservation-reporting-system/CASE_PROJECT.md):**
   - Pahami 17 *User Stories* (US-1 s/d US-17).
   - Aturan jam operasional: **07:00 – 20:00 WIB**, slot kelipatan **30 menit**.
   - Batas pembatalan mandiri pengguna: **Maksimal H-1 (>= 24 jam)**.
   - Otoritas pembatalan darurat (*Override*) oleh Petugas jika terjadi force-majeure.

2. **[doc-process-system/behavioral/03_state_machine_reservasi.md](file:///c:/my-project/reservation-reporting-system/doc-process-system/behavioral/03_state_machine_reservasi.md):**
   - Transisi status reservasi:  
     `Pending` ──(Approve Petugas)──► `Approved` ──(Waktu Terlewati)──► `Completed`  
     `Pending` ──(Reject Petugas)──► `Rejected`  
     `Pending` ──(Batal Mandiri H-1)──► `Cancelled`  
     `Approved` ──(Override Petugas)──► `Cancelled`

3. **[doc-process-system/behavioral/04_state_machine_fasilitas.md](file:///c:/my-project/reservation-reporting-system/doc-process-system/behavioral/04_state_machine_fasilitas.md):**
   - Status fasilitas: `aktif` ⇄ `dalam perbaikan` ⇄ `nonaktif`.
   - Jika `dalam perbaikan`, fasilitas **otomatis terblokir** dari kalender dan tidak dapat disetujui reservasi baru.

4. **[doc-process-system/interaction/01_sequence_diagram_reservasi.md](file:///c:/my-project/reservation-reporting-system/doc-process-system/interaction/01_sequence_diagram_reservasi.md):**
   - Memahami sekuensial: View ➔ Request ➔ FormRequest ➔ Transaction Begin ➔ `SELECT ... FOR UPDATE` ➔ Overlap Check ➔ Commit/Rollback.

---

### FASE 2: Lapisan Data (Migrations & Eloquent Models)

Pelajari bagaimana data disimpan dan dihubungkan.

#### 1. Membedah Migrasi Basis Data:
- [0001_01_01_000000_create_users_table.php](file:///c:/my-project/reservation-reporting-system/database/migrations/0001_01_01_000000_create_users_table.php):
  - Kolom `identity_number` (NIM/NIP terindeks).
  - Kolom `status` enum: `pending`, `active`, `rejected`.
  - Kolom `assignment_zone` (zona kerja operasional petugas).
- [2026_09_20_000001_create_facilities_table.php](file:///c:/my-project/reservation-reporting-system/database/migrations/2026_09_20_000001_create_facilities_table.php):
  - Kolom `equipment` dengan tipe `json` (menyimpan array perlengkapan seperti Proyektor, AC, Sound System).
  - Kolom `status` enum (`aktif`, `dalam perbaikan`, `nonaktif`).
- [2026_09_20_000002_create_reservations_table.php](file:///c:/my-project/reservation-reporting-system/database/migrations/2026_09_20_000002_create_reservations_table.php):
  - Kolom kunci: `ticket_code`, `reservation_date`, `start_time`, `end_time`, `total_slots`.
  - Dua relasi pengguna: `user_id` (pemohon) dan `reviewed_by` (petugas pemeriksa).
- [2026_09_20_000003_create_damage_reports_table.php](file:///c:/my-project/reservation-reporting-system/database/migrations/2026_09_20_000003_create_damage_reports_table.php):
  - `attachment_photo`, `status` (`baru`, `diproses`, `selesai`, `ditolak`), `is_facility_locked` (boolean).

#### 2. Membedah Model Eloquent:
- [User.php](file:///c:/my-project/reservation-reporting-system/app/Models/User.php):
  - Traits: `HasFactory`, `Notifiable`, `HasRoles` (Spatie), `SoftDeletes`.
  - Relasi: `reservations()`, `damageReports()`, `reviewedReservations()`, `handledReports()`.
- [Facility.php](file:///c:/my-project/reservation-reporting-system/app/Models/Facility.php):
  - `protected $casts = ['equipment' => 'array', 'capacity' => 'integer'];` (otomatis serialize/deserialize JSON ke PHP array).
  - Scope: `scopeActive()` (`where('status', 'aktif')`).
- [Reservation.php](file:///c:/my-project/reservation-reporting-system/app/Models/Reservation.php):
  - Relasi Invers: `user()`, `facility()`, `reviewer()`.
  - Query Scopes: `scopeApproved()`, `scopeOnDate($date)`.
- [DamageReport.php](file:///c:/my-project/reservation-reporting-system/app/Models/DamageReport.php):
  - `casts = ['is_facility_locked' => 'boolean', 'resolved_at' => 'datetime']`.
  - Relasi: `user()`, `facility()`, `handler()`.

---

### FASE 3: Keamanan, Autentikasi, & Otorisasi RBAC

1. **Alur Pendaftaran Sivitas Mandiri (Self-Registration):**
   - File: [RegisteredUserController.php](file:///c:/my-project/reservation-reporting-system/app/Http/Controllers/Auth/RegisteredUserController.php)
   - Sivitas mendaftar melalui `/register`.
   - Akun baru otomatis diberi status `'pending'`, **bukan** langsung aktif!
   - Pengguna dengan status `'pending'` dicegat di alur login oleh [AuthenticatedSessionController.php](file:///c:/my-project/reservation-reporting-system/app/Http/Controllers/Auth/AuthenticatedSessionController.php) sehingga tidak dapat mengakses sistem sebelum diverifikasi oleh Admin.

2. **Verifikasi Akun oleh Admin (ADM-01):**
   - File: [AdminUserManagementController.php](file:///c:/my-project/reservation-reporting-system/app/Http/Controllers/AdminUserManagementController.php)
   - Method `verifyUser($id)`: Mengubah status dari `'pending'` menjadi `'active'`.
   - Method `rejectUser($id, RejectUserRequest $request)`: Mengubah status menjadi `'rejected'` dengan mencatat alasan penolakan pada kolom `rejection_reason`.

3. **Proteksi Role Spatie:**
   - Didefinisikan di [routes/web.php](file:///c:/my-project/reservation-reporting-system/routes/web.php) menggunakan middleware:
     - `Route::middleware(['auth', 'role:admin'])`
     - `Route::middleware(['auth', 'role:petugas'])`
     - `Route::middleware(['auth', 'role:pengguna'])`
   - Redirect cerdas di route `/dashboard`:
     ```php
     if ($user->hasRole('admin')) return redirect()->route('admin.dashboard');
     if ($user->hasRole('petugas')) return redirect()->route('petugas.dashboard');
     return redirect()->route('user.dashboard');
     ```

---

### FASE 4: Core Engine #1 - Manajemen Reservasi & Algoritma Anti-Bentrok

Inilah bagian **paling kritikal** di seluruh repositori. Pahami setiap baris kodenya:

#### 1. Validasi Input Pengajuan (`StoreReservationRequest.php`)
- Memastikan format tanggal `Y-m-d` dan tidak boleh di masa lampau (`after_or_equal:today`).
- Memastikan `start_time` dan `end_time` berformat `H:i`, berada di rentang **07:00 s/d 20:00**, dengan selisih kelipatan **30 menit**.

#### 2. Logika Simpan Pengajuan (`ReservationController::store`)
Lihat [ReservationController.php](file:///c:/my-project/reservation-reporting-system/app/Http/Controllers/ReservationController.php#L47-L117):
- **Generate Kode Tiket**: Format `TKT-YYYYMMDD-XXXX` unik.
- **Pengecekan Awal Overlap**: Memeriksa apakah sudah ada reservasi bertatus `approved` yang bertabrakan sebelum memasukkan status `pending`.

#### 3. Logika Persetujuan Petugas & Anti Race Condition (`ReservationManagementController::approve`)
Lihat [ReservationManagementController.php](file:///c:/my-project/reservation-reporting-system/app/Http/Controllers/ReservationManagementController.php#L90-L155):
- Menggunakan transaksi dan **Pessimistic Locking**:
  ```php
  DB::transaction(function () use ($id) {
      // 1. Kunci baris data di level database mesin InnoDB (Anti Race Condition)
      $res = Reservation::lockForUpdate()->findOrFail($id);

      // 2. Cegah pemrosesan ganda
      if ($res->status !== 'pending') {
          throw new \Exception('Reservasi sudah diproses sebelumnya.');
      }

      // 3. Cegah approval jika fasilitas masuk mode perbaikan
      $facility = Facility::find($res->facility_id);
      if ($facility && $facility->status === 'dalam perbaikan') {
          throw new \Exception('Fasilitas sedang dalam perbaikan.');
      }

      // 4. FORMULA OVERLAP SQL MUTLAK:
      // (req_start < existing_end) AND (req_end > existing_start)
      $overlap = Reservation::where('facility_id', $res->facility_id)
          ->where('reservation_date', $res->reservation_date)
          ->where('status', 'approved')
          ->where('id', '!=', $res->id)
          ->where(function ($query) use ($res) {
              $query->where('start_time', '<', $res->end_time)
                    ->where('end_time', '>', $res->start_time);
          })
          ->exists();

      if ($overlap) {
          throw new \Exception('Jadwal bentrok dengan reservasi yang telah disetujui sebelumnya!');
      }

      // 5. Update menjadi approved
      $res->update([
          'status'      => 'approved',
          'reviewed_by' => Auth::id(),
          'reviewed_at' => Carbon::now(),
      ]);
  });
  ```

#### 4. Pembatalan Mandiri H-1 vs Override Petugas
- **Pembatalan Mandiri Sivitas (`ReservationController::cancel`)**:
  - Divalidasi: `$hoursDifference = Carbon::now()->diffInHours($startDateTime, false);`
  - Jika `$hoursDifference < 24`, aksi digagalkan (aturan batas H-1).
- **Pembatalan Darurat Petugas (`ReservationManagementController::forceCancel`)**:
  - Petugas memiliki wewenang membatalkan reservasi yang sudah `approved` dengan syarat wajib menyertakan alasan pembatalan (`cancellation_reason`).

---

### FASE 5: Core Engine #2 - Pelaporan Kerusakan & Sinkronisasi Fasilitas

1. **Pengajuan Tiket Kerusakan (`ReportController::store`):**
   - File: [ReportController.php](file:///c:/my-project/reservation-reporting-system/app/Http/Controllers/ReportController.php)
   - Unggah berkas foto bukti kerusakan (maksimal 2MB, format jpg/jpeg/png).
   - Disimpan ke storage publik: `$path = $request->file('attachment_photo')->store('damage-reports', 'public');`.
   - Kode laporan otomatis: `REP-YYYYMMDD-XXXX`. Status awal: `'baru'`.

2. **Penanganan & Sinkronisasi Mode Perbaikan (`ReportManagementController`):**
   - File: [ReportManagementController.php](file:///c:/my-project/reservation-reporting-system/app/Http/Controllers/ReportManagementController.php)
   - Siklus status tiket: `'baru'` ➔ `'diproses'` ➔ `'selesai'` (atau `'ditolak'`).
   - Fitur **Toggle Maintenance** (`toggleMaintenance`):
     - Petugas dapat mengunci fasilitas menjadi `'dalam perbaikan'`.
     - Ketika status ini aktif, di kalender publik slot fasilitas tersebut langsung terkunci, dan controller approval otomatis menolak permohonan reservasi baru pada fasilitas tersebut.
   - Saat laporan diubah menjadi `'selesai'`, petugas wajib menginput `resolution_note` (catatan teknisi) dan mencatat timestamp `resolved_at`.

---

### FASE 6: Modul Master Data Admin & Ekspor Dokumen

1. **Master Fasilitas (CRUD):**
   - File: [FacilityController.php](file:///c:/my-project/reservation-reporting-system/app/Http/Controllers/FacilityController.php)
   - Input spesifikasi ruangan: Nama, kode, kategori, gedung, lantai, kapasitas, peralatan JSON, foto fasilitas, status aktif.

2. **Dashboard KPI & Analitik Statuter:**
   - File: [ExportController.php](file:///c:/my-project/reservation-reporting-system/app/Http/Controllers/ExportController.php)
   - Perhitungan KPI:
     - *Occupancy Rate (%):* `(approvedReservations / totalReservations) * 100`
     - *SLA Resolution Index (%):* `(resolvedDamageCount / totalDamageReports) * 100`
     - Utilisasi jam per fasilitas: `total_slots * 0.5` jam.

3. **Ekspor Dokumen Resmi:**
   - **PDF Landscape A4:** Menggunakan `Barryvdh\DomPDF\Facade\Pdf::loadView(...)`.
   - **Excel / CSV Spreadsheet:** Menggunakan pustaka *Maatwebsite Laravel Excel* (`App\Exports\ReservationsExport` dan `App\Exports\DamageReportsExport`).

---

### FASE 7: Arsitektur Frontend UI/UX (Blade, Tailwind, & Alpine.js)

1. **Hierarki Layouting (Blade Template Inheritance):**
   - Layout utama dibungkus di [resources/views/layouts/](file:///c:/my-project/reservation-reporting-system/resources/views/layouts/):
     - `layouts/admin.blade.php` ➔ Digunakan dengan komponen `<x-admin-layout>`
     - `layouts/petugas.blade.php` ➔ Digunakan dengan komponen `<x-petugas-layout>`
     - `layouts/public.blade.php` ➔ Digunakan dengan komponen `<x-public-layout>`
     - `layouts/app.blade.php` ➔ Layout pengguna sivitas.

2. **Daur Ulang Komponen Mandiri (`resources/views/components/cava/`):**
   - `<x-cava.sidebar>`: Sidebar navigasi responsif dengan state route aktif (`request()->routeIs(...)`).
   - `<x-cava.header>`: Header informasi dengan profile menu dropdown.
   - `<x-cava.stat-card>`: Kartu metrik Bento UI dengan warna status gradasi.
   - `<x-cava.status-badge>`: Badge warna semantik (Hijau: Approved/Selesai, Kuning: Pending/Diproses, Merah: Rejected/Cancelled).
   - `<x-cava.slot-matrix>`: Grid pemilih slot waktu 30 menit interaktif.

3. **Interaktivitas Alpine.js Ringan:**
   - Alpine.js digunakan untuk interaksi modal, tab filter, dan dropdown tanpa me-reload halaman:
     - `x-data="{ showModal: false, selectedId: null }"`
     - `@click="showModal = true; selectedId = 12"`
     - `x-show="showModal"` dengan transisi halus `x-transition`.

---

## 3. Bedah Sintaks & Pola Desain Kunci (*Syntax & Patterns Deep-Dive*)

### 1. Sintaks PHP 8.2+ Modern pada Proyek Ini
- **Union Types:**
  ```php
  public function approve(int|string $id): RedirectResponse
  ```
  *Artinya:* Parameter `$id` boleh berupa integer atau string (misal UUID), dan return value wajib berupa objek `RedirectResponse`.
- **Typed Class Properties & Array Generics:**
  ```php
  /** @var list<string> */
  protected $fillable = ['ticket_code', 'user_id', ...];
  ```
- **Nullsafe Operator (`?->`):**
  ```php
  $reviewerName = $reservation->reviewer?->name ?? 'Belum diverifikasi';
  ```
  *Artinya:* Jika relasi `reviewer` bernilai null, kode tidak akan crash, melainkan mengembalikan nilai default.

### 2. Sintaks Laravel 11 & Eloquent
- **Eager Loading Anti-N+1 Query:**
  ```php
  $reservations = Reservation::with(['facility', 'user'])->where(...)->get();
  ```
  *Alasan:* Jika Anda menggunakan `$res->facility->name` dalam perulangan `foreach` tanpa `with()`, Laravel akan mengeksekusi 1 kueri tambahan untuk setiap baris data (Problem N+1). Dengan `with()`, Laravel hanya mengeksekusi tepat 2 kueri.
- **Form Request Authorization & Rules:**
  ```php
  public function authorize(): bool {
      return Auth::check(); // Hanya user yang login yang boleh memproses
  }
  ```
- **Soft Deletes (`use SoftDeletes;`):**
  Saat `Facility::destroy($id)` dipanggil, data tidak dihapus permanen dari hard disk basis data, melainkan kolom `deleted_at` diisi timestamp. Ini menjamin riwayat reservasi masa lalu tidak kehilangan relasi ke data fasilitas (*data integrity preservation*).

### 3. Logika Overlap SQL (Rumus Matematika Rentang Waktu)
Dua rentang waktu $[A_{start}, A_{end}]$ dan $[B_{start}, B_{end}]$ dikatakan saling bertumpukan (*overlap*) jika dan hanya jika:
$$\text{Overlap} \iff (A_{start} < B_{end}) \land (A_{end} > B_{start})$$
Implementasi Eloquent-nya:
```php
$query->where('start_time', '<', $newEndTime)
      ->where('end_time', '>', $newStartTime);
```
*Mengapa bukan `whereBetween`?*  
Karena `whereBetween` akan gagal mendeteksi jika permohonan baru memiliki durasi lebih panjang yang "menelan" seluruh jadwal yang ada di dalamnya (misal jadwal lama 09:00-10:00, jadwal baru 08:00-12:00). Rumus irisan di atas 100% tahan uji terhadap segala kombinasi irisan rentang waktu.

---

## 4. Panduan Menelusuri File Kode (*Code Tour*)

Gunakan tabel daftar file ini untuk membuka dan membaca kodenya langsung di editor Anda:

| Urutan Baca | Komponen | Lokasi File | Fokus Analisis |
|:---:|---|---|---|
| 1 | **Skema DB** | [05_erd_database.md](file:///c:/my-project/reservation-reporting-system/doc-process-system/structural/05_erd_database.md) | Relasi users, facilities, reservations, damage_reports |
| 2 | **Routing** | [routes/web.php](file:///c:/my-project/reservation-reporting-system/routes/web.php) | Pemetaan endpoint, penamaan route, middleware RBAC |
| 3 | **Model Induk** | [app/Models/User.php](file:///c:/my-project/reservation-reporting-system/app/Models/User.php) | Relasi Spatie role, relasi tiket peminjaman dan laporan |
| 4 | **Model Fasilitas** | [app/Models/Facility.php](file:///c:/my-project/reservation-reporting-system/app/Models/Facility.php) | JSON casting kolom `equipment`, query scope `scopeActive` |
| 5 | **Model Reservasi** | [app/Models/Reservation.php](file:///c:/my-project/reservation-reporting-system/app/Models/Reservation.php) | Relasi user & facility, casts tanggal, scope `scopeApproved` |
| 6 | **Form Pengajuan** | [app/Http/Controllers/ReservationController.php](file:///c:/my-project/reservation-reporting-system/app/Http/Controllers/ReservationController.php) | Method `create()`, `store()`, `history()`, dan `cancel()` |
| 7 | **Approval Petugas** | [app/Http/Controllers/ReservationManagementController.php](file:///c:/my-project/reservation-reporting-system/app/Http/Controllers/ReservationManagementController.php) | Method `approve()` (baca baris 90-150: `lockForUpdate` & overlap) |
| 8 | **Tiket Kerusakan** | [app/Http/Controllers/ReportController.php](file:///c:/my-project/reservation-reporting-system/app/Http/Controllers/ReportController.php) | Upload berkas bukti gambar dan penomoran tiket |
| 9 | **Resolusi Tiket** | [app/Http/Controllers/ReportManagementController.php](file:///c:/my-project/reservation-reporting-system/app/Http/Controllers/ReportManagementController.php) | Method `updateStatus()` & `toggleMaintenance()` |
| 10 | **Verifikasi User** | [app/Http/Controllers/AdminUserManagementController.php](file:///c:/my-project/reservation-reporting-system/app/Http/Controllers/AdminUserManagementController.php) | Antrean verifikasi akun `pending` |
| 11 | **Ekspor Laporan** | [app/Http/Controllers/ExportController.php](file:///c:/my-project/reservation-reporting-system/app/Http/Controllers/ExportController.php) | Perhitungan KPI okupansi, DomPDF, dan Laravel Excel |
| 12 | **Layout Blade** | [resources/views/layouts/public.blade.php](file:///c:/my-project/reservation-reporting-system/resources/views/layouts/public.blade.php) | Komponen master layout, Alpine.js, Tailwind styling |

---

## 5. Checklist Verifikasi Pemahaman (*Self-Assessment Checkpoints*)

Setelah mempelajari seluruh tahapan di atas, ujilah pemahaman Anda dengan pertanyaan evaluasi mandiri berikut:

- [ ] **Alur Autentikasi:** Mengapa mahasiswa yang baru selesai mendaftar mandiri tidak bisa langsung login ke sistem? File mana yang memblokirnya dan query apa yang dijalankan?
- [ ] **Integritas Konkurensi:** Apa akibatnya jika operator memanggil query `approve()` tanpa `lockForUpdate()` ketika ada 2 operator yang menekan tombol setuju di detik yang sama untuk ruangan yang sama?
- [ ] **Algoritma Overlap:** Mengapa rumus validasi tumpang-tindih jadwal menggunakan `start < new_end AND end > new_start`? Ujilah dengan skenario waktu bersinggungan di menit yang sama!
- [ ] **Otorisasi Multi-Lapisan:** Di mana saja hak akses diperiksa ketika pengguna membuka halaman `/reports/management`? Sebutkan pengecekan di level Route, Controller, dan Blade!
- [ ] **Manajemen Berkas Bukti:** Bagaimana file gambar kerusakan disimpan dan bagaimana cara sistem membersihkannya ketika reservasi atau laporan dibatalkan?

---

## 6. Daftar Lengkap Keterhubungan Seluruh File & Komponen Sistem (100% Codebase Coverage)

Bagian ini membedah **seluruh file tanpa terkecuali** di dalam repositori CAVA ke dalam format daftar hierarkis terstruktur (*Request ➔ Route ➔ Middleware ➔ FormRequest ➔ Controller ➔ Model/Database ➔ Blade View/Component*), lengkap dengan penjelasan mendalam mengenai peran dan alur interaksi masing-masing file pada setiap fitur.

### 📌 Pembagian Tanggung Jawab & Pemetaan Modul Tim Pengembang (4 Orang)

Berikut adalah pemetaan nomor poin modul yang wajib dipahami dan dikuasai oleh masing-masing anggota tim pengembang:

1. **Programmer 1 — Modul Admin (Pengembangan & Tata Kelola Admin):**
   * **Nomor Poin yang Wajib Dipahami:** **3, 6, 7, 8**
     * **Poin 3:** Master Data Fasilitas Kampus (Skema, Seeder, Model, & CRUD Admin - ADM-03)
     * **Poin 6:** Konsol Admin: Verifikasi Akun Pending Sivitas & Manajemen Pengguna Internal (ADM-01 & ADM-02)
     * **Poin 7:** Konsol Admin: Dasbor Eksekutif & Statistik Utama (ADM-04 / US-17)
     * **Poin 8:** Konsol Admin: Rekapitulasi Analitik, Audit Okupansi, & Ekspor Resmi (PDF & Excel - ADM-04)

2. **Programmer 2 — Modul Pengguna (Portal Sivitas Akademika / User):**
   * **Nomor Poin yang Wajib Dipahami:** **9, 10, 11, 12**
     * **Poin 9:** Portal Pengguna (Sivitas): Dasbor Pribadi & Ringkasan Tiket Aktif
     * **Poin 10:** Portal Pengguna: Formulir Pengajuan Reservasi Ruangan Mandiri (USR-01)
     * **Poin 11:** Portal Pengguna: Riwayat Reservasi & Pembatalan Mandiri H-1 (USR-02 & USR-03)
     * **Poin 12:** Portal Pengguna: Pelaporan Kerusakan Fasilitas & Pelacakan Tiket (USR-04 & USR-05)

3. **Programmer 3 — Modul Petugas (Operasional Harian Petugas Sarpras):**
   * **Nomor Poin yang Wajib Dipahami:** **13, 14, 15, 16**
     * **Poin 13:** Operasional Petugas Sarpras: Dasbor Monitoring Antrean Cerdas (PTG-01 / US-8)
     * **Poin 14:** Operasional Petugas Sarpras: Matriks Jadwal 30 Menit Operasional & Detail Pemohon (Schedule Matrix)
     * **Poin 15:** Operasional Petugas Sarpras: Persetujuan, Penolakan, & Pembatalan Darurat / Override (PTG-02 & PTG-03)
     * **Poin 16:** Operasional Petugas Sarpras: Manajemen Tiket Kerusakan, Resolusi & Mode Perbaikan Fasilitas (PTG-04 & PTG-05)

4. **Programmer 4 + Project Manager — Fondasi Inti, Autentikasi, Portal Publik & Komponen Bersama:**
   * **Nomor Poin yang Wajib Dipahami:** **1, 2, 4, 5, 17, 18, 19**
     * **Poin 1:** Infrastruktur Kernel, Bootstrap, & Konfigurasi Lingkungan (Laravel 11 Core)
     * **Poin 2:** Fondasi Basis Data Pengguna, Cache, Jobs, & Otorisasi Spatie RBAC
     * **Poin 4:** Portal Publik: Beranda, Katalog Fasilitas, Filter & Matriks Ketersediaan (PUB-01 & PUB-02)
     * **Poin 5:** Alur Autentikasi Pengguna & Registrasi Mandiri Sivitas (Laravel Breeze Core - AUTH-01 & AUTH-02)
     * **Poin 17:** Manajemen Akun Profil Pengguna (Profile Settings & Password)
     * **Poin 18:** Pusat Informasi Publik, Kebijakan, Syarat & Bantuan Sivitas
     * **Poin 19:** Komponen Pembungkus Tampilan & Desain Bersama (Layouts & Shared UI Components)
     * *(Peran Supervisi PM):* Memahami arsitektur global, fondasi basis data antarmodul, konsistensi UI design system, dan integrasi antar fitur yang dikerjakan oleh Programmer 1, 2, dan 3.

---

### 1. Infrastruktur Kernel, Bootstrap, & Konfigurasi Lingkungan (Laravel 11 Core)

* **Berkas yang Terhubung:**
  * [composer.json](file:///c:/my-project/reservation-reporting-system/composer.json) — Dependensi Pustaka PHP (Fondasi Level Dependensi Proyek)
  * [package.json](file:///c:/my-project/reservation-reporting-system/package.json) — Dependensi Node.js & Front-End
  * [bootstrap/app.php](file:///c:/my-project/reservation-reporting-system/bootstrap/app.php) — Kernel Bootstrap aplikasi Laravel 11 (Titik Masuk Konfigurasi Global)
  * [bootstrap/providers.php](file:///c:/my-project/reservation-reporting-system/bootstrap/providers.php) — Registri Service Provider
  * [routes/console.php](file:///c:/my-project/reservation-reporting-system/routes/console.php) — Perintah CLI Artisan
  * [vite.config.js](file:///c:/my-project/reservation-reporting-system/vite.config.js) — Bundler Asset & Hot Module Replacement
  * [tailwind.config.js](file:///c:/my-project/reservation-reporting-system/tailwind.config.js) — Konfigurasi Utility Styling & Theme
  * [resources/js/app.js](file:///c:/my-project/reservation-reporting-system/resources/js/app.js) — Inisialisasi Runtime Front-End Alpine.js
* **Penjelasan Alur & Peran Masing-Masing File:**
  1. **bootstrap/app.php:** Titik masuk tunggal konfigurasi Laravel 11 menggunakan `ApplicationBuilder`. Bertanggung jawab mendaftarkan rute web (`web.php`), console (`console.php`), endpoint health check (`/up`), mendefinisikan alias middleware Spatie RBAC (`role`, `permission`, `role_or_permission`), serta menangani render exception JSON jika request mengarah ke API (`api/*`).
  2. **bootstrap/providers.php:** Menampung array service provider aplikasi yang dimuat saat kernel Laravel booting.
  3. **routes/console.php:** Tempat deklarasi perintah CLI kustom menggunakan closure `Artisan::command()`.
  4. **resources/js/app.js:** Mengimpor framework Alpine.js, menautkannya ke window global browser (`window.Alpine = Alpine;`), dan mengeksekusi `Alpine.start()` agar komponen UI (seperti modal, dropdown, dan tab) dapat bereaksi interaktif.
  5. **tailwind.config.js:** Menentukan path template yang dipindai compiler (`./resources/views/**/*.blade.php`), konfigurasi palet warna khusus institusi CAVA, font family (Inter, Outfit), dan animasi micro-interaction.
  6. **vite.config.js:** Mengonfigurasi plugin Laravel Vite untuk mengompilasi file `resources/css/app.css` dan `resources/js/app.js` secara otomatis saat proses build atau local development.
  7. **package.json & composer.json:** Mengelola seluruh dependensi proyek: pustaka PHP (Spatie Laravel Permission, DomPDF, Laravel Excel, Breeze) dan pustaka JavaScript (Tailwind CSS, Alpine.js, PostCSS, Autoprefixer).

---

### 2. Fondasi Basis Data Pengguna, Cache, Jobs, & Otorisasi Spatie RBAC

* **Berkas yang Terhubung:**
  * [0001_01_01_000000_create_users_table.php](file:///c:/my-project/reservation-reporting-system/database/migrations/0001_01_01_000000_create_users_table.php) — Migrasi Tabel Pengguna (Titik 0 Skema DB)
  * [0001_01_01_000001_create_cache_table.php](file:///c:/my-project/reservation-reporting-system/database/migrations/0001_01_01_000001_create_cache_table.php) — Migrasi Cache Sistem
  * [0001_01_01_000002_create_jobs_table.php](file:///c:/my-project/reservation-reporting-system/database/migrations/0001_01_01_000002_create_jobs_table.php) — Migrasi Antrean Pekerjaan
  * [2026_09_20_151512_create_permission_tables.php](file:///c:/my-project/reservation-reporting-system/database/migrations/2026_09_20_151512_create_permission_tables.php) — Migrasi Tabel RBAC Spatie
  * [2026_10_03_000001_add_inactive_and_soft_deletes_to_users_table.php](file:///c:/my-project/reservation-reporting-system/database/migrations/2026_10_03_000001_add_inactive_and_soft_deletes_to_users_table.php) — Migrasi Pembaruan Status & Soft Deletes
  * [app/Models/User.php](file:///c:/my-project/reservation-reporting-system/app/Models/User.php) — Model Eloquent User (Titik 0 Entitas Data, Relasi & RBAC)
  * [database/seeders/RolePermissionSeeder.php](file:///c:/my-project/reservation-reporting-system/database/seeders/RolePermissionSeeder.php) — Seeder Hak Akses
  * [database/seeders/UserSeeder.php](file:///c:/my-project/reservation-reporting-system/database/seeders/UserSeeder.php) — Seeder Akun Default
  * [database/seeders/DatabaseSeeder.php](file:///c:/my-project/reservation-reporting-system/database/seeders/DatabaseSeeder.php) — Seeder Master Eksekusi
* **Penjelasan Alur & Peran Masing-Masing File:**
  1. **Migrasi create_users_table:** Membangun tabel `users` dengan atribut nomor induk (`identity_number`), nomor telepon, departemen/jurusan, lampiran identitas (`id_card_path`), peran (`role`), dan status awal `pending`.
  2. **Migrasi cache & jobs:** Menyediakan tabel database persisten untuk caching kueri cepat dan queue worker background process.
  3. **Migrasi permission_tables:** Disediakan oleh paket *spatie/laravel-permission* untuk merakit skema tabel peran dan perizinan (`roles`, `permissions`, `model_has_roles`, dll).
  4. **Migrasi modifikasi users (2026_10_03):** Memperluas kolom `status` menjadi string untuk mengakomodasi status `'inactive'` dan menambahkan kolom `deleted_at` (*SoftDeletes*) agar pengguna tidak terhapus permanen dari relasi audit.
  5. **RolePermissionSeeder.php:** Membersihkan cache izin Spatie dan mendaftarkan 4 role resmi sistem: `admin`, `petugas`, `pengguna`, `visitor`.
  6. **UserSeeder.php & DatabaseSeeder.php:** Menanamkan data akun bawaan untuk pengujian (Super Admin, Petugas Sarpras Zona 1 & 2, serta Pengguna Sivitas Akademika) lengkap dengan sinkronisasi role Spatie.
  7. **Model User.php:** Titik sentral autentikasi; mengimplementasikan traits `HasRoles`, `Notifiable`, `SoftDeletes`, enkripsi kata sandi otomatis (`hashed`), dan relasi Eloquent: `reservations()`, `damageReports()`, `reviewedReservations()`, serta `handledReports()`.

---

### 3. Master Data Fasilitas Kampus (Skema, Seeder, Model, & CRUD Admin - ADM-03)

* **Berkas yang Terhubung:**
  * [2026_09_20_000001_create_facilities_table.php](file:///c:/my-project/reservation-reporting-system/database/migrations/2026_09_20_000001_create_facilities_table.php) — Migrasi Tabel Fasilitas (Titik 0 Skema DB)
  * [app/Models/Facility.php](file:///c:/my-project/reservation-reporting-system/app/Models/Facility.php) — Model Eloquent Fasilitas (Definisi Casting, Scope, & Relasi)
  * [database/seeders/FacilitySeeder.php](file:///c:/my-project/reservation-reporting-system/database/seeders/FacilitySeeder.php) — Data Awal Fasilitas Umum
  * [database/seeders/FacilityUndipSeeder.php](file:///c:/my-project/reservation-reporting-system/database/seeders/FacilityUndipSeeder.php) — Data Venue Riil Kampus
  * [app/Http/Requests/Admin/StoreFacilityRequest.php](file:///c:/my-project/reservation-reporting-system/app/Http/Requests/Admin/StoreFacilityRequest.php) — Validasi Tambah Fasilitas
  * [app/Http/Requests/Admin/UpdateFacilityRequest.php](file:///c:/my-project/reservation-reporting-system/app/Http/Requests/Admin/UpdateFacilityRequest.php) — Validasi Ubah Fasilitas
  * [routes/web.php](file:///c:/my-project/reservation-reporting-system/routes/web.php) — Endpoint Grup Admin (`/admin/facilities`)
  * [app/Http/Controllers/FacilityController.php](file:///c:/my-project/reservation-reporting-system/app/Http/Controllers/FacilityController.php) — Controller CRUD Fasilitas
  * [app/View/Components/AdminLayout.php](file:///c:/my-project/reservation-reporting-system/app/View/Components/AdminLayout.php) — Komponen Pembungkus Layout Admin
  * [resources/views/layouts/admin.blade.php](file:///c:/my-project/reservation-reporting-system/resources/views/layouts/admin.blade.php) — Master Layout Admin
  * [resources/views/admin/facility-master.blade.php](file:///c:/my-project/reservation-reporting-system/resources/views/admin/facility-master.blade.php) — Tampilan Manajemen Fasilitas
* **Penjelasan Alur & Peran Masing-Masing File:**
  1. **Migrasi create_facilities_table:** Membangun tabel `facilities` dengan kolom kode unik (`code`), nama, kategori (auditorium, lab, ruang_rapat, kelas), gedung, lantai, kapasitas kursi, kolom JSON peralatan (`equipment`), foto sampul, dan status (`aktif`, `dalam perbaikan`, `nonaktif`).
  2. **FacilitySeeder & FacilityUndipSeeder:** Memasukkan dummy data venue representatif kampus (Auditorium, Gedung Serbaguna, Lab Komputer) beserta rincian inventaris alatnya.
  3. **Model Facility.php:** Menerapkan casting atribut otomatis: `equipment` (array PHP ⇄ JSON DB) dan `capacity` (integer). Menyediakan scope kueri `scopeActive()` (`WHERE status = 'aktif'`) dan relasi `hasMany` ke reservasi serta laporan kerusakan.
  4. **StoreFacilityRequest & UpdateFacilityRequest:** Memvalidasi form input admin: kode unik, format gambar valid (maks 2MB, JPG/PNG), validasi batas minimal kapasitas integer, dan kelengkapan deskripsi.
  5. **FacilityController.php:** Menjalankan logika bisnis: menampilkan daftar master fasilitas dengan filter kategori dan pencarian nama, menyimpan data baru beserta upload file cover (`store`), memperbarui data (`update`), mengubah status operasional (`toggleStatus`), serta menghapus ruangan via soft-delete (`destroy`).
  6. **Routing web.php:** Menghubungkan URL `/admin/facility-master` dan endpoint resource `/admin/facilities` di bawah perlindungan middleware `['auth', 'role:admin']`.
  7. **View facility-master.blade.php & Layout admin.blade.php:** Merender antarmuka Bento UI untuk admin, modal form Alpine.js untuk tambah/edit data, badge status operasional, dan thumbnail gambar fasilitas.

---

### 4. Portal Publik: Beranda, Katalog Fasilitas, Filter & Matriks Ketersediaan (PUB-01 & PUB-02)

* **Berkas yang Terhubung:**
  * [app/Models/Facility.php](file:///c:/my-project/reservation-reporting-system/app/Models/Facility.php) — Model Fasilitas (Titik 0 Entitas Fasilitas & Scope Kueri)
  * [app/Models/Reservation.php](file:///c:/my-project/reservation-reporting-system/app/Models/Reservation.php) — Model Reservasi (Titik 0 Data Peminjaman Terisi)
  * [routes/web.php](file:///c:/my-project/reservation-reporting-system/routes/web.php) — Rute Publik CAVA
  * [app/Http/Controllers/PublicFacilityController.php](file:///c:/my-project/reservation-reporting-system/app/Http/Controllers/PublicFacilityController.php) — Controller Portal Publik
  * [app/View/Components/PublicLayout.php](file:///c:/my-project/reservation-reporting-system/app/View/Components/PublicLayout.php) — Class Komponen Layout Publik
  * [resources/views/layouts/public.blade.php](file:///c:/my-project/reservation-reporting-system/resources/views/layouts/public.blade.php) — Master Layout Portal Publik
  * [resources/views/public/home.blade.php](file:///c:/my-project/reservation-reporting-system/resources/views/public/home.blade.php) — Landing Page Beranda
  * [resources/views/public/catalog.blade.php](file:///c:/my-project/reservation-reporting-system/resources/views/public/catalog.blade.php) — Katalog & Filter Fasilitas
  * [resources/views/public/availability.blade.php](file:///c:/my-project/reservation-reporting-system/resources/views/public/availability.blade.php) — Visualisasi Kalender Matriks Publik
  * [resources/views/components/cava/slot-matrix.blade.php](file:///c:/my-project/reservation-reporting-system/resources/views/components/cava/slot-matrix.blade.php) — Grid Matriks Slot 30 Menit
  * [resources/views/components/cava/status-badge.blade.php](file:///c:/my-project/reservation-reporting-system/resources/views/components/cava/status-badge.blade.php) — Komponen Badge Status
  * [resources/views/components/cava/header.blade.php](file:///c:/my-project/reservation-reporting-system/resources/views/components/cava/header.blade.php) — Navigasi Header Publik
  * [resources/views/components/cava/footer.blade.php](file:///c:/my-project/reservation-reporting-system/resources/views/components/cava/footer.blade.php) — Footer Publik
* **Penjelasan Alur & Peran Masing-Masing File:**
  1. **routes/web.php:** Menyediakan rute terbuka tanpa kewajiban login: `/` (home), `/public/catalog` (katalog), `/public/availability` (kalender jadwal), serta API JSON publik `/api/availability/{date}` dan `/api/facilities/search`.
  2. **PublicFacilityController.php:**
     * `home()`: Menampilkan ringkasan direktori venue teratas dan metrik kampus.
     * `index()`: Menangani filter multi-dimensi (pencarian kata kunci, kategori majemuk, opsi peralatan array, kapasitas minimal) dan menyimpan filter terakhir ke cookie browser pengguna.
     * `availability()` & `getMatrixAvailability()`: Menarik jadwal seluruh fasilitas pada tanggal terpilih, memetakan slot 30 menit (07:00-20:00 WIB) yang terisi oleh reservasi berstatus `approved`, sekaligus menyembunyikan identitas pemohon demi privasi data.
     * `searchSuggestions()`: Menyediakan data autocomplete instan dalam format JSON untuk input search bar.
  3. **Views public/*.blade.php:**
     * `home.blade.php`: Tampilan beranda modern dengan hero section, fitur pencarian cepat, panduan alur reservasi, dan showcase venue.
     * `catalog.blade.php`: Direktori kartu fasilitas grid responsive dengan drawer filter samping dan modal detail spesifikasi alat.
     * `availability.blade.php`: Antarmuka pemilihan tanggal dan kalender visual ketersediaan.
  4. **Komponen CAVA (slot-matrix, status-badge, header, footer):** Komponen Blade modular yang membungkus logika visual pewarnaan slot (Hijau = Tersedia, Abu-abu = Dipesan, Kuning = Dalam Perbaikan) dan konsistensi header/footer institusi.

---

### 5. Alur Autentikasi Pengguna & Registrasi Mandiri Sivitas (Laravel Breeze Core - AUTH-01 & AUTH-02)

* **Berkas yang Terhubung:**
  * [app/Models/User.php](file:///c:/my-project/reservation-reporting-system/app/Models/User.php) — Model Pengguna (Titik 0 Entitas Akun, Status & Roles)
  * [app/Http/Requests/Auth/LoginRequest.php](file:///c:/my-project/reservation-reporting-system/app/Http/Requests/Auth/LoginRequest.php) — Validasi & Rate Limit Login
  * [routes/auth.php](file:///c:/my-project/reservation-reporting-system/routes/auth.php) — Pendaftaran Rute Autentikasi
  * [app/Http/Controllers/Auth/RegisteredUserController.php](file:///c:/my-project/reservation-reporting-system/app/Http/Controllers/Auth/RegisteredUserController.php) — Registrasi Mandiri Sivitas
  * [app/Http/Controllers/Auth/AuthenticatedSessionController.php](file:///c:/my-project/reservation-reporting-system/app/Http/Controllers/Auth/AuthenticatedSessionController.php) — Login & Pemrosesan Sesi
  * [app/Http/Controllers/Auth/PasswordResetLinkController.php](file:///c:/my-project/reservation-reporting-system/app/Http/Controllers/Auth/PasswordResetLinkController.php) — Permintaan Tautan Reset Sandi
  * [app/Http/Controllers/Auth/NewPasswordController.php](file:///c:/my-project/reservation-reporting-system/app/Http/Controllers/Auth/NewPasswordController.php) — Form Sandi Baru
  * [app/Http/Controllers/Auth/EmailVerificationPromptController.php](file:///c:/my-project/reservation-reporting-system/app/Http/Controllers/Auth/EmailVerificationPromptController.php) — Prompt Verifikasi Email
  * [app/Http/Controllers/Auth/VerifyEmailController.php](file:///c:/my-project/reservation-reporting-system/app/Http/Controllers/Auth/VerifyEmailController.php) — Eksekusi Verifikasi Email
  * [app/Http/Controllers/Auth/EmailVerificationNotificationController.php](file:///c:/my-project/reservation-reporting-system/app/Http/Controllers/Auth/EmailVerificationNotificationController.php) — Kirim Ulang Email Verifikasi
  * [app/Http/Controllers/Auth/ConfirmablePasswordController.php](file:///c:/my-project/reservation-reporting-system/app/Http/Controllers/Auth/ConfirmablePasswordController.php) — Konfirmasi Sandi Sebelum Aksi Sensitif
  * [app/Http/Controllers/Auth/PasswordController.php](file:///c:/my-project/reservation-reporting-system/app/Http/Controllers/Auth/PasswordController.php) — Ubah Sandi Mandiri
  * [app/View/Components/GuestLayout.php](file:///c:/my-project/reservation-reporting-system/app/View/Components/GuestLayout.php) — Class Pembungkus GuestLayout
  * [resources/views/layouts/guest.blade.php](file:///c:/my-project/reservation-reporting-system/resources/views/layouts/guest.blade.php) — Layout Khusus Guest
  * [resources/views/auth/login.blade.php](file:///c:/my-project/reservation-reporting-system/resources/views/auth/login.blade.php) — Halaman Login
  * [resources/views/auth/register.blade.php](file:///c:/my-project/reservation-reporting-system/resources/views/auth/register.blade.php) — Halaman Registrasi
  * [resources/views/auth/forgot-password.blade.php](file:///c:/my-project/reservation-reporting-system/resources/views/auth/forgot-password.blade.php) — Halaman Lupa Sandi
  * [resources/views/auth/reset-password.blade.php](file:///c:/my-project/reservation-reporting-system/resources/views/auth/reset-password.blade.php) — Halaman Reset Sandi
  * [resources/views/auth/verify-email.blade.php](file:///c:/my-project/reservation-reporting-system/resources/views/auth/verify-email.blade.php) — Halaman Notifikasi Verifikasi Email
  * [resources/views/auth/confirm-password.blade.php](file:///c:/my-project/reservation-reporting-system/resources/views/auth/confirm-password.blade.php) — Halaman Konfirmasi Sandi
  * [resources/views/components/text-input.blade.php](file:///c:/my-project/reservation-reporting-system/resources/views/components/text-input.blade.php) — Komponen Input Text
  * [resources/views/components/input-label.blade.php](file:///c:/my-project/reservation-reporting-system/resources/views/components/input-label.blade.php) — Komponen Label Form
  * [resources/views/components/input-error.blade.php](file:///c:/my-project/reservation-reporting-system/resources/views/components/input-error.blade.php) — Komponen Tampilan Pesan Error
  * [resources/views/components/primary-button.blade.php](file:///c:/my-project/reservation-reporting-system/resources/views/components/primary-button.blade.php) — Komponen Tombol Utama
* **Penjelasan Alur & Peran Masing-Masing File:**
  1. **routes/auth.php:** Mendefinisikan gerbang HTTP untuk tamu (guest) dan pengguna terautentikasi (auth) dengan proteksi throttle request.
  2. **RegisteredUserController.php:** Menangani pendaftaran mandiri mahasiswa/dosen. Mengunggah bukti kartu identitas (KTM/NIP) ke storage publik, mengunci status akun ke `'pending'`, menugaskan peran `'pengguna'`, dan **secara sengaja tidak langsung me-login-kan pengguna**, melainkan mengarahkannya ke halaman informasi antrean verifikasi admin.
  3. **LoginRequest.php & AuthenticatedSessionController.php:** Menangani autentikasi kredensial email & password dengan perlindungan rate-limiter (anti brute-force). Mengecek kolom `status` di tabel users:
     * Jika status `'pending'`: Login ditolak dan dimunculkan pesan edukasi bahwa akun sedang ditinjau Admin.
     * Jika status `'rejected'`: Login ditolak dengan memunculkan alasan penolakan (`rejection_reason`).
     * Jika status `'active'`: Sesi dibuat dan pengguna di-redirect dinamis sesuai hak perannya.
  4. **Password & Email Verification Controllers:** Mengatur alur pemulihan kata sandi bertoken aman melalui email, verifikasi link bertandatangan (signed URL), dan konfirmasi sandi ulang sebelum mengeksekusi operasi berbahaya.
  5. **Views auth/*.blade.php & Layout guest.blade.php:** Antarmuka visual autentikasi dengan form input, kartu pratinjau cepat akun demo, pesan feedback error dinamis (`$errors`), dan layout minimalis.

---

### 6. Konsol Admin: Verifikasi Akun Pending Sivitas & Manajemen Pengguna Internal (ADM-01 & ADM-02)

* **Berkas yang Terhubung:**
  * [app/Models/User.php](file:///c:/my-project/reservation-reporting-system/app/Models/User.php) — Model Pengguna (Titik 0 Entitas Akun & Status)
  * [app/Http/Requests/Admin/RejectUserRequest.php](file:///c:/my-project/reservation-reporting-system/app/Http/Requests/Admin/RejectUserRequest.php) — Validasi Penolakan Akun
  * [app/Http/Requests/Admin/StoreInternalUserRequest.php](file:///c:/my-project/reservation-reporting-system/app/Http/Requests/Admin/StoreInternalUserRequest.php) — Validasi Tambah Akun Petugas/Sivitas Internal
  * [routes/web.php](file:///c:/my-project/reservation-reporting-system/routes/web.php) — Endpoint `/admin/user-management`, verify, reject, toggle, restore, destroy
  * [app/Http/Controllers/AdminUserManagementController.php](file:///c:/my-project/reservation-reporting-system/app/Http/Controllers/AdminUserManagementController.php) — Controller Manajemen Akun Admin
  * [app/View/Components/AdminLayout.php](file:///c:/my-project/reservation-reporting-system/app/View/Components/AdminLayout.php) — Komponen Pembungkus Layout Admin
  * [resources/views/layouts/admin.blade.php](file:///c:/my-project/reservation-reporting-system/resources/views/layouts/admin.blade.php) — Layout Admin
  * [resources/views/admin/user-management.blade.php](file:///c:/my-project/reservation-reporting-system/resources/views/admin/user-management.blade.php) — View Manajemen Pengguna Admin
  * [resources/views/components/modal.blade.php](file:///c:/my-project/reservation-reporting-system/resources/views/components/modal.blade.php) — Komponen Dialog Modal
  * [resources/views/components/danger-button.blade.php](file:///c:/my-project/reservation-reporting-system/resources/views/components/danger-button.blade.php) — Tombol Aksi Bahaya
* **Penjelasan Alur & Peran Masing-Masing File:**
  1. **routes/web.php:** Endpoint terisolasi di bawah middleware `['auth', 'role:admin']` untuk menangani operasional verifikasi sivitas mandiri dan manipulasi akun internal.
  2. **AdminUserManagementController.php:**
     * `index()`: Mengagregasikan akun ke dalam tab terpisah (Antrean Pending, Sivitas Aktif, Direktori Petugas Sarpras, dan Akun Nonaktif) dengan penghitungan kuantitas badge.
     * `verifyUser($id)`: Menyetujui pendaftaran mandiri, memperbarui status akun dari `'pending'` menjadi `'active'`, sehingga pengguna dapat segera login.
     * `rejectUser($id, RejectUserRequest)`: Menolak pendaftaran dan menyimpan catatan alasan penolakan pada kolom `rejection_reason`.
     * `storePetugas()` & `storePengguna()`: Membuat akun baru secara internal oleh Admin (akun internal langsung aktif tanpa melewati status pending).
     * `toggleStatus()`, `destroy()`, `restore()`: Mengubah status aktif/non-aktif, mengeksekusi soft delete, dan memulihkan kembali akun.
  3. **RejectUserRequest & StoreInternalUserRequest:** Memvalidasi kewajiban pengisian alasan penolakan minimal 5 karakter, serta memvalidasi keunikan email institusi dan nomor identitas (NIM/NIP) saat pembuatan akun internal.
  4. **View user-management.blade.php:** Antarmuka dashboard admin lengkap dengan modal pratinjau bukti kartu identitas mahasiswa, dialog konfirmasi penolakan, modal pendaftaran petugas sarpras baru, dan filter pencarian instan.

---

### 7. Konsol Admin: Dasbor Eksekutif & Statistik Utama (ADM-04 / US-17)

* **Berkas yang Terhubung:**
  * [app/Models/User.php](file:///c:/my-project/reservation-reporting-system/app/Models/User.php) — Model User (Titik 0 Entitas Sivitas)
  * [app/Models/Facility.php](file:///c:/my-project/reservation-reporting-system/app/Models/Facility.php) — Model Fasilitas (Titik 0 Data Sarpras)
  * [app/Models/Reservation.php](file:///c:/my-project/reservation-reporting-system/app/Models/Reservation.php) — Model Reservasi (Titik 0 Log Okupansi)
  * [app/Models/DamageReport.php](file:///c:/my-project/reservation-reporting-system/app/Models/DamageReport.php) — Model Laporan Kerusakan (Titik 0 Insiden Fasilitas)
  * [routes/web.php](file:///c:/my-project/reservation-reporting-system/routes/web.php) — Endpoint `/admin/dashboard`
  * [app/Http/Controllers/AdminDashboardController.php](file:///c:/my-project/reservation-reporting-system/app/Http/Controllers/AdminDashboardController.php) — Controller Dasbor Admin
  * [resources/views/layouts/admin.blade.php](file:///c:/my-project/reservation-reporting-system/resources/views/layouts/admin.blade.php) — Master Layout Admin
  * [resources/views/admin/dashboard.blade.php](file:///c:/my-project/reservation-reporting-system/resources/views/admin/dashboard.blade.php) — Tampilan Dasbor Admin
  * [resources/views/components/cava/stat-card.blade.php](file:///c:/my-project/reservation-reporting-system/resources/views/components/cava/stat-card.blade.php) — Komponen Kartu Statistik Bento
* **Penjelasan Alur & Peran Masing-Masing File:**
  1. **routes/web.php:** Menyediakan rute dashboard eksekutif untuk super admin sarpras.
  2. **AdminDashboardController.php:** Menjalankan kueri kalkulasi metrik global secara real-time: total permohonan reservasi, tingkat okupansi (*occupancy rate percentage*), total insiden kerusakan dan rasio perbaikan (*SLA resolution index*), jumlah antrean verifikasi akun sivitas pending, serta mengambil 5 transaksi reservasi dan laporan terbaru menggunakan relasi eager loading `with(['facility', 'user'])`.
  3. **View admin/dashboard.blade.php & Komponen stat-card:** Menampilkan antarmuka eksekutif Bento UI yang menampilkan 4 kartu KPI utama bergradasi, grafik visual tren okupansi fasilitas, tabel ringkasan aktivitas terkini, dan banner alert jika ada permohonan akun pending yang membutuhkan persetujuan segera.

---

### 8. Konsol Admin: Rekapitulasi Analitik, Audit Okupansi, & Ekspor Resmi (PDF & Excel - ADM-04)

* **Berkas yang Terhubung:**
  * [app/Models/Reservation.php](file:///c:/my-project/reservation-reporting-system/app/Models/Reservation.php) — Model Reservasi (Titik 0 Data Transaksi Sewa)
  * [app/Models/DamageReport.php](file:///c:/my-project/reservation-reporting-system/app/Models/DamageReport.php) — Model Kerusakan (Titik 0 Data Tiket Sarpras)
  * [app/Exports/ReservationsExport.php](file:///c:/my-project/reservation-reporting-system/app/Exports/ReservationsExport.php) — Class Export Excel Reservasi
  * [app/Exports/DamageReportsExport.php](file:///c:/my-project/reservation-reporting-system/app/Exports/DamageReportsExport.php) — Class Export Excel Laporan Kerusakan
  * [routes/web.php](file:///c:/my-project/reservation-reporting-system/routes/web.php) — Endpoint `/admin/export-report`, download PDF, download Excel
  * [app/Http/Controllers/ExportController.php](file:///c:/my-project/reservation-reporting-system/app/Http/Controllers/ExportController.php) — Controller Ekspor & Agregasi
  * [resources/views/admin/export-report.blade.php](file:///c:/my-project/reservation-reporting-system/resources/views/admin/export-report.blade.php) — Antarmuka Panel Filter Ekspor
  * [resources/views/admin/exports/reservations-pdf.blade.php](file:///c:/my-project/reservation-reporting-system/resources/views/admin/exports/reservations-pdf.blade.php) — Template Cetak PDF Reservasi
  * [resources/views/admin/exports/damage-reports-pdf.blade.php](file:///c:/my-project/reservation-reporting-system/resources/views/admin/exports/damage-reports-pdf.blade.php) — Template Cetak PDF Laporan Kerusakan
* **Penjelasan Alur & Peran Masing-Masing File:**
  1. **routes/web.php:** Mendaftarkan endpoint panel filter dan 4 endpoint stream download berkas resmi (`/admin/export/reservations/pdf`, `/excel`, `/admin/export/damage-reports/pdf`, `/excel`).
  2. **ExportController.php:** Mengagregasi data berdasarkan rentang parameter `start_date` dan `end_date`, menghitung total jam operasional terpakai (`total_slots * 0.5 jam`), serta memicu proses unduh file biner ke browser:
     * `exportReservationsPdf()` & `exportDamageReportsPdf()`: Memanggil library `DomPDF` (`Barryvdh\DomPDF\Facade\Pdf::loadView(...)`) untuk mengubah view Blade khusus menjadi dokumen PDF Landscape format A4 resmi.
     * `exportReservationsExcel()` & `exportDamageReportsExcel()`: Memicu library *Laravel Excel* (`Excel::download(...)`).
  3. **ReservationsExport.php & DamageReportsExport.php:** Mengonfigurasi heading kolom spreadsheet, mapping data atribut, lebar kolom otomatis, dan pewarnaan header biru tua institusi.
  4. **Views exports/*.blade.php:** Template HTML cetak statis tanpa script JavaScript eksternal yang dirancang presisi dengan kop surat universitas, tabel rekapitulasi, tanda tangan digital, dan cap legalitas biro sarana prasarana.

---

### 9. Portal Pengguna (Sivitas): Dasbor Pribadi & Ringkasan Tiket Aktif

* **Berkas yang Terhubung:**
  * [app/Models/Facility.php](file:///c:/my-project/reservation-reporting-system/app/Models/Facility.php) — Model Fasilitas (Titik 0 Master Ruangan)
  * [app/Models/Reservation.php](file:///c:/my-project/reservation-reporting-system/app/Models/Reservation.php) — Model Reservasi (Titik 0 Data Permohonan Sivitas)
  * [app/Models/DamageReport.php](file:///c:/my-project/reservation-reporting-system/app/Models/DamageReport.php) — Model Kerusakan (Titik 0 Data Pengaduan)
  * [routes/web.php](file:///c:/my-project/reservation-reporting-system/routes/web.php) — Endpoint `/user/dashboard`
  * [app/Http/Controllers/DashboardUserController.php](file:///c:/my-project/reservation-reporting-system/app/Http/Controllers/DashboardUserController.php) — Controller Dasbor Pengguna
  * [app/View/Components/AppLayout.php](file:///c:/my-project/reservation-reporting-system/app/View/Components/AppLayout.php) — Class Pembungkus AppLayout
  * [resources/views/layouts/app.blade.php](file:///c:/my-project/reservation-reporting-system/resources/views/layouts/app.blade.php) — Master Layout Pengguna
  * [resources/views/user/dashboard.blade.php](file:///c:/my-project/reservation-reporting-system/resources/views/user/dashboard.blade.php) — View Dasbor Sivitas
  * [resources/views/components/cava/header.blade.php](file:///c:/my-project/reservation-reporting-system/resources/views/components/cava/header.blade.php) — Header Pengguna
  * [resources/views/components/cava/sidebar.blade.php](file:///c:/my-project/reservation-reporting-system/resources/views/components/cava/sidebar.blade.php) — Sidebar Sivitas
* **Penjelasan Alur & Peran Masing-Masing File:**
  1. **routes/web.php:** Endpoint beranda mahasiswa dan dosen yang diproteksi oleh middleware `['auth', 'role:pengguna']`.
  2. **DashboardUserController.php:** Mengambil reservasi mendatang yang telah disetujui (`upcomingReservation`), 3 riwayat permohonan terakhir, 2 tiket keluhan kerusakan fasilitas aktif milik user login, dan total akumulasi permohonan.
  3. **View user/dashboard.blade.php:** Antarmuka personal sivitas yang menampilkan kartu agenda peminjaman ruang terdekat lengkap dengan countdown waktu, status persetujuan, tombol pintasan untuk membuat reservasi baru, dan panduan aturan kampus.
  4. **Layout app.blade.php & Komponen CAVA:** Menyediakan tata letak antarmuka aplikasi dengan navigasi samping responsif, penanda rute aktif, dropdown profil pengguna, dan panel notifikasi.

---

### 10. Portal Pengguna: Formulir Pengajuan Reservasi Ruangan Mandiri (USR-01)

* **Berkas yang Terhubung:**
  * [2026_09_20_000002_create_reservations_table.php](file:///c:/my-project/reservation-reporting-system/database/migrations/2026_09_20_000002_create_reservations_table.php) — Skema Tabel Reservasi (Titik 0 Basis Data)
  * [app/Models/Facility.php](file:///c:/my-project/reservation-reporting-system/app/Models/Facility.php) — Model Fasilitas (Titik 0 Definisi Ruangan)
  * [app/Models/Reservation.php](file:///c:/my-project/reservation-reporting-system/app/Models/Reservation.php) — Model Reservasi (Titik 0 Logika Overlap & Status)
  * [app/Http/Requests/StoreReservationRequest.php](file:///c:/my-project/reservation-reporting-system/app/Http/Requests/StoreReservationRequest.php) — FormRequest Validasi Reservasi
  * [routes/web.php](file:///c:/my-project/reservation-reporting-system/routes/web.php) — Endpoint `/user/reservation-form` & POST `/user/reservations`
  * [app/Http/Controllers/ReservationController.php](file:///c:/my-project/reservation-reporting-system/app/Http/Controllers/ReservationController.php) — Controller Reservasi (method `create` & `store`)
  * [resources/views/layouts/app.blade.php](file:///c:/my-project/reservation-reporting-system/resources/views/layouts/app.blade.php) — Layout Pengguna
  * [resources/views/user/reservation-form.blade.php](file:///c:/my-project/reservation-reporting-system/resources/views/user/reservation-form.blade.php) — Antarmuka Form Pengajuan
  * [resources/views/components/cava/slot-matrix.blade.php](file:///c:/my-project/reservation-reporting-system/resources/views/components/cava/slot-matrix.blade.php) — Pemilih Slot Waktu Interaktif
* **Penjelasan Alur & Peran Masing-Masing File:**
  1. **routes/web.php:** Endpoint menampilkan form pengajuan sewa dan endpoint penangkap payload permohonan baru.
  2. **StoreReservationRequest.php:** Validasi form tingkat server:
     * Tanggal wajib hari ini atau masa depan (`after_or_equal:today`).
     * Jam sewa dibatasi dalam rentang operasional kampus **07:00 – 20:00 WIB**.
     * Jam mulai dan selesai wajib menggunakan slot kelipatan **30 menit**.
     * Jam mulai harus lebih awal dari jam selesai (`before:end_time`).
     * Jumlah peserta tidak boleh melebihi kapasitas ruangan.
     * Upload berkas surat izin kegiatan (PDF/DOCX, maks 2MB).
  3. **ReservationController.php (`create` & `store`):**
     * Memastikan fasilitas yang dipilih berstatus aktif (bukan dalam perbaikan).
     * Menghitung total slot durasi (`diffInMinutes / 30`).
     * Membangkitkan kode tiket unik acak `TKT-YYYYMMDD-XXXX`.
     * Memeriksa irisan waktu awal dengan jadwal lain yang telah berstatus `approved`.
     * Membungkus operasi ke dalam `DB::transaction()` dan menyimpan tiket dengan status awal `'pending'`.
  4. **View user/reservation-form.blade.php & Komponen slot-matrix:** Formulir interaktif dengan kalkulator durasi otomatis, pratinjau spesifikasi fasilitas terpilih, indikator sisa slot waktu, dan upload berkas surat izin drag-and-drop.

---

### 11. Portal Pengguna: Riwayat Reservasi & Pembatalan Mandiri H-1 (USR-02 & USR-03)

* **Berkas yang Terhubung:**
  * [app/Models/Reservation.php](file:///c:/my-project/reservation-reporting-system/app/Models/Reservation.php) — Model Reservasi (Titik 0 Logika Status & Relasi)
  * [routes/web.php](file:///c:/my-project/reservation-reporting-system/routes/web.php) — Endpoint `/user/reservation-history` & DELETE cancel
  * [app/Http/Controllers/ReservationController.php](file:///c:/my-project/reservation-reporting-system/app/Http/Controllers/ReservationController.php) — Controller Reservasi (method `history` & `cancel`)
  * [resources/views/layouts/app.blade.php](file:///c:/my-project/reservation-reporting-system/resources/views/layouts/app.blade.php) — Layout Pengguna
  * [resources/views/user/reservation-history.blade.php](file:///c:/my-project/reservation-reporting-system/resources/views/user/reservation-history.blade.php) — View Riwayat Tiket Sivitas
  * [resources/views/components/modal.blade.php](file:///c:/my-project/reservation-reporting-system/resources/views/components/modal.blade.php) — Dialog Modal Konfirmasi
  * [resources/views/components/cava/status-badge.blade.php](file:///c:/my-project/reservation-reporting-system/resources/views/components/cava/status-badge.blade.php) — Badge Status
* **Penjelasan Alur & Peran Masing-Masing File:**
  1. **routes/web.php:** Endpoint daftar tiket reservasi pengguna dan endpoint pembatalan mandiri.
  2. **ReservationController.php (`history` & `cancel`):**
     * `history()`: Menarik seluruh riwayat reservasi milik `Auth::id()` dengan relasi *eager loaded* (`facility`, `reviewer`), filter tab status (Semua, Menunggu, Disetujui, Ditolak, Dibatalkan), dan paginasi 10 baris.
     * `cancel($id)`: Memastikan tiket milik pengguna aktif; mengecek status layak batal (hanya status `pending` atau `approved`); **menghitung selisih waktu mulai dengan saat ini $\ge 24$ jam (aturan batas mutlak H-1)**; jika valid, status diubah menjadi `'cancelled'` dan mencatat alasan pembatalan pemohon.
  3. **View user/reservation-history.blade.php:** Antarmuka kartu riwayat tiket interaktif, pencarian kode tiket instan, drawer pratinjau detail catatan petugas, dan tombol konfirmasi pembatalan mandiri yang otomatis dinonaktifkan jika waktu pelaksanaan tersisa kurang dari 24 jam.

---

### 12. Portal Pengguna: Pelaporan Kerusakan Fasilitas & Pelacakan Tiket (USR-04 & USR-05)

* **Berkas yang Terhubung:**
  * [2026_09_20_000003_create_damage_reports_table.php](file:///c:/my-project/reservation-reporting-system/database/migrations/2026_09_20_000003_create_damage_reports_table.php) — Skema Tabel Kerusakan (Titik 0 Basis Data)
  * [app/Models/Facility.php](file:///c:/my-project/reservation-reporting-system/app/Models/Facility.php) — Model Fasilitas
  * [app/Models/DamageReport.php](file:///c:/my-project/reservation-reporting-system/app/Models/DamageReport.php) — Model Laporan Kerusakan
  * [database/seeders/DemoReportSeeder.php](file:///c:/my-project/reservation-reporting-system/database/seeders/DemoReportSeeder.php) — Seeder Sampel Tiket Kerusakan
  * [app/Http/Requests/StoreDamageReportRequest.php](file:///c:/my-project/reservation-reporting-system/app/Http/Requests/StoreDamageReportRequest.php) — Validasi Pelaporan Kerusakan
  * [routes/web.php](file:///c:/my-project/reservation-reporting-system/routes/web.php) — Endpoint form laporan, POST laporan, dan riwayat laporan
  * [app/Http/Controllers/ReportController.php](file:///c:/my-project/reservation-reporting-system/app/Http/Controllers/ReportController.php) — Controller Pelaporan Kerusakan
  * [resources/views/layouts/app.blade.php](file:///c:/my-project/reservation-reporting-system/resources/views/layouts/app.blade.php) — Layout Pengguna
  * [resources/views/user/report-form.blade.php](file:///c:/my-project/reservation-reporting-system/resources/views/user/report-form.blade.php) — Formulir Lapor Kerusakan
  * [resources/views/user/report-history.blade.php](file:///c:/my-project/reservation-reporting-system/resources/views/user/report-history.blade.php) — Riwayat Pelacakan Tiket Kerusakan
* **Penjelasan Alur & Peran Masing-Masing File:**
  1. **routes/web.php:** Rute formulir pelaporan kerusakan sarpras (`/user/report-form`), pengiriman POST laporan (`/user/reports`), dan halaman log riwayat tiket (`/user/report-history`).
  2. **StoreDamageReportRequest.php:** Memvalidasi keabsahan ID fasilitas, pilihan kategori masalah (AC, Kelistrikan, Audio/Proyektor, Furnitur, Kebersihan, Bangunan), deskripsi kronologi kerusakan minimal 15 karakter, dan upload lampiran foto bukti (maksimal 2MB).
  3. **ReportController.php:**
     * `create()`: Menyediakan data fasilitas aktif untuk pilihan dropdown form.
     * `store()`: Menyimpan foto bukti ke `storage/app/public/damage-reports/`, membangkitkan kode tiket pengaduan unik `REP-YYYYMMDD-XXXX`, menyimpan tiket dengan status awal `'baru'`, dan me-redirect dengan notifikasi sukses.
     * `history()`: Menampilkan log tiket pengaduan milik pengguna aktif dengan filter status dan informasi progres penanganan teknisi.
  4. **DemoReportSeeder.php:** Menanamkan data riwayat pengaduan kerusakan realistis untuk demonstrasi alur penanganan tiket.
  5. **Views user/report-form.blade.php & report-history.blade.php:** Formulir yang dioptimalkan untuk mobile browser (dapat langsung mengambil foto dari kamera HP), serta antarmuka pelacakan tiket bergaya linimasa (Baru ➔ Diproses ➔ Selesai).

---

### 13. Operasional Petugas Sarpras: Dasbor Monitoring Antrean Cerdas (PTG-01 / US-8)

* **Berkas yang Terhubung:**
  * [app/Models/Facility.php](file:///c:/my-project/reservation-reporting-system/app/Models/Facility.php) — Model Fasilitas (Titik 0 Status Venue & Mode Perbaikan)
  * [app/Models/Reservation.php](file:///c:/my-project/reservation-reporting-system/app/Models/Reservation.php) — Model Reservasi (Titik 0 Antrean Peminjaman)
  * [app/Models/DamageReport.php](file:///c:/my-project/reservation-reporting-system/app/Models/DamageReport.php) — Model Kerusakan (Titik 0 Data Insiden)
  * [routes/web.php](file:///c:/my-project/reservation-reporting-system/routes/web.php) — Endpoint `/petugas/dashboard`
  * [app/Http/Controllers/DashboardPetugasController.php](file:///c:/my-project/reservation-reporting-system/app/Http/Controllers/DashboardPetugasController.php) — Controller Dasbor Petugas
  * [app/View/Components/PetugasLayout.php](file:///c:/my-project/reservation-reporting-system/app/View/Components/PetugasLayout.php) — Class Pembungkus PetugasLayout
  * [resources/views/layouts/petugas.blade.php](file:///c:/my-project/reservation-reporting-system/resources/views/layouts/petugas.blade.php) — Master Layout Petugas
  * [resources/views/petugas/dashboard.blade.php](file:///c:/my-project/reservation-reporting-system/resources/views/petugas/dashboard.blade.php) — Tampilan Dasbor Petugas
  * [resources/views/components/cava/sidebar.blade.php](file:///c:/my-project/reservation-reporting-system/resources/views/components/cava/sidebar.blade.php) — Sidebar Khusus Petugas
  * [resources/views/components/cava/header.blade.php](file:///c:/my-project/reservation-reporting-system/resources/views/components/cava/header.blade.php) — Header Petugas
  * [resources/views/components/cava/stat-card.blade.php](file:///c:/my-project/reservation-reporting-system/resources/views/components/cava/stat-card.blade.php) — Kartu Statistik
* **Penjelasan Alur & Peran Masing-Masing File:**
  1. **routes/web.php:** Endpoint sentral operasional harian petugas sarpras yang dilindungi oleh middleware `['auth', 'role:petugas']`.
  2. **DashboardPetugasController.php:** Menghitung jumlah permohonan reservasi yang masih berstatus pending, jumlah keluhan kerusakan sarana yang berstatus baru atau sedang diproses, jumlah venue kampus yang sedang terkunci dalam mode pemeliharaan, serta menarik 5 data antrean teratas secara FIFO (*First-In First-Out*) dengan relasi *eager loaded* ke pengguna dan fasilitas.
  3. **View petugas/dashboard.blade.php & Layout petugas.blade.php:** Antarmuka satu atap dengan kartu indikator antrean bento, tabel verifikasi cepat dengan tombol aksi langsung, peringatan fasilitas terkunci, dan pintasan menuju lembar matriks jadwal slot.

---

### 14. Operasional Petugas Sarpras: Matriks Jadwal 30 Menit Operasional & Detail Pemohon (Schedule Matrix)

* **Berkas yang Terhubung:**
  * [app/Models/Facility.php](file:///c:/my-project/reservation-reporting-system/app/Models/Facility.php) — Model Fasilitas (Titik 0 Entitas Venue)
  * [app/Models/Reservation.php](file:///c:/my-project/reservation-reporting-system/app/Models/Reservation.php) — Model Reservasi (Titik 0 Entitas Jadwal & Data Pemohon)
  * [routes/web.php](file:///c:/my-project/reservation-reporting-system/routes/web.php) — Endpoint `/petugas/schedule-matrix` & API matrix
  * [app/Http/Controllers/PetugasScheduleMatrixController.php](file:///c:/my-project/reservation-reporting-system/app/Http/Controllers/PetugasScheduleMatrixController.php) — Controller Matriks Petugas
  * [resources/views/layouts/petugas.blade.php](file:///c:/my-project/reservation-reporting-system/resources/views/layouts/petugas.blade.php) — Layout Petugas
  * [resources/views/petugas/schedule-matrix.blade.php](file:///c:/my-project/reservation-reporting-system/resources/views/petugas/schedule-matrix.blade.php) — View Matriks Petugas
  * [resources/views/components/cava/slot-matrix.blade.php](file:///c:/my-project/reservation-reporting-system/resources/views/components/cava/slot-matrix.blade.php) — Komponen Slot Grid
* **Penjelasan Alur & Peran Masing-Masing File:**
  1. **routes/web.php:** Rute lembar matriks kerja petugas dan API penyedia payload jadwal harian per gedung.
  2. **PetugasScheduleMatrixController.php:** Mengambil seluruh daftar venue berdasarkan filter gedung kampus; menghitung jumlah pemakaian hari ini; memuat kueri reservasi harian secara mendalam lengkap dengan **identitas sivitas pemohon, nomor telepon pemohon, tujuan kegiatan, dan surat izin yang diunggah** (berbeda dengan matriks publik yang menyembunyikan identitas pemohon).
  3. **View petugas/schedule-matrix.blade.php:** Antarmuka visual matriks kerja 30 menit; petugas dapat mengklik kotak slot jadwal yang terisi untuk memunculkan drawer detail pemohon secara lengkap, status verifikasi tiket, dan tombol pintasan untuk menyetujui atau membatalkan reservasi tersebut.

---

### 15. Operasional Petugas Sarpras: Persetujuan, Penolakan, & Pembatalan Darurat / Override (PTG-02 & PTG-03)

* **Berkas yang Terhubung:**
  * [app/Models/Facility.php](file:///c:/my-project/reservation-reporting-system/app/Models/Facility.php) — Model Fasilitas (Titik 0 Status Operasional Venue)
  * [app/Models/Reservation.php](file:///c:/my-project/reservation-reporting-system/app/Models/Reservation.php) — Model Reservasi (Titik 0 Logika Status Tiket & Relasi)
  * [app/Http/Requests/RejectReservationRequest.php](file:///c:/my-project/reservation-reporting-system/app/Http/Requests/RejectReservationRequest.php) — Validasi Penolakan
  * [app/Http/Requests/ForceCancelReservationRequest.php](file:///c:/my-project/reservation-reporting-system/app/Http/Requests/ForceCancelReservationRequest.php) — Validasi Override Darurat
  * [routes/web.php](file:///c:/my-project/reservation-reporting-system/routes/web.php) — Endpoint `/petugas/reservation-management`, approve, reject, force-cancel
  * [app/Http/Controllers/ReservationManagementController.php](file:///c:/my-project/reservation-reporting-system/app/Http/Controllers/ReservationManagementController.php) — Controller Manajemen Persetujuan
  * [resources/views/layouts/petugas.blade.php](file:///c:/my-project/reservation-reporting-system/resources/views/layouts/petugas.blade.php) — Layout Petugas
  * [resources/views/petugas/reservation-management.blade.php](file:///c:/my-project/reservation-reporting-system/resources/views/petugas/reservation-management.blade.php) — View Lembar Antrean Persetujuan
  * [resources/views/components/modal.blade.php](file:///c:/my-project/reservation-reporting-system/resources/views/components/modal.blade.php) — Modal Dialog Aksi
  * [resources/views/components/danger-button.blade.php](file:///c:/my-project/reservation-reporting-system/resources/views/components/danger-button.blade.php) — Tombol Bahaya
* **Penjelasan Alur & Peran Masing-Masing File:**
  1. **routes/web.php:** Endpoint antrean reservasi, PATCH persetujuan (`approve`), PATCH penolakan (`reject`), dan DELETE pembatalan darurat sepihak (`force-cancel`).
  2. **ReservationManagementController.php:**
     * `index()`: Mengambil seluruh antrean reservasi dengan tab bento per status, filter pencarian kode tiket atau nama pemohon, dan paginasi data.
     * `approve($id)`: **Engine Anti-Bentrok Jadwal.** Membuka transaksi database, mengunci baris data dengan `Reservation::lockForUpdate()->findOrFail($id)` (*Pessimistic Locking*), memastikan fasilitas tidak sedang dalam mode perbaikan, memvalidasi kueri overlap rentang waktu dengan tiket lain yang telah disetujui (`start_time < existing_end AND end_time > existing_start`); jika aman, mengubah status menjadi `'approved'` serta mencatat `reviewed_by` dan timestamp `reviewed_at`.
     * `reject($id, RejectReservationRequest)`: Menolak tiket pending dan mencatat alasan penolakan petugas pada kolom `rejection_reason`.
     * `forceCancel($id, ForceCancelReservationRequest)`: **Fitur Override Darurat Petugas.** Membatalkan reservasi yang sudah berstatus `approved` jika terjadi force-majeure (ruangan mendadak bocor atau korsleting) dengan syarat wajib mencantumkan alasan pembatalan sepihak.
  3. **RejectReservationRequest & ForceCancelReservationRequest:** Memvalidasi agar alasan penolakan atau pembatalan paksa diisi secara formal minimal 5 karakter.
  4. **View petugas/reservation-management.blade.php:** Lembar kerja verifikasi antrean bento dengan tombol aksi satu-klik Approve, modal input alasan Reject, dan modal Override darurat berwarna merah tegas.

---

### 16. Operasional Petugas Sarpras: Manajemen Tiket Kerusakan, Resolusi & Mode Perbaikan Fasilitas (PTG-04 & PTG-05)

* **Berkas yang Terhubung:**
  * [app/Models/Facility.php](file:///c:/my-project/reservation-reporting-system/app/Models/Facility.php) — Model Fasilitas (Titik 0 Status Ruangan & Mode Perbaikan)
  * [app/Models/DamageReport.php](file:///c:/my-project/reservation-reporting-system/app/Models/DamageReport.php) — Model Kerusakan (Titik 0 Entitas Tiket & Transisi Status)
  * [app/Http/Requests/UpdateReportStatusRequest.php](file:///c:/my-project/reservation-reporting-system/app/Http/Requests/UpdateReportStatusRequest.php) — Validasi Resolusi Tiket
  * [routes/web.php](file:///c:/my-project/reservation-reporting-system/routes/web.php) — Endpoint `/petugas/report-management`, update status, toggle-maintenance
  * [app/Http/Controllers/ReportManagementController.php](file:///c:/my-project/reservation-reporting-system/app/Http/Controllers/ReportManagementController.php) — Controller Manajemen Kerusakan Petugas
  * [resources/views/layouts/petugas.blade.php](file:///c:/my-project/reservation-reporting-system/resources/views/layouts/petugas.blade.php) — Layout Petugas
  * [resources/views/petugas/report-management.blade.php](file:///c:/my-project/reservation-reporting-system/resources/views/petugas/report-management.blade.php) — View Manajemen Tiket Petugas
  * [resources/views/components/modal.blade.php](file:///c:/my-project/reservation-reporting-system/resources/views/components/modal.blade.php) — Komponen Modal Dialog
* **Penjelasan Alur & Peran Masing-Masing File:**
  1. **routes/web.php:** Endpoint pemantauan seluruh tiket kerusakan fasilitas dan saklar mode pemeliharaan sarpras.
  2. **ReportManagementController.php:**
     * `index()`: Mengambil tiket kerusakan sarana dengan filter tab status (`baru`, `diproses`, `selesai`, `ditolak`), indikator jumlah antrean, dan relasi *eager loaded* ke pelapor dan fasilitas.
     * `updateStatus($id, UpdateReportStatusRequest)`: Mengubah status penanganan tiket teknisi. Jika tiket diubah menjadi `'selesai'`, petugas wajib menginput `resolution_note` (catatan perbaikan) dan otomatis mengisi `resolved_at` serta `handled_by`.
     * `toggleMaintenance($id)`: Mengubah status fasilitas dari `'aktif'` ke `'dalam perbaikan'` atau sebaliknya. Ketika mode perbaikan aktif, fasilitas **otomatis diblokir** dari kalender peminjaman secara global.
  3. **UpdateReportStatusRequest.php:** Memvalidasi status transisi tiket dan memastikan catatan teknisi tidak kosong saat tiket diselesaikan.
  4. **View petugas/report-management.blade.php:** Antarmuka lembar kerja tiket perbaikan bento dengan thumbnail bukti foto kerusakan, modal pembaruan fase penanganan teknisi, dan saklar tombol cepat status pemeliharaan fasilitas (*Maintenance Mode Switch*).

---

### 17. Manajemen Akun Profil Pengguna (Profile Settings & Password)

* **Berkas yang Terhubung:**
  * [app/Models/User.php](file:///c:/my-project/reservation-reporting-system/app/Models/User.php) — Model User (Titik 0 Entitas Akun Pengguna)
  * [app/Http/Requests/ProfileUpdateRequest.php](file:///c:/my-project/reservation-reporting-system/app/Http/Requests/ProfileUpdateRequest.php) — Validasi Ubah Profil
  * [routes/web.php](file:///c:/my-project/reservation-reporting-system/routes/web.php) — Endpoint `/profile` (GET, PATCH, DELETE)
  * [app/Http/Controllers/ProfileController.php](file:///c:/my-project/reservation-reporting-system/app/Http/Controllers/ProfileController.php) — Controller Profil Pengguna
  * [resources/views/layouts/app.blade.php](file:///c:/my-project/reservation-reporting-system/resources/views/layouts/app.blade.php) — Layout Pengguna
  * [resources/views/profile/edit.blade.php](file:///c:/my-project/reservation-reporting-system/resources/views/profile/edit.blade.php) — View Pengaturan Akun
  * [resources/views/profile/partials/update-profile-information-form.blade.php](file:///c:/my-project/reservation-reporting-system/resources/views/profile/partials/update-profile-information-form.blade.php) — Form Biodata
  * [resources/views/profile/partials/update-password-form.blade.php](file:///c:/my-project/reservation-reporting-system/resources/views/profile/partials/update-password-form.blade.php) — Form Kata Sandi
  * [resources/views/profile/partials/delete-user-form.blade.php](file:///c:/my-project/reservation-reporting-system/resources/views/profile/partials/delete-user-form.blade.php) — Form Hapus Akun
* **Penjelasan Alur & Peran Masing-Masing File:**
  1. **routes/web.php:** Endpoint manajemen informasi profil bagi seluruh pengguna terotentikasi.
  2. **ProfileController.php:**
     * `edit()`: Merender halaman pengaturan akun dengan parsing data sesi pengguna saat ini.
     * `update(ProfileUpdateRequest)`: Memvalidasi nama dan keunikan email baru; jika email diganti, status verifikasi email di-reset menjadi null.
     * `destroy()`: Memvalidasi password aktif pengguna sebelum menghapus akun secara permanen/soft delete, me-logout sesi, dan mengosongkan session token.
  3. **ProfileUpdateRequest.php:** Memastikan keunikan alamat email dengan mengabaikan ID pengguna aktif saat ini (`Rule::unique(User::class)->ignore($this->user()->id)`).
  4. **Views profile/*.blade.php:** Antarmuka pengaturan akun modular yang membagi 3 area fungsional: pembaruan informasi profil, penggantian password mandiri dengan validasi kecocokan sandi, dan dialog modal konfirmasi penghapusan akun mandiri.

---

### 18. Pusat Informasi Publik, Kebijakan, Syarat & Bantuan Sivitas

* **Berkas yang Terhubung:**
  * [routes/web.php](file:///c:/my-project/reservation-reporting-system/routes/web.php) — Endpoint `/informasi/{section?}`, `/kebijakan`, `/syarat-ketentuan`, `/bantuan`
  * [app/Http/Controllers/PublicInformationController.php](file:///c:/my-project/reservation-reporting-system/app/Http/Controllers/PublicInformationController.php) — Controller Informasi Publik
  * [resources/views/layouts/public.blade.php](file:///c:/my-project/reservation-reporting-system/resources/views/layouts/public.blade.php) — Layout Publik
  * [resources/views/public/information.blade.php](file:///c:/my-project/reservation-reporting-system/resources/views/public/information.blade.php) — View Pusat Informasi
* **Penjelasan Alur & Peran Masing-Masing File:**
  1. **routes/web.php:** Endpoint publik dengan parameter segmen opsional `{section?}` beserta route alias redirect langsung untuk tautan cepat `/kebijakan`, `/syarat-ketentuan`, dan `/bantuan`.
  2. **PublicInformationController.php:** Menangkap parameter segmen URL dan merender halaman pusat informasi dengan tab aktif yang bersesuaian.
  3. **View public/information.blade.php:** Halaman panduan regulasi resmi kampus yang memuat: ketentuan jam operasional peminjaman (07:00-20:00 WIB), aturan batas pembatalan mandiri H-1, mekanisme pelaporan kerusakan sarpras, SOP surat izin kegiatan, kontak darurat biro sarana prasarana, dan daftar FAQ interaktif berbasis accordion Alpine.js.

---

### 19. Komponen Pembungkus Tampilan & Desain Bersama (Layouts & Shared UI Components)

* **Berkas yang Terhubung:**
  * [app/View/Components/AdminLayout.php](file:///c:/my-project/reservation-reporting-system/app/View/Components/AdminLayout.php) — Class AdminLayout (Titik 0 Logika Komponen Backend)
  * [app/View/Components/PetugasLayout.php](file:///c:/my-project/reservation-reporting-system/app/View/Components/PetugasLayout.php) — Class PetugasLayout
  * [app/View/Components/PublicLayout.php](file:///c:/my-project/reservation-reporting-system/app/View/Components/PublicLayout.php) — Class PublicLayout
  * [app/View/Components/AppLayout.php](file:///c:/my-project/reservation-reporting-system/app/View/Components/AppLayout.php) — Class AppLayout
  * [app/View/Components/GuestLayout.php](file:///c:/my-project/reservation-reporting-system/app/View/Components/GuestLayout.php) — Class GuestLayout
  * [resources/views/layouts/app.blade.php](file:///c:/my-project/reservation-reporting-system/resources/views/layouts/app.blade.php) — Master Layout Pengguna
  * [resources/views/layouts/admin.blade.php](file:///c:/my-project/reservation-reporting-system/resources/views/layouts/admin.blade.php) — Master Layout Admin
  * [resources/views/layouts/petugas.blade.php](file:///c:/my-project/reservation-reporting-system/resources/views/layouts/petugas.blade.php) — Master Layout Petugas
  * [resources/views/layouts/public.blade.php](file:///c:/my-project/reservation-reporting-system/resources/views/layouts/public.blade.php) — Master Layout Publik
  * [resources/views/layouts/guest.blade.php](file:///c:/my-project/reservation-reporting-system/resources/views/layouts/guest.blade.php) — Master Layout Tamu
  * [resources/views/layouts/navigation.blade.php](file:///c:/my-project/reservation-reporting-system/resources/views/layouts/navigation.blade.php) — Navigasi Bawaan Breeze
  * [resources/views/components/text-input.blade.php](file:///c:/my-project/reservation-reporting-system/resources/views/components/text-input.blade.php) — Komponen Input Text
  * [resources/views/components/input-label.blade.php](file:///c:/my-project/reservation-reporting-system/resources/views/components/input-label.blade.php) — Komponen Label Input
  * [resources/views/components/input-error.blade.php](file:///c:/my-project/reservation-reporting-system/resources/views/components/input-error.blade.php) — Komponen Error Input
  * [resources/views/components/primary-button.blade.php](file:///c:/my-project/reservation-reporting-system/resources/views/components/primary-button.blade.php) — Tombol Primer
  * [resources/views/components/secondary-button.blade.php](file:///c:/my-project/reservation-reporting-system/resources/views/components/secondary-button.blade.php) — Tombol Sekunder
  * [resources/views/components/danger-button.blade.php](file:///c:/my-project/reservation-reporting-system/resources/views/components/danger-button.blade.php) — Tombol Danger Merah
  * [resources/views/components/modal.blade.php](file:///c:/my-project/reservation-reporting-system/resources/views/components/modal.blade.php) — Dialog Modal Alpine.js
  * [resources/views/components/dropdown.blade.php](file:///c:/my-project/reservation-reporting-system/resources/views/components/dropdown.blade.php) — Dropdown Menu
  * [resources/views/components/dropdown-link.blade.php](file:///c:/my-project/reservation-reporting-system/resources/views/components/dropdown-link.blade.php) — Link Dropdown
  * [resources/views/components/nav-link.blade.php](file:///c:/my-project/reservation-reporting-system/resources/views/components/nav-link.blade.php) — Tautan Navigasi Aktif
  * [resources/views/components/responsive-nav-link.blade.php](file:///c:/my-project/reservation-reporting-system/resources/views/components/responsive-nav-link.blade.php) — Tautan Navigasi Mobile
  * [resources/views/components/application-logo.blade.php](file:///c:/my-project/reservation-reporting-system/resources/views/components/application-logo.blade.php) — Logo CAVA
  * [resources/views/components/auth-session-status.blade.php](file:///c:/my-project/reservation-reporting-system/resources/views/components/auth-session-status.blade.php) — Banner Notifikasi Sesi
  * [resources/views/components/cava/sidebar.blade.php](file:///c:/my-project/reservation-reporting-system/resources/views/components/cava/sidebar.blade.php) — Sidebar Kustom CAVA
  * [resources/views/components/cava/header.blade.php](file:///c:/my-project/reservation-reporting-system/resources/views/components/cava/header.blade.php) — Header Kustom CAVA
  * [resources/views/components/cava/footer.blade.php](file:///c:/my-project/reservation-reporting-system/resources/views/components/cava/footer.blade.php) — Footer Kustom CAVA
  * [resources/views/components/cava/stat-card.blade.php](file:///c:/my-project/reservation-reporting-system/resources/views/components/cava/stat-card.blade.php) — Kartu Metrik Bento
  * [resources/views/components/cava/status-badge.blade.php](file:///c:/my-project/reservation-reporting-system/resources/views/components/cava/status-badge.blade.php) — Badge Status Semantik
  * [resources/views/components/cava/slot-matrix.blade.php](file:///c:/my-project/reservation-reporting-system/resources/views/components/cava/slot-matrix.blade.php) — Matriks Slot Kalender
* **Penjelasan Alur & Peran Masing-Masing File:**
  1. **Layouts (*.blade.php) & Class View Components:** Menjadi master container HTML (`<html>`, `<head>`, `@vite`, slot injeksi konten `{{ $slot }}`). Memisahkan tata letak visual per role (`admin.blade.php`, `petugas.blade.php`, `app.blade.php`, `public.blade.php`, `guest.blade.php`), di-render melalui Class Component di `app/View/Components/`.
  2. **Komponen Kustom CAVA (cava/*):**
     * `sidebar.blade.php`: Navigasi samping Bento UI yang secara cerdas mendeteksi role aktif dan mengecek status route saat ini menggunakan `request()->routeIs(...)` untuk mengaktifkan warna latar item menu.
     * `header.blade.php`: Topbar modern dengan breadcrumb dinamis, profil ringkas pengguna, tombol notifikasi, dan tombol logout.
     * `footer.blade.php`: Hak cipta institusi dan info build.
     * `stat-card.blade.php`: Kartu visual Bento dengan ikon Material Symbols dan label metrik dinamis.
     * `status-badge.blade.php`: Badge warna semantik Tailwind dengan warna kontekstual (Pending/Diproses = Kuning Amber, Disetujui/Selesai = Hijau Emerald, Ditolak/Batal = Merah Rose).
     * `slot-matrix.blade.php`: Komponen kalender matriks slot 30 menit interaktif berbasis Alpine.js.
  3. **Komponen UI Bersama (*.blade.php):** Membungkus elemen form HTML (`text-input`, `input-label`, `input-error`, `primary-button`, `secondary-button`, `danger-button`, `modal`, `dropdown`, `dropdown-link`, `application-logo`, `auth-session-status`). Masing-masing komponen menangkap atribut Blade (`$attributes`), error validasi `$errors->get($messages)`, dan event Alpine.js secara terisolasi (DRY - *Don't Repeat Yourself*).

---

## 20. Taksonomi & Penjelasan Jenis-Jenis Berkas dalam Ekosistem Laravel (Serta Kegunaannya)

Bagian ini menyajikan klasifikasi menyeluruh mengenai seluruh jenis berkas (*file types*) yang membentuk arsitektur sistem CAVA. Penjelasan ini menyertakan **lokasi path direktori fisik**, **format ekstensi dan penamaan berkas**, serta **peran dan kegunaan fungsional masing-masing jenis berkas** tanpa perlu mencantumkan daftar nama file satu per satu:

### 1. Model (Eloquent ORM Model)
* **Lokasi Direktori (Path)**: `app/Models/`
* **Format Nama & Ekstensi Berkas**: `[NamaEntitas].php` (PascalCase, contoh pola: `Facility.php`, `Reservation.php`, `User.php`)
* **Definisi & Peran**: Kelas representasi objek data untuk entitas basis data yang menerapkan pola desain *Active Record*.
* **Kegunaan & Untuk Apa**:
  * Mengabstraksi seluruh komunikasi ke tabel MySQL tanpa perlu menulis kueri SQL mentah secara manual.
  * Mendefinisikan hubungan antar data (*Eloquent Relationships*) seperti One-to-Many (`hasMany`), Invers Many-to-One (`belongsTo`), dan Many-to-Many (`belongsToMany`).
  * Mengelola konversi tipe data otomatis (*Attribute Type Casting*) melalui method `casts()`, seperti serialisasi otomatis kolom JSON menjadi array PHP atau string waktu menjadi objek Carbon.
  * Melindungi basis data dari serangan eksploitasi parameter tidak terotorisasi (*Mass Assignment Vulnerability* / CWE-915) menggunakan whitelist `$fillable`.
  * Menyediakan fungsi penyaring kueri yang dapat digunakan berulang kali (*Local Query Scopes* seperti `scopeActive()` atau `scopeApproved()`).

### 2. Controller (HTTP Controller Layer)
* **Lokasi Direktori (Path)**: `app/Http/Controllers/` (serta sub-folder seperti `app/Http/Controllers/Auth/`)
* **Format Nama & Ekstensi Berkas**: `[NamaFitur]Controller.php` (PascalCase berakhiran Controller, contoh pola: `ReservationController.php`, `PublicFacilityController.php`)
* **Definisi & Peran**: Pengendali alur logika bisnis (*traffic coordinator*) dalam arsitektur Model-View-Controller (MVC).
* **Kegunaan & Untuk Apa**:
  * Menerima HTTP Request yang diteruskan oleh Router dan mengekstrak parameter masukan dari pengguna.
  * Mengorkestrasi alur proses bisnis: memanggil model, menjalankan kalkulasi ketersediaan slot, mengeksekusi transaksi database terisolasi (`DB::transaction()`), dan menerapkan penguncian baris (*pessimistic locking* via `lockForUpdate()`).
  * Menentukan respon akhir aplikasi: mengoper variabel data ke template Blade View untuk antarmuka browser, melakukan pengalihan halaman (*HTTP Redirect*), atau mengembalikan struktur JSON untuk konsumsi API/AJAX.

### 3. Form Request (Validation & Gatekeeper Layer)
* **Lokasi Direktori (Path)**: `app/Http/Requests/` (serta sub-folder seperti `app/Http/Requests/Auth/`)
* **Format Nama & Ekstensi Berkas**: `[NamaAksi]Request.php` (PascalCase berakhiran Request, contoh pola: `StoreReservationRequest.php`, `LoginRequest.php`)
* **Definisi & Peran**: Kelas khusus turunan `Illuminate\Foundation\Http\FormRequest` yang memisahkan logika validasi dan sanitasi dari Controller.
* **Kegunaan & Untuk Apa**:
  * Bertindak sebagai filter penjaga gerbang (gatekeeper) sebelum data kiriman pengguna menyentuh Controller.
  * Memverifikasi hak otorisasi pemohon (`authorize()`) sebelum aksi diproses.
  * Menegakkan aturan integritas data (`rules()`) seperti keharusan pengisian (*required*), batasan tipe data (*string, integer, date*), ekspresi reguler interval waktu 30 menit, dan batasan ukuran berkas unggahan bukti fisik (*file upload max size*).
  * Menyediakan kamus pesan kegagalan kustom berbahasa Indonesia (`messages()`) yang informatif dan ramah pengguna.
  * Otomatis membatalkan eksekusi dan mengembalikan respon error (Redirect dengan flash session `$errors` atau HTTP JSON 422 Unprocessable Content) jika validasi tidak terpenuhi.

### 4. Migration (Database Migration / Schema Blueprint)
* **Lokasi Direktori (Path)**: `database/migrations/`
* **Format Nama & Ekstensi Berkas**: `YYYY_MM_DD_HHMMSS_create_[nama_tabel]_table.php` (Stempel waktu + snake_case, contoh pola: `2026_09_20_000001_create_facilities_table.php`)
* **Definisi & Peran**: Sistem *version control* terstruktur untuk arsitektur skema basis data aplikasi.
* **Kegunaan & Untuk Apa**:
  * Mendefinisikan cetak biru (blueprint DDL) pembuatan, pembaruan, dan penghapusan tabel basis data, kolom, tipe data native, nilai bawaan (*default values*), dan indeks komposit secara terprogram.
  * Mengatur integritas referensial dan aksi berantai (*Foreign Key Constraints* dengan `cascadeOnDelete()` atau `nullOnDelete()`).
  * Memastikan seluruh lingkungan kerja anggota tim developer maupun server produksi memiliki struktur database yang 100% sinkron dan konsisten cukup dengan mengeksekusi `php artisan migrate`.

### 5. Seeder (Database Seeder / Initial Population)
* **Lokasi Direktori (Path)**: `database/seeders/`
* **Format Nama & Ekstensi Berkas**: `[NamaSubjek]Seeder.php` (PascalCase berakhiran Seeder, contoh pola: `DatabaseSeeder.php`, `FacilitySeeder.php`, `UserSeeder.php`)
* **Definisi & Peran**: Skrip penyuntik data awal (*data population loader*) ke dalam tabel basis data.
* **Kegunaan & Untuk Apa**:
  * Mengisi basis data dengan data master fundamental yang menjadi prasyarat sistem berjalan (misal: akun awal Super Admin, master izin dan peran Spatie RBAC, daftar kategori ruangan).
  * Mengisi sekumpulan data tiruan (*dummy records*) untuk kebutuhan demonstrasi, simulasi transaksi bentrok jadwal, dan peninjauan laporan kerusakan tanpa memerlukan input manual lewat antarmuka pengguna.
  * Menerapkan pipeline urutan eksekusi (*Dependency Pipeline*) pada `DatabaseSeeder` agar pemuatan data tidak melanggar foreign key constraint.

### 6. Factory (Model Factory / Dummy Data Generator)
* **Lokasi Direktori (Path)**: `database/factories/`
* **Format Nama & Ekstensi Berkas**: `[NamaModel]Factory.php` (PascalCase berakhiran Factory, contoh pola: `UserFactory.php`, `FacilityFactory.php`)
* **Definisi & Peran**: Cetak biru generator data acak untuk entitas model menggunakan pustaka Faker.
* **Kegunaan & Untuk Apa**:
  * Memproduksi puluhan hingga ribuan baris data tiruan berformat realistis (nama acak, email, stempel waktu, nomor identitas) secara instan.
  * Digunakan secara intensif untuk mempercepat penulisan skenario pengujian otomatis (*automated testing*) atau pengisian basis data melalui Seeder.

### 7. Route (Routing Table Definition)
* **Lokasi Direktori (Path)**: `routes/`
* **Format Nama & Ekstensi Berkas**: `[nama_domain].php` (snake_case/kebab-case berektensi `.php`, contoh pola: `web.php`, `auth.php`, `console.php`)
* **Definisi & Peran**: Berkas pendaftaran pemetaan jalur URL (Uniform Resource Identifier) HTTP ke pengendali aksi (Controller).
* **Kegunaan & Untuk Apa**:
  * Mendaftarkan seluruh alamat endpoint web dan API yang sah dan dapat diakses oleh klien.
  * Menentukan kata kerja metode HTTP (GET, POST, PUT, PATCH, DELETE) yang sesuai dengan standar RESTful.
  * Mengaitkan middleware pengaman (seperti `auth`, `verified`, `role:admin`, `role:petugas`) ke kelompok rute tertentu (*Route Grouping*).
  * Memberikan nama pengenal unik (*named routes*) untuk pemanggilan URL dinamis yang kebal terhadap perubahan path fisik URL di kemudian hari.

### 8. Middleware (HTTP Request Filter Layer)
* **Lokasi Direktori (Path)**: `app/Http/Middleware/` (serta dikonfigurasikan secara sentral di `bootstrap/app.php`)
* **Format Nama & Ekstensi Berkas**: `[NamaFilter]Middleware.php` atau `[NamaFilter].php` (PascalCase berektensi `.php`)
* **Definisi & Peran**: Lapisan penyaring dan inspeksi keamanan perantara yang beroperasi di antara permintaan browser dan Controller.
* **Kegunaan & Untuk Apa**:
  * Memeriksa status otentikasi sesi pengguna (apakah sudah login atau masih bertindak sebagai *guest*).
  * Menegakkan otorisasi peran (*Role-Based Access Control* / RBAC) untuk membatasi akses modul administratif hanya bagi aktor yang berhak.
  * Memvalidasi token Cross-Site Request Forgery (CSRF) untuk mencegah serangan manipulasi request dari situs pihak ketiga.
  * Menerapkan pembatasan laju percobaan (*rate limiting / throttling*) guna melindungi sistem dari serangan *brute-force* atau *denial-of-service*.

### 9. Service Provider (Bootstrap & Dependency Injection Layer)
* **Lokasi Direktori (Path)**: `app/Providers/` (serta didaftarkan di `bootstrap/providers.php`)
* **Format Nama & Ekstensi Berkas**: `[NamaLayanan]ServiceProvider.php` (PascalCase berakhiran ServiceProvider, contoh pola: `AppServiceProvider.php`)
* **Definisi & Peran**: Titik sentral inisialisasi awal (*bootstrapping*) dan pengikatan layanan ke dalam Laravel Service Container (IoC).
* **Kegunaan & Untuk Apa**:
  * Mengonfigurasi layanan inti framework tingkat global sebelum request ditangani (seperti pada method `register()` dan `boot()`).
  * Mengintegrasikan paket pustaka pihak ketiga (seperti DomPDF, PhpSpreadsheet, Spatie Permission) ke dalam siklus hidup framework.
  * Mendaftarkan konfigurasi default aplikasi, seperti menetapkan template paginasi Tailwind CSS secara global (`Paginator::useTailwind()`).

### 10. Blade View (Presentation / UI Template Layer)
* **Lokasi Direktori (Path)**: `resources/views/` (dengan sub-folder seperti `resources/views/user/`, `admin/`, `petugas/`, `public/`, `auth/`)
* **Format Nama & Ekstensi Berkas**: `[nama-tampilan].blade.php` (kebab-case ganda berektensi `.blade.php`, contoh pola: `reservation-form.blade.php`, `dashboard.blade.php`)
* **Definisi & Peran**: Berkas antarmuka pengguna berbasis template engine bawaan Laravel (`.blade.php`).
* **Kegunaan & Untuk Apa**:
  * Merender struktur tampilan halaman HTML dinamis kepada browser klien.
  * Mengonsumsi dan menampilkan data yang diinjeksi oleh Controller menggunakan sintaks Blade ekspresif (`{{ }}`, `@if`, `@foreach`, `@forelse`, `@empty`).
  * Menyisipkan formulir interaktif lengkap dengan token keamanan `@csrf` dan metode spoofing (`@method('PATCH')`, `@method('DELETE')`).
  * Mengintegrasikan reaktivitas sisi klien menggunakan Alpine.js serta tata letak visual modern berbasis Tailwind CSS.

### 11. Blade Component (Reusable UI Component)
* **Lokasi Direktori (Path)**: `resources/views/components/` (serta subdirektori modul kustom seperti `resources/views/components/cava/`)
* **Format Nama & Ekstensi Berkas**: `[nama-komponen].blade.php` (kebab-case berektensi `.blade.php`, contoh pola: `stat-card.blade.php`, `modal.blade.php`, `status-badge.blade.php`)
* **Definisi & Peran**: Komponen antarmuka pengguna modular yang dapat disematkan berulang kali di berbagai view menggunakan tag `<x-[nama-komponen]>`.
* **Kegunaan & Untuk Apa**:
  * Menghilangkan duplikasi kode HTML/CSS (*Don't Repeat Yourself* / DRY).
  * Mengisolasi elemen visual spesifik yang sering digunakan (seperti tombol aksi, badge penanda status, kartu statistik metrik, pop-up modal dialog, dan tautan navigasi).
  * Memungkinkan perubahan styling atau interaktivitas komponen visual di seluruh aplikasi cukup dengan menyunting satu berkas komponen terpusat.

### 12. Layout Template (Master Layout Wrapper)
* **Lokasi Direktori (Path)**: `resources/views/layouts/` (dan kelas pengatur komponen di `app/View/Components/`)
* **Format Nama & Ekstensi Berkas**: `[nama-layout].blade.php` (kebab-case berektensi `.blade.php`, contoh pola: `app.blade.php`, `admin.blade.php`, `petugas.blade.php`, `public.blade.php`)
* **Definisi & Peran**: Berkas kerangka induk yang membungkus tata letak global dokumen web.
* **Kegunaan & Untuk Apa**:
  * Menyediakan konsistensi visual di seluruh aplikasi dengan menyediakan kerangka dasar HTML (`<html>`, `<head>`, meta tag, pemanggilan bundel aset CSS/JS `@vite`, bilah navigasi atas, sidebar menu, dan footer).
  * Memisahkan layout antarmuka berdasarkan profil pengguna (misalnya layout khusus Admin, Petugas Sarpras, Pengguna Sivitas, dan Pengunjung Publik).
  * Halaman konten spesifik cukup mengisi bagian slot penampung (`{{ $slot }}`) tanpa perlu menyusun ulang kerangka dokumen dari awal.

### 13. Policy (Resource Authorization Policy)
* **Lokasi Direktori (Path)**: `app/Policies/`
* **Format Nama & Ekstensi Berkas**: `[NamaModel]Policy.php` (PascalCase berakhiran Policy, contoh pola: `ReservationPolicy.php`)
* **Definisi & Peran**: Kelas aturan otorisasi granular berbasis entitas model tertentu.
* **Kegunaan & Untuk Apa**:
  * Mengatur hak akses granular per instans data (misalnya: memverifikasi apakah sivitas yang sedang login berhak membatalkan tiket reservasi tertentu miliknya sendiri, atau apakah seorang petugas memiliki wewenang menyetujui jadwal tersebut).
  * Dipanggil melalui helper `$this->authorize('update', $reservation)` di controller atau direktif `@can` di Blade view.

### 14. Configuration File
* **Lokasi Direktori (Path)**: `config/` (serta konfigurasi kernel modern di `bootstrap/app.php`)
* **Format Nama & Ekstensi Berkas**: `[nama_konfigurasi].php` (snake_case berektensi `.php`, contoh pola: `app.php`, `database.php`, `auth.php`, `filesystems.php`)
* **Definisi & Peran**: Berkas penyimpan pengaturan parameter dan konfigurasi runtime aplikasi.
* **Kegunaan & Untuk Apa**:
  * Mengatur driver koneksi database, sistem penyimpanan sesi (*session*), mekanisme caching, filesystem storage, dan driver pengiriman email.
  * Memetakan variabel lingkungan dari berkas `.env` ke dalam konfigurasi aplikasi yang aman dan dapat di-cache (`php artisan config:cache`) untuk performa tinggi pada server produksi.
