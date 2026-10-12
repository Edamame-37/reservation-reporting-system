<?php

/**
 * ====================================================================================================
 * NAMA FILE    : 2026_09_20_000002_create_reservations_table.php
 * LOKASI       : database/migrations/2026_09_20_000002_create_reservations_table.php
 * FUNGSI       : Migrasi Pembuatan Skema Tabel Transaksi Permohonan Reservasi Ruang Kampus (Titik 0 Basis Data)
 * MODUL CAVA   : 10. Portal Pengguna: Formulir Pengajuan Reservasi Ruangan Mandiri (USR-01)
 * ====================================================================================================
 * 
 * ARSITEKTUR & PERAN DALAM SISTEM:
 * 1. Titik 0 Transaksi Sivitas:
 *    - Tabel `reservations` merupakan jantung transaksi seluruh aktivitas peminjaman fasilitas di CAVA.
 *    - Setiap permohonan yang diajukan oleh mahasiswa/dosen melalui formulir mandiri (USR-01) disimpan
 *      pada tabel ini dengan status awal `'pending'`.
 * 
 * 2. Integritas Relasional & Cascade Behavior:
 *    - Foreign key `user_id` merujuk ke tabel `users` dengan `cascadeOnDelete()`: Jika entitas akun pengguna
 *      dihapus permanen, seluruh reservasi terkait akan terhapus demi menjaga konsistensi integritas referensial.
 *    - Foreign key `facility_id` merujuk ke tabel `facilities` dengan `cascadeOnDelete()`.
 *    - Foreign key `reviewed_by` merujuk ke tabel `users` (petugas pemeriksa) dengan `nullOnDelete()`: Jika akun
 *      petugas dihapus, catatan riwayat persetujuan tetap dipertahankan dan nilai kolom menjadi NULL.
 * 
 * 3. Optimasi Kueri Anti-Bentrok & Composite Indexing:
 *    - Indeks komposit `idx_res_facility_date_status` dibentuk dari `(facility_id, reservation_date, status)`.
 *    - Indeks ini sangat krusial untuk mempercepat evaluasi algoritma overlap:
 *      `WHERE facility_id = ? AND reservation_date = ? AND status = 'approved' AND start_time < ? AND end_time > ?`
 *      sehingga pengecekan bentrok jadwal dapat selesai dalam hitungan sub-milidetik bahkan pada jutaan baris data.
 * 
 * ====================================================================================================
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menjalankan migrasi untuk mendirikan tabel 'reservations'.
     * 
     * CARA KERJA:
     * - Schema Builder Laravel membuat tabel baru di basis data dengan nama 'reservations'.
     * - Setiap kolom dikonfigurasi dengan tipe data, batasan ukuran, nilai default, dan indeks.
     * 
     * @return void
     */
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            // Kolom Primary Key (BigIncrements / unsignedBigInteger auto_increment)
            $table->id();

            // Kolom Kode Tiket Unik Publik (Format: TKT-YYYYMMDD-XXXX), contoh: TKT-20240428-009A
            // Diberi indeks unique untuk memastikan tidak ada duplikasi kode tiket di seluruh sistem
            $table->string('ticket_code', 40)->unique();

            // Kolom Foreign Key ke Pengguna Pemohon (Mahasiswa/Dosen)
            // cascadeOnDelete(): Hapus otomatis riwayat peminjaman jika akun pengguna dihapus permanen
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            // Kolom Foreign Key ke Fasilitas Ruangan / Venue yang Dipinjam
            // cascadeOnDelete(): Hapus otomatis permohonan jika data fasilitas dihapus permanen
            $table->foreignId('facility_id')->constrained('facilities')->cascadeOnDelete();

            // Kolom Tanggal Pelaksanaan Kegiatan (Format: YYYY-MM-DD)
            // Diberi indeks tunggal untuk mempercepat pencarian jadwal per tanggal
            $table->date('reservation_date')->index();

            // Kolom Waktu Mulai Peminjaman (Format: HH:MM:00, kelipatan 30 menit dalam rentang 07:00-20:00 WIB)
            $table->time('start_time');

            // Kolom Waktu Selesai Peminjaman (Format: HH:MM:00, kelipatan 30 menit dalam rentang 07:00-20:00 WIB)
            $table->time('end_time');

            // Kolom Jumlah Slot Durasi (1 slot = 30 menit; contoh: 2 jam = 4 slot)
            // unsignedTinyInteger (0 - 255) menghemat alokasi memori fisik database
            $table->unsignedTinyInteger('total_slots');

            // Kolom Deskripsi Tujuan Penggunaan Fasilitas (Nama kegiatan, agenda, nomor kontak PIC)
            $table->text('purpose');

            // Kolom Estimasi Jumlah Partisipan / Peserta yang Hadir (Default: 1 orang)
            $table->unsignedInteger('participants_count')->default(1);

            // Kolom Path Berkas Lampiran Surat Izin Kegiatan / Pengantar Resmi (Nullable, PDF/DOCX)
            $table->string('permit_letter_path', 255)->nullable();

            // Kolom Status Siklus Hidup Reservasi:
            // - 'pending'   : Menunggu peninjauan oleh petugas sarpras (status awal)
            // - 'approved'  : Disetujui petugas sarpras; jadwal mengunci ketersediaan ruangan
            // - 'rejected'  : Ditolak petugas sarpras disertai catatan alasan
            // - 'cancelled' : Dibatalkan secara mandiri oleh pemohon sebelum batas H-1
            // - 'completed' : Kegiatan telah selesai dilaksanakan
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled', 'completed'])->default('pending')->index();

            // Kolom Catatan Alasan Penolakan dari Petugas Sarpras (Nullable)
            $table->text('rejection_reason')->nullable();

            // Kolom Catatan Alasan Pembatalan dari Pemohon Mandiri (Nullable)
            $table->text('cancellation_reason')->nullable();

            // Kolom Foreign Key ke Petugas Sarpras yang Meninjau Tiket
            // nullOnDelete(): Jika akun petugas dihapus, kolom diubah menjadi NULL tanpa merusak tiket reservasi
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();

            // Kolom Waktu Eksekusi Peninjauan / Verifikasi oleh Petugas (Nullable timestamp)
            $table->timestamp('reviewed_at')->nullable();

            // Kolom Waktu Pembuatan (created_at) dan Pembaruan (updated_at)
            $table->timestamps();

            // Indeks Komposit: Mengoptimalkan performa kueri anti-bentrok jadwal & matriks ketersediaan
            // Algoritma: Memfilter fasilitas -> tanggal -> status 'approved' dalam satu lintasan indeks B-Tree
            $table->index(['facility_id', 'reservation_date', 'status'], 'idx_res_facility_date_status');
        });
    }

    /**
     * Membatalkan migrasi (Rollback) dengan menghapus tabel 'reservations'.
     * 
     * @return void
     */
    public function down(): void
    {
        // Menghapus tabel reservations dari skema database jika ada
        Schema::dropIfExists('reservations');
    }
};
