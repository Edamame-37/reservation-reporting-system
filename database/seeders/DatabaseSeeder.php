<?php
/**
 * NAMA FILE    : DatabaseSeeder.php
 * FUNGSI       : Seeder Utama (Master Orchestrator Seeder)
 * DESKRIPSI    : Mengorkestrasi eksekusi seluruh seeder sistem secara berurutan sesuai relasi ketergantungan data.
 * CARA KERJA   : Memanggil RolePermissionSeeder -> UserSeeder -> FacilitySeeder saat perintah php artisan db:seed dijalankan.
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * FUNCTION/PROCEDURE : run()
     * KEGUNAAN           : Mengeksekusi penanaman master data secara berurutan.
     * CARA KERJA         : Menjalankan seeder otorisasi terlebih dahulu, kemudian seeder pengguna, dan inventaris fasilitas kampus.
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            UserSeeder::class,
            FacilitySeeder::class,
        ]);
    }
}
