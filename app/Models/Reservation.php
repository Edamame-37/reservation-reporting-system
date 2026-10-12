<?php

/**
 * ==============================================================================================
 * MODEL ELOQUENT ENTITAS PERMOHONAN RESERVASI RUANGAN (RESERVATION TRANSACTION)
 * ==============================================================================================
 * Berkas: app/Models/Reservation.php
 * 
 * PERAN & FUNGSI UTAMA:
 * 1. Merepresentasikan entitas transaksi permohonan peminjaman ruangan dan venue kampus.
 * 2. Menyimpan detail tiket peminjaman: kode unik tiket (`ticket_code`), tanggal kegiatan,
 *    rentang waktu per 30 menit (jam mulai & selesai), tujuan kegiatan, jumlah peserta,
 *    dan unggahan berkas surat izin kegiatan (`permit_letter_path`).
 * 3. Mengelola state machine siklus status permohonan:
 *    - `pending`   : Menunggu peninjauan dan verifikasi oleh Petugas Sarpras.
 *    - `approved`  : Telah disetujui resmi; mengunci slot jadwal pada matriks ketersediaan publik.
 *    - `rejected`  : Ditolak oleh petugas disertai alasan resmi penolakan.
 *    - `cancelled` : Dibatalkan secara mandiri oleh pemohon (H-1) atau pembatalan darurat oleh petugas.
 *    - `completed` : Kegiatan telah selesai diselenggarakan tanpa insiden.
 * 4. Berperan sebagai titik sentral data pada Portal Publik (Modul 4) untuk menyusun kalender
 *    ketersediaan dan visualisasi matriks slot 30 menit tanpa mengekspos identitas pribadi pemohon.
 * ==============================================================================================
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
     * CARA KERJA & ALUR:
     * Menegaskan pemetaan nama tabel di basis data secara eksplisit ('reservations').
     *
     * @var string
     */
    protected $table = 'reservations';

    /**
     * The attributes that are mass assignable.
     * 
     * CARA KERJA & ALUR:
     * Melindungi sistem dari kerentanan Mass Assignment Vulnerability (CWE-915).
     * Hanya atribut-atribut berikut yang dapat diisi melalui `$request->validated()`.
     *
     * @var list<string>
     */
    protected $fillable = [
        'ticket_code',         // Kode tiket unik (contoh: 'RSV-20261013-ABCD') untuk pelacakan.
        'user_id',             // Foreign Key ke tabel users (pengguna pemohon reservasi).
        'facility_id',         // Foreign Key ke tabel facilities (ruangan/venue yang dipinjam).
        'reservation_date',    // Tanggal pelaksanaan kegiatan (format Y-m-d).
        'start_time',          // Waktu mulai kegiatan dalam slot 30 menit (contoh: '08:00').
        'end_time',            // Waktu selesai kegiatan dalam slot 30 menit (contoh: '11:30').
        'total_slots',         // Jumlah total blok slot 30 menit yang dikonsumsi (contoh: 7 slot).
        'purpose',             // Deskripsi tujuan kegiatan, nama acara, dan narasi pengajuan.
        'participants_count',  // Estimasi jumlah orang/peserta yang menghadiri acara.
        'permit_letter_path',  // Path penyimpanan berkas PDF surat izin kegiatan resmi.
        'status',              // Status permohonan: 'pending', 'approved', 'rejected', 'cancelled', 'completed'.
        'rejection_reason',    // Catatan alasan jika permohonan ditolak oleh petugas.
        'cancellation_reason', // Catatan alasan jika dibatalkan oleh pemohon atau petugas.
        'reviewed_by',         // Foreign Key ke tabel users (petugas yang memverifikasi).
        'reviewed_at',         // Timestamp waktu saat verifikasi diputuskan.
    ];

    /**
     * Get the attributes that should be cast.
     * 
     * CARA KERJA & ALUR:
     * - 'reservation_date' => 'date'     : Mengonversi string tanggal SQL menjadi objek Carbon date.
     * - 'reviewed_at'      => 'datetime' : Mengonversi timestamp SQL menjadi objek Carbon datetime.
     * - 'total_slots'      => 'integer'  : Memastikan hitungan blok slot selalu berupa bilangan bulat.
     * - 'participants_count' => 'integer': Memastikan kapasitas peserta berupa integer.
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
     * ==========================================================================================
     * RELASI ELOQUENT: PENGGUNA PEMOHON RESERVASI
     * ==========================================================================================
     * 
     * KEGUNAAN   : Mengambil data pengguna sivitas kampus yang membuat tiket reservasi ini.
     * CARA KERJA : Relasi Invers Belongs-To (`belongsTo`) yang menghubungkan `reservations.user_id` ke `users.id`.
     * CONTOH     : `$reservation->user->name;`
     * 
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * ==========================================================================================
     * RELASI ELOQUENT: FASILITAS / RUANGAN YANG DIPINJAM
     * ==========================================================================================
     * 
     * KEGUNAAN   : Mengambil metadata ruangan (nama, kode, gedung, kapasitas) yang dipesan.
     * CARA KERJA : Relasi Invers Belongs-To (`belongsTo`) yang menghubungkan `reservations.facility_id` ke `facilities.id`.
     * CONTOH     : `$reservation->facility->name;`
     * 
     * @return BelongsTo
     */
    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class, 'facility_id');
    }

    /**
     * ==========================================================================================
     * RELASI ELOQUENT: PETUGAS PENINJAU / VERIFIKATOR (REVIEWER)
     * ==========================================================================================
     * 
     * KEGUNAAN   : Mengambil identitas petugas yang menyetujui, menolak, atau membatalkan reservasi.
     * CARA KERJA : Relasi Invers Belongs-To (`belongsTo`) yang menghubungkan `reservations.reviewed_by` ke `users.id`.
     * CONTOH     : `$reservation->reviewer->name;`
     * 
     * @return BelongsTo
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * ==========================================================================================
     * LOCAL QUERY SCOPE: FILTER RESERVASI YANG TELAH DISETUJUI (APPROVED ONLY)
     * ==========================================================================================
     * 
     * KEGUNAAN   : Memfilter hanya permohonan yang berstatus resmi 'approved'.
     * PERAN PUB  : Digunakan oleh matriks ketersediaan jadwal publik (`PublicFacilityController::getMatrixAvailability`)
     *              karena hanya reservasi yang sudah disetujui yang sah mengunci slot waktu ruangan.
     * CARA KERJA : Menambahkan klausul SQL `WHERE status = 'approved'` pada query builder.
     * CARA PAKAI : `Reservation::approved()->where('facility_id', $id)->get();`
     * 
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'approved');
    }

    /**
     * ==========================================================================================
     * LOCAL QUERY SCOPE: FILTER RESERVASI BERDASARKAN TANGGAL KEGIATAN
     * ==========================================================================================
     * 
     * KEGUNAAN   : Memfilter reservasi pada tanggal tertentu untuk penyusunan matriks jadwal slot harian.
     * CARA KERJA : Menambahkan klausul SQL `WHERE reservation_date = $date` pada query builder.
     * CARA PAKAI : `Reservation::approved()->onDate('2026-10-15')->get();`
     * 
     * @param  Builder  $query
     * @param  string   $date Format tanggal 'Y-m-d'
     * @return Builder
     */
    public function scopeOnDate(Builder $query, string $date): Builder
    {
        return $query->where('reservation_date', $date);
    }
}

