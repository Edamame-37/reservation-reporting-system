<?php

/**
 * ==============================================================================================
 * BERKAS MIGRASI MASTER DATA FASILITAS & RUANGAN KAMPUS (TITIK 0 SKEMA DB)
 * ==============================================================================================
 * Berkas: database/migrations/2026_09_20_000001_create_facilities_table.php
 * 
 * PERAN & FUNGSI UTAMA:
 * 1. Merupakan pondasi skema basis data inti untuk seluruh katalog ruangan dan venue di sistem CAVA.
 * 2. Menyimpan metadata lengkap sarana prasarana: kode unik ruangan, nama gedung, lantai,
 *    kapasitas kursi, inventaris fasilitas internal (format JSON), foto ruangan, dan status operasional.
 * 3. Mengintegrasikan Soft Deletes (`deleted_at`) guna melindungi data historis permohonan reservasi
 *    serta tiket pelaporan kerusakan agar relasi basis data tidak menjadi *orphaned* atau terhapus
 *    secara permanen ketika suatu ruangan dinonaktifkan/dihapus oleh Administrator.
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
     * Dijalankan saat `php artisan migrate` untuk mengeksekusi DDL "CREATE TABLE `facilities` (...)".
     */
    public function up(): void
    {
        Schema::create('facilities', function (Blueprint $table) {
            // $table->id()
            // - Menghasilkan kolom BIGINT UNSIGNED AUTO_INCREMENT sebagai Primary Key.
            $table->id();

            // $table->string('code', 30)->unique()
            // - Kode unik ruangan / natural key (contoh: 'AUD-H01', 'LAB-C204', 'RK-TEK3').
            // - Batas panjang 30 karakter dengan constraint UNIQUE untuk mencegah duplikasi kode ruangan.
            // - Mempercepat pencarian O(1) berbasis kode pada URL atau filter cepat.
            $table->string('code', 30)->unique();

            // $table->string('name', 150)
            // - Nama resmi ruangan atau aula kampus (contoh: 'Auditorium Prof. Soedarto', 'Lab Jaringan Komputer').
            $table->string('name', 150);

            // $table->enum('category', ['auditorium', 'lab', 'kelas', 'olahraga', 'rapat'])
            // - Tipe data ENUM MySQL/PostgreSQL untuk membatasi kategori ruangan secara konsisten.
            // - Memudahkan kategorisasi pada filter katalog publik dan formulir admin.
            $table->enum('category', ['auditorium', 'lab', 'kelas', 'olahraga', 'rapat']);

            // $table->string('building', 100)
            // - Nama gedung atau kluster kampus tempat ruangan berada (contoh: 'Gedung Rektorat', 'Gedung Lab Terpadu').
            $table->string('building', 100);

            // $table->string('floor_location', 50)->nullable()
            // - Keterangan lantai atau sayap gedung (contoh: 'Lantai 2 Sayap Barat'). Bersifat opsional (nullable).
            $table->string('floor_location', 50)->nullable();

            // $table->unsignedInteger('capacity')
            // - Kapasitas daya tampung maksimum (jumlah kursi/orang).
            // - Menggunakan unsignedInteger (bilangan bulat positif) untuk mencegah input angka minus secara fisik.
            $table->unsignedInteger('capacity');

            // $table->json('equipment')->nullable()
            // - Kolom native JSON basis data untuk menampung daftar fasilitas/alat pendukung fleksibel
            //   (contoh: ["Proyektor 4K", "Sound System 1000W", "AC Dual Inverter", "Microphone Wireless"]).
            // - Di model Facility, kolom ini otomatis di-cast menjadi array PHP murni (`'equipment' => 'array'`).
            $table->json('equipment')->nullable();

            // $table->text('description')->nullable()
            // - Deskripsi naratif, panduan khusus, atau tata tertib penggunaan ruangan (tipe TEXT hingga 64KB).
            $table->text('description')->nullable();

            // $table->string('image_path', 255)->nullable()
            // - Menyimpan relative path lokasi foto cover ruangan di storage disk (contoh: 'facilities/cover_abc123.webp').
            $table->string('image_path', 255)->nullable();

            // $table->enum('status', ['aktif', 'dalam perbaikan', 'nonaktif'])->default('aktif')->index()
            // - State machine status operasional ruangan:
            //   * 'aktif'          : Ruangan siap dipesan oleh sivitas kampus di katalog.
            //   * 'dalam perbaikan': Ruangan sedang dalam pemeliharaan teknis; terkunci dari pemesanan baru.
            //   * 'nonaktif'       : Ruangan ditutup sementara untuk kegiatan internal atau renovasi.
            // - Diberi ->index() karena hampir seluruh query publik & validasi reservasi memfilter `WHERE status = 'aktif'`.
            $table->enum('status', ['aktif', 'dalam perbaikan', 'nonaktif'])->default('aktif')->index();

            // $table->timestamps()
            // - Menghasilkan kolom TIMESTAMP `created_at` dan `updated_at`.
            $table->timestamps();

            // $table->softDeletes()
            // - Menghasilkan kolom TIMESTAMP `deleted_at` (nullable).
            // - Mengaktifkan fitur Soft Deletes Eloquent: menghapus data dari tampilan tanpa menghapus baris SQL aseli,
            //   sehingga laporan audit dan riwayat reservasi masa lalu tetap utuh.
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     * 
     * CARA KERJA & ALUR:
     * Dijalankan saat rollback untuk menghapus tabel `facilities` dari basis data jika ada.
     */
    public function down(): void
    {
        Schema::dropIfExists('facilities');
    }
};

