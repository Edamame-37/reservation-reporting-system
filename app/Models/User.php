<?php

/**
 * ==============================================================================================
 * SISTEM INFORMASI MANAJEMEN FASILITAS & SARANA PRASARANA KAMPUS (CAVA)
 * ==============================================================================================
 * Berkas          : app/Models/User.php
 * Entitas         : User (Pengguna, Sivitas Akademika, Petugas, & Administrator)
 * Layer Arsitektur: Data Access Layer / Eloquent Authenticatable Model (ORM & Security)
 * ==============================================================================================
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

// ==============================================================================================
// 1. DEKLARASI NAMESPACE (PHP NATIVE)
// ==============================================================================================
// SYNTAX     : namespace App\Models;
// UNTUK APA  : Mengelompokkan class ini ke dalam hierarki namespace virtual 'App\Models' sesuai
//              standar autoloading PSR-4 Composer.
// CARA KERJA : Mencegah benturan nama (name collision) antar class yang berbeda. Composer memetakan
//              namespace 'App\' ke direktori fisik 'app/', sehingga class ini dapat dipanggil
//              di mana pun dengan Fully Qualified Class Name (FQCN) `\App\Models\User`.
namespace App\Models;

// ==============================================================================================
// 2. IMPORT NAMESPACE & DEPENDENCIES (PHP NATIVE 'use')
// ==============================================================================================
// SYNTAX     : use <Vendor>\<Package>\<ClassName>;
// UNTUK APA  : Mengimpor class, interface, trait, atau authentication contracts ke dalam berkas ini
//              agar dapat diakses langsung menggunakan nama pendek tanpa menulis FQCN panjang.
// CARA KERJA : Diuraikan oleh PHP engine saat kompilasi berkas (compile-time aliasing).
use Illuminate\Database\Eloquent\Factories\HasFactory;      // Trait untuk integrasi Database Seeder & Factory
use Illuminate\Database\Eloquent\Relations\HasMany;         // Tipe kembalian relasi One-to-Many Eloquent
use Illuminate\Database\Eloquent\SoftDeletes;               // Trait soft delete bawaan Laravel
use Illuminate\Foundation\Auth\User as Authenticatable;     // Base Class Authenticatable User bawaan Laravel Breeze
use Illuminate\Notifications\Notifiable;                   // Trait notifikasi (email, database, SMS)
use Spatie\Permission\Traits\HasRoles;                     // Trait Role-Based Access Control dari Spatie Permission

// ==============================================================================================
// 3. DEKLARASI CLASS & INHERITANCE (PHP NATIVE 'class ... extends ...')
// ==============================================================================================
// SYNTAX     : class User extends Authenticatable
// UNTUK APA  : Mendefinisikan class entitas `User` yang mewarisi class `Illuminate\Foundation\Auth\User`
//              (Authenticatable).
// CARA KERJA : Mewarisi implementasi interface `Illuminate\Contracts\Auth\Authenticatable`
//              (seperti `getAuthIdentifier()`, `getAuthPassword()`) dan `CanResetPassword`
//              sehingga class ini dapat langsung dikenali oleh sistem session dan guard Laravel.
class User extends Authenticatable
{
    // ==========================================================================================
    // 4. TRAIT COMPOSITION (PHP NATIVE 'use TraitName;')
    // ==========================================================================================
    // SYNTAX     : use HasFactory, Notifiable, HasRoles, SoftDeletes;
    // UNTUK APA  : Menyuntikkan fungsionalitas tambahan ke dalam class tanpa inheritance bertingkat.
    // CARA KERJA :
    // - HasFactory   : Menyediakan method `User::factory()` untuk pembuatan data uji otomatis.
    // - Notifiable   : Menyediakan method `$user->notify($instance)` untuk pengiriman notifikasi.
    // - HasRoles     : Menyediakan method RBAC seperti `$user->assignRole()`, `$user->hasRole()`,
    //                  dan `$user->can()` via relasi polymorphic Spatie.
    // - SoftDeletes  : Mengintersepsi method `delete()` menjadi pengisian kolom `deleted_at`.
    use HasFactory, Notifiable, HasRoles, SoftDeletes;

    // ==========================================================================================
    // 5. PROTEKSI MASS ASSIGNMENT (LARAVEL ELOQUENT SECURITY PROPERTY)
    // ==========================================================================================
    // SYNTAX     : protected $fillable = [ ... ];
    // UNTUK APA  : Menentukan daftar putih (whitelist) kolom tabel yang diizinkan untuk diisi
    //              secara serentak dari input pengguna (`User::create($request->validated())`).
    // CARA KERJA : Melindungi sistem dari eksploitasi Mass Assignment Vulnerability (CWE-915).
    //              Eloquent secara otomatis mengabaikan kolom apa pun di luar daftar ini saat
    //              proses pengisian massal.
    /**
     * The attributes that are mass assignable.
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

    // ==========================================================================================
    // 6. PENYEMBUNYIAN ATRIBUT DARI SERIALISASI (LARAVEL ELOQUENT HIDDEN PROPERTY)
    // ==========================================================================================
    // SYNTAX     : protected $hidden = [ ... ];
    // UNTUK APA  : Menyembunyikan atribut sensitif dari hasil konversi array/JSON (`$user->toArray()`).
    // CARA KERJA : Mencegah kebocoran data keamanan (seperti hash password dan token 'remember me')
    //              ke klien browser atau respon API RESTful.
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // ==========================================================================================
    // 7. TYPE CASTING ATRIBUT MODEL (LARAVEL 11 NATIVE METHOD 'casts(): array')
    // ==========================================================================================
    // SYNTAX     : protected function casts(): array { return [ ... ]; }
    // UNTUK APA  : Mengonversi format data SQL ke tipe data objek PHP secara otomatis saat dibaca/disimpan.
    // CARA KERJA :
    // - 'email_verified_at' => 'datetime' : Mengonversi string waktu SQL menjadi objek `Illuminate\Support\Carbon`.
    // - 'password' => 'hashed'            : Mengamankan kata sandi baru secara otomatis menggunakan
    //                                       algoritma hashing default (Bcrypt/Argon2) saat diisi ke atribut.
    /**
     * Get the attributes that should be cast.
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

    // ==========================================================================================
    // 8. RELASI ELOQUENT ONE-TO-MANY: PERMOHONAN RESERVASI OLEH PEMOHON
    // ==========================================================================================
    // SYNTAX     : public function reservations(): HasMany
    // UNTUK APA  : Menghubungkan akun pengguna ini dengan seluruh tiket reservasi yang pernah diajukannya.
    // CARA KERJA : Relasi One-to-Many (`hasMany`) yang menghubungkan `users.id` ke `reservations.user_id`.
    //              Mengembalikan instance `HasMany` untuk query chaining atau koleksi model.
    /**
     * Mendapatkan seluruh riwayat permohonan reservasi yang diajukan oleh pengguna ini.
     * 
     * @return HasMany
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'user_id');
    }

    // ==========================================================================================
    // 9. RELASI ELOQUENT ONE-TO-MANY: LAPORAN KERUSAKAN OLEH PELAPOR
    // ==========================================================================================
    // SYNTAX     : public function damageReports(): HasMany
    // UNTUK APA  : Menghubungkan akun pengguna dengan seluruh laporan kerusakan sarana yang dibuatnya.
    // CARA KERJA : Relasi One-to-Many (`hasMany`) yang menghubungkan `users.id` ke `damage_reports.user_id`.
    /**
     * Mendapatkan seluruh tiket pelaporan kerusakan fasilitas yang dilaporkan oleh pengguna ini.
     * 
     * @return HasMany
     */
    public function damageReports(): HasMany
    {
        return $this->hasMany(DamageReport::class, 'user_id');
    }

    // ==========================================================================================
    // 10. RELASI ELOQUENT ONE-TO-MANY: TINJAUAN RESERVASI OLEH PETUGAS/ADMIN
    // ==========================================================================================
    // SYNTAX     : public function reviewedReservations(): HasMany
    // UNTUK APA  : Menghubungkan akun petugas/admin dengan seluruh reservasi yang diverifikasi olehnya.
    // CARA KERJA : Relasi One-to-Many (`hasMany`) yang menghubungkan `users.id` ke `reservations.reviewed_by`.
    /**
     * Mendapatkan seluruh daftar reservasi yang ditinjau/diverifikasi oleh petugas ini.
     * 
     * @return HasMany
     */
    public function reviewedReservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'reviewed_by');
    }

    // ==========================================================================================
    // 11. RELASI ELOQUENT ONE-TO-MANY: PENANGANAN TIKET KERUSAKAN OLEH PETUGAS/TEKNISI
    // ==========================================================================================
    // SYNTAX     : public function handledReports(): HasMany
    // UNTUK APA  : Menghubungkan akun teknisi/petugas dengan tiket kerusakan yang ditanganinya.
    // CARA KERJA : Relasi One-to-Many (`hasMany`) yang menghubungkan `users.id` ke `damage_reports.handled_by`.
    /**
     * Mendapatkan seluruh tiket kerusakan fasilitas yang ditangani/diperbaiki oleh petugas ini.
     * 
     * @return HasMany
     */
    public function handledReports(): HasMany
    {
        return $this->hasMany(DamageReport::class, 'handled_by');
    }
}
