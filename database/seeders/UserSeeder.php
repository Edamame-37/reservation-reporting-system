<?php
/**
 * NAMA FILE    : UserSeeder.php
 * FUNGSI       : Seeder inisialisasi akun pengguna awal (Admin, Petugas, dan Sivitas)
 * DESKRIPSI    : Menyediakan akun siap pakai untuk pengujian hak akses sesuai data representatif pada mockup UI.
 * CARA KERJA   : Membuat akun dengan User::firstOrCreate(), mengenkripsi sandi default ('password'), dan menugaskan role Spatie.
 */

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * FUNCTION/PROCEDURE : run()
     * KEGUNAAN           : Mengeksekusi penanaman data akun default ke dalam tabel users.
     * CARA KERJA         : Mengiterasi daftar akun predefined, menyimpan ke database, dan menautkan relasi Spatie role.
     */
    public function run(): void
    {
        $defaultPassword = Hash::make('password');

        // 1. Akun Super Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@kampus.ac.id'],
            [
                'name'            => 'Super Administrator',
                'password'        => $defaultPassword,
                'identity_number' => '198001012005011001',
                'role'            => 'admin',
                'department'      => 'Biro Sarana & Prasarana Kampus',
                'phone_number'    => '081122334455',
                'status'          => 'active',
            ]
        );
        $admin->syncRoles(['admin']);

        // 2. Akun Petugas Sarpras Zona 1
        $petugas1 = User::firstOrCreate(
            ['email' => 'petugas1@kampus.ac.id'],
            [
                'name'            => 'Bambang Sudarsono, S.T.',
                'password'        => $defaultPassword,
                'identity_number' => '198503152010121002',
                'role'            => 'petugas',
                'department'      => 'Pengelola Venue & Gedung Utama',
                'phone_number'    => '081234567890',
                'assignment_zone' => 'Zona 1 (Rektorat & Auditorium)',
                'status'          => 'active',
            ]
        );
        $petugas1->syncRoles(['petugas']);

        // 3. Akun Petugas Sarpras Zona 2
        $petugas2 = User::firstOrCreate(
            ['email' => 'petugas2@kampus.ac.id'],
            [
                'name'            => 'Rudi Hermawan (Laboran)',
                'password'        => $defaultPassword,
                'identity_number' => '199004212015041001',
                'role'            => 'petugas',
                'department'      => 'Laboratorium Terpadu & Jaringan',
                'phone_number'    => '081298765432',
                'assignment_zone' => 'Zona 2 (Gedung Lab Terpadu C)',
                'status'          => 'active',
            ]
        );
        $petugas2->syncRoles(['petugas']);

        // 4. Akun Pengguna / Mahasiswa Aktif (Dimas Pratama - Mockup Actor)
        $mhs1 = User::firstOrCreate(
            ['email' => 'dimas@mahasiswa.ac.id'],
            [
                'name'            => 'Dimas Pratama',
                'password'        => $defaultPassword,
                'identity_number' => '2110512044',
                'role'            => 'mahasiswa',
                'department'      => 'Informatika - Fakultas Ilmu Komputer',
                'phone_number'    => '085711223344',
                'status'          => 'active',
            ]
        );
        $mhs1->syncRoles(['pengguna']);

        // 5. Akun Pengguna / Mahasiswa Aktif (Siti Nurhaliza - Mockup Actor)
        $mhs2 = User::firstOrCreate(
            ['email' => 'siti@mahasiswa.ac.id'],
            [
                'name'            => 'Siti Nurhaliza',
                'password'        => $defaultPassword,
                'identity_number' => '2110512089',
                'role'            => 'mahasiswa',
                'department'      => 'Sistem Informasi - Fasilkom',
                'phone_number'    => '085899887766',
                'status'          => 'active',
            ]
        );
        $mhs2->syncRoles(['pengguna']);

        // 6. Akun Mahasiswa Registrasi Mandiri Status PENDING (Untuk pengujian UR15 verifikasi admin)
        $mhsPending = User::firstOrCreate(
            ['email' => 'ahmad.faisal@mahasiswa.ac.id'],
            [
                'name'            => 'Ahmad Faisal',
                'password'        => $defaultPassword,
                'identity_number' => '2310512001',
                'role'            => 'mahasiswa',
                'department'      => 'Teknik Komputer',
                'phone_number'    => '081377889900',
                'status'          => 'pending',
            ]
        );
        $mhsPending->syncRoles(['pengguna']);
    }
}
