<?php

/**
 * ====================================================================================================
 * SISTEM INFORMASI RESERVASI & PELAPORAN FASILITAS KAMPUS (CAVA)
 * MODUL 6: KONSOL ADMIN - VERIFIKASI AKUN PENDING SIVITAS & MANAJEMEN PENGGUNA INTERNAL (ADM-01 & ADM-02)
 * BERKAS: app/Http/Controllers/AdminUserManagementController.php
 * ====================================================================================================
 * 
 * DESKRIPSI BERKAS:
 * Controller ini adalah pengendali utama otorisasi dan tata kelola akun pengguna pada Konsol Admin CAVA.
 * Bertanggung jawab menangani verifikasi pendaftaran mandiri sivitas (ADM-01), persetujuan aktivasi akun,
 * penolakan dengan audit alasan, serta pembuatan akun internal langsung tanpa verifikasi (ADM-02).
 * 
 * PERAN & ARSITEKTUR DALAM SISTEM CAVA:
 * 1. Gatekeeper Akun Sivitas (ADM-01 / UR15):
 *    - Mahasiswa dan dosen yang mendaftar mandiri via Breeze akan terhenti di status 'pending'.
 *    - Admin melalui controller ini meninjau keabsahan bukti identitas (KTM/NIP/Surat Tugas) lalu memutuskan
 *      apakah akun diverifikasi (status -> 'active') atau ditolak (status -> 'rejected').
 * 2. Manajemen Pengguna Internal (ADM-02 / US-13 & US-14):
 *    - Pembuatan akun Petugas Sarpras secara terpusat untuk teknisi dan operator pemeliharaan.
 *    - Pembuatan akun sivitas langsung aktif jika didaftarkan secara kolektif oleh admin kampus.
 * 3. Kebijakan Keamanan & Perlindungan Akun Super Admin:
 *    - Melarang mutlak pengubahan status, penghapusan, atau pembuatan akun tambahan ber-role 'admin'
 *      lewat antarmuka web biasa guna mencegah privilege escalation atau penguncian akun admin.
 * 4. Audit & Soft Deletion:
 *    - Mendukung penghapusan akun secara aman (Soft Delete) sehingga data transaksi historis peminjaman
 *      dan pelaporan kerusakan masa lampau tetap utuh (referential integrity terjaga).
 * ====================================================================================================
 */

namespace App\Http\Controllers;

use App\Http\Requests\Admin\RejectUserRequest;
use App\Http\Requests\Admin\StoreInternalUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

/**
 * Class AdminUserManagementController
 * 
 * Mengendalikan alur interaksi antarmuka administrasi pengguna, verifikasi pendaftaran sivitas,
 * dan tata kelola personil sarpras kampus.
 */
