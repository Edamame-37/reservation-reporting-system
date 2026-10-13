<?php

/**
 * ==============================================================================================
 * SISTEM INFORMASI MANAJEMEN FASILITAS & SARANA PRASARANA KAMPUS (CAVA)
 * ==============================================================================================
 * Berkas          : app/Models/Facility.php
 * Entitas         : Facility (Fasilitas & Ruangan Kampus)
 * Layer Arsitektur: Data Access Layer / Eloquent Model (ORM)
 * ==============================================================================================
 * PERAN & FUNGSI UTAMA:
 * 1. Merepresentasikan entitas sarana prasarana fisik kampus (Auditorium, Laboratorium Komputer,
 *    Ruang Kelas Teori, Ruang Rapat Senat, Arena Olahraga).
 * 2. Mengelola konversi otomatis tipe data (Type Casting) kolom native JSON basis data
 *    menjadi array PHP murni untuk inventaris peralatan terpasang (`equipment`).
 * 3. Menyediakan relasi Eloquent One-to-Many ke permohonan reservasi (`Reservation`) dan tiket
 *    kerusakan sarana (`DamageReport`).
 * 4. Menyediakan Local Query Scope (`scopeActive`) untuk mempermudah penyaringan fasilitas
 *    yang siap pakai pada modul peminjaman publik dan formulir reservasi.
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
//              di mana pun dengan Fully Qualified Class Name (FQCN) `\App\Models\Facility`.
namespace App\Models;

// ==============================================================================================
// 2. IMPORT NAMESPACE & DEPENDENCIES (PHP NATIVE 'use')
// ==============================================================================================
// SYNTAX     : use <Vendor>\<Package>\<ClassName>;
// UNTUK APA  : Mengimpor class, interface, trait, atau query builder ke dalam file ini agar dapat
//              dipanggil langsung menggunakan nama pendek (short name) tanpa menulis FQCN panjang.
// CARA KERJA : Diuraikan oleh PHP engine saat kompilasi file (compile-time aliasing).
use Illuminate\Database\Eloquent\Builder;              // Builder kueri Eloquent untuk manipulasi kueri SQL
use Illuminate\Database\Eloquent\Factories\HasFactory;  // Trait untuk integrasi Database Seeder & Factory
use Illuminate\Database\Eloquent\Model;               // Base Class Eloquent ORM dari Laravel
use Illuminate\Database\Eloquent\Relations\HasMany;   // Tipe kembalian relasi One-to-Many Eloquent
use Illuminate\Database\Eloquent\SoftDeletes;         // Trait soft delete bawaan Laravel

// ==============================================================================================
// 3. DEKLARASI CLASS & INHERITANCE (PHP NATIVE 'class ... extends ...')
// ==============================================================================================
// SYNTAX     : class Facility extends Model
// UNTUK APA  : Mendefinisikan class cetak biru `Facility` yang mewarisi seluruh fungsionalitas
//              Active Record dari class induk `Illuminate\Database\Eloquent\Model`.
// CARA KERJA : Setiap instance `Facility` merepresentasikan 1 baris rekaman pada tabel database.
//              Seluruh method bawaan ORM seperti `save()`, `delete()`, `where()`, `find()`,
//              dan event hooks diwarisi secara otomatis.
class Facility extends Model
{
    // ==========================================================================================
    // 4. TRAIT COMPOSITION (PHP NATIVE 'use TraitName;')
    // ==========================================================================================
    // SYNTAX     : use HasFactory, SoftDeletes;
    // UNTUK APA  : Menyuntikkan metode dan properti dari trait ke dalam class tanpa multiple inheritance.
    // CARA KERJA :
    // - HasFactory  : Menyediakan method statis `Facility::factory()` untuk pembuatan data dummy/testing.
    // - SoftDeletes : Mengintersepsi method `delete()` agar tidak mengeksekusi SQL `DELETE FROM`,
    //                 melainkan `UPDATE facilities SET deleted_at = NOW() WHERE id = ...`.
    //                 Kueri `SELECT` berikutnya otomatis ditambahkan klausul `WHERE deleted_at IS NULL`.
    use HasFactory, SoftDeletes;

    // ==========================================================================================
    // 5. PENETAPAN NAMA TABEL BASIS DATA (LARAVEL ELOQUENT CONVENTION OVERRIDE)
    // ==========================================================================================
    // SYNTAX     : protected $table = 'facilities';
    // UNTUK APA  : Menegaskan nama tabel SQL secara eksplisit di database MySQL.
    // CARA KERJA : Modifiers 'protected' membatasi akses properti hanya untuk class ini dan turunannya.
    //              Eloquent secara default menggunakan penamaan jamak (plural) snake_case ('facilities').
    //              Mendeklarasikannya secara eksplisit mencegah kesalahan resolusi nama tabel.
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'facilities';

    // ==========================================================================================
    // 6. PROTEKSI MASS ASSIGNMENT (LARAVEL ELOQUENT SECURITY PROPERTY)
    // ==========================================================================================
    // SYNTAX     : protected $fillable = [ ... ];
    // UNTUK APA  : Whitelist (daftar putih) nama kolom database yang diizinkan untuk diisi secara massal
    //              melalui metode `Facility::create($data)` atau `$facility->update($data)`.
    // CARA KERJA : Mencegah kerentanan Mass Assignment Vulnerability (CWE-915). Jika pengguna jahat
    //              mengirimkan payload HTTP seperti `['id' => 99, 'deleted_at' => null]`,
    //              Eloquent akan mengabaikan kolom tersebut karena tidak ada dalam daftar $fillable.
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'code',           // Kode unik ruangan (contoh: AUD-H01, LAB-C204).
        'name',           // Nama lengkap ruangan/venue.
        'category',       // Kategori venue: 'auditorium', 'lab', 'kelas', 'olahraga', 'rapat'.
        'building',       // Nama gedung/lokasi komplek (contoh: 'Gedung Rektorat').
        'floor_location', // Lantai/sayap gedung (contoh: 'Lantai 2 Sayap Barat').
        'capacity',       // Kapasitas daya tampung maksimum (orang/kursi).
        'equipment',      // Array perlengkapan pendukung terpasang (disimpan sebagai JSON).
        'description',    // Deskripsi spesifikasi dan aturan penggunaan ruangan.
        'image_path',     // Path berkas gambar sampul ruangan di storage.
        'status',         // Status operasional: 'aktif', 'dalam perbaikan', 'nonaktif'.
    ];

    // ==========================================================================================
    // 7. TYPE CASTING ATRIBUT MODEL (LARAVEL 11 NATIVE METHOD 'casts(): array')
    // ==========================================================================================
    // SYNTAX     : protected function casts(): array { return [ ... ]; }
    // UNTUK APA  : Mengonversi format kolom data SQL ke tipe data bawaan PHP secara otomatis saat
    //              dibaca (getter) dan disimpan (setter).
    // CARA KERJA :
    // - 'equipment' => 'array'   : Kolom JSON di MySQL otomatis di-unserialize menjadi native array PHP
    //                              saat diakses `$facility->equipment`, dan di-encode ke JSON saat disimpan.
    // - 'capacity'  => 'integer' : Menjamin nilai kapasitas selalu bertipe data int primitif PHP.
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'equipment' => 'array',
            'capacity'  => 'integer',
        ];
    }

    // ==========================================================================================
    // 8. DEFINISI RELASI ELOQUENT ONE-TO-MANY (LARAVEL RELATIONSHIP & PHP 8 RETURN TYPE)
    // ==========================================================================================
    // SYNTAX     : public function reservations(): HasMany
    // UNTUK APA  : Mendefinisikan relasi One-to-Many antara 1 fasilitas dengan banyak tiket permohonan
    //              reservasi jadwal penggunaan ruangan.
    // CARA KERJA :
    // - Return type `: HasMany` menjamin integritas tipe data kembalian (PHP 8 Strict Typing).
    // - `$this->hasMany(Reservation::class, 'facility_id')`: Menghubungkan primary key `facilities.id`
    //   dengan foreign key `reservations.facility_id`.
    // - Jika dipanggil sebagai metode `$facility->reservations()`, mengembalikan instance Query Builder
    //   untuk chaining query lanjutan. Jika dipanggil sebagai properti dinamis `$facility->reservations`,
    //   mengembalikan objek `Illuminate\Database\Eloquent\Collection` hasil eksekusi kueri.
    /**
     * Mendapatkan seluruh data reservasi yang terhubung dengan fasilitas ini.
     * 
     * @return HasMany
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'facility_id');
    }

    // ==========================================================================================
    // 9. DEFINISI RELASI ELOQUENT ONE-TO-MANY KE LAPORAN KERUSAKAN
    // ==========================================================================================
    // SYNTAX     : public function damageReports(): HasMany
    // UNTUK APA  : Mendefinisikan relasi One-to-Many antara 1 fasilitas dengan tiket komplain/insiden
    //              kerusakan sarana prasarana yang diajukan oleh sivitas.
    // CARA KERJA :
    // - `$this->hasMany(DamageReport::class, 'facility_id')`: Menghubungkan primary key `facilities.id`
    //   dengan foreign key `damage_reports.facility_id`.
    // - Memungkinkan eager loading via `Facility::with('damageReports')->get()` untuk menghindari
    //   masalah N+1 query problem.
    /**
     * Mendapatkan seluruh tiket pelaporan kerusakan yang terkait dengan fasilitas ini.
     * 
     * @return HasMany
     */
    public function damageReports(): HasMany
    {
        return $this->hasMany(DamageReport::class, 'facility_id');
    }

    // ==========================================================================================
    // 10. LOCAL QUERY SCOPE (LARAVEL ELOQUENT SCOPE METHOD)
    // ==========================================================================================
    // SYNTAX     : public function scopeActive(Builder $query): Builder
    // UNTUK APA  : Membungkus klausul kueri berulang (reusable query filter) untuk menyaring hanya
    //              fasilitas yang memiliki status operasional 'aktif'.
    // CARA KERJA :
    // - Laravel Eloquent mendeteksi konvensi penamaan prefix `scope[NamaScope]`.
    // - Saat developer memanggil `Facility::active()->get()`, Eloquent secara otomatis memotong prefix
    //   'scope' dan mengoper kueri builder saat ini ke parameter `$query`.
    // - Method menambahkan kondisi SQL: `WHERE status = 'aktif'`.
    // - Mengembalikan instance `Builder` agar pemanggilan method dapat disambung (fluent chaining).
    /**
     * Scope query untuk hanya menyaring fasilitas yang berstatus operasional aktif.
     * 
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'aktif');
    }
}
