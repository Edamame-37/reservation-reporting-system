<?php
/**
 * NAMA FILE    : DamageReport.php
 * FUNGSI       : Model Eloquent entitas Tiket Pengaduan Kerusakan Fasilitas
 * DESKRIPSI    : Merepresentasikan laporan malfungsi sarana kampus, lampiran foto (maks 2MB), pelacakan status penanganan, dan catatan resolusi.
 * CARA KERJA   : Berkomunikasi dengan tabel damage_reports, menghubungkan relasi ke User (pelapor & teknisi) dan Facility.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DamageReport extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'damage_reports';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'report_code',
        'user_id',
        'facility_id',
        'category',
        'description',
        'attachment_photo',
        'status',
        'is_facility_locked',
        'resolution_note',
        'handled_by',
        'resolved_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_facility_locked' => 'boolean',
            'resolved_at'        => 'datetime',
        ];
    }

    /**
     * FUNCTION/PROCEDURE : user()
     * KEGUNAAN           : Menarik profil akun pengguna yang melaporkan kerusakan fasilitas ini.
     * CARA KERJA         : Menghubungkan damage_reports.user_id ke users.id (Relasi Invers Belongs-To).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * FUNCTION/PROCEDURE : facility()
     * KEGUNAAN           : Menarik data fasilitas kampus yang dilaporkan mengalami kendala/rusak.
     * CARA KERJA         : Menghubungkan damage_reports.facility_id ke facilities.id (Relasi Invers Belongs-To).
     */
    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class, 'facility_id');
    }

    /**
     * FUNCTION/PROCEDURE : handler()
     * KEGUNAAN           : Menarik data identitas Petugas Sarpras atau Teknisi yang bertugas menangani tiket perbaikan ini.
     * CARA KERJA         : Menghubungkan damage_reports.handled_by ke users.id (Relasi Invers Belongs-To Petugas).
     */
    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    /**
     * FUNCTION/PROCEDURE : handledBy()
     * KEGUNAAN           : Alias relasi handler() untuk teknisi atau petugas yang menangani tiket.
     * CARA KERJA         : Meneruskan ke relasi handler().
     */
    public function handledBy(): BelongsTo
    {
        return $this->handler();
    }
}
