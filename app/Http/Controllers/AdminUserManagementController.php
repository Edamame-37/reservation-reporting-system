<?php
/**
 * NAMA FILE    : AdminUserManagementController.php
 * FUNGSI       : Controller pengelola otorisasi dan pembuatan akun internal (Konsol Admin)
 * DESKRIPSI    : Bertanggung jawab menangani pendaftaran akun petugas langsung (US-13) dan akun pengguna langsung (US-14 / ADM-02) dengan status langsung aktif tanpa verifikasi mandiri.
 * CARA KERJA   : Menerima HTTP POST terotentikasi Admin, memvalidasi payload via StoreInternalUserRequest, menyimpan kredensial ke database, dan menugaskan peran Spatie (petugas / pengguna).
 */

namespace App\Http\Controllers;

use App\Http\Requests\Admin\StoreInternalUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AdminUserManagementController extends Controller
{
    /**
     * FUNCTION/PROCEDURE : index()
     * KEGUNAAN           : Menampilkan halaman utama manajemen pengguna dengan data terfilter (pending, sivitas aktif, petugas).
     * CARA KERJA         : Mengambil baris data dari tabel users berdasarkan status dan peran, menghitung ringkasan metrik antrean, dan merender tampilan admin.user-management.
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');

        // 1. Data Pemohon Registrasi Mandiri Berstatus Pending
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
            ->whereIn('role', ['mahasiswa', 'dosen', 'staf']);
        if ($search) {
            $sivitasQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('identity_number', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        $sivitasUsers = $sivitasQuery->orderBy('created_at', 'desc')->get();

        // 3. Data Petugas Sarpras Operasional (UR13)
        $petugasQuery = User::where('role', 'petugas');
        if ($search) {
            $petugasQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('identity_number', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        $petugasUsers = $petugasQuery->orderBy('created_at', 'desc')->get();

        // Agregat Ringkasan Statistik
        $pendingCount = User::where('status', 'pending')->count();
        $sivitasCount = User::where('status', 'active')->whereIn('role', ['mahasiswa', 'dosen', 'staf'])->count();
        $petugasCount = User::where('role', 'petugas')->count();

        return view('admin.user-management', compact(
            'pendingUsers',
            'sivitasUsers',
            'petugasUsers',
            'pendingCount',
            'sivitasCount',
            'petugasCount'
        ));
    }

    /**
     * FUNCTION/PROCEDURE : storeUser()
     * KEGUNAAN           : Mendaftarkan akun internal baru (petugas atau pengguna) secara langsung oleh Super Admin (ADM-02).
     * CARA KERJA         : Memvalidasi payload via StoreInternalUserRequest, memproteksi agar role admin dilarang, mengenkripsi password, menyimpan ke tabel users dengan status 'active' (bypass verifikasi), dan menyematkan peran Spatie (petugas / pengguna).
     */
    public function storeUser(StoreInternalUserRequest $request): RedirectResponse
    {
        $roleInput = $request->input('role', $request->input('role_type', 'pengguna'));
        $role = strtolower($roleInput);

        // Edge Case: Proteksi larangan membuat akun Admin tambahan
        if ($role === 'admin') {
            return back()->withErrors(['role' => 'Pembuatan akun dengan peran Admin dilarang demi keamanan sistem.']);
        }

        $isPetugas = ($role === 'petugas');

        // Pemetaan nomor identitas (NIP / NIM / Identifier)
        $identityNumber = $request->input('identity_number', 
            $isPetugas ? $request->input('nip') : $request->input('identifier')
        );

        // Pemetaan zona penugasan khusus petugas
        $zone = $isPetugas ? $request->input('assignment_zone', $request->input('zone')) : null;

        // Pemetaan peran basis data enum users
        $dbRole = $isPetugas ? 'petugas' : (in_array($role, ['mahasiswa', 'dosen', 'staf']) ? $role : 'mahasiswa');

        // Penentuan password (default 'password' jika tidak diisi)
        $rawPassword = $request->filled('password') ? $request->input('password') : 'password';

        // Pembuatan akun langsung aktif (bypass verifikasi)
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

        // Penyematan peran otorisasi Spatie
        $spatieRole = $isPetugas ? 'petugas' : 'pengguna';
        $user->assignRole($spatieRole);

        return back()->with('success', "Akun internal {$user->name} ({$spatieRole}) berhasil didaftarkan dan langsung aktif.");
    }

    /**
     * FUNCTION/PROCEDURE : storePetugas()
     * KEGUNAAN           : Endpoint khusus penangkap formulir pendaftaran Petugas Sarpras (US-13).
     * CARA KERJA         : Menginjeksi role 'petugas' ke request lalu mengeksekusi logika storeUser().
     */
    public function storePetugas(StoreInternalUserRequest $request): RedirectResponse
    {
        $request->merge(['role' => 'petugas']);
        return $this->storeUser($request);
    }

    /**
     * FUNCTION/PROCEDURE : storePengguna()
     * KEGUNAAN           : Endpoint khusus penangkap formulir pendaftaran Sivitas Langsung (US-14).
     * CARA KERJA         : Mengambil role_type (mahasiswa/dosen/staf) lalu mengeksekusi logika storeUser().
     */
    public function storePengguna(StoreInternalUserRequest $request): RedirectResponse
    {
        $roleType = $request->input('role_type', 'mahasiswa');
        $request->merge(['role' => $roleType]);
        return $this->storeUser($request);
    }
}
