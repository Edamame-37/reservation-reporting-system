<?php
/**
 * NAMA FILE    : 2026_09_20_000001_create_facilities_table.php
 * FUNGSI       : Migrasi pembuatan tabel master inventaris fasilitas dan ruangan kampus
 * DESKRIPSI    : Menyimpan data spesifikasi ruang, gedung, kapasitas, perlengkapan (JSON), dan status operasional.
 * CARA KERJA   : Membangun skema tabel facilities dengan dukungan soft deletes agar riwayat peminjaman lama tidak hilang.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('facilities', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique(); // Contoh: AUD-H01, LAB-C204
            $table->string('name', 150);
            $table->enum('category', ['auditorium', 'lab', 'kelas', 'olahraga', 'rapat']);
            $table->string('building', 100);
            $table->string('floor_location', 50)->nullable();
            $table->unsignedInteger('capacity');
            $table->json('equipment')->nullable(); // Array daftar alat pendukung
            $table->text('description')->nullable();
            $table->string('image_path', 255)->nullable();
            $table->enum('status', ['aktif', 'dalam perbaikan', 'nonaktif'])->default('aktif')->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('facilities');
    }
};
