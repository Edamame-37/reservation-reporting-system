<?php
/**
 * NAMA FILE    : AdminUserManagementController.php
 * FUNGSI       : Controller pengelola otorisasi dan verifikasi akun sivitas kampus (Konsol Admin)
 * DESKRIPSI    : Bertanggung jawab menangani antrean verifikasi akun baru status pending (UR15 / ADM-01), persetujuan aktivasi, penolakan registrasi, dan penyediaan dataset pengguna.
 * CARA KERJA   : Menerima HTTP Request terotentikasi Admin, berkomunikasi dengan Model User untuk membaca dan mengubah kolom status ('pending' -> 'active' / 'rejected').
 */

namespace App\Http\Controllers;

use App\Http\Requests\Admin\RejectUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
     * FUNCTION/PROCEDURE : verifyUser()
     * KEGUNAAN           : Memverifikasi dan mengaktifkan akun pengguna baru yang masih berstatus pending.
     * CARA KERJA         : Mengambil data pengguna berdasarkan ID, memvalidasi bahwa status akun saat ini adalah 'pending', mengubah nilai status menjadi 'active', dan menyimpan ke basis data.
     */
    public function verifyUser(int|string $id): RedirectResponse
    {
        $user = User::findOrFail($id);

        if ($user->status !== 'pending') {
            return back()->withErrors(['error' => 'Akun pengguna ini sudah diproses sebelumnya dan tidak berstatus pending.']);
        }

        $user->status = 'active';
        $user->rejection_reason = null;
        $user->save();

        return back()->with('success', "Akun pengguna {$user->name} berhasil diverifikasi dan diaktifkan.");
    }

    /**
     * FUNCTION/PROCEDURE : rejectUser()
     * KEGUNAAN           : Menolak pendaftaran akun pengguna berstatus pending dan mencatat alasan penolakan.
     * CARA KERJA         : Memvalidasi payload via RejectUserRequest, memverifikasi status 'pending', memperbarui status menjadi 'rejected', menyimpan alasan pada kolom rejection_reason, dan memutus akses akun.
     */
    public function rejectUser(RejectUserRequest $request, int|string $id): RedirectResponse
    {
        $user = User::findOrFail($id);

        if ($user->status !== 'pending') {
            return back()->withErrors(['error' => 'Akun pengguna ini sudah diproses sebelumnya dan tidak berstatus pending.']);
        }

        $reasonMap = [
            'invalid_ktm'     => 'Foto KTM / SK buram atau tidak terbaca',
            'mismatched_data' => 'Data NIM/NIP tidak cocok dengan pangkalan data PD-DIKTI',
            'invalid_email'   => 'Bukan domain email resmi universitas',
            'other'           => 'Alasan lainnya',
        ];

        $reasonKey = $request->input('reason');
        $reasonText = $reasonMap[$reasonKey] ?? $reasonKey;

        if ($request->filled('notes')) {
            $reasonText .= ' (Catatan: ' . $request->input('notes') . ')';
        }

        $user->status = 'rejected';
        $user->rejection_reason = $reasonText;
        $user->save();

        return back()->with('success', "Pendaftaran akun {$user->name} telah ditolak.");
    }

    /**
     * FUNCTION/PROCEDURE : storeUser()
     * KEGUNAAN           : Membuat akun internal baru (Petugas / Pengguna) secara langsung tanpa pendaftaran mandiri.
     * CARA KERJA         : Memvalidasi input (memastikan role bukan admin), membuat akun dengan status 'active', dan memberikan role Spatie.
     */
    public function storeUser(Request $request): RedirectResponse
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role'     => 'required|in:petugas,pengguna', // Cegah pembuatan admin baru
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => \Illuminate\Support\Facades\Hash::make($request->password),
            'status'   => 'active', // Langsung aktif
            'role'     => $request->role,
        ]);

        $user->assignRole($request->role);

        return back()->with('success', "Akun {$user->name} dengan peran {$request->role} berhasil dibuat.");
    }
}
