<?php

/**
 * ==============================================================================================
 * SEEDER BASIS DATA: INISIALISASI PERAN & HAK AKSES SISTEM (SPATIE RBAC)
 * ==============================================================================================
 * Berkas: database/seeders/RolePermissionSeeder.php
 * 
 * PERAN & FUNGSI UTAMA:
 * 1. Menginisialisasi data master peran (roles) Role-Based Access Control (RBAC) ke tabel `roles`.
 * 2. Menjamin integritas otorisasi awal saat aplikasi pertama kali di-deploy (`php artisan db:seed`).
 * 3. Mendaftarkan 4 tingkatan peran esensial pada sistem CAVA:
 *    - `admin`   : Administrator sistem dengan kendali penuh atas pengguna, master ruangan, dan audit.
 *    - `petugas` : Staf operasional/teknisi sarpras yang memverifikasi reservasi dan menangani kerusakan.
 *    - `pengguna`: Sivitas akademika terverifikasi yang berhak memesan ruangan dan melapor fasilitas.
 *    - `visitor` : Pengguna publik/tamu dengan hak akses peninjauan terbatas (katalog dan jadwal umum).
 * ==============================================================================================
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * CARA KERJA & ALUR:
     * 1. Membersihkan cache memori izin Spatie untuk menghindari inkonsistensi cache.
     * 2. Menyiapkan daftar nama peran baku sistem.
     * 3. Melakukan perulangan dan menyimpan setiap peran menggunakan `firstOrCreate()` agar idempoten.
     */
    public function run(): void
    {
        // ------------------------------------------------------------------------------------------
        // 1. RESET CACHE MEMORI IZIN SPATIE
        // ------------------------------------------------------------------------------------------
        // Spatie menyimpan data roles dan permissions di memory/cache untuk menghindari puluhan
        // SQL query di setiap HTTP request.
        // `PermissionRegistrar::forgetCachedPermissions()` memaksa penghapusan cache tersebut
        // sehingga data peran yang baru di-seed akan segera dikenali oleh aplikasi.
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // ------------------------------------------------------------------------------------------
        // 2. DAFTAR PERAN RESMI SISTEM CAVA
        // ------------------------------------------------------------------------------------------
        $roles = [
            'admin',    // Administrator Utama (Pengelola Sistem)
            'petugas',  // Petugas Sarana Prasarana / Verifikator Lapangan
            'pengguna', // Pengguna Sivitas Kampus (Pemohon Reservasi & Pelapor Kerusakan)
            'visitor',  // Pengunjung Publik / Tamu
        ];

        // ------------------------------------------------------------------------------------------
        // 3. PENYIMPANAN DATA IDEMPOTEN KE BASIS DATA
        // ------------------------------------------------------------------------------------------
        foreach ($roles as $role) {
            // Role::firstOrCreate(attributes, values)
            // - Prinsip Idempoten: Jika record dengan 'name' dan 'guard_name' sudah ada di basis data,
            //   metode ini hanya akan mengambil record tersebut tanpa melakukan INSERT baru.
            // - Jika belum ada, metode ini mengeksekusi "INSERT INTO roles ...".
            // - Menjamin seeder aman dijalankan berulang kali tanpa memicu SQL error "Duplicate entry".
            // - 'guard_name' => 'web' memastikan peran ini berlaku untuk session guard web browser pengguna.
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }
    }
}

