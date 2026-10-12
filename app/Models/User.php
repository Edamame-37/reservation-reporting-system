<?php

/**
 * ==============================================================================================
 * MODEL ELOQUENT ENTITAS PENGGUNA (USER & AUTHENTICATION)
 * ==============================================================================================
 * Berkas: app/Models/User.php
 * 
 * PERAN & FUNGSI UTAMA:
 * 1. Merepresentasikan entitas pengguna tunggal di dalam sistem aplikasi CAVA.
 * 2. Menjadi jembatan utama autentikasi web (Laravel Auth Guard), verifikasi login sandi,
 *    dan pemeliharaan status login pengguna.
 * 3. Mengintegrasikan Role-Based Access Control (RBAC) melalui Spatie Permission (`HasRoles`),
 *    memungkinkan pengecekan izin dinamis ($user->can('...'), $user->hasRole('admin')).
 * 4. Mendukung mekanisme Soft Deletes untuk mencegah penghapusan permanen tidak sengaja dan
 *    menjaga jejak audit (audit trail) pada data historis peminjaman ruangan serta pelaporan kerusakan.
 * 5. Menghubungkan relasi One-to-Many ke permohonan reservasi (`Reservation`) dan tiket
 *    kerusakan sarana prasarana (`DamageReport`), baik sebagai pemohon maupun sebagai petugas pemeriksa.
 * ==============================================================================================
 */

namespace App\Models;

// Mengimpor kelas pendukung Eloquent, Relasi, dan Trait Inti Laravel
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

/**
 * Class User
 * 
 * Mewarisi `Illuminate\Foundation\Auth\User` (Authenticatable) yang mengimplementasikan interface:
 * - Illuminate\Contracts\Auth\Authenticatable (metode getAuthIdentifier, getAuthPassword, dll.)
 * - Illuminate\Contracts\Auth\CanResetPassword (fitur reset kata sandi)
 */
