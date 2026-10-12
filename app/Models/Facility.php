<?php

/**
 * ==============================================================================================
 * MODEL ELOQUENT ENTITAS FASILITAS & RUANGAN KAMPUS (FACILITY ENTITY)
 * ==============================================================================================
 * Berkas: app/Models/Facility.php
 * 
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

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Facility extends Model
{
    // ==========================================================================================
    // TRAIT ELOQUENT
    // ==========================================================================================
    // - HasFactory  : Memungkinkan pengisian data uji/dummy via Facility::factory()->create().
    // - SoftDeletes : Mengaktifkan penghapusan lunak (deleted_at). Fasilitas yang dihapus admin
    //                 tidak akan terhapus fisik dari database, sehingga riwayat reservasi masa lalu
    //                 tetap utuh dan valid.
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     * 
     * CARA KERJA & ALUR:
     * Menegaskan pemetaan nama tabel di basis data secara eksplisit ('facilities').
     *
     * @var string
     */
    protected $table = 'facilities';

    /**
     * The attributes that are mass assignable.
     * 
     * CARA KERJA & ALUR:
     * Mencegah kerentanan Mass Assignment Vulnerability (CWE-915).
     * Hanya daftar kolom berikut yang diizinkan untuk diisi massal melalui
     * `Facility::create($request->validated())` atau `$facility->update($request->validated())`.
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

    /**
     * Get the attributes that should be cast.
     * 
     * CARA KERJA & ALUR:
     * - 'equipment' => 'array'   : Melakukan serialisasi dua arah otomatis. Saat membaca dari SQL JSON,
     *                              Laravel mengonversinya menjadi PHP Array. Saat menyimpan, Laravel
     *                              mengonversi PHP Array menjadi JSON string secara transparan.
     * - 'capacity'  => 'integer' : Menjamin nilai kapasitas selalu bertipe data integer PHP primitif.
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

    /**
     * ==========================================================================================
     * RELASI ELOQUENT: RIWAYAT & JADWAL RESERVASI RUANGAN
     * ==========================================================================================
     * 
     * KEGUNAAN   : Menarik seluruh riwayat dan agenda reservasi yang berlangsung pada ruangan ini.
     * CARA KERJA : Relasi One-to-Many (`hasMany`) yang menghubungkan `facilities.id` ke `reservations.facility_id`.
     * CONTOH     : `$facility->reservations()->where('reservation_date', $today)->get();`
     * 
     * @return HasMany
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'facility_id');
    }

    /**
     * ==========================================================================================
     * RELASI ELOQUENT: TIKET PELAPORAN KERUSAKAN FASILITAS
     * ==========================================================================================
     * 
     * KEGUNAAN   : Menarik seluruh tiket komplain atau laporan insiden kerusakan sarana di ruangan ini.
     * CARA KERJA : Relasi One-to-Many (`hasMany`) yang menghubungkan `facilities.id` ke `damage_reports.facility_id`.
     * CONTOH     : `$facility->damageReports()->where('status', 'pending')->count();`
     * 
     * @return HasMany
     */
    public function damageReports(): HasMany
    {
        return $this->hasMany(DamageReport::class, 'facility_id');
    }

    /**
     * ==========================================================================================
     * LOCAL QUERY SCOPE: FILTER RUANGAN AKTIF OPERASIONAL
     * ==========================================================================================
     * 
     * KEGUNAAN   : Memfilter hanya fasilitas yang berstatus 'aktif' (siap digunakan/dipesan publik).
     * CARA KERJA : Menambahkan klausul SQL `WHERE facilities.status = 'aktif'` pada query builder.
     * CARA PAKAI : `Facility::active()->orderBy('name')->get();`
     * 
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'aktif');
    }
}

