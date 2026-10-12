<?php

/**
 * ====================================================================================================
 * NAMA FILE    : 2026_09_20_000003_create_damage_reports_table.php
 * LOKASI       : database/migrations/2026_09_20_000003_create_damage_reports_table.php
 * FUNGSI       : Migrasi Pembuatan Skema Tabel Tiket Pengaduan Kerusakan Fasilitas Kampus (Titik 0 Basis Data)
 * MODUL CAVA   : 12. Portal Pengguna: Pelaporan Kerusakan Fasilitas & Pelacakan Tiket (USR-04 & USR-05)
 * ====================================================================================================
 * 
 * ARSITEKTUR & PERAN DALAM SISTEM:
 * 1. Titik 0 Tiket Pengaduan Sarpras:
 *    - Tabel `damage_reports` menampung seluruh laporan malfungsi fisik, kerusakan inventaris, atau kendala
 *      teknis (AC, proyektor, kelistrikan) yang diadukan oleh sivitas akademika (USR-04).
 * 
 * 2. Integritas Relasional & Cascade Behavior:
 *    - `user_id`: Foreign key ke tabel `users` (pelapor) dengan `cascadeOnDelete()`.
 *    - `facility_id`: Foreign key ke tabel `facilities` (ruangan bermasalah) dengan `cascadeOnDelete()`.
 *    - `handled_by`: Foreign key ke tabel `users` (staf teknisi/petugas sarpras penanggung jawab) dengan `nullOnDelete()`.
 * 
 * 3. Flag Penguncian Fasilitas (`is_facility_locked`):
 *    - Kolom boolean ini menandai apakah kerusakan bersangkutan telah memicu mode perbaikan
 *      (`status = 'dalam perbaikan'`) pada master fasilitas sehingga ruangan otomatis terkunci dari peminjaman.
 * 
 * 4. Optimasi Kueri Indeks:
 *    - Indeks komposit `idx_reports_facility_status` pada `(facility_id, status)` mempercepat audit kondisi
 *      fisik per venue dan penghitungan jumlah tiket aktif di dashboard operasional sarpras.
 * 
 * ====================================================================================================
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menjalankan migrasi untuk mendirikan tabel 'damage_reports'.
     * 
     * @return void
     */
    public function up(): void
    {
        Schema::create('damage_reports', function (Blueprint $table) {
            // Kolom Primary Key (BigIncrements / unsignedBigInteger auto_increment)
            $table->id();

            // Kolom Kode Tiket Pengaduan Unik (Format: REP-YYYYMMDD-XXXX atau RPT-YYYYMMDD-XXXX)
            // Diberi indeks unique untuk identifikasi resmi tiket di seluruh sistem
            $table->string('report_code', 40)->unique();

            // Kolom Foreign Key ke Pengguna Sivitas Pelapor (Mahasiswa / Dosen)
            // cascadeOnDelete(): Menghapus otomatis riwayat laporan jika akun dihapus permanen
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            // Kolom Foreign Key ke Fasilitas Ruangan / Gedung yang Mengalami Kerusakan
            // cascadeOnDelete(): Menghapus tiket laporan jika entitas fasilitas dihapus permanen
            $table->foreignId('facility_id')->constrained('facilities')->cascadeOnDelete();

            // Kolom Kategori Kerusakan (Contoh: 'AC', 'Kelistrikan', 'Audio & Proyektor', 'Furnitur', 'Bangunan')
            $table->string('category', 100);

            // Kolom Deskripsi Kronologi Kerusakan, Dampak, dan Lokasi Detail
            $table->text('description');

            // Kolom Path Berkas Bukti Foto Kerusakan di Penyimpanan Storage Publik (Maksimal 2MB, Nullable)
            $table->string('attachment_photo', 255)->nullable();

            // Kolom Status Siklus Hidup Penanganan Tiket Kerusakan:
            // - 'baru'     : Laporan baru diajukan oleh pengguna, belum diverifikasi teknisi sarpras
            // - 'diproses' : Teknisi sedang melakukan inspeksi fisik atau perbaikan di lokasi
            // - 'selesai'  : Perbaikan telah tuntas dilaksanakan dan diverifikasi
            // - 'ditolak'  : Laporan ditolak (duplikasi, informasi tidak valid, dll)
            $table->enum('status', ['baru', 'diproses', 'selesai', 'ditolak'])->default('baru')->index();

            // Kolom Status Penguncian Operasional:
            // Bernilai true jika insiden kerusakan cukup fatal sehingga fasilitas harus dialihkan ke mode perbaikan
            $table->boolean('is_facility_locked')->default(false);

            // Kolom Catatan Tindak Lanjut / Resolusi Teknis dari Petugas Sarpras (Nullable)
            $table->text('resolution_note')->nullable();

            // Kolom Foreign Key ke Petugas Sarpras / Teknisi yang Menangani Laporan
            // nullOnDelete(): Mempertahankan rekam jejak resolusi tiket meskipun akun teknisi dihapus
            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();

            // Kolom Waktu Penyelesaian Perbaikan / Penutupan Tiket (Nullable timestamp)
            $table->timestamp('resolved_at')->nullable();

            // Kolom Waktu Pembuatan (created_at) dan Pembaruan (updated_at)
            $table->timestamps();

            // Indeks Komposit: Mengoptimalkan kueri pemfilteran tiket aktif per ruangan
            $table->index(['facility_id', 'status'], 'idx_reports_facility_status');
        });
    }

    /**
     * Membatalkan migrasi (Rollback) dengan menghapus tabel 'damage_reports'.
     * 
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('damage_reports');
    }
};