class User extends Authenticatable
{
    // ==========================================================================================
    // TRAIT ELOQUENT & SPATIE EXTENSIONS
    // ==========================================================================================
    // - HasFactory   : Memungkinkan pembuatan dummy data otomatis menggunakan model factory (User::factory()->create()).
    // - Notifiable   : Menyediakan method ->notify() untuk pengiriman email, database notifications, atau SMS.
    // - HasRoles     : Trait Spatie RBAC yang menambahkan relasi polymorphic many-to-many ke roles dan permissions.
    // - SoftDeletes  : Mengaktifkan filter otomatis `WHERE deleted_at IS NULL` dan method `$user->delete()`, `$user->restore()`.
    use HasFactory, Notifiable, HasRoles, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     * 
     * CARA KERJA & ALUR:
     * Melindungi sistem dari kerentanan Mass Assignment Vulnerability (CWE-915).
     * Hanya atribut-atribut yang terdaftar secara eksplisit di dalam array `$fillable` ini yang
     * diizinkan untuk diisi secara massal saat memanggil `User::create($request->all())`
     * atau `$user->update($data)`.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',             // Nama lengkap sivitas akademika / pengguna sistem.
        'email',            // Alamat email unik sebagai identifier akun utama saat login.
        'password',         // Kata sandi terenkripsi (hashed password).
        'identity_number',  // Nomor identitas resmi (NIM untuk Mahasiswa, NIP untuk Dosen/Tendik).
        'role',             // Kolom redundan/fallback string peran pengguna ('admin', 'petugas', 'user').
        'department',       // Nama unit kerja, fakultas, program studi, atau instansi pemohon.
        'phone_number',     // Nomor telepon / WhatsApp aktif untuk keperluan konfirmasi darurat.
        'id_card_path',     // Lokasi path penyimpanan berkas foto kartu identitas (KTM/KTP/Kartu Pegawai).
        'status',           // Status akun pengguna: 'pending' (menunggu verifikasi), 'active', 'inactive', 'rejected'.
        'rejection_reason', // Catatan alasan dari admin/petugas jika permohonan pendaftaran akun pengguna ditolak.
        'assignment_zone',  // Zona/gedung penugasan khusus untuk petugas pemeriksa (misal: 'Gedung A', 'Laboratorium').
    ];

    /**
     * The attributes that should be hidden for serialization.
     * 
     * CARA KERJA & ALUR:
     * Menyembunyikan atribut sensitif dari hasil serialisasi JSON atau konversi ke array
     * (misal: `$user->toArray()` atau saat model dikembalikan sebagai HTTP JSON response).
     * Hal ini mencegah bocornya hash password atau remember_token ke frontend/browser.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     * 
     * CARA KERJA & ALUR:
     * Fitur Type-Casting bawaan Laravel 11 berbasis method `casts(): array`.
     * - 'email_verified_at' => 'datetime' : Otomatis mengubah string datetime SQL menjadi objek Carbon PHP.
     * - 'password' => 'hashed'            : Fitur otomatisasi keamanan di mana setiap nilai plaintext baru
     *                                       yang di-set ke `$user->password` akan otomatis di-hash menggunakan
     *                                       algoritma Bcrypt/Argon2 tanpa perlu memanggil `Hash::make()` manual.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * ==========================================================================================
     * RELASI ELOQUENT: PEMOHON RESERVASI RUANGAN
     * ==========================================================================================
     * 
     * KEGUNAAN   : Menarik seluruh riwayat permohonan reservasi ruangan yang diajukan oleh pengguna ini.
     * CARA KERJA : Relasi One-to-Many (`hasMany`) yang mencocokkan `users.id` ke `reservations.user_id`.
     * CONTOH     : `$user->reservations()->where('status', 'approved')->get();`
     * 
     * @return HasMany
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'user_id');
    }

    /**
     * ==========================================================================================
     * RELASI ELOQUENT: PEMBUAT TIKET LAPORAN KERUSAKAN
     * ==========================================================================================
     * 
     * KEGUNAAN   : Menarik seluruh riwayat tiket pelaporan kerusakan sarana yang dilaporkan pengguna ini.
     * CARA KERJA : Relasi One-to-Many (`hasMany`) yang mencocokkan `users.id` ke `damage_reports.user_id`.
     * CONTOH     : `$user->damageReports()->latest()->get();`
     * 
     * @return HasMany
     */
    public function damageReports(): HasMany
    {
        return $this->hasMany(DamageReport::class, 'user_id');
    }

    /**
     * ==========================================================================================
     * RELASI ELOQUENT: PENINJAU / VERIFIKATOR RESERVASI (ROLE: PETUGAS / ADMIN)
     * ==========================================================================================
     * 
     * KEGUNAAN   : Menarik daftar reservasi yang ditinjau, disetujui, atau ditolak oleh petugas ini.
     * CARA KERJA : Relasi One-to-Many (`hasMany`) yang mencocokkan `users.id` ke `reservations.reviewed_by`.
     * CONTOH     : `$petugas->reviewedReservations()->count();`
     * 
     * @return HasMany
     */
    public function reviewedReservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'reviewed_by');
    }

    /**
     * ==========================================================================================
     * RELASI ELOQUENT: PENANGAN TIKET KERUSAKAN / TEKNISI (ROLE: PETUGAS / TEKNISI)
     * ==========================================================================================
     * 
     * KEGUNAAN   : Menarik daftar tiket kerusakan fasilitas yang ditangani atau diperbaiki oleh petugas ini.
     * CARA KERJA : Relasi One-to-Many (`hasMany`) yang mencocokkan `users.id` ke `damage_reports.handled_by`.
     * CONTOH     : `$teknisi->handledReports()->where('status', 'in_progress')->get();`
     * 
     * @return HasMany
     */
    public function handledReports(): HasMany
    {
        return $this->hasMany(DamageReport::class, 'handled_by');
    }
}