class AdminUserManagementController extends Controller
{
    /**
     * FUNCTION: index()
     * ------------------------------------------------------------------------------------------------
     * KEGUNAAN   : Menampilkan antarmuka utama manajemen pengguna terbagi dalam 4 tab kategori:
     *              1. Antrean Verifikasi Pending (sivitas yang baru mendaftar mandiri).
     *              2. Direktori Sivitas Aktif (mahasiswa, dosen, staf yang telah disetujui).
     *              3. Petugas Sarpras Aktif (tim operasional teknisi dan pemeliharaan fasilitas).
     *              4. Arsip Akun Non-aktif / Dihapus (pengguna yang dinonaktifkan atau di-soft delete).
     * CARA KERJA : 
     * - Mengambil kata kunci pencarian dari query string request (`$request->query('search')`).
     * - Menjalankan kueri Eloquent terpisah untuk masing-masing tab dengan filter kata kunci (nama, email, NIM/NIP, prodi).
     * - Menggunakan `withCount(['reservations', 'damageReports'])` untuk menghitung aktivitas sivitas secara efisien.
     * - Menghitung jumlah total (agregat count) untuk ditampilkan pada badge notifikasi di header tab.
     * - Merender view `admin.user-management` dengan membawa seluruh koleksi data.
     * 
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        // Mengambil parameter kata kunci pencarian global dari request URL (?search=...)
        $search = $request->query('search');

        // ============================================================================================
        // 1. DATA PEMOHON REGISTRASI MANDIRI BERSTATUS PENDING (ADM-01 / UR15)
        // ============================================================================================
        // Menyaring akun pengguna yang baru mendaftar dan menunggu verifikasi berkas kartu identitas
        $pendingQuery = User::where('status', 'pending');
        if ($search) {
            // Pencarian multi-kolom (nama, NIM/NIP, atau email)
            $pendingQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('identity_number', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        // Mengurutkan antrean dari yang paling baru mendaftar (FIFO / LIFO review queue)
        $pendingUsers = $pendingQuery->orderBy('created_at', 'desc')->get();

        // ============================================================================================
        // 2. DATA SIVITAS AKADEMIKA AKTIF TERDAFTAR (MAHASISWA, DOSEN, STAF)
        // ============================================================================================
        // Menampilkan seluruh civitas kampus yang akunnya aktif dan berhak meminjam ruangan
        $sivitasQuery = User::where('status', 'active')
            ->whereIn('role', ['mahasiswa', 'dosen', 'staf'])
            // withCount mencegah N+1 Query Problem saat menghitung total reservasi dan pelaporan
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

        // ============================================================================================
        // 3. DATA PETUGAS SARPRAS OPERASIONAL (ADM-02 / UR13)
        // ============================================================================================
        // Menampilkan daftar personil biro sarana prasarana yang mengelola fasilitas dan tiket perbaikan
        $petugasQuery = User::where('role', 'petugas')->where('status', 'active');
        if ($search) {
            $petugasQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('identity_number', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        $petugasUsers = $petugasQuery->orderBy('created_at', 'desc')->get();

        // ============================================================================================
        // 4. DATA AKUN NON-AKTIF, DITOLAK, & DI-SOFT DELETE (ARSIP AUDIT)
        // ============================================================================================
        // Mengambil seluruh akun bermasalah atau dinonaktifkan, termasuk yang terkena Soft Delete
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

        // ============================================================================================
        // AGREGAT JUMLAH BADGE TAB (STATISTIK CEPAT PADA HEADER NAVIGASI)
        // ============================================================================================
        $pendingCount  = User::where('status', 'pending')->count();
        $sivitasCount  = User::where('status', 'active')->whereIn('role', ['mahasiswa', 'dosen', 'staf'])->count();
        $petugasCount  = User::where('role', 'petugas')->where('status', 'active')->count();
        $inactiveCount = $inactiveUsers->count();

        // Mengirimkan seluruh variabel data ke template Blade admin.user-management
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
     * FUNCTION: verifyUser()
     * ------------------------------------------------------------------------------------------------
     * KEGUNAAN   : Menyetujui pendaftaran mandiri sivitas, mengubah status akun dari 'pending' ke 'active'.
     * CARA KERJA :
     * 1. Menemukan pengguna berdasarkan ID via `User::findOrFail($id)`.
     * 2. Memastikan status akun saat ini benar-benar 'pending'. Jika bukan, mengembalikan pesan kesalahan
     *    untuk menghindari pemrosesan ganda (idempotency protection).
     * 3. Mengubah atribut `status` menjadi `'active'`.
     * 4. Mengosongkan atribut `rejection_reason` (jika pendaftar sebelumnya pernah ditolak).
     * 5. Menyimpan perubahan ke basis data via `$user->save()`.
     * 6. Mengalihkan kembali dengan flash message sukses ke Admin.
     * 
     * @param int|string $id ID pengguna pemohon
     * @return RedirectResponse
     */
    public function verifyUser(int|string $id): RedirectResponse
    {
        // Mencari entitas pengguna berdasarkan primary key atau melempar ModelNotFoundException (HTTP 404)
        $user = User::findOrFail($id);

        // Validasi idempotensi: Hanya akun berstatus 'pending' yang boleh diverifikasi
        if ($user->status !== 'pending') {
            return back()->withErrors(['error' => 'Akun pengguna ini sudah diproses sebelumnya dan tidak berstatus pending.']);
        }

        // Memperbarui status akun menjadi aktif sehingga pengguna diizinkan login
        $user->status = 'active';
        $user->rejection_reason = null;
        $user->save();

        // Mengembalikan respon redirect dengan notifikasi toast sukses
        return back()->with('success', "Akun pengguna {$user->name} berhasil diverifikasi dan diaktifkan.");
    }

