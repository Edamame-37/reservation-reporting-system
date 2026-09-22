<?php
/**
 * NAMA FILE    : Reservation.php
 * FUNGSI       : Model Eloquent entitas Transaksi Permohonan Reservasi Ruang Kampus
 * DESKRIPSI    : Merepresentasikan tiket reservasi, slot waktu 30 menit (07:00-20:00 WIB), status approval, dan log pembatalan.
 * CARA KERJA   : Berkomunikasi dengan tabel reservations, menghubungkan relasi ke User (pemohon & reviewer) dan Facility.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'reservations';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'ticket_code',
        'user_id',
        'facility_id',
        'reservation_date',
        'start_time',
        'end_time',
        'total_slots',
        'purpose',
        'participants_count',
        'permit_letter_path',
        'status',
        'rejection_reason',
        'cancellation_reason',
        'reviewed_by',
        'reviewed_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'reservation_date'   => 'date',
            'reviewed_at'        => 'datetime',
            'total_slots'        => 'integer',
            'participants_count' => 'integer',
        ];
    }

    /**
     * FUNCTION/PROCEDURE : user()
     * KEGUNAAN           : Menarik data profil sivitas (Mahasiswa/Dosen/Staf) yang mengajukan permohonan reservasi ini.
     * CARA KERJA         : Menghubungkan reservations.user_id ke users.id (Relasi Invers Belongs-To).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * FUNCTION/PROCEDURE : facility()
     * KEGUNAAN           : Menarik data detail fasilitas kampus yang dipesan pada tiket reservasi ini.
     * CARA KERJA         : Menghubungkan reservations.facility_id ke facilities.id (Relasi Invers Belongs-To).
     */
    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class, 'facility_id');
    }

    /**
     * FUNCTION/PROCEDURE : reviewer()
     * KEGUNAAN           : Menarik data identitas Petugas Sarpras yang mengeksekusi verifikasi persetujuan/penolakan/pembatalan.
     * CARA KERJA         : Menghubungkan reservations.reviewed_by ke users.id (Relasi Invers Belongs-To Petugas).
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * FUNCTION/PROCEDURE : scopeApproved()
     * KEGUNAAN           : Memfilter kueri khusus reservasi yang telah disetujui resmi oleh petugas.
     * CARA KERJA         : Menambahkan klausul WHERE status = 'approved' pada query builder.
     */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'approved');
    }

    /**
     * FUNCTION/PROCEDURE : scopeOnDate()
     * KEGUNAAN           : Memfilter reservasi pada tanggal tertentu untuk penyusunan matriks ketersediaan jadwal.
     * CARA KERJA         : Menambahkan klausul WHERE reservation_date = $date pada query builder.
     */
    public function scopeOnDate(Builder $query, string $date): Builder
    {
        return $query->where('reservation_date', $date);
    }
}
