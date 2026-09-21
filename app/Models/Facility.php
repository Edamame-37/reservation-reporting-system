<?php
/**
 * NAMA FILE    : Facility.php
 * FUNGSI       : Model Eloquent entitas Master Fasilitas & Ruang Kampus
 * DESKRIPSI    : Merepresentasikan katalog ruangan, spesifikasi kapasitas, daftar alat terpasang (JSON), dan status operasional.
 * CARA KERJA   : Berkomunikasi dengan tabel facilities, menyediakan helper scope aktif, dan relasi ke reservasi & tiket kerusakan.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Facility extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'facilities';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'code',
        'name',
        'category',
        'building',
        'floor_location',
        'capacity',
        'equipment',
        'description',
        'image_path',
        'status',
    ];

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

    /**
     * FUNCTION/PROCEDURE : reservations()
     * KEGUNAAN           : Menarik seluruh riwayat reservasi yang pernah atau akan berlangsung pada fasilitas ini.
     * CARA KERJA         : Menghubungkan facilities.id ke reservations.facility_id (Relasi One-to-Many).
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'facility_id');
    }

    /**
     * FUNCTION/PROCEDURE : damageReports()
     * KEGUNAAN           : Menarik seluruh tiket pengaduan kerusakan yang tercatat pada fasilitas ini.
     * CARA KERJA         : Menghubungkan facilities.id ke damage_reports.facility_id (Relasi One-to-Many).
     */
    public function damageReports(): HasMany
    {
        return $this->hasMany(DamageReport::class, 'facility_id');
    }

    /**
     * FUNCTION/PROCEDURE : scopeActive()
     * KEGUNAAN           : Memfilter fasilitas yang sedang dalam status operasional normal (bukan dalam perbaikan/nonaktif).
     * CARA KERJA         : Menambahkan kondisi WHERE status = 'aktif' pada kueri Builder Eloquent.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'aktif');
    }
}