    /**
     * FUNCTION: rejectUser()
     * ------------------------------------------------------------------------------------------------
     * KEGUNAAN   : Menolak permohonan pendaftaran akun sivitas dan mencatat alasan penolakan secara resmi.
     * CARA KERJA :
     * 1. Menerima payload yang telah divalidasi oleh `RejectUserRequest` (reason wajib, notes opsional).
     * 2. Memastikan status akun saat ini adalah 'pending'.
     * 3. Memetakan kode kategori alasan (`invalid_ktm`, `mismatched_data`, `invalid_email`, dll.)
     *    ke deskripsi teks Bahasa Indonesia yang informatif.
     * 4. Menggabungkan catatan tambahan (`notes`) jika diisi oleh Admin.
     * 5. Mengubah nilai atribut `status` menjadi `'rejected'` dan menyimpan alasan ke kolom `rejection_reason`.
     * 6. Menyimpan record dan mengembalikan pesan kilat (flash session).
     * 
     * @param RejectUserRequest $request
     * @param int|string $id
     * @return RedirectResponse
     */
    public function rejectUser(RejectUserRequest $request, int|string $id): RedirectResponse
    {
        $user = User::findOrFail($id);

        // Pencegahan pemrosesan ulang pada akun yang bukan berstatus 'pending'
        if ($user->status !== 'pending') {
            return back()->withErrors(['error' => 'Akun pengguna ini sudah diproses sebelumnya dan tidak berstatus pending.']);
        }

        // Kamus pemetaan kode alasan penolakan standar sistem CAVA
        $reasonMap = [
            'invalid_ktm'     => 'Foto KTM / SK buram atau tidak terbaca',
            'mismatched_data' => 'Data NIM/NIP tidak cocok dengan pangkalan data PD-DIKTI',
            'invalid_email'   => 'Bukan domain email resmi universitas',
            'other'           => 'Alasan lainnya',
        ];

        $reasonKey = $request->input('reason');
        $reasonText = $reasonMap[$reasonKey] ?? $reasonKey;

        // Menyertakan catatan spesifik dari administrator jika tersedia
        if ($request->filled('notes')) {
            $reasonText .= ' (Catatan: ' . $request->input('notes') . ')';
        }

        // Mengubah status akun menjadi rejected dan menyimpan catatan alasan penolakan
        $user->status = 'rejected';
        $user->rejection_reason = $reasonText;
        $user->save();

        return back()->with('success', "Pendaftaran akun {$user->name} telah ditolak.");
    }

