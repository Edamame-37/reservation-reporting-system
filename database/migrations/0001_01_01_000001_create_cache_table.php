<?php

/**
 * ==============================================================================================
 * BERKAS MIGRASI SISTEM CACHE & ATOMIC LOCKING BASIS DATA (LARAVEL 11)
 * ==============================================================================================
 * Berkas: database/migrations/0001_01_01_000001_create_cache_table.php
 * 
 * PERAN & FUNGSI UTAMA:
 * 1. Menyediakan skema tabel SQL ketika aplikasi menggunakan driver 'database' untuk caching
 *    (CACHE_STORE=database di file .env).
 * 2. Menyimpan data cache sementara (key-value) beserta masa kedaluwarsanya untuk mempercepat
 *    performa sistem tanpa harus query ulang ke database utama atau API eksternal.
 * 3. Menyediakan tabel atomic locks (`cache_locks`) untuk mekanisme concurrency control
 *    (pencegahan race conditions, misalnya saat eksekusi race condition pada reservasi serentak
 *    atau eksekusi job/task yang tidak boleh dijalankan ganda).
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
     * Menjalankan skrip DDL (Data Definition Language) ke basis data untuk membuat tabel `cache`
     * dan `cache_locks` secara otomatis ketika perintah `php artisan migrate` dijalankan.
     */
    public function up(): void
    {
        // ------------------------------------------------------------------------------------------
        // 1. TABEL CACHE UTAMA (`cache`)
        // ------------------------------------------------------------------------------------------
        // Schema::create('nama_tabel', callback) mengeksekusi DDL "CREATE TABLE `cache` (...)".
        Schema::create('cache', function (Blueprint $table) {
            // $table->string('key')->primary()
            // - Membuat kolom 'key' (VARCHAR 255) yang dijadikan Primary Key tabel.
            // - Mengapa Primary Key? Karena lookup cache selalu berbasis kunci unik O(1) indeks (B-Tree).
            // - Menjamin tidak ada duplikasi entri cache dengan identifier yang sama.
            $table->string('key')->primary();

            // $table->mediumText('value')
            // - Kolom MEDIUMTEXT (mampu menampung payload serialisasi string/JSON/objek hingga ~16 Megabytes).
            // - Digunakan oleh serializer bawaan Laravel (serialize/unserialize atau JSON) untuk menyimpan
            //   nilai variabel, array data query, atau status sesi cache.
            $table->mediumText('value');

            // $table->bigInteger('expiration')->index()
            // - Kolom BIGINT (64-bit integer) untuk menyimpan timestamp waktu kedaluwarsa (UNIX Epoch timestamp).
            // - Diberi ->index() agar kueri pembersihan cache yang usang (pruning/garbage collection:
            //   "DELETE FROM cache WHERE expiration <= ?") berjalan secepat kilat tanpa full-table scan.
            $table->bigInteger('expiration')->index();
        });

        // ------------------------------------------------------------------------------------------
        // 2. TABEL ATOMIC LOCKS (`cache_locks`)
        // ------------------------------------------------------------------------------------------
        // Digunakan oleh fitur Laravel Atomic Lock: Cache::lock('proses-reservasi', 10)->get(...)
        // Memastikan hanya 1 worker/request yang dapat memproses transaksi kritis pada satu waktu.
        Schema::create('cache_locks', function (Blueprint $table) {
            // $table->string('key')->primary()
            // - Nama/kunci lock yang bersifat unik (misal: "lock:reservasi:jadwal_12").
            // - Menjadi Primary Key agar upaya perebutan lock kedua secara serentak langsung gagal/terblokir.
            $table->string('key')->primary();

            // $table->string('owner')
            // - Menyimpan token identitas unik (biasanya UUID/string random) dari proses atau thread
            //   yang berhasil mengklaim lock ini.
            // - Digunakan saat melepaskan lock ($lock->release()) untuk memverifikasi bahwa hanya proses
            //   pemilik aseli yang boleh melepaskan lock tersebut.
            $table->string('owner');

            // $table->bigInteger('expiration')->index()
            // - Waktu kedaluwarsa lock (UNIX timestamp). Jika proses crash atau timeout,
            //   lock akan kedaluwarsa secara otomatis agar sistem tidak mengalami deadlock selamanya.
            $table->bigInteger('expiration')->index();
        });
    }

    /**
     * Reverse the migrations.
     * 
     * CARA KERJA & ALUR:
     * Dijalankan saat rollback (`php artisan migrate:rollback` atau `migrate:reset`).
     * Menghapus tabel `cache` dan `cache_locks` secara bersih dari skema basis data jika tabel ada.
     */
    public function down(): void
    {
        // Schema::dropIfExists() mengeksekusi "DROP TABLE IF EXISTS `cache`".
        Schema::dropIfExists('cache');

        // Schema::dropIfExists() mengeksekusi "DROP TABLE IF EXISTS `cache_locks`".
        Schema::dropIfExists('cache_locks');
    }
};

