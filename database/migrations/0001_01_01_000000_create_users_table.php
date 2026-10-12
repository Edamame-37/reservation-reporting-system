<?php
/**
 * NAMA FILE    : 0001_01_01_000000_create_users_table.php
 * LOKASI       : database/migrations/0001_01_01_000000_create_users_table.php
 * FUNGSI       : Migrasi Pembuatan Skema Tabel Dasar Pengguna, Token Reset Sandi, dan Sesi Database
 * DESKRIPSI    : Mendefinisikan tabel fisik 'users', 'password_reset_tokens', dan 'sessions' pada basis data MySQL.
 *                Menyimpan identitas sivitas akademika (Mahasiswa, Dosen, Staf, Petugas Sarpras, dan Admin),
 *                status verifikasi persetujuan akun mandiri, serta atribut kepegawaian/kemahasiswaan.
 * CARA KERJA   : 1. Method up() dieksekusi saat perintah `php artisan migrate` dijalankan.
 *                2. Schema::create('users', ...) membangun tabel users beserta constraint unik dan indeks.
 *                3. Schema::create('password_reset_tokens', ...) membuat tabel token reset sandi.
 *                4. Schema::create('sessions', ...) membuat tabel penampung sesi login aktif.
 *                5. Method down() dipanggil saat `php artisan migrate:rollback` untuk menghapus tabel (drop).
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// =========================================================================================
// DEKLARASI KELAS MIGRASI ANONIM (ANONYMOUS MIGRATION CLASS)
// =========================================================================================
// SYNTAX     : return new class extends Migration { ... };
// CARA KERJA : Fitur anonymous migration bawaan Laravel modern untuk menghindari tabrakan
//              nama kelas (class name collision) jika terdapat migrasi dengan nama kelas serupa.
return new class extends Migration
{
    /**
     * Run the migrations.
     * FUNGSI : Membangun skema tabel pada basis data saat proses migrasi berjalan.
     */
    public function up(): void
    {
        // ---------------------------------------------------------------------------------
        // 1. TABEL 'users' (PENGGUNA SISTEM CAVA)
        // ---------------------------------------------------------------------------------
        Schema::create('users', function (Blueprint $table) {
            // $table->id(): Kolom Primary Key BIGINT UNSIGNED AUTO_INCREMENT bernama 'id'.
            $table->id();

            // $table->string('name', 150): VARCHAR(150) menyimpan nama lengkap pengguna.
            $table->string('name', 150);

            // $table->string('email', 150)->unique(): VARCHAR(150) dengan UNIQUE index untuk kredensial login.
            $table->string('email', 150)->unique();

            // $table->timestamp('email_verified_at')->nullable(): Waktu verifikasi email (NULL jika belum).
            $table->timestamp('email_verified_at')->nullable();

            // $table->string('password'): VARCHAR(255) menyimpan hash kata sandi aman (Bcrypt / Argon2id).
            $table->string('password');

            // $table->string('identity_number', 50)->nullable()->index(): Nomor Induk Mahasiswa (NIM)
            // atau Nomor Induk Pegawai (NIP). Ditambahkan index() agar pencarian data sivitas berkecepatan tinggi.
            $table->string('identity_number', 50)->nullable()->index();

            // $table->enum('role', [...])->default('mahasiswa'): Klasifikasi peran dasar pada skema awal.
            // Catatan: Pada implementasi hak akses lanjut, peran resmi disinkronkan oleh Spatie RBAC.
            $table->enum('role', ['admin', 'petugas', 'mahasiswa', 'dosen', 'staf'])->default('mahasiswa');

            // $table->string('department', 100)->nullable(): Unit kerja, Program Studi, atau Fakultas pemohon.
            $table->string('department', 100)->nullable();

            // $table->string('phone_number', 25)->nullable(): Nomor kontak aktif WhatsApp untuk konfirmasi darurat sarpras.
            $table->string('phone_number', 25)->nullable();

            // $table->string('id_card_path', 255)->nullable(): Path penyimpanan berkas foto KTM/KTP/SK di disk storage publik.
            $table->string('id_card_path', 255)->nullable();

            // $table->enum('status', [...])->default('pending'): Siklus verifikasi pendaftaran mandiri sivitas.
            // Mahasiswa baru mendaftar mandiri berstatus 'pending' hingga disetujui Admin menjadi 'active'.
            $table->enum('status', ['pending', 'active', 'rejected'])->default('pending');

            // $table->text('rejection_reason')->nullable(): Catatan alasan penolakan pendaftaran jika status bernilai 'rejected'.
            $table->text('rejection_reason')->nullable();

            // $table->string('assignment_zone', 100)->nullable(): Wilayah operasional bagi akun Petugas (misal: 'Zona 1 Gedung Kuliah').
            $table->string('assignment_zone', 100)->nullable();

            // $table->rememberToken(): Kolom VARCHAR(100) nullable 'remember_token' untuk fitur sesi "Remember Me".
            $table->rememberToken();

            // $table->timestamps(): Menghasilkan kolom TIMESTAMP 'created_at' dan 'updated_at' otomatis.
            $table->timestamps();
        });

        // ---------------------------------------------------------------------------------
        // 2. TABEL 'password_reset_tokens' (TOKEN LUPA KATA SANDI)
        // ---------------------------------------------------------------------------------
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            // Email sebagai Primary Key mencegah satu email memiliki multi-token aktif yang bertabrakan.
            $table->string('email')->primary();

            // Token acak terenkripsi yang dikirimkan melalui tautan email reset sandi.
            $table->string('token');

            // Waktu pembuatan token (digunakan untuk memvalidasi batas kedaluwarsa token, default 60 menit).
            $table->timestamp('created_at')->nullable();
        });

        // ---------------------------------------------------------------------------------
        // 3. TABEL 'sessions' (PENYIMPANAN SESI BASIS DATA)
        // ---------------------------------------------------------------------------------
        Schema::create('sessions', function (Blueprint $table) {
            // ID Sesi string acak sebagai Primary Key.
            $table->string('id')->primary();

            // foreignId('user_id'): Menautkan sesi ke pengguna yang sedang login (NULL jika tamu/guest).
            $table->foreignId('user_id')->nullable()->index();

            // Alamat IP klien (IPv4 atau IPv6 hingga 45 karakter).
            $table->string('ip_address', 45)->nullable();

            // Informasi User Agent browser klien.
            $table->text('user_agent')->nullable();

            // Payload data sesi serial/base64 yang disimpan aplikasi.
            $table->longText('payload');

            // Timestamp integer aktivitas terakhir pengguna (digunakan untuk garbage collection sesi kedaluwarsa).
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     * FUNGSI : Membatalkan migrasi dan menghapus tabel-tabel terkait saat rollback.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
