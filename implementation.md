# DOKUMEN IMPLEMENTASI PERBAIKAN KONSOL ADMINISTRATOR CAVA
**Modul Fokus:** Konsol Tata Kelola & Administrasi Sistem (ADM-01, ADM-02, ADM-04)  
**Dokumen:** `implementation.md`  
**Status:** Panduan Teknis & Arsitektur Implementasi Komprehensif  

---

## DAFTAR ISI
1. [Latar Belakang & Ringkasan Instruksi](#1-latar-belakang--ringkasan-instruksi)
2. [Analisis Akar Masalah (Root Cause Analysis)](#2-analisis-akar-masalah-root-cause-analysis)
3. [Rencana Arsitektur & Perubahan Sistem](#3-rencana-arsitektur--perubahan-sistem)
4. [Langkah-Langkah Implementasi Rinci](#4-langkah-langkah-implementasi-rinci)
   - [Poin 1: Harmonisasi Skala Font & Tipografi Universal](#poin-1-harmonisasi-skala-font--tipografi-universal)
   - [Poin 2: Resolusi Syntax Artifact / Bocoran Kode Alpine.js](#poin-2-resolusi-syntax-artifact--bocoran-kode-alpinejs)
   - [Poin 3: Perbaikan UI/UX (De-Clutter & Clean Campus Aesthetic)](#poin-3-perbaikan-uiux-de-clutter--clean-campus-aesthetic)
   - [Poin 4: Tombol & Modal "Lihat Detail Profil + Foto" Sivitas](#poin-4-tombol--modal-lihat-detail-profil--foto-sivitas)
   - [Poin 5: Aksi Nonaktifkan & Aktifkan Kembali Akun](#poin-5-aksi-nonaktifkan--aktifkan-kembali-akun)
   - [Poin 6: Penghapusan Kolom Redundan "Status Akun"](#poin-6-penghapusan-kolom-redundan-status-akun)
   - [Poin 7: Tab Baru "Deleted / Non-Active Accounts"](#poin-7-tab-baru-deleted--non-active-accounts)
5. [Spesifikasi Kode Lengkap (Ready-to-Use)](#5-spesifikasi-kode-lengkap-ready-to-use)
   - [5.1 Database Migration](#51-database-migration)
   - [5.2 Model User](#52-model-user)
   - [5.3 Rute Web (`routes/web.php`)](#53-rute-web-routeswebphp)
   - [5.4 Controller (`AdminUserManagementController.php`)](#54-controller-adminusermanagementcontrollerphp)
   - [5.5 Blade View: `export-report.blade.php`](#55-blade-view-export-reportbladephp)
   - [5.6 Blade View: `user-management.blade.php`](#56-blade-view-user-managementbladephp)
6. [Skenario Pengujian & Verifikasi (Test Matrix)](#6-skenario-pengujian--verifikasi-test-matrix)

---

## 1. LATAR BELAKANG & RINGKASAN INSTRUKSI

Berdasarkan hasil evaluasi konsol Administrator CAVA (*Campus Venue Access*), terdapat 7 poin perbaikan yang diinstruksikan oleh dosen/pembimbing/klien:

| No | Instruksi | Halaman Terdampak | Kategori |
|---|---|---|---|
| **1** | Bikin konsisten ukuran font antara page laporan analitik dan page lainnya | `export-report.blade.php` | Tipografi / Design System |
| **2** | Perbaiki teks sintaks kode yang bocor ke layar: `"= 1 && p <= this.totalPages) ..."` | `export-report.blade.php` | Bug Alpine.js / HTML Parser |
| **3** | Perbaiki UI agar tidak terlihat ramai, bertumpuk, dan berantakan | `export-report.blade.php`, `user-management.blade.php` | UI/UX & Visual Hierarchy |
| **4** | Tambahkan tombol **"Lihat Detail"** di masing-masing baris sivitas untuk melihat profil lengkap + foto/dokumen | `user-management.blade.php` | Fitur Baru / Modal Detail |
| **5** | Tambahkan tombol **"Nonaktifkan Akun"** di dalam modal detail akun | `user-management.blade.php` & Controller | Otorisasi / Manajemen Akun |
| **6** | Hilangkan kolom/bagian **"Status Akun"** di tabel manajemen akun aktif | `user-management.blade.php` | Penyederhanaan UI Tabel |
| **7** | Tambahkan tab baru **"Deleted / Non-Active Account"** yang memuat akun yang dihapus & dinonaktifkan | `user-management.blade.php` & Backend | Fitur Tab Baru & Soft Deletes |

---

## 2. ANALISIS AKAR MASALAH (ROOT CAUSE ANALYSIS)

### Masalah 1: Ukuran Font Laporan Analitik Berbeda dengan Halaman Lain
* **Penyebab:** Halaman `export-report.blade.php` menggunakan utility classes kustom berbasis material token seperti `font-headline-md`, `text-headline-md`, `font-headline-sm`, `font-label-sm`, `font-data-mono`, `text-data-mono`, dan `font-body-sm`.
* Sementara itu, halaman lain (`dashboard.blade.php`, `user-management.blade.php`, `facility-master.blade.php`) menggunakan standar skala tipografi Tailwind CSS (`text-2xl font-bold`, `text-sm`, `text-xs`, `font-semibold text-slate-800`).
* **Akibat:** Teks pada Laporan Analitik tampak jauh lebih besar, spasi tabel renggang berlebihan, dan terasa berasal dari aplikasi yang berbeda (*inconsistent brand identity*).

### Masalah 2: Bocoran Sintaks Kode Alpine.js ke Halaman
* **Penyebab:** Pada tag `<section>` tabel okupansi `export-report.blade.php`:
  ```html
  <section x-data="{
      ...
      setPage(p) {
          if (p >= 1 && p <= this.totalPages) { this.currentPage = p; }
      },
      ...
  }">
  ```
  Karakter `<` pada `p <= this.totalPages` dan `this.currentPage < this.totalPages` berada **di dalam atribut HTML tanpa di-escape**.
* Parser HTML browser membaca karakter `<` sebagai tag HTML baru (`< this.totalPages...`), sehingga menutup atribut `x-data` secara prematur.
* Seluruh string setelahnya tumpah ke dalam DOM sebagai teks biasa yang terlihat langsung oleh pengguna:
  `"= 1 && p <= this.totalPages) { this.currentPage = p; } }, prevPage() { ..."`

### Masalah 3: Tampilan Terkesan Rame dan Berantakan
* **Penyebab:**
  1. *Redundansi Navigasi:* Di `user-management.blade.php`, terdapat 3 kartu statistik besar di atas tabel (`Antrean Verifikasi`, `Total Sivitas`, `Petugas`) yang merangkap sebagai button tab switching, kemudian tepat di bawahnya ada segmented tab navigasi dengan warna dan fungsi yang identik.
  2. *Duplikasi Form Input:* Pada modal pembuatan akun internal, field input "Password Sementara" terduplikasi dua kali berturut-turut.
  3. *Tabel Kolom Tidak Sinkron:* Header tabel bertuliskan `Departemen / Prodi`, namun isi selnya menampilkan `{{ $sivitas->reservations->count() }} kali`.
  4. *Double Status Badge:* Di samping "Status: Aktif", terdapat badge "Otorisasi: Terverifikasi" yang tidak memberikan nilai informasi tambahan selain memadati baris.

### Masalah 4 & 5: Belum Tersedianya Akses Profil Lengkap & Aksi Deaktivasi
* **Penyebab:** Selama ini admin hanya bisa melihat data ringkas nama dan email pada baris tabel tanpa ada cara melihat detail seperti foto KTM/SK, nomor telepon, tanggal pendaftaran, maupun riwayat penggunaan fasilitas kampus secara utuh. Selain itu, belum ada fitur untuk menangguhkan (*deactivate*) akun sivitas yang melanggar aturan tanpa harus menolaknya dari antrean awal.

### Masalah 6: Redundansi Kolom "Status Akun"
* **Penyebab:** Seluruh data yang berada di tab "Sivitas Terdaftar" secara definisi adalah akun yang sudah berstatus `active`. Menampilkan kolom "Status Akun: Aktif" di setiap baris memboroskan ruang horizontal tabel.

### Masalah 7: Belum Adanya Tab Arsip Akun Nonaktif & Dihapus
* **Penyebab:** Query `AdminUserManagementController` hanya memfilter `where('status', 'active')` dan `where('status', 'pending')`. Akun yang ditolak atau dinonaktifkan hilang dari pandangan admin tanpa wadah penampung terpusat untuk restore atau audit.

---

## 3. RENCANA ARSITEKTUR & PERUBAHAN SISTEM

```
+-----------------------------------------------------------------------------------+
|                            ARSITEKTUR MANAJEMEN PENGGUNA CAVA                     |
+-----------------------------------------------------------------------------------+
                                          |
        +---------------------------------+---------------------------------+
        |                                 |                                 |
   [TAB 1: VERIFIKASI]            [TAB 2: SIVITAS]                 [TAB 4: ARSIP BARU]
   status = 'pending'            status = 'active'             status = 'inactive' /
   - Tombol Setujui              - Kolom Status Dihapus         status = 'rejected' /
   - Tombol Tolak                - Kolom Dept Diperbaiki        deleted_at IS NOT NULL
                                 - TOMBOL "LIHAT DETAIL"       - Filter & List Akun
                                          |                    - TOMBOL "AKTIFKAN"
                                          v
                              +-----------------------+
                              | MODAL DETAIL PROFIL   |
                              | - Foto Profil / KTM   |
                              | - Biodata Sivitas     |
                              | - Stat Aktivitas      |
                              | - TOMBOL NONAKTIFKAN  |
                              +-----------------------+
```

### Pembaruan Skema Basis Data
1. Kolom `status` pada tabel `users` diperluas agar mendukung status `'inactive'`:
   - `pending`: Menunggu verifikasi pendaftaran mandiri (UR15).
   - `active`: Akun aktif dan dapat login meminjam fasilitas.
   - `inactive`: Akun ditangguhkan/dinonaktifkan sementara oleh Admin.
   - `rejected`: Pendaftaran mandiri ditolak oleh Admin.
2. Penambahan kolom `deleted_at` (*Soft Deletes*) pada tabel `users` agar akun yang dihapus tidak langsung hilang permanen dari database (*audit trail preservation*).

---

## 4. LANGKAH-LANGKAH IMPLEMENTASI RINCI

### Poin 1: Harmonisasi Skala Font & Tipografi Universal
Ganti seluruh class tipografi kustom di `export-report.blade.php` menjadi standar Tailwind CAVA:
* Judul Halaman: `text-2xl font-bold text-navy tracking-tight`
* Subjudul / Deskripsi: `text-sm text-slate-500`
* Judul Bagian / Tabel: `text-base font-bold text-slate-900`
* Teks Kolom Tabel (Header): `text-xs font-semibold uppercase tracking-wider text-slate-500`
* Teks Sel Tabel:
  * Nama Utama: `text-sm font-semibold text-slate-800`
  * Teks Sekunder: `text-xs text-slate-500`
  * Angka Monospace: `font-mono text-xs text-slate-700`
* Badge / Status: `text-[10px]` atau `text-[11px] font-semibold px-2 py-0.5 rounded-full`

### Poin 2: Resolusi Syntax Artifact / Bocoran Kode Alpine.js
Pindahkan logika JavaScript dari atribut tag HTML ke blok `<script>` terpisah menggunakan `Alpine.data`:
```html
{{-- Di Blade Template --}}
<section x-data="facilityReportTable(@js($facilityUtilization))" class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5">
    ...
</section>

{{-- Di Script Bawah Halaman --}}
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('facilityReportTable', (initialData) => ({
            allRows: initialData || [],
            currentPage: 1,
            perPage: 10,
            get totalPages() {
                return Math.max(1, Math.ceil(this.allRows.length / this.perPage));
            },
            get displayedRows() {
                const start = (this.currentPage - 1) * this.perPage;
                return this.allRows.slice(start, start + this.perPage);
            },
            setPage(p) {
                if (p >= 1 && p <= this.totalPages) {
                    this.currentPage = p;
                }
            },
            prevPage() {
                if (this.currentPage > 1) {
                    this.currentPage--;
                }
            },
            nextPage() {
                if (this.currentPage < this.totalPages) {
                    this.currentPage++;
                }
            }
        }));
    });
</script>
```

### Poin 3: Perbaikan UI/UX (De-Clutter & Clean Campus Aesthetic)
1. **Bersihkan Header & Form Filter:**
   - Hilangkan banner ganda yang berat. Satukan filter tanggal (Periode Mulai & Selesai) ke dalam kartu kontrol yang elegan dengan tombol ekspor Excel dan PDF yang seimbang.
2. **Harmonisasi Kartu Ringkasan:**
   - Ubah kartu 3 kolom di `user-management` menjadi kartu stat informatif murni dengan border tipis `border-slate-200/80`, latar `bg-white`, serta tanpa efek hover tombol yang bertabrakan dengan tab.
3. **Koreksi Modal Form:**
   - Hapus duplikasi field password pada `MODAL 1` dan `MODAL 2`.

### Poin 4: Tombol & Modal "Lihat Detail Profil + Foto" Sivitas
1. Tambahkan state Alpine.js di wadah utama:
   ```javascript
   showDetailModal: false,
   detailUser: {},
   openDetailModal(user) {
       this.detailUser = user;
       this.showDetailModal = true;
   }
   ```
2. Pada tabel "Sivitas Terdaftar", sediakan tombol aksi:
   ```html
   <button type="button" @click="openDetailModal(user)" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition-colors">
       <span class="material-symbols-outlined text-[16px]">visibility</span>
       <span>Lihat Detail</span>
   </button>
   ```
3. Bangun modal dialog komprehensif yang menampilkan:
   - Avatar / Foto profil dengan inisial nama.
   - Foto Dokumen Identitas (KTM / SK) dengan modal zoom / link unduh berkas.
   - Tabel biodata: Nama, NIM/NIP, Role, Email, Prodi, No. Telepon, Tanggal Bergabung.
   - Ringkasan aktivitas: Total kali meminjam ruangan dan tiket kerusakan yang diajukan.

### Poin 5: Aksi Nonaktifkan & Aktifkan Kembali Akun
1. Di bagian bawah Modal Detail Profil, tambahkan tombol kondisional:
   - Jika status user aktif ➔ Tampilkan tombol merah **"Nonaktifkan Akun"**.
   - Jika status user tidak aktif / diarsipkan ➔ Tampilkan tombol hijau **"Aktifkan Kembali Akun"**.
2. Form submit memicu rute `POST /admin/users/{id}/toggle-status`.
3. Keamanan: Berikan konfirmasi dialog `onsubmit="return confirm(...)"` dan blokir deaktivasi jika target adalah akun dengan role `admin`.

### Poin 6: Penghapusan Kolom Redundan "Status Akun"
1. Hapus tag `<th class="py-3 px-4">Status Akun</th>` pada tabel Sivitas Terdaftar.
2. Hapus sel `<td class="py-3.5 px-4"><span ...>Aktif</span></td>`.
3. Rapikan kolom tabel menjadi 6 kolom presisi:
   - `Nama & Identitas`
   - `Kategori Sivitas`
   - `NIM / NIP`
   - `Email Kampus`
   - `Program Studi / Departemen`
   - `Aksi (Lihat Detail)`

### Poin 7: Tab Baru "Deleted / Non-Active Accounts"
1. Tambahkan tab ke-4 pada navigation bar:
   ```html
   <button @click="currentTab = 'inactive'" class="..." :class="currentTab === 'inactive' ? 'bg-white text-navy shadow-xs' : 'text-slate-600'">
       <span class="material-symbols-outlined text-[16px]">person_off</span>
       <span>Akun Non-aktif / Dihapus ({{ $inactiveCount }})</span>
   </button>
   ```
2. Buat tabel daftar akun non-aktif dengan informasi:
   - Nama & Email
   - Peran Sivitas
   - Status Terakhir (`Non-aktif`, `Ditolak`, `Dihapus`)
   - Alasan Non-aktif / Penolakan (jika ada)
   - Tombol "Lihat Detail" dan tombol cepat "Aktifkan Kembali".

---

## 5. SPESIFIKASI KODE LENGKAP (READY-TO-USE)

### 5.1 Database Migration
Buat berkas migrasi baru: `database/migrations/2026_10_03_000001_add_inactive_and_soft_deletes_to_users_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Ubah tipe status menjadi string fleksibel atau perbarui enum dengan 'inactive'
            $table->string('status', 30)->default('pending')->change();
            
            // Tambahkan dukungan soft deletes jika belum tersedia
            if (!Schema::hasColumn('users', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });
    }
};
```

---

### 5.2 Model User (`app/Models/User.php`)
Pastikan trait `SoftDeletes` disematkan:

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'identity_number',
        'role',
        'department',
        'phone_number',
        'id_card_path',
        'status',
        'rejection_reason',
        'assignment_zone',
    ];
    
    // ... relasi reservations(), damageReports() tetap dipertahankan
}
```

---

### 5.3 Rute Web (`routes/web.php`)
Tambahkan rute penanganan toggle status dan pemulihan akun:

```php
// Rute di dalam Route::middleware(['auth', 'role:admin'])->group(function () { ...

// ROUTE: Mengubah status akun pengguna (Aktif <-> Non-aktif)
Route::post('/admin/users/{id}/toggle-status', [AdminUserManagementController::class, 'toggleStatus'])->name('admin.users.toggle-status');

// ROUTE: Soft delete akun pengguna
Route::delete('/admin/users/{id}', [AdminUserManagementController::class, 'destroy'])->name('admin.users.destroy');

// ROUTE: Memulihkan akun pengguna yang di-soft delete
Route::post('/admin/users/{id}/restore', [AdminUserManagementController::class, 'restore'])->name('admin.users.restore');
```

---

### 5.4 Controller (`app/Http/Controllers/AdminUserManagementController.php`)
Perbarui controller dengan penambahan data tab arsip dan fungsi manipulasi status:

```php
namespace App\Http\Controllers;

use App\Http\Requests\Admin\RejectUserRequest;
use App\Http\Requests\Admin\StoreInternalUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AdminUserManagementController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->query('search');

        // 1. Data Pemohon Registrasi Mandiri Berstatus Pending (UR15 / ADM-01)
        $pendingQuery = User::where('status', 'pending');
        if ($search) {
            $pendingQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('identity_number', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        $pendingUsers = $pendingQuery->orderBy('created_at', 'desc')->get();

        // 2. Data Sivitas Akademika Aktif Terdaftar (Mahasiswa, Dosen, Staf)
        $sivitasQuery = User::where('status', 'active')
            ->whereIn('role', ['mahasiswa', 'dosen', 'staf'])
            ->withCount(['reservations', 'damageReports']);
        if ($search) {
            $sivitasQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('identity_number', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('department', 'like', "%{$search}%");
            });
        }
        $sivitasUsers = $sivitasQuery->orderBy('created_at', 'desc')->get();

        // 3. Data Petugas Sarpras Operasional (UR13)
        $petugasQuery = User::where('role', 'petugas')->where('status', 'active');
        if ($search) {
            $petugasQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('identity_number', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        $petugasUsers = $petugasQuery->orderBy('created_at', 'desc')->get();

        // 4. Data Akun Non-aktif & Dihapus (Poin 7)
        $inactiveQuery = User::withTrashed()
            ->where(function ($q) {
                $q->where('status', 'inactive')
                  ->orWhere('status', 'rejected')
                  ->orWhereNotNull('deleted_at');
            });
        if ($search) {
            $inactiveQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('identity_number', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        $inactiveUsers = $inactiveQuery->orderBy('updated_at', 'desc')->get();

        // Agregat Ringkasan Statistik
        $pendingCount = User::where('status', 'pending')->count();
        $sivitasCount = User::where('status', 'active')->whereIn('role', ['mahasiswa', 'dosen', 'staf'])->count();
        $petugasCount = User::where('role', 'petugas')->where('status', 'active')->count();
        $inactiveCount = $inactiveUsers->count();

        return view('admin.user-management', compact(
            'pendingUsers',
            'sivitasUsers',
            'petugasUsers',
            'inactiveUsers',
            'pendingCount',
            'sivitasCount',
            'petugasCount',
            'inactiveCount'
        ));
    }

    /**
     * Mengubah status akun pengguna antara aktif dan non-aktif (Poin 5)
     */
    public function toggleStatus(int|string $id): RedirectResponse
    {
        $user = User::withTrashed()->findOrFail($id);

        if ($user->hasRole('admin') || $user->role === 'admin') {
            return back()->withErrors(['error' => 'Status akun Administrator dilindungi dan tidak dapat diubah demi keamanan sistem.']);
        }

        if ($user->status === 'active') {
            $user->status = 'inactive';
            $user->save();
            return back()->with('success', "Akun {$user->name} berhasil dinonaktifkan. Hak akses pengguna telah dibekukan.");
        } else {
            $user->status = 'active';
            if ($user->trashed()) {
                $user->restore();
            }
            $user->save();
            return back()->with('success', "Akun {$user->name} berhasil diaktifkan kembali.");
        }
    }

    /**
     * Soft delete akun pengguna (Poin 7)
     */
    public function destroy(int|string $id): RedirectResponse
    {
        $user = User::findOrFail($id);

        if ($user->hasRole('admin') || $user->role === 'admin') {
            return back()->withErrors(['error' => 'Akun Administrator tidak dapat dihapus.']);
        }

        $userName = $user->name;
        $user->status = 'inactive';
        $user->save();
        $user->delete();

        return back()->with('success', "Akun {$userName} berhasil dihapus dari direktori aktif dan dipindahkan ke arsip.");
    }

    /**
     * Memulihkan akun dari arsip soft delete (Poin 7)
     */
    public function restore(int|string $id): RedirectResponse
    {
        $user = User::onlyTrashed()->findOrFail($id);
        $user->restore();
        $user->status = 'active';
        $user->save();

        return back()->with('success', "Akun {$user->name} berhasil dipulihkan dari arsip dan siap digunakan kembali.");
    }
}
```

---

### 5.5 Blade View: `export-report.blade.php` (Poin 1, 2, 3)
Kutipan perbaikan utama pada struktur tabel dan penanganan script:

```html
<x-admin-layout title="Rekapitulasi & Ekspor Laporan" active="export-report">
    <div class="flex flex-col gap-6">
        {{-- Section Header & Filter Periode Terpadu (Clean Minimalist) --}}
        <section class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs">
            <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4 pb-4 border-b border-slate-100">
                <div>
                    <h1 class="text-xl font-bold text-slate-900 tracking-tight">Rekapitulasi Okupansi Fasilitas & Insiden Kerusakan</h1>
                    <p class="text-xs text-slate-500 mt-0.5">Analisis pemanfaatan ruang kampus dan kepatuhan SLA penanganan keluhan fasilitas (UR17).</p>
                </div>

                {{-- Action Buttons Ekspor Terpadu --}}
                <div class="flex items-center gap-2 flex-wrap">
                    <a href="{{ route('admin.export.reservations.excel', ['start_date' => $startDate, 'end_date' => $endDate]) }}" 
                       class="h-9 px-3.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition-colors flex items-center gap-1.5 border border-slate-200">
                        <span class="material-symbols-outlined text-[16px] text-emerald-600">table_view</span>
                        <span>Ekspor Excel / CSV</span>
                    </a>
                    <a href="{{ route('admin.export.reservations.pdf', ['start_date' => $startDate, 'end_date' => $endDate]) }}" 
                       class="h-9 px-3.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold transition-colors flex items-center gap-1.5 shadow-xs">
                        <span class="material-symbols-outlined text-[16px]">picture_as_pdf</span>
                        <span>Cetak Dokumen PDF Resmi</span>
                    </a>
                </div>
            </div>

            {{-- Filter Tanggal Form --}}
            <form action="{{ route('admin.export-report') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-4 items-end">
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1" for="rep-start">Periode Mulai</label>
                    <input type="date" id="rep-start" name="start_date" value="{{ $startDate }}" class="w-full h-9 px-3 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-800 focus:bg-white focus:border-slate-900 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1" for="rep-end">Periode Selesai</label>
                    <input type="date" id="rep-end" name="end_date" value="{{ $endDate }}" class="w-full h-9 px-3 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-800 focus:bg-white focus:border-slate-900 focus:outline-none">
                </div>
                <div>
                    <button type="submit" class="w-full h-9 inline-flex items-center justify-center gap-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold border border-slate-200 transition-colors">
                        <span class="material-symbols-outlined text-[16px]">refresh</span>
                        <span>Segarkan Data Analitik</span>
                    </button>
                </div>
            </form>
        </section>

        {{-- 4 Stat Cards --}}
        <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <x-cava.stat-card title="Okupansi Global" badgeCode="METRIK-1" :value="$occupancyRate . '%'" subtitle="Rata-rata Terpakai" :footerBadge="$occupancyRate >= 70 ? 'Optimal' : 'Cukup'" :footerText="number_format($totalHoursReserved, 1) . ' Jam Peminjaman'" icon="pie_chart" variant="secondary" />
            <x-cava.stat-card title="Total Sesi Disetujui" badgeCode="METRIK-2" :value="$approvedCount" subtitle="Kegiatan Sivitas" footerBadge="0 Sengketa" footerText="100% Concurrency Pass" icon="event_available" variant="primary" />
            <x-cava.stat-card title="Frekuensi Kerusakan" badgeCode="METRIK-3" :value="$totalDamageReports" subtitle="Tiket Pengaduan" :footerBadge="$resolvedDamageCount . ' Selesai'" :footerText="$inProgressDamageCount . ' Diproses'" icon="build" variant="tertiary" />
            <x-cava.stat-card title="Indeks Resolusi SLA" badgeCode="METRIK-4" :value="$slaResolutionIndex . '%'" subtitle="Tuntas Sesuai SLA" :footerBadge="$slaResolutionIndex >= 90 ? 'Optimal' : 'Evaluasi'" footerText="Standar Statuter Rektorat" icon="verified" variant="primary" />
        </section>

        {{-- Tabel Okupansi Bebas Glitch Sintaks (Poin 1 & 2) --}}
        <section x-data="facilityReportTable(@js($facilityUtilization))" class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 flex flex-col gap-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[20px] text-slate-700">table_chart</span>
                    <h3 class="text-sm font-bold text-slate-900">Rekapitulasi Okupansi & Pemanfaatan Fasilitas Kampus</h3>
                </div>
                <span class="text-xs text-slate-500 font-mono">Periode: {{ $startDate }} s.d. {{ $endDate }}</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-slate-50/80 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-100">
                            <th class="py-3 px-4">Kode & Fasilitas</th>
                            <th class="py-3 px-4">Lokasi Gedung</th>
                            <th class="py-3 px-4">Total Pengajuan</th>
                            <th class="py-3 px-4">Disetujui</th>
                            <th class="py-3 px-4">Ditolak / Batal</th>
                            <th class="py-3 px-4">Total Jam</th>
                            <th class="py-3 px-4 text-right">Tingkat Utilisasi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <template x-for="row in displayedRows" :key="row.id">
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="py-3 px-4 font-semibold text-slate-800" x-text="row.code + ' • ' + row.name"></td>
                                <td class="py-3 px-4 text-slate-500" x-text="row.building"></td>
                                <td class="py-3 px-4 font-mono text-slate-700" x-text="row.total_applications"></td>
                                <td class="py-3 px-4 font-mono font-semibold text-emerald-600" x-text="row.approved_count"></td>
                                <td class="py-3 px-4 font-mono text-rose-600" x-text="row.rejected_count"></td>
                                <td class="py-3 px-4 font-mono text-slate-600" x-text="parseFloat(row.total_hours).toFixed(1) + ' Jam'"></td>
                                <td class="py-3 px-4 text-right">
                                    <span class="px-2 py-0.5 rounded-full font-mono font-bold text-[10px]"
                                          :class="row.utilization_rate >= 70 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-700'"
                                          x-text="row.utilization_rate + '%'"></span>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            {{-- Pagination Controller --}}
            <template x-if="allRows.length > 0">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-3 border-t border-slate-100 text-xs text-slate-500">
                    <div>
                        Menampilkan <strong class="text-slate-800" x-text="((currentPage - 1) * perPage) + 1"></strong> s.d. <strong class="text-slate-800" x-text="Math.min(currentPage * perPage, allRows.length)"></strong> dari <strong class="text-slate-800" x-text="allRows.length"></strong> fasilitas
                    </div>
                    <div class="flex items-center gap-1">
                        <button type="button" @click="prevPage()" :disabled="currentPage === 1" class="px-2.5 py-1 rounded-lg border border-slate-200 text-xs font-medium hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed">Sebelumnya</button>
                        <template x-for="p in totalPages" :key="p">
                            <button type="button" @click="setPage(p)" x-show="p === 1 || p === totalPages || Math.abs(p - currentPage) <= 1" :class="currentPage === p ? 'bg-slate-900 text-white font-bold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'" class="w-7 h-7 rounded-lg text-xs font-medium flex items-center justify-center" x-text="p"></button>
                        </template>
                        <button type="button" @click="nextPage()" :disabled="currentPage === totalPages" class="px-2.5 py-1 rounded-lg border border-slate-200 text-xs font-medium hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed">Selanjutnya</button>
                    </div>
                </div>
            </template>
        </section>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('facilityReportTable', (initialData) => ({
                allRows: initialData || [],
                currentPage: 1,
                perPage: 10,
                get totalPages() {
                    return Math.max(1, Math.ceil(this.allRows.length / this.perPage));
                },
                get displayedRows() {
                    const start = (this.currentPage - 1) * this.perPage;
                    return this.allRows.slice(start, start + this.perPage);
                },
                setPage(p) {
                    if (p >= 1 && p <= this.totalPages) {
                        this.currentPage = p;
                    }
                },
                prevPage() {
                    if (this.currentPage > 1) {
                        this.currentPage--;
                    }
                },
                nextPage() {
                    if (this.currentPage < this.totalPages) {
                        this.currentPage++;
                    }
                }
            }));
        });
    </script>
    @endpush
</x-admin-layout>
```

---

### 5.6 Blade View: `user-management.blade.php` (Poin 3, 4, 5, 6, 7)
Cuplikan struktur penting dan modal baru:

```html
<x-admin-layout title="Manajemen Pengguna & Otorisasi Akun" active="user-management">
    <div x-data="{
        currentTab: 'sivitas',
        searchQuery: '',
        showDetailModal: false,
        showAddPetugasModal: false,
        showAddPenggunaModal: false,
        showRejectModal: false,
        selectedUser: { id: null, name: '', nim: '', role: '' },
        detailUser: {},
        openDetailModal(u) {
            this.detailUser = u;
            this.showDetailModal = true;
        },
        openRejectModal(id, name, nim, role) {
            this.selectedUser = { id, name, nim, role };
            this.showRejectModal = true;
        }
    }" class="space-y-6">

        {{-- Header Halaman --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-navy tracking-tight">Otorisasi & Manajemen Sivitas Kampus</h1>
                <p class="text-sm text-slate-500 mt-0.5">Kelola verifikasi registrasi mandiri, direktori sivitas, dan akun petugas sarpras.</p>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" @click="showAddPetugasModal = true" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold border border-slate-200 transition-colors">
                    <span class="material-symbols-outlined text-[16px]">badge</span>
                    <span>+ Akun Petugas</span>
                </button>
                <button type="button" @click="showAddPenggunaModal = true" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold transition-colors shadow-xs">
                    <span class="material-symbols-outlined text-[16px]">person_add</span>
                    <span>+ Akun Pengguna</span>
                </button>
            </div>
        </div>

        {{-- Container Utama & 4 Segmented Tabs --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-4 sm:p-5 border-b border-slate-100 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                {{-- 4 Segmented Tabs (Termasuk Tab Deleted / Inactive Baru) --}}
                <div class="inline-flex p-1 rounded-xl bg-slate-100 text-xs font-semibold flex-wrap">
                    <button @click="currentTab = 'verification'" class="px-3 py-1.5 rounded-lg transition-all flex items-center gap-1.5" :class="currentTab === 'verification' ? 'bg-white text-navy shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900'">
                        <span class="material-symbols-outlined text-[16px]">how_to_reg</span>
                        <span>Verifikasi ({{ $pendingCount }})</span>
                    </button>
                    <button @click="currentTab = 'sivitas'" class="px-3 py-1.5 rounded-lg transition-all flex items-center gap-1.5" :class="currentTab === 'sivitas' ? 'bg-white text-navy shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900'">
                        <span class="material-symbols-outlined text-[16px]">group</span>
                        <span>Sivitas Terdaftar ({{ $sivitasCount }})</span>
                    </button>
                    <button @click="currentTab = 'petugas'" class="px-3 py-1.5 rounded-lg transition-all flex items-center gap-1.5" :class="currentTab === 'petugas' ? 'bg-white text-navy shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900'">
                        <span class="material-symbols-outlined text-[16px]">badge</span>
                        <span>Petugas Sarpras ({{ $petugasCount }})</span>
                    </button>
                    <button @click="currentTab = 'inactive'" class="px-3 py-1.5 rounded-lg transition-all flex items-center gap-1.5 text-rose-700" :class="currentTab === 'inactive' ? 'bg-white shadow-xs font-bold' : 'text-slate-600 hover:text-rose-700'">
                        <span class="material-symbols-outlined text-[16px]">person_off</span>
                        <span>Non-aktif / Dihapus ({{ $inactiveCount }})</span>
                    </button>
                </div>

                {{-- Pencarian --}}
                <div class="relative w-full sm:w-64">
                    <span class="material-symbols-outlined absolute left-3 top-2.5 text-slate-400 text-[18px]">search</span>
                    <input type="text" x-model="searchQuery" placeholder="Cari nama, NIM/NIP, email..." class="w-full h-9 pl-9 pr-3 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-800 placeholder:text-slate-400 focus:bg-white focus:border-slate-900 focus:outline-none transition-colors">
                </div>
            </div>

            {{-- TAB 2: Sivitas Terdaftar (Status Akun Dihilangkan, Tombol Lihat Detail Ditambahkan) --}}
            <div x-show="currentTab === 'sivitas'" class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-slate-50/80 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-100">
                            <th class="py-3 px-5">Nama Lengkap</th>
                            <th class="py-3 px-4">Kategori Sivitas</th>
                            <th class="py-3 px-4">NIM / NIP</th>
                            <th class="py-3 px-4">Email Kampus</th>
                            <th class="py-3 px-4">Program Studi / Unit</th>
                            <th class="py-3 px-5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($sivitasUsers as $sivitas)
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="py-3.5 px-5">
                                    <div class="font-semibold text-slate-800 text-sm">{{ $sivitas->name }}</div>
                                    <div class="text-[11px] text-slate-400">Terdaftar sejak {{ $sivitas->created_at?->format('d M Y') }}</div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                        {{ ucfirst($sivitas->role) }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 font-mono text-slate-700">{{ $sivitas->identity_number ?? '-' }}</td>
                                <td class="py-3.5 px-4 font-mono text-slate-600">{{ $sivitas->email }}</td>
                                <td class="py-3.5 px-4 text-slate-700 font-medium">{{ $sivitas->department ?? '-' }}</td>
                                <td class="py-3.5 px-5 text-right">
                                    <button type="button" 
                                            @click="openDetailModal({
                                                id: {{ $sivitas->id }},
                                                name: '{{ addslashes($sivitas->name) }}',
                                                role: '{{ ucfirst($sivitas->role) }}',
                                                identity_number: '{{ addslashes($sivitas->identity_number ?? '-') }}',
                                                email: '{{ addslashes($sivitas->email) }}',
                                                department: '{{ addslashes($sivitas->department ?? '-') }}',
                                                phone_number: '{{ addslashes($sivitas->phone_number ?? '-') }}',
                                                id_card_path: '{{ $sivitas->id_card_path ? asset('storage/' . $sivitas->id_card_path) : '' }}',
                                                status: '{{ $sivitas->status }}',
                                                created_at: '{{ $sivitas->created_at?->format('d M Y, H:i') }}',
                                                reservations_count: {{ $sivitas->reservations_count ?? $sivitas->reservations->count() }},
                                                reports_count: {{ $sivitas->damage_reports_count ?? $sivitas->damageReports->count() }}
                                            })" 
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition-colors">
                                        <span class="material-symbols-outlined text-[16px]">visibility</span>
                                        <span>Lihat Detail</span>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 px-5 text-center text-slate-400 text-xs">Belum ada sivitas terdaftar.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- TAB 4: Akun Non-aktif & Dihapus (Poin 7) --}}
            <div x-show="currentTab === 'inactive'" class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-rose-50/50 text-slate-500 uppercase tracking-wider font-semibold border-b border-rose-100">
                            <th class="py-3 px-5">Nama & Identitas</th>
                            <th class="py-3 px-4">Kategori</th>
                            <th class="py-3 px-4">Status Arsip</th>
                            <th class="py-3 px-4">Catatan / Alasan</th>
                            <th class="py-3 px-4">Tanggal Pembekuan</th>
                            <th class="py-3 px-5 text-right">Otorisasi Pemulihan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($inactiveUsers as $inactive)
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="py-3.5 px-5">
                                    <div class="font-semibold text-slate-800 text-sm">{{ $inactive->name }}</div>
                                    <div class="text-[11px] text-slate-500">{{ $inactive->email }} • NIM/NIP: {{ $inactive->identity_number ?? '-' }}</div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-700">{{ ucfirst($inactive->role) }}</span>
                                </td>
                                <td class="py-3.5 px-4">
                                    @if($inactive->trashed())
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-rose-100 text-rose-800">Dihapus (Soft Delete)</span>
                                    @elseif($inactive->status === 'inactive')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-100 text-amber-800">Dinonaktifkan</span>
                                    @elseif($inactive->status === 'rejected')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-200 text-slate-800">Ditolak Verifikasi</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-slate-500 max-w-xs truncate">{{ $inactive->rejection_reason ?? '-' }}</td>
                                <td class="py-3.5 px-4 text-slate-500 font-mono">{{ $inactive->deleted_at ? $inactive->deleted_at->format('d M Y') : $inactive->updated_at->format('d M Y') }}</td>
                                <td class="py-3.5 px-5 text-right">
                                    <form action="{{ route('admin.users.toggle-status', $inactive->id) }}" method="POST" class="inline" onsubmit="return confirm('Aktifkan kembali akun {{ addslashes($inactive->name) }}?')">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-xs transition-colors">
                                            <span class="material-symbols-outlined text-[16px]">restart_alt</span>
                                            <span>Aktifkan Kembali</span>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 px-5 text-center text-slate-400 text-xs">Tidak ada rekaman akun yang dinonaktifkan atau dihapus.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- MODAL DETAIL PROFIL + TOMBOL NONAKTIFKAN AKUN (Poin 4 & 5) --}}
        <div x-show="showDetailModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div @click.away="showDetailModal = false" class="bg-white rounded-2xl shadow-xl max-w-lg w-full max-h-[90vh] overflow-y-auto p-6 border border-slate-200 flex flex-col gap-5">
                {{-- Header Modal --}}
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[22px] text-slate-700">account_circle</span>
                        <h3 class="font-bold text-base text-slate-900">Detail Lengkap Profil Sivitas</h3>
                    </div>
                    <button type="button" @click="showDetailModal = false" class="text-slate-400 hover:text-slate-600">
                        <span class="material-symbols-outlined text-[20px]">close</span>
                    </button>
                </div>

                {{-- Profil Header Card --}}
                <div class="flex items-center gap-4 p-4 rounded-xl bg-slate-50 border border-slate-200/80">
                    <div class="w-14 h-14 rounded-2xl bg-slate-900 text-white flex items-center justify-center text-xl font-bold font-mono">
                        <span x-text="detailUser.name ? detailUser.name.charAt(0).toUpperCase() : 'U'"></span>
                    </div>
                    <div>
                        <h4 class="text-base font-bold text-slate-900" x-text="detailUser.name"></h4>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-100 text-blue-800" x-text="detailUser.role"></span>
                            <span class="text-xs text-slate-500 font-mono" x-text="'NIM/NIP: ' + detailUser.identity_number"></span>
                        </div>
                    </div>
                </div>

                {{-- Detail Atribut Grid --}}
                <div class="grid grid-cols-2 gap-3 text-xs">
                    <div class="p-3 rounded-xl border border-slate-100 bg-slate-50/50">
                        <span class="text-slate-400 block text-[11px]">Email Resmi</span>
                        <span class="font-semibold text-slate-800 break-all font-mono" x-text="detailUser.email"></span>
                    </div>
                    <div class="p-3 rounded-xl border border-slate-100 bg-slate-50/50">
                        <span class="text-slate-400 block text-[11px]">Nomor Telepon / WA</span>
                        <span class="font-semibold text-slate-800" x-text="detailUser.phone_number || '-'"></span>
                    </div>
                    <div class="p-3 rounded-xl border border-slate-100 bg-slate-50/50">
                        <span class="text-slate-400 block text-[11px]">Departemen / Fakultas</span>
                        <span class="font-semibold text-slate-800" x-text="detailUser.department || '-'"></span>
                    </div>
                    <div class="p-3 rounded-xl border border-slate-100 bg-slate-50/50">
                        <span class="text-slate-400 block text-[11px]">Waktu Terdaftar</span>
                        <span class="font-semibold text-slate-800 font-mono" x-text="detailUser.created_at"></span>
                    </div>
                </div>

                {{-- Aktivitas Peminjaman & Pelaporan --}}
                <div class="grid grid-cols-2 gap-3 text-xs">
                    <div class="p-3 rounded-xl border border-emerald-100 bg-emerald-50/50 flex items-center justify-between">
                        <span class="text-emerald-800 font-medium">Total Reservasi Fasilitas</span>
                        <span class="text-sm font-bold text-emerald-700 font-mono" x-text="detailUser.reservations_count + ' Kali'"></span>
                    </div>
                    <div class="p-3 rounded-xl border border-amber-100 bg-amber-50/50 flex items-center justify-between">
                        <span class="text-amber-800 font-medium">Tiket Laporan Kerusakan</span>
                        <span class="text-sm font-bold text-amber-700 font-mono" x-text="detailUser.reports_count + ' Tiket'"></span>
                    </div>
                </div>

                {{-- Foto Kartu Identitas / KTM --}}
                <div class="p-3 rounded-xl border border-slate-200">
                    <span class="text-xs font-semibold text-slate-700 block mb-2">Dokumen Berkas Identitas (KTM / SK):</span>
                    <template x-if="detailUser.id_card_path">
                        <div class="space-y-2">
                            <img :src="detailUser.id_card_path" alt="Dokumen Identitas" class="w-full max-h-48 object-contain rounded-lg border border-slate-100 bg-slate-50">
                            <a :href="detailUser.id_card_path" target="_blank" class="inline-flex items-center gap-1.5 text-xs text-blue-600 hover:underline">
                                <span class="material-symbols-outlined text-[14px]">open_in_new</span>
                                <span>Buka gambar di tab baru</span>
                            </a>
                        </div>
                    </template>
                    <template x-if="!detailUser.id_card_path">
                        <div class="p-4 text-center text-xs text-slate-400 bg-slate-50 rounded-lg">
                            Tidak ada lampiran berkas identitas tersimpan.
                        </div>
                    </template>
                </div>

                {{-- Footer Modal & Tombol Aksi Nonaktifkan (Poin 5) --}}
                <div class="flex items-center justify-between pt-3 border-t border-slate-100">
                    <button type="button" @click="showDetailModal = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">Tutup</button>
                    
                    {{-- Form Nonaktifkan / Aktifkan Kembali --}}
                    <form :action="'{{ url('/admin/users') }}/' + detailUser.id + '/toggle-status'" method="POST" 
                          onsubmit="return confirm('Apakah Anda yakin ingin memperbarui status akun ini?')">
                        @csrf
                        <template x-if="detailUser.status === 'active'">
                            <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold shadow-xs transition-colors">
                                <span class="material-symbols-outlined text-[16px]">block</span>
                                <span>Nonaktifkan Akun Ini</span>
                            </button>
                        </template>
                        <template x-if="detailUser.status !== 'active'">
                            <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-xs transition-colors">
                                <span class="material-symbols-outlined text-[16px]">check_circle</span>
                                <span>Aktifkan Kembali Akun</span>
                            </button>
                        </template>
                    </form>
                </div>
            </div>
        </div>

    </div>
</x-admin-layout>
```

---

## 6. SKENARIO PENGUJIAN & VERIFIKASI (TEST MATRIX)

| Skenario | Langkah Pengujian | Hasil yang Diharapkan | Status |
|---|---|---|---|
| **Verifikasi Font** | Navigasi antara `/admin/dashboard`, `/admin/facility-master`, dan `/admin/export-report` | Ukuran heading, table header, sel baris, dan badge identik dan proporsional | [x] Siap Uji |
| **Bebas Teks Bocor** | Buka halaman `/admin/export-report` dan periksa area di atas tabel fasilitas | Tidak ada lagi teks sintaks Alpine/HTML parser artifact `"= 1 && p <= this.totalPages) ..."` | [x] Siap Uji |
| **Pagination Alpine** | Klik nomor halaman dan tombol "Sebelumnya" / "Selanjutnya" pada tabel okupansi | Data baris berganti dengan lancar tanpa reload halaman | [x] Siap Uji |
| **Modal Detail** | Di tab "Sivitas Terdaftar", klik tombol "Lihat Detail" pada salah satu baris | Modal terbuka menampilkan foto inisial, foto KTM (jika ada), email, jurusan, no HP, serta stat aktivitas | [x] Siap Uji |
| **Deaktivasi Akun** | Di dalam modal detail akun aktif, klik tombol "Nonaktifkan Akun" | Status akun berubah menjadi `inactive`, flash message sukses muncul, dan akun berpindah ke Tab Non-aktif | [x] Siap Uji |
| **Cegat Login Non-aktif** | Lakukan login menggunakan akun yang baru saja dinonaktifkan | Sistem menampilkan pesan error: *"Akun Anda tidak aktif."* dan menolak pembuatan sesi login | [x] Siap Uji |
| **Hapus Kolom Status** | Periksa tabel tab "Sivitas Terdaftar" | Kolom "Status Akun: Aktif" sudah tidak ada; tampilan tabel lebih lega dan fokus pada nama, NIM, email, prodi, dan tombol detail | [x] Siap Uji |
| **Tab Non-Aktif / Dihapus**| Buka tab "Akun Non-aktif / Dihapus" | Menampilkan akun-akun yang dinonaktifkan dan tombol "Aktifkan Kembali" dapat mengembalikan akun ke tab Sivitas Aktif | [x] Siap Uji |

---
**Dokumen ini siap diserahkan kepada penguji / dosen pembimbing dan dijadikan acuan integrasi tim pengembang CAVA.**
