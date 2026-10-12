<?php

/**
 * ==============================================================================================
 * BERKAS MIGRASI TABEL OTORISASI RBAC (SPATIE LARAVEL PERMISSION)
 * ==============================================================================================
 * Berkas: database/migrations/2026_09_20_151512_create_permission_tables.php
 * 
 * PERAN & FUNGSI UTAMA:
 * 1. Mengimplementasikan struktur tabel Role-Based Access Control (RBAC) berstandar industri.
 * 2. Membangun 5 tabel relasional inti yang saling terhubung:
 *    - `permissions`           : Daftar hak akses granular (contoh: 'reservations.approve', 'rooms.edit').
 *    - `roles`                 : Daftar kelompok peran pengguna (contoh: 'admin', 'petugas', 'user').
 *    - `model_has_permissions`: Tabel pivot polimorfik relasi langsung antara Model (User) dan Permission.
 *    - `model_has_roles`      : Tabel pivot polimorfik relasi antara Model (User) dan Role.
 *    - `role_has_permissions` : Tabel pivot many-to-many yang menghubungkan Role dengan hak aksesnya.
 * 3. Menggunakan Polimorfisme Eloquent (`model_type` + `model_id`) sehingga hak akses/peran dapat
 *    diberikan tidak hanya kepada model User, melainkan model entitas lain di masa depan jika dibutuhkan.
 * ==============================================================================================
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * CARA KERJA & ALUR:
     * 1. Mengambil konfigurasi tabel dan kolom dinamis dari `config/permission.php`.
     * 2. Memastikan konfigurasi valid sebelum menjalankan eksekusi DDL.
     * 3. Membuat tabel master: `permissions` dan `roles`.
     * 4. Membuat tabel pivot polimorfik: `model_has_permissions` dan `model_has_roles`.
     * 5. Membuat tabel pivot relasi: `role_has_permissions`.
     * 6. Membersihkan cache permission agar perubahan struktur terbaca langsung oleh aplikasi.
     */
    public function up(): void
    {
        // ------------------------------------------------------------------------------------------
        // PEMBACAAN KONFIGURASI DARI config/permission.php
        // ------------------------------------------------------------------------------------------
        // Mengizinkan nama tabel, kolom pivot, dan fitur multi-tenant (teams) disesuaikan secara dinamis.
        $teams = config('permission.teams');
        $tableNames = config('permission.table_names');
        $columnNames = config('permission.column_names');
        $pivotRole = $columnNames['role_pivot_key'] ?? 'role_id';
        $pivotPermission = $columnNames['permission_pivot_key'] ?? 'permission_id';

        // Validasi: Jika config belum termuat (misal karena config cache kadaluarsa), lemparkan Exception instruktif.
        throw_if(empty($tableNames), 'Error: config/permission.php not loaded. Run [php artisan config:clear] and try again.');
        throw_if($teams && empty($columnNames['team_foreign_key'] ?? null), 'Error: team_foreign_key on config/permission.php not loaded. Run [php artisan config:clear] and try again.');

        // ------------------------------------------------------------------------------------------
        // 1. TABEL MASTER HAK AKSES (`permissions`)
        // ------------------------------------------------------------------------------------------
        Schema::create($tableNames['permissions'], static function (Blueprint $table) {
            $table->id(); // BIGINT UNSIGNED AUTO_INCREMENT sebagai Primary Key permission.
            $table->string('name'); // Nama hak akses (contoh: 'view reservations', 'export reports').
            $table->string('guard_name'); // Guard autentikasi yang berlaku (contoh: 'web', 'api').
            $table->timestamps(); // created_at dan updated_at.

            // Memastikan kombinasi nama permission dan guard bersifat unik (tidak boleh ada permission ganda di guard yang sama).
            $table->unique(['name', 'guard_name']);
        });

        // ------------------------------------------------------------------------------------------
        // 2. TABEL MASTER PERAN (`roles`)
        // ------------------------------------------------------------------------------------------
        Schema::create($tableNames['roles'], static function (Blueprint $table) use ($teams, $columnNames) {
            $table->id(); // BIGINT UNSIGNED AUTO_INCREMENT sebagai Primary Key role.
            
            // Dukungan multi-team / multi-tenant (jika diaktifkan pada konfigurasi).
            if ($teams || config('permission.testing')) {
                $table->unsignedBigInteger($columnNames['team_foreign_key'])->nullable();
                $table->index($columnNames['team_foreign_key'], 'roles_team_foreign_key_index');
            }
            
            $table->string('name'); // Nama peran (contoh: 'admin', 'petugas', 'user').
            $table->string('guard_name'); // Guard autentikasi yang berlaku (contoh: 'web').
            $table->timestamps(); // created_at dan updated_at.
            
            // Integritas Unik: Menjamin tidak ada duplikasi peran pada guard (dan team) yang sama.
            if ($teams || config('permission.testing')) {
                $table->unique([$columnNames['team_foreign_key'], 'name', 'guard_name']);
            } else {
                $table->unique(['name', 'guard_name']);
            }
        });

        // ------------------------------------------------------------------------------------------
        // 3. TABEL PIVOT POLIMORFIK: MODEL ➔ PERMISSIONS (`model_has_permissions`)
        // ------------------------------------------------------------------------------------------
        // Digunakan ketika hak akses (permission) diberikan langsung ke entitas pengguna secara individual
        // (direct permission override) tanpa melalui peran (role).
        Schema::create($tableNames['model_has_permissions'], static function (Blueprint $table) use ($tableNames, $columnNames, $pivotPermission, $teams) {
            // Foreign Key ke tabel permissions (`permission_id`).
            $table->unsignedBigInteger($pivotPermission);

            // Kolom Polimorfisme:
            // - `model_type`: Menyimpan nama kelas FQCN (misal: 'App\Models\User').
            // - `model_morph_key`: Menyimpan ID entitas pengguna (`user_id`).
            $table->string('model_type');
            $table->unsignedBigInteger($columnNames['model_morph_key']);
            $table->index([$columnNames['model_morph_key'], 'model_type'], 'model_has_permissions_model_id_model_type_index');

            // Foreign Key Constraint:
            // Jika record permission di tabel master `permissions` dihapus, seluruh relasi pivot terkait akan dihapus otomatis (CASCADE).
            $table->foreign($pivotPermission)
                ->references('id')
                ->on($tableNames['permissions'])
                ->cascadeOnDelete();
                
            // Menjadikan kombinasi kolom sebagai Composite Primary Key untuk mencegah pemberian hak ganda.
            if ($teams) {
                $table->unsignedBigInteger($columnNames['team_foreign_key']);
                $table->index($columnNames['team_foreign_key'], 'model_has_permissions_team_foreign_key_index');

                $table->primary([$columnNames['team_foreign_key'], $pivotPermission, $columnNames['model_morph_key'], 'model_type'],
                    'model_has_permissions_permission_model_type_primary');
            } else {
                $table->primary([$pivotPermission, $columnNames['model_morph_key'], 'model_type'],
                    'model_has_permissions_permission_model_type_primary');
            }
        });

        // ------------------------------------------------------------------------------------------
        // 4. TABEL PIVOT POLIMORFIK: MODEL ➔ ROLES (`model_has_roles`)
        // ------------------------------------------------------------------------------------------
        // Menyimpan penugasan peran ke pengguna (contoh: User ID 1 memiliki role 'admin').
        Schema::create($tableNames['model_has_roles'], static function (Blueprint $table) use ($tableNames, $columnNames, $pivotRole, $teams) {
            // Foreign Key ke tabel roles (`role_id`).
            $table->unsignedBigInteger($pivotRole);

            // Kolom Polimorfisme (model_type = 'App\Models\User', model_morph_key = user.id).
            $table->string('model_type');
            $table->unsignedBigInteger($columnNames['model_morph_key']);
            $table->index([$columnNames['model_morph_key'], 'model_type'], 'model_has_roles_model_id_model_type_index');

            // Foreign Key Constraint dengan penghapusan kaskade jika role di tabel master terhapus.
            $table->foreign($pivotRole)
                ->references('id')
                ->on($tableNames['roles'])
                ->cascadeOnDelete();
                
            // Composite Primary Key menjamin seorang user tidak memiliki role yang sama berulang kali.
            if ($teams) {
                $table->unsignedBigInteger($columnNames['team_foreign_key']);
                $table->index($columnNames['team_foreign_key'], 'model_has_roles_team_foreign_key_index');

                $table->primary([$columnNames['team_foreign_key'], $pivotRole, $columnNames['model_morph_key'], 'model_type'],
                    'model_has_roles_role_model_type_primary');
            } else {
                $table->primary([$pivotRole, $columnNames['model_morph_key'], 'model_type'],
                    'model_has_roles_role_model_type_primary');
            }
        });

        // ------------------------------------------------------------------------------------------
        // 5. TABEL PIVOT: ROLES ➔ PERMISSIONS (`role_has_permissions`)
        // ------------------------------------------------------------------------------------------
        // Menentukan paket hak akses apa saja yang dimiliki oleh suatu peran
        // (contoh: Role 'admin' memiliki semua permission, Role 'petugas' hanya memiliki hak approval & inspeksi).
        Schema::create($tableNames['role_has_permissions'], static function (Blueprint $table) use ($tableNames, $pivotRole, $pivotPermission) {
            $table->unsignedBigInteger($pivotPermission);
            $table->unsignedBigInteger($pivotRole);

            // Integritas Referensial Kaskade:
            $table->foreign($pivotPermission)
                ->references('id')
                ->on($tableNames['permissions'])
                ->cascadeOnDelete();

            $table->foreign($pivotRole)
                ->references('id')
                ->on($tableNames['roles'])
                ->cascadeOnDelete();

            // Composite Primary Key [permission_id, role_id].
            $table->primary([$pivotPermission, $pivotRole], 'role_has_permissions_permission_id_role_id_primary');
        });

        // ------------------------------------------------------------------------------------------
        // 6. INVALIDASI CACHE OTORISASI
        // ------------------------------------------------------------------------------------------
        // Spatie menyimpan data otorisasi di dalam Cache (driver Redis atau Database) untuk kecepatan cek $user->can().
        // Perintah ini menghapus entri cache lama agar struktur dan data baru langsung terefleksi.
        app('cache')
            ->store(config('permission.cache.store') != 'default' ? config('permission.cache.store') : null)
            ->forget(config('permission.cache.key'));
    }

    /**
     * Reverse the migrations.
     * 
     * CARA KERJA & ALUR:
     * Menghapus tabel otorisasi dalam urutan terbalik dari relasi foreign key
     * (dari tabel pivot anak ke tabel master induk) untuk menghindari Foreign Key Constraint Violation.
     */
    public function down(): void
    {
        $tableNames = config('permission.table_names');

        throw_if(empty($tableNames), 'Error: config/permission.php not found and defaults could not be merged. Please publish the package configuration before proceeding, or drop the tables manually.');

        // Hapus tabel pivot terlebih dahulu:
        Schema::dropIfExists($tableNames['role_has_permissions']);
        Schema::dropIfExists($tableNames['model_has_roles']);
        Schema::dropIfExists($tableNames['model_has_permissions']);
        
        // Hapus tabel master:
        Schema::dropIfExists($tableNames['roles']);
        Schema::dropIfExists($tableNames['permissions']);
    }
};