    /**
     * FUNCTION: storeUser()
     * ------------------------------------------------------------------------------------------------
     * KEGUNAAN   : Mendaftarkan akun internal baru secara langsung oleh Super Admin (ADM-02).
     * CARA KERJA :
     * 1. Menerima data tervalidasi via `StoreInternalUserRequest`.
     * 2. Melakukan pemeriksaan keamanan lapis kedua untuk memastikan peran 'admin' dilarang keras.
     * 3. Menentukan jenis peran (Petugas vs Sivitas: Mahasiswa, Dosen, Staf).
     * 4. Memetakan data atribut identitas (NIP untuk petugas atau NIM/Identifier untuk sivitas).
     * 5. Menentukan kata sandi awal (default 'password' jika tidak diisi khusus oleh Admin) dan menghash via `Hash::make()`.
     * 6. Membuat record pengguna baru dengan status langsung `'active'` (bypass antrean verifikasi).
     * 7. Menyematkan peran Spatie (`assignRole('petugas')` atau `assignRole('pengguna')`).
     * 8. Mengembalikan flash message sukses.
     * 
     * @param StoreInternalUserRequest $request
     * @return RedirectResponse
     */
    public function storeUser(StoreInternalUserRequest $request): RedirectResponse
    {
        $roleInput = $request->input('role', $request->input('role_type', 'pengguna'));
        $role = strtolower($roleInput);

        // Proteksi Keamanan: Mencegah pembuatan akun Admin tambahan via antarmuka web
        if ($role === 'admin') {
            return back()->withErrors(['role' => 'Pembuatan akun dengan peran Admin dilarang demi keamanan sistem.']);
        }

        $isPetugas = ($role === 'petugas');

        // Pemetaan nomor identitas resmi (NIP bagi petugas sarpras atau NIM bagi mahasiswa/sivitas)
        $identityNumber = $request->input('identity_number', 
            $isPetugas ? $request->input('nip') : $request->input('identifier')
        );

        // Pemetaan zona tanggung jawab teknis (hanya berlaku untuk personil petugas sarpras)
        $zone = $isPetugas ? $request->input('assignment_zone', $request->input('zone')) : null;

        // Penentuan enumerasi peran kolom database 'role'
        $dbRole = $isPetugas ? 'petugas' : (in_array($role, ['mahasiswa', 'dosen', 'staf']) ? $role : 'mahasiswa');

        // Hashing kata sandi kredensial (default 'password' jika field sengaja dikosongkan)
        $rawPassword = $request->filled('password') ? $request->input('password') : 'password';

        // Penyimpanan entitas pengguna ke basis data dengan status 'active' seketika
        $user = User::create([
            'name'            => $request->input('name'),
            'email'           => $request->input('email'),
            'password'        => Hash::make($rawPassword),
            'identity_number' => $identityNumber,
            'role'            => $dbRole,
            'department'      => $request->input('department', $isPetugas ? 'Unit Pelaksana Teknis Sarpras' : null),
            'assignment_zone' => $zone,
            'status'          => 'active',
        ]);

        // Menyematkan peran Spatie Permission untuk manajemen hak akses sistem
        $spatieRole = $isPetugas ? 'petugas' : 'pengguna';
        $user->assignRole($spatieRole);

        return back()->with('success', "Akun internal {$user->name} ({$spatieRole}) berhasil didaftarkan dan langsung aktif.");
    }

    /**
     * FUNCTION: storePetugas()
     * ------------------------------------------------------------------------------------------------
     * KEGUNAAN   : Handler khusus formulir pendaftaran akun Petugas Sarpras (US-13).
     * CARA KERJA : Menyuntikkan `'role' => 'petugas'` ke request payload, lalu mendelegasikan
     *              eksekusi ke method `storeUser()`.
     * 
     * @param StoreInternalUserRequest $request
     * @return RedirectResponse
     */
    public function storePetugas(StoreInternalUserRequest $request): RedirectResponse
    {
        // Menyuntikkan role petugas ke dalam request bag sebelum diproses
        $request->merge(['role' => 'petugas']);
        return $this->storeUser($request);
    }

    /**
     * FUNCTION: storePengguna()
     * ------------------------------------------------------------------------------------------------
     * KEGUNAAN   : Handler khusus formulir penambahan akun Sivitas Akademika langsung oleh Admin (US-14).
     * CARA KERJA : Mengambil input `role_type` (default 'mahasiswa'), menyuntikkan ke payload request,
     *              lalu mendelegasikan eksekusi ke method `storeUser()`.
     * 
     * @param StoreInternalUserRequest $request
     * @return RedirectResponse
     */
    public function storePengguna(StoreInternalUserRequest $request): RedirectResponse
    {
        $roleType = $request->input('role_type', 'mahasiswa');
        $request->merge(['role' => $roleType]);
        return $this->storeUser($request);
    }

