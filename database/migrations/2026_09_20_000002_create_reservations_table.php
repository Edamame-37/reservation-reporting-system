<?php
/**
 * NAMA FILE    : 2026_09_20_000002_create_reservations_table.php
 * FUNGSI       : Migrasi pembuatan tabel transaksi permohonan reservasi ruang kampus
 * DESKRIPSI    : Menyimpan data pemesanan ruang dengan slot durasi 30 menit (07:00-20:00), status approval, dan log verifikasi petugas.
 * CARA KERJA   : Membangun skema tabel reservations dengan foreign key ke users & facilities, serta indeks komposit untuk optimasi anti-bentrok.
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
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_code', 40)->unique(); // Contoh: TKT-20240428-009
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('facility_id')->constrained('facilities')->cascadeOnDelete();
            $table->date('reservation_date')->index();
            $table->time('start_time'); // Format HH:MM:00 (kelipatan 30m)
            $table->time('end_time');   // Format HH:MM:00 (kelipatan 30m)
            $table->unsignedTinyInteger('total_slots'); // Jumlah slot 30 menit
            $table->text('purpose'); // Tujuan kegiatan, nama acara, PIC
            $table->unsignedInteger('participants_count')->default(1);
            $table->string('permit_letter_path', 255)->nullable(); // Berkas surat izin
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled', 'completed'])->default('pending')->index();
            $table->text('rejection_reason')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            // Indeks komposit untuk mempercepat pencarian bentrok jadwal dan matriks ketersediaan
            $table->index(['facility_id', 'reservation_date', 'status'], 'idx_res_facility_date_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
