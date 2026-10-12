<?php

/**
 * ==============================================================================================
 * BERKAS MIGRASI ALTERASI TABEL USERS: STATUS FLEKSIBEL & SOFT DELETES
 * ==============================================================================================
 * Berkas: database/migrations/2026_10_03_000001_add_inactive_and_soft_deletes_to_users_table.php
 * 
 * PERAN & FUNGSI UTAMA:
 * 1. Mengubah tipe kolom `status` pada tabel `users` dari tipe data ENUM kaku menjadi `string(30)`
 *    agar fleksibel menampung berbagai siklus hidup akun pengguna: 'pending', 'active', 'inactive', 'banned'.
 * 2. Menambahkan fitur Soft Deletes (`deleted_at`) secara defensif pada tabel `users`.
 * 3. Menjaga Integritas Data & Audit Trail:
 *    Ketika akun pengguna dinonaktifkan atau dihapus oleh Administrator, data pengguna tidak benar-benar
 *    hilang dari hard drive (mencegah rusaknya relasi historis pada riwayat reservasi, log kerusakan,
 *    dan persetujuan ruangan).
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
     * Menggunakan `Schema::table()` (bukan `Schema::create()`) untuk menjalankan perintah ALTER TABLE
     * pada tabel `users` yang sudah ada sebelumnya di basis data.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // $table->string('status', 30)->default('pending')->change()
            // - Menjalankan "ALTER TABLE `users` MODIFY `status` VARCHAR(30) DEFAULT 'pending'".
            // - Method ->change() menginstruksikan Laravel untuk memodifikasi definisi kolom yang sudah ada.
            // - Mengubah tipe ENUM menjadi VARCHAR(30) memberikan fleksibilitas tinggi tanpa perlu alter
            //   tabel berulang kali saat menambah variasi status baru di masa depan.
            $table->string('status', 30)->default('pending')->change();

            // Pengecekan Defensif (Defensive Programming):
            // Memastikan kolom `deleted_at` belum pernah dibuat sebelumnya untuk mencegah error "Duplicate column".
            if (!Schema::hasColumn('users', 'deleted_at')) {
                // $table->softDeletes()
                // - Menambahkan kolom `deleted_at` dengan tipe TIMESTAMP NULL.
                // - Diaktifkan oleh Trait `Illuminate\Database\Eloquent\SoftDeletes` pada Model User.
                // - Saat model dihapus ($user->delete()), Laravel otomatis mengisi kolom ini dengan timestamp sekarang.
                // - Secara default, semua query Eloquent (User::all(), User::where()) otomatis menambahkan
                //   klausa "WHERE `deleted_at` IS NULL" sehingga user yang terhapus tidak muncul di aplikasi.
                $table->softDeletes();
            }
        });
    }

    /**
     * Reverse the migrations.
     * 
     * CARA KERJA & ALUR:
     * Membalikkan perubahan jika migrasi di-rollback dengan menghapus kolom `deleted_at`.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Memeriksa keberadaan kolom sebelum mencoba menghapusnya:
            if (Schema::hasColumn('users', 'deleted_at')) {
                // $table->dropSoftDeletes()
                // - Menjalankan "ALTER TABLE `users` DROP COLUMN `deleted_at`".
                $table->dropSoftDeletes();
            }
        });
    }
};

