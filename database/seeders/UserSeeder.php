<?php

/**
 * ==============================================================================================
 * SEEDER BASIS DATA: AKUN PENGGUNA DEFAULT & PENUGASAN ROLE SPATIE
 * ==============================================================================================
 * Berkas: database/seeders/UserSeeder.php
 * 
 * PERAN & FUNGSI UTAMA:
 * 1. Menyediakan data awal (mock/seed data) akun pengguna untuk seluruh tingkatan aktor sistem:
 *    - Administrator Sistem (Full Control & Manajemen Pengguna)
 *    - Petugas Lapangan / Laboran (Inspeksi Ruang & Penanganan Tiket Kerusakan berdasarkan Zona)
 *    - Sivitas Akademika Aktif (Mahasiswa Pemohon Pinjam Ruang & Pelapor Kerusakan)
 *    - Akun Mahasiswa Berstatus PENDING (Khusus pengujian alur verifikasi pendaftaran akun oleh Admin)
 * 2. Mengaitkan masing-masing akun dengan peran Spatie RBAC melalui method `$user->syncRoles(...)`.
 * 3. Menjamin idempotensi eksekusi seeder menggunakan `User::firstOrCreate()`.
 * ==============================================================================================
 */

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * CARA KERJA & ALUR:
     * 1. Melakukan pra-komputasi hashing password (`Hash::make('password')`) sekali saja di awal
     *    untuk menghemat beban CPU (Bcrypt hashing memiliki computational cost yang tinggi).
     * 2. Membuat akun Super Administrator dan menyinkronkan peran Spatie 'admin'.
     * 3. Membuat akun Petugas Sarpras Zona 1 dan Zona 2 beserta penugasan zona (`assignment_zone`).
     * 4. Membuat akun Mahasiswa berstatus 'active' dengan peran Spatie 'pengguna'.
     * 5. Membuat akun Mahasiswa berstatus 'pending' untuk pengujian modul verifikasi registrasi admin.
     */
    public function run(): void
    {
        // Pra-hashing kata sandi:
        // Hash::make() mengeksekusi algoritma Bcrypt (work factor 12) untuk menghasilkan string 60 karakter.
        // Dihitung sekali di sini agar tidak menghabiskan waktu CPU saat men-seed banyak akun secara berulang.
        $defaultPassword = Hash::make('password');

        // ------------------------------------------------------------------------------------------
        // 1. AKUN SUPER ADMINISTRATOR
        // ------------------------------------------------------------------------------------------
        // Memiliki hak akses tertinggi untuk konfigurasi sistem, validasi akun baru, dan pelaporan global.
        $admin = User::firstOrCreate(
            ['email' => 'admin@kampus.ac.id'], // Kunci pencarian unik
            [
                'name'            => 'Super Administrator',
                'password'        => $defaultPassword,
                'identity_number' => '198001012005011001',
                'role'            => 'admin',
                'department'      => 'Biro Sarana & Prasarana Kampus',
                'phone_number'    => '081122334455',
                'status'          => 'active', // Akun langsung aktif
            ]
        );
        // $admin->syncRoles(['admin'])
        // - Method dari trait Spatie HasRoles.
        // - Menghapus semua relasi role lama di tabel `model_has_roles` untuk user ini,
        //   lalu menautkan role 'admin' ke user ID ini secara tepat dan bersih.
        $admin->syncRoles(['admin']);

        // ------------------------------------------------------------------------------------------
        // 2. AKUN PETUGAS SARPRAS: ZONA 1 (Rektorat & Auditorium)
        // ------------------------------------------------------------------------------------------
        // Diberikan tanggung jawab operasional pada gedung-gedung serbaguna dan rektorat.
        $petugas1 = User::firstOrCreate(
            ['email' => 'petugas1@kampus.ac.id'],
            [
                'name'            => 'Bambang Sudarsono, S.T.',
                'password'        => $defaultPassword,
                'identity_number' => '198503152010121002',
                'role'            => 'petugas',
                'department'      => 'Pengelola Venue & Gedung Utama',
                'phone_number'    => '081234567890',
                'assignment_zone' => 'Zona 1 (Rektorat & Auditorium)', // Zona penugasan verifikasi
                'status'          => 'active',
            ]
        );
        $petugas1->syncRoles(['petugas']);

        // ------------------------------------------------------------------------------------------
        // 3. AKUN PETUGAS SARPRAS: ZONA 2 (Laboratorium Terpadu & Jaringan)
        // ------------------------------------------------------------------------------------------
        // Diberikan tugas penanganan fasilitas komputer dan perangkat keras lab.
        $petugas2 = User::firstOrCreate(
            ['email' => 'petugas2@kampus.ac.id'],
            [
                'name'            => 'Rudi Hermawan (Laboran)',
                'password'        => $defaultPassword,
                'identity_number' => '199004212015041001',
                'role'            => 'petugas',
                'department'      => 'Laboratorium Terpadu & Jaringan',
                'phone_number'    => '081298765432',
                'assignment_zone' => 'Zona 2 (Gedung Lab Terpadu C)', // Zona penugasan verifikasi
                'status'          => 'active',
            ]
        );
        $petugas2->syncRoles(['petugas']);

        // ------------------------------------------------------------------------------------------
        // 4. AKUN SIVITAS AKADEMIKA / MAHASISWA AKTIF 1 (Dimas Pratama)
        // ------------------------------------------------------------------------------------------
        // Pengguna aktif yang sudah lolos verifikasi akun dan siap mengajukan reservasi.
        $mhs1 = User::firstOrCreate(
            ['email' => 'dimas@mahasiswa.ac.id'],
            [
                'name'            => 'Dimas Pratama',
                'password'        => $defaultPassword,
                'identity_number' => '2110512044', // NIM Mahasiswa
                'role'            => 'mahasiswa',
                'department'      => 'Informatika - Fakultas Ilmu Komputer',
                'phone_number'    => '085711223344',
                'status'          => 'active',
            ]
        );
        // Menautkan peran Spatie 'pengguna' agar memiliki izin booking ruangan:
        $mhs1->syncRoles(['pengguna']);

        // ------------------------------------------------------------------------------------------
        // 5. AKUN SIVITAS AKADEMIKA / MAHASISWA AKTIF 2 (Siti Nurhaliza)
        // ------------------------------------------------------------------------------------------
        $mhs2 = User::firstOrCreate(
            ['email' => 'siti@mahasiswa.ac.id'],
            [
                'name'            => 'Siti Nurhaliza',
                'password'        => $defaultPassword,
                'identity_number' => '2110512089', // NIM Mahasiswa
                'role'            => 'mahasiswa',
                'department'      => 'Sistem Informasi - Fasilkom',
                'phone_number'    => '085899887766',
                'status'          => 'active',
            ]
        );
        $mhs2->syncRoles(['pengguna']);

        // ------------------------------------------------------------------------------------------
        // 6. AKUN PENDING VERIFIKASI (Ahmad Faisal) - PENGUJIAN ALUR UR15 VERIFIKASI ADMIN
        // ------------------------------------------------------------------------------------------
        // Akun ini sengaja diset berstatus 'pending' sebagai data uji bagi Admin untuk memverifikasi
        // foto kartu identitas (KTM), lalu memutuskan apakah akan menyetujui (Approve) atau menolak (Reject).
        $mhsPending = User::firstOrCreate(
            ['email' => 'ahmad.faisal@mahasiswa.ac.id'],
            [
                'name'            => 'Ahmad Faisal',
                'password'        => $defaultPassword,
                'identity_number' => '2310512001',
                'role'            => 'mahasiswa',
                'department'      => 'Teknik Komputer',
                'phone_number'    => '081377889900',
                'status'          => 'pending', // Menunggu persetujuan Admin
            ]
        );
        $mhsPending->syncRoles(['pengguna']);
    }
}

