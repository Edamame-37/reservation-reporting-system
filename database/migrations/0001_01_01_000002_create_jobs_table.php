<?php

/**
 * ==============================================================================================
 * BERKAS MIGRASI SISTEM ANTREAN TUGAS ASINKRON (LARAVEL QUEUES & JOBS)
 * ==============================================================================================
 * Berkas: database/migrations/0001_01_01_000002_create_jobs_table.php
 * 
 * PERAN & FUNGSI UTAMA:
 * 1. Menyediakan skema basis data SQL untuk fitur pemrosesan latar belakang (background jobs/queues)
 *    saat aplikasi menggunakan konfigurasi `QUEUE_CONNECTION=database` di file .env.
 * 2. Memisahkan tugas berat (seperti kirim email notifikasi, generasi laporan PDF, broadcast webhook)
 *    dari siklus HTTP request-response agar pengguna mendapatkan respon HTTP instan tanpa menunggu.
 * 3. Menyediakan tabel pelacakan batching (`job_batches`) dan tabel penampung kegagalan (`failed_jobs`)
 *    untuk keperluan audit, logging, dan retry otomatis/manual (`queue:retry`).
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
     * Dijalankan oleh `php artisan migrate` untuk menginisialisasi 3 tabel inti queue:
     * 1. `jobs`        : Antrean pekerjaan aktif yang menunggu atau sedang diproses worker.
     * 2. `job_batches` : Pelacakan progres eksekusi sekumpulan job yang dikelompokkan (batch).
     * 3. `failed_jobs` : Arsip job yang gagal total setelah melewati batas percobaan maksimum (tries).
     */
    public function up(): void
    {
        // ------------------------------------------------------------------------------------------
        // 1. TABEL ANTREAN JOB AKTIF (`jobs`)
        // ------------------------------------------------------------------------------------------
        Schema::create('jobs', function (Blueprint $table) {
            // $table->id()
            // - Kolom BIGINT UNSIGNED AUTO_INCREMENT sebagai Primary Key pekerjaan.
            $table->id();

            // $table->string('queue')->index()
            // - Nama antrean/pipa (misal: 'default', 'emails', 'high_priority').
            // - Diberi ->index() agar worker dapat memfilter `WHERE queue = 'default'` secara efisien.
            $table->string('queue')->index();

            // $table->longText('payload')
            // - Kolom LONGTEXT (hingga 4 Gigabytes) yang menyimpan JSON serialisasi dari class Job PHP,
            //   beserta argumen konstruktor, model binding Eloquent, dan metadata job.
            $table->longText('payload');

            // $table->unsignedSmallInteger('attempts')
            // - Penghitung angka berapa kali job ini sudah dicoba dieksekusi oleh worker.
            // - Jika nilai ini melebihi batas properti `$tries` pada class Job, job akan dipindahkan ke `failed_jobs`.
            $table->unsignedSmallInteger('attempts');

            // $table->unsignedInteger('reserved_at')->nullable()
            // - Timestamp UNIX kapan sebuah proses worker mulai mengunci dan menjalankan job ini.
            // - Bernilai NULL jika job masih bebas dan siap diambil oleh worker mana pun.
            // - Berfungsi mencegah dua worker mengeksekusi job yang sama secara serentak.
            $table->unsignedInteger('reserved_at')->nullable();

            // $table->unsignedInteger('available_at')
            // - Timestamp UNIX kapan job ini diizinkan untuk dieksekusi.
            // - Mendukung penundaan eksekusi (delayed jobs), contoh: `SendReminderEmail::dispatch()->delay(now()->addMinutes(10))`.
            $table->unsignedInteger('available_at');

            // $table->unsignedInteger('created_at')
            // - Timestamp UNIX saat pertama kali job dimasukkan ke dalam antrean oleh aplikasi.
            $table->unsignedInteger('created_at');
        });

        // ------------------------------------------------------------------------------------------
        // 2. TABEL BATCHING PEKERJAAN (`job_batches`)
        // ------------------------------------------------------------------------------------------
        // Digunakan oleh fitur Laravel Bus::batch([...]) untuk memantau status agregat sekumpulan job.
        Schema::create('job_batches', function (Blueprint $table) {
            // $table->string('id')->primary()
            // - ID unik berformat UUID (String) yang merepresentasikan satu siklus batch pekerjaan.
            $table->string('id')->primary();

            // $table->string('name')
            // - Nama deskriptif untuk batch (misalnya: "Impor Data Pengguna CSV").
            $table->string('name');

            // $table->integer('total_jobs')
            // - Jumlah total seluruh sub-pekerjaan yang terdaftar di dalam batch ini.
            $table->integer('total_jobs');

            // $table->integer('pending_jobs')
            // - Jumlah sisa pekerjaan yang belum selesai diproses.
            $table->integer('pending_jobs');

            // $table->integer('failed_jobs')
            // - Jumlah pekerjaan dalam batch yang mengalami kegagalan.
            $table->integer('failed_jobs');

            // $table->longText('failed_job_ids')
            // - Menyimpan array JSON dari ID pekerjaan-pekerjaan yang gagal di dalam batch ini.
            $table->longText('failed_job_ids');

            // $table->mediumText('options')->nullable()
            // - Menyimpan serialisasi closure/callback pendukung, seperti `then()`, `catch()`, atau `finally()`.
            $table->mediumText('options')->nullable();

            // $table->integer('cancelled_at')->nullable()
            // - Timestamp pembatalan batch jika proses batch dibatalkan di tengah jalan (`$batch->cancel()`).
            $table->integer('cancelled_at')->nullable();

            // $table->integer('created_at')
            // - Timestamp UNIX saat batch pertama kali dibuat.
            $table->integer('created_at');

            // $table->integer('finished_at')->nullable()
            // - Timestamp UNIX saat seluruh job dalam batch telah tuntas dikerjakan atau dibatalkan.
            $table->integer('finished_at')->nullable();
        });

        // ------------------------------------------------------------------------------------------
        // 3. TABEL ARSIP PEKERJAAN GAGAL (`failed_jobs`)
        // ------------------------------------------------------------------------------------------
        // Tempat penampungan tugas (Dead-Letter Queue) yang mengalami exception fatal berulang kali.
        Schema::create('failed_jobs', function (Blueprint $table) {
            // $table->id()
            // - Primary key auto increment tabel log kegagalan.
            $table->id();

            // $table->string('uuid')->unique()
            // - UUID unik untuk setiap record kegagalan.
            // - Digunakan saat menjalankan perintah artisan retry, contoh: `php artisan queue:retry <uuid>`.
            $table->string('uuid')->unique();

            // $table->string('connection')
            // - Nama koneksi queue yang digunakan saat job gagal (misal: 'database', 'redis').
            $table->string('connection');

            // $table->string('queue')
            // - Nama antrean pipa tempat job tersebut sebelumnya berada (misal: 'default').
            $table->string('queue');

            // $table->longText('payload')
            // - Payload serialisasi job aseli yang gagal, siap direkonstruksi jika dilakukan retry.
            $table->longText('payload');

            // $table->longText('exception')
            // - Pesan error lengkap beserta Full Stack Trace PHP saat terjadi kegagalan untuk kemudahan debugging developer.
            $table->longText('exception');

            // $table->timestamp('failed_at')->useCurrent()
            // - Kolom TIMESTAMP SQL default saat ini (CURRENT_TIMESTAMP) saat kegagalan dicatat.
            $table->timestamp('failed_at')->useCurrent();

            // $table->index(['connection', 'queue', 'failed_at'])
            // - Indeks gabungan (composite index) untuk mempercepat filter pencarian kegagalan berdasarkan koneksi, antrean, dan rentang waktu.
            $table->index(['connection', 'queue', 'failed_at']);
        });
    }

    /**
     * Reverse the migrations.
     * 
     * CARA KERJA & ALUR:
     * Dijalankan saat rollback untuk menghapus ketiga tabel antrean dari basis data secara aman.
     */
    public function down(): void
    {
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('job_batches');
        Schema::dropIfExists('failed_jobs');
    }
};