    /**
     * FUNCTION: toggleStatus()
     * ------------------------------------------------------------------------------------------------
     * KEGUNAAN   : Mengubah status aktif / non-aktif akun pengguna secara cepat (Quick Suspension).
     * CARA KERJA :
     * 1. Menemukan pengguna termasuk yang berstatus soft-deleted via `withTrashed()->findOrFail($id)`.
     * 2. Memastikan akun target BUKAN Super Admin (`$user->hasRole('admin')`). Akun admin kebal suspensi.
     * 3. Jika status saat ini `'active'`, ubah menjadi `'inactive'` (menghalangi login berikutnya).
     * 4. Jika status saat ini tidak aktif, ubah kembali menjadi `'active'` dan pulihkan jika ter-soft-delete.
     * 5. Menyimpan perubahan ke basis data dan memberikan flash message.
     * 
     * @param int|string $id
     * @return RedirectResponse
     */
    public function toggleStatus(int|string $id): RedirectResponse
    {
        $user = User::withTrashed()->findOrFail($id);

        // Perlindungan Keamanan: Menolak keras penonaktifan akun sesama Administrator
        if ($user->hasRole('admin') || $user->role === 'admin') {
            return back()->withErrors(['error' => 'Status akun Administrator dilindungi dan tidak dapat diubah demi keamanan sistem.']);
        }

        if ($user->status === 'active') {
            // Menonaktifkan akun
            $user->status = 'inactive';
            $user->save();
            return back()->with('success', "Akun {$user->name} berhasil dinonaktifkan. Pengguna tidak dapat login ke sistem.");
        } else {
            // Mengaktifkan kembali akun
            $user->status = 'active';
            if ($user->trashed()) {
                $user->restore();
            }
            $user->save();
            return back()->with('success', "Akun {$user->name} berhasil diaktifkan kembali.");
        }
    }

    /**
     * FUNCTION: destroy()
     * ------------------------------------------------------------------------------------------------
     * KEGUNAAN   : Menghapus akun pengguna dari direktori aktif dengan mekanisme Soft Delete.
     * CARA KERJA :
     * 1. Menemukan akun pengguna via `findOrFail($id)`.
     * 2. Memastikan akun target BUKAN Super Admin (akun admin tidak boleh dihapus).
     * 3. Mengubah status akun menjadi `'inactive'`.
     * 4. Memanggil `$user->delete()` yang mengisi timestamp kolom `deleted_at`.
     * 5. Record dipindahkan dari tab aktif ke tab arsip tanpa menghapus baris data dari database.
     * 
     * @param int|string $id
     * @return RedirectResponse
     */
    public function destroy(int|string $id): RedirectResponse
    {
        $user = User::findOrFail($id);

        // Larangan penghapusan akun Administrator
        if ($user->hasRole('admin') || $user->role === 'admin') {
            return back()->withErrors(['error' => 'Akun Administrator tidak dapat dihapus.']);
        }

        $userName = $user->name;
        // Mengubah status menjadi inactive dan menerapkan soft delete
        $user->status = 'inactive';
        $user->save();
        $user->delete();

        return back()->with('success', "Akun {$userName} berhasil dihapus dari direktori aktif dan dipindahkan ke arsip.");
    }

    /**
     * FUNCTION: restore()
     * ------------------------------------------------------------------------------------------------
     * KEGUNAAN   : Memulihkan akun pengguna yang sebelumnya telah di-soft delete atau diarsipkan.
     * CARA KERJA :
     * 1. Menemukan record akun yang telah di-soft delete menggunakan scope `onlyTrashed()->findOrFail($id)`.
     * 2. Memanggil method `$user->restore()` untuk mengosongkan nilai `deleted_at`.
     * 3. Mengembalikan nilai status menjadi `'active'`.
     * 4. Menyimpan perubahan dan mengembalikan flash notification sukses.
     * 
     * @param int|string $id
     * @return RedirectResponse
     */
    public function restore(int|string $id): RedirectResponse
    {
        // Mengambil record khusus pengguna yang berstatus soft-deleted
        $user = User::onlyTrashed()->findOrFail($id);
        
        // Menghapus tanda soft-delete (deleted_at kembali NULL)
        $user->restore();
        
        // Mengaktifkan kembali hak akses login pengguna
        $user->status = 'active';
        $user->save();

        return back()->with('success', "Akun {$user->name} berhasil dipulihkan dari arsip dan siap digunakan kembali.");
    }
}
