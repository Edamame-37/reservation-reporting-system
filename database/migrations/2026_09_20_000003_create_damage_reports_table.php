<?php
/**
 * NAMA FILE    : 2026_09_20_000003_create_damage_reports_table.php
 * FUNGSI       : Migrasi pembuatan tabel tiket pelaporan kerusakan fisik dan malfungsi fasilitas
 * DESKRIPSI    : Menyimpan pengaduan kerusakan aset sarpras, foto bukti (maks 2MB), pelacakan progres, dan log resolusi.
 * CARA KERJA   : Membangun skema tabel damage_reports dengan foreign key ke users dan facilities, serta flag penguncian status ruang.
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
        Schema::create('damage_reports', function (Blueprint $table) {
            $table->id();
            $table->string('report_code', 40)->unique(); // Contoh: RPT-20240422-001
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('facility_id')->constrained('facilities')->cascadeOnDelete();
            $table->string('category', 100); // Contoh: Kelistrikan, AC & Pendingin, Proyektor & Audio
            $table->text('description');
            $table->string('attachment_photo', 255)->nullable(); // Unggahan foto bukti maks 2MB
            $table->enum('status', ['baru', 'diproses', 'selesai', 'ditolak'])->default('baru')->index();
            $table->boolean('is_facility_locked')->default(false); // Flag sinkronisasi 'dalam perbaikan' ke fasilitas
            $table->text('resolution_note')->nullable();
            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['facility_id', 'status'], 'idx_reports_facility_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('damage_reports');
    }
};
