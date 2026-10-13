<?php

/**
 * ==============================================================================================
 * SISTEM INFORMASI MANAJEMEN FASILITAS & SARANA PRASARANA KAMPUS (CAVA)
 * ==============================================================================================
 * Berkas          : app/Models/Reservation.php
 * Entitas         : Reservation (Transaksi Permohonan Reservasi Ruangan)
 * Layer Arsitektur: Data Access Layer / Eloquent Model (ORM)
 * ==============================================================================================
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
 * 4. Berperan sebagai titik sentral data pada Portal Publik untuk menyusun kalender
 *    ketersediaan dan visualisasi matriks slot 30 menit tanpa mengekspos identitas pribadi pemohon.
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
//              di mana pun dengan Fully Qualified Class Name (FQCN) `\App\Models\Reservation`.
namespace App\Models;

// ==============================================================================================
// 2. IMPORT NAMESPACE & DEPENDENCIES (PHP NATIVE 'use')
// ==============================================================================================
// SYNTAX     : use <Vendor>\<Package>\<ClassName>;
// UNTUK APA  : Mengimpor class query builder, model base, relasi, dan trait ke dalam scope berkas
//              ini agar dapat dipanggil menggunakan nama pendek tanpa FQCN panjang.
// CARA KERJA : Diuraikan oleh PHP engine saat kompilasi file (compile-time aliasing).
use Illuminate\Database\Eloquent\Builder;              // Builder kueri Eloquent untuk kueri SQL fleksibel
use Illuminate\Database\Eloquent\Factories\HasFactory;  // Trait untuk integrasi Database Seeder & Factory
use Illuminate\Database\Eloquent\Model;               // Base Class Eloquent ORM dari Laravel
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Tipe kembalian relasi Many-to-One (BelongsTo)

// ==============================================================================================
// 3. DEKLARASI CLASS & INHERITANCE (PHP NATIVE 'class ... extends ...')
// ==============================================================================================
// SYNTAX     : class Reservation extends Model
// UNTUK APA  : Mendefinisikan class cetak biru `Reservation` yang mewarisi seluruh kemampuan
//              Active Record dari class induk `Illuminate\Database\Eloquent\Model`.
// CARA KERJA : Setiap instance `Reservation` memetakan baris rekaman pada tabel `reservations`
//              ke dalam objek PHP dengan method CRUD, event lifecycle, dan relationship.
class Reservation extends Model
{
    // ==========================================================================================
    // 4. TRAIT COMPOSITION (PHP NATIVE 'use TraitName;')
    // ==========================================================================================
    // SYNTAX     : use HasFactory;
    // UNTUK APA  : Menyuntikkan metode `Reservation::factory()` ke dalam class untuk pembuatan
    //              data dummy pada automated test atau database seeding.
    // CARA KERJA : PHP menyalin method-method yang ada di trait `HasFactory` ke dalam class ini.
    use HasFactory;

    // ==========================================================================================
    // 5. PENETAPAN NAMA TABEL BASIS DATA (LARAVEL ELOQUENT CONVENTION OVERRIDE)
    // ==========================================================================================
    // SYNTAX     : protected $table = 'reservations';
    // UNTUK APA  : Menegaskan nama tabel SQL secara eksplisit di database MySQL.
    // CARA KERJA : Modifiers 'protected' membatasi akses properti hanya untuk class ini dan turunannya.
    //              Memastikan kueri SQL selalu mengarah ke tabel `reservations`.
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'reservations';

    // ==========================================================================================
    // 6. PROTEKSI MASS ASSIGNMENT (LARAVEL ELOQUENT SECURITY PROPERTY)
    // ==========================================================================================
    // SYNTAX     : protected $fillable = [ ... ];
    // UNTUK APA  : Menentukan daftar putih (whitelist) kolom database yang diizinkan untuk diisi
    //              secara serentak melalui `$reservation->update($data)` atau `Reservation::create($data)`.
    // CARA KERJA : Mencegah kerentanan Mass Assignment Vulnerability (CWE-915). Parameter request
    //              yang tidak terdaftar di sini akan diabaikan secara otomatis oleh Eloquent.
    /**
     * The attributes that are mass assignable.
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

    // ==========================================================================================
    // 7. TYPE CASTING ATRIBUT MODEL (LARAVEL 11 NATIVE METHOD 'casts(): array')
    // ==========================================================================================
    // SYNTAX     : protected function casts(): array { return [ ... ]; }
    // UNTUK APA  : Mengonversi format kolom data SQL ke tipe data objek atau primitif PHP secara otomatis.
    // CARA KERJA :
    // - 'reservation_date'   => 'date'     : Diubah menjadi instance `Illuminate\Support\Carbon` (format tanggal).
    // - 'reviewed_at'        => 'datetime' : Diubah menjadi instance `Illuminate\Support\Carbon` (format datetime).
    // - 'total_slots'        => 'integer'  : Dijamin selalu bertipe int murni PHP.
    // - 'participants_count' => 'integer'  : Dijamin selalu bertipe int murni PHP.
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

    // ==========================================================================================
    // 8. RELASI ELOQUENT INVERSE MANY-TO-ONE: PENGGUNA PEMOHON
    // ==========================================================================================
    // SYNTAX     : public function user(): BelongsTo
    // UNTUK APA  : Menghubungkan tiket reservasi ini ke entitas akun pengguna (`User`) yang mengajukannya.
    // CARA KERJA : Relasi Invers Belongs-To (`belongsTo`) yang menghubungkan foreign key
    //              `reservations.user_id` ke primary key `users.id`.
    /**
     * Mendapatkan entitas pengguna sivitas yang mengajukan permohonan reservasi ini.
     * 
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // ==========================================================================================
    // 9. RELASI ELOQUENT INVERSE MANY-TO-ONE: FASILITAS / RUANGAN DIPINJAM
    // ==========================================================================================
    // SYNTAX     : public function facility(): BelongsTo
    // UNTUK APA  : Menghubungkan tiket reservasi ke ruangan/venue (`Facility`) yang dipesan.
    // CARA KERJA : Relasi Invers Belongs-To (`belongsTo`) yang menghubungkan foreign key
    //              `reservations.facility_id` ke primary key `facilities.id`.
    /**
     * Mendapatkan entitas fasilitas/ruangan kampus yang dipesan pada reservasi ini.
     * 
     * @return BelongsTo
     */
    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class, 'facility_id');
    }

    // ==========================================================================================
    // 10. RELASI ELOQUENT INVERSE MANY-TO-ONE: PETUGAS PENINJAU / VERIFIKATOR
    // ==========================================================================================
    // SYNTAX     : public function reviewer(): BelongsTo
    // UNTUK APA  : Menghubungkan tiket reservasi ke akun petugas/admin (`User`) yang memverifikasinya.
    // CARA KERJA : Relasi Invers Belongs-To (`belongsTo`) yang menghubungkan foreign key
    //              `reservations.reviewed_by` ke primary key `users.id`.
    /**
     * Mendapatkan identitas petugas sarpras yang meninjau/memverifikasi tiket reservasi ini.
     * 
     * @return BelongsTo
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // ==========================================================================================
    // 11. LOCAL QUERY SCOPE: FILTER STATUS DISETUJUI (APPROVED)
    // ==========================================================================================
    // SYNTAX     : public function scopeApproved(Builder $query): Builder
    // UNTUK APA  : Menyaring hanya reservasi yang sah disetujui (berstatus 'approved') untuk keperluan
    //              penguncian slot waktu pada matriks ketersediaan jadwal publik.
    // CARA KERJA : Menambahkan klausul SQL `WHERE status = 'approved'` ke instance Query Builder.
    /**
     * Scope query untuk hanya menyaring reservasi yang telah disetujui resmi.
     * 
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'approved');
    }

    // ==========================================================================================
    // 12. LOCAL QUERY SCOPE: FILTER BERDASARKAN TANGGAL KEGIATAN
    // ==========================================================================================
    // SYNTAX     : public function scopeOnDate(Builder $query, string $date): Builder
    // UNTUK APA  : Menyaring data reservasi pada tanggal tertentu untuk penyusunan matriks 30 menit.
    // CARA KERJA : Menambahkan klausul SQL `WHERE reservation_date = $date` ke query builder.
    /**
     * Scope query untuk menyaring reservasi pada tanggal tertentu.
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
