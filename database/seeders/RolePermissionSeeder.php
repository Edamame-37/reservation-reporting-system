<?php
/**
 * NAMA FILE    : RolePermissionSeeder.php
 * FUNGSI       : Seeder inisialisasi peran dan hak akses sistem (Spatie RBAC)
 * DESKRIPSI    : Mendaftarkan 4 peran utama sistem: admin, petugas, pengguna, dan visitor.
 * CARA KERJA   : Memanggil Role::firstOrCreate() dari Spatie Permission untuk mencegah duplikasi peran saat dieksekusi berulang.
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * FUNCTION/PROCEDURE : run()
     * KEGUNAAN           : Mengeksekusi penanaman peran otorisasi awal ke dalam tabel roles.
     * CARA KERJA         : Me-reset cache permission Spatie, lalu membuat record peran admin, petugas, pengguna, dan visitor.
     */
    public function run(): void
    {
        // Reset cache izin Spatie agar sinkron
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $roles = [
            'admin',
            'petugas',
            'pengguna',
            'visitor',
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }
    }
}
