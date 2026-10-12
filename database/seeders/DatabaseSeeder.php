<?php

/**
 * ==============================================================================================
 * MASTER ORCHESTRATOR SEEDER BASIS DATA (LARAVEL 11)
 * ==============================================================================================
 * Berkas: database/seeders/DatabaseSeeder.php
 * 
 * PERAN & FUNGSI UTAMA:
 * 1. Berfungsi sebagai orkestrator sentral (Root Seeder) yang dieksekusi secara default ketika
 *    perintah CLI `php artisan db:seed` atau `php artisan migrate --seed` dijalankan.
 * 2. Mengatur urutan eksekusi seeder turunan secara berurutan dan disiplin guna mematuhi
 *    Integritas Ketergantungan Foreign Key (Data Dependency Pipeline).
 * 3. Menghindari error constraint basis data (contoh: UserSeeder tidak boleh dieksekusi sebelum
 *    tabel roles diisi oleh RolePermissionSeeder).
 * ==============================================================================================
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * CARA KERJA & ALUR:
     * Memanggil method `$this->call([...])` dengan array class seeder yang diurutkan secara ketat:
     * 1. `RolePermissionSeeder` : Menyiapkan master RBAC (admin, petugas, pengguna, visitor).
     * 2. `UserSeeder`           : Menyiapkan user awal & menautkan role dari step 1.
     * 3. `FacilitySeeder`       : Menyiapkan master kategori & fasilitas ruangan dasar.
     * 4. `FacilityUndipSeeder`  : Menyiapkan master katalog ruangan & sarpras spesifik UNDIP.
     */
    public function run(): void
    {
        // $this->call(array $classNames)
        // - Menginstansiasi setiap class seeder melalui Laravel Service Container (IoC).
        // - Menjalankan method run() masing-masing seeder secara sekuensial (berurutan dari atas ke bawah).
        // - Menampilkan log durasi dan status eksekusi seeder di terminal secara rapi.
        $this->call([
            RolePermissionSeeder::class, // Langkah 1: Otorisasi & Peran Spatie RBAC (Prasyarat User)
            UserSeeder::class,           // Langkah 2: Akun Pengguna Awal (Bergantung pada tabel roles)
            FacilitySeeder::class,       // Langkah 3: Master Data Fasilitas & Ruangan Awal
            FacilityUndipSeeder::class,  // Langkah 4: Master Data Fasilitas Riil Kampus
        ]);
    }
}

