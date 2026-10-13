<?php

/**
 * ==============================================================================================
 * SISTEM INFORMASI MANAJEMEN FASILITAS & SARANA PRASARANA KAMPUS (CAVA)
 * ==============================================================================================
 * Berkas          : app/Models/DamageReport.php
 * Entitas         : DamageReport (Tiket Laporan Kerusakan Sarana & Prasarana Kampus)
 * Layer Arsitektur: Data Access Layer / Eloquent Model (ORM)
 * ==============================================================================================
 * PERAN & FUNGSI UTAMA:
 * 1. Manajemen Insiden & Tiketing Sarpras:
 *    Menampung seluruh aduan kendala fasilitas fisik kampus dari sivitas akademika (AC mati, proyektor rusak,
 *    pintu terkunci, kebocoran sanitasi, dll.) lengkap dengan bukti foto lampiran (maks. 2MB).
 * 2. Kunci Ketersediaan Fasilitas (Maintenance Mode Lock):
 *    Atribut `is_facility_locked` (boolean) menentukan apakah ruangan secara otomatis dikunci dari jadwal
 *    reservasi publik saat mengalami kerusakan berat demi mencegah komplain peminjam.
 * 3. Indikator Kinerja & SLA Penanganan (Service Level Agreement):
 *    Menyimpan pelacakan penugasan teknisi (`handled_by`), catatan penyelesaian masalah (`resolution_note`),
 *    serta stempel waktu resolusi (`resolved_at`) untuk menghitung SLA Resolution Index pada Dasbor Admin.
 * 4. Siklus Hidup Status Tiket (Ticket Lifecycle):
 *    - `submitted`   : Laporan baru diajukan oleh sivitas, belum ditinjau petugas.
 *    - `in_review`   : Laporan sedang diverifikasi oleh koordinator sarpras.
 *    - `in_progress` : Teknisi sedang melakukan investigasi/perbaikan di lokasi.
 *    - `resolved`    : Perbaikan selesai dan fasilitas dinyatakan normal kembali.
 *    - `rejected`    : Laporan ditolak (misal: laporan palsu atau fasilitas bukan aset kampus).
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
//              di mana pun dengan Fully Qualified Class Name (FQCN) `\App\Models\DamageReport`.
namespace App\Models;

// ==============================================================================================
// 2. IMPORT NAMESPACE & DEPENDENCIES (PHP NATIVE 'use')
// ==============================================================================================
// SYNTAX     : use <Vendor>\<Package>\<ClassName>;
// UNTUK APA  : Mengimpor class factory, base model, dan relasi ke dalam scope berkas ini agar
//              dapat dipanggil menggunakan nama pendek tanpa menulis FQCN panjang.
// CARA KERJA : Diuraikan oleh PHP engine saat kompilasi berkas (compile-time aliasing).
use Illuminate\Database\Eloquent\Factories\HasFactory;  // Trait untuk pembuatan data uji dan Database Seeder
use Illuminate\Database\Eloquent\Model;               // Base Class Eloquent ORM dari Laravel
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Tipe kembalian relasi Many-to-One (BelongsTo)

// ==============================================================================================
// 3. DEKLARASI CLASS & INHERITANCE (PHP NATIVE 'class ... extends ...')
// ==============================================================================================
// SYNTAX     : class DamageReport extends Model
// UNTUK APA  : Mendefinisikan class cetak biru `DamageReport` yang mewarisi seluruh kemampuan
//              Active Record dari class induk `Illuminate\Database\Eloquent\Model`.
// CARA KERJA : Memetakan baris rekaman pada tabel database `damage_reports` ke objek PHP lengkap
//              dengan metode CRUD, validasi relasi, dan lifecycle hooks.
class DamageReport extends Model
{
    // ==========================================================================================
    // 4. TRAIT COMPOSITION (PHP NATIVE 'use TraitName;')
    // ==========================================================================================
    // SYNTAX     : use HasFactory;
    // UNTUK APA  : Menyuntikkan metode `DamageReport::factory()` untuk pembuatan data dummy testing.
    // CARA KERJA : PHP engine menyalin metode dari trait `HasFactory` ke dalam class ini.
    use HasFactory;

    // ==========================================================================================
    // 5. PENETAPAN NAMA TABEL BASIS DATA (LARAVEL ELOQUENT CONVENTION OVERRIDE)
    // ==========================================================================================
    // SYNTAX     : protected $table = 'damage_reports';
    // UNTUK APA  : Menegaskan nama tabel SQL secara eksplisit di database MySQL.
    // CARA KERJA : Modifier 'protected' membatasi akses variabel hanya untuk class ini dan turunannya.
    //              Eloquent secara konsisten mengarahkan kueri ke tabel `damage_reports`.
    /**
     * Nama tabel yang terasosiasi dengan model ini di basis data MySQL.
     *
     * @var string
     */
    protected $table = 'damage_reports';

    // ==========================================================================================
    // 6. PROTEKSI MASS ASSIGNMENT (LARAVEL ELOQUENT SECURITY PROPERTY)
    // ==========================================================================================
    // SYNTAX     : protected $fillable = [ ... ];
    // UNTUK APA  : Menentukan daftar kolom database yang diizinkan untuk diisi secara serentak
    //              melalui `$damageReport->update($data)` atau `DamageReport::create($data)`.
    // CARA KERJA : Melindungi sistem dari Mass Assignment Vulnerability (CWE-915). Kolom di luar
    //              daftar ini akan diabaikan secara otomatis saat pengisian massal.
    /**
     * Daftar atribut yang diizinkan untuk pengisian massal (Mass Assignment Protection).
     *
     * @var list<string>
     */
    protected $fillable = [
        'report_code',        // Kode tiket unik (contoh: 'DMG-20261013-001') untuk identifikasi resmi.
        'user_id',            // Foreign key ID pengguna sivitas yang mengajukan laporan kerusakan.
        'facility_id',        // Foreign key ID fasilitas/ruangan yang mengalami kendala teknis.
        'category',           // Kategori kerusakan (kelistrikan, mebeler, multimedia, sanitasi, dll).
        'description',        // Rincian narasi keluhan kerusakan dari pelapor.
        'attachment_photo',   // Path penyimpanan file foto bukti kerusakan pada storage publik.
        'status',             // Status penanganan: 'submitted', 'in_review', 'in_progress', 'resolved', 'rejected'.
        'is_facility_locked', // Flag pengunci operasional ruangan (true = dinonaktifkan sementara dari peminjaman).
        'resolution_note',    // Catatan teknis tindakan korektif yang dilakukan oleh teknisi sarpras.
        'handled_by',         // Foreign key ID petugas sarpras/teknisi yang menangani tiket.
        'resolved_at',        // Timestamp penyelesaian tiket perbaikan.
    ];

    // ==========================================================================================
    // 7. TYPE CASTING ATRIBUT MODEL (LARAVEL 11 NATIVE METHOD 'casts(): array')
    // ==========================================================================================
    // SYNTAX     : protected function casts(): array { return [ ... ]; }
    // UNTUK APA  : Mengonversi format kolom data SQL ke tipe data bawaan PHP secara otomatis.
    // CARA KERJA :
    // - 'is_facility_locked' => 'boolean' : Mengonversi integer tinyint (0/1) SQL menjadi boolean murni true/false.
    // - 'resolved_at'        => 'datetime': Mengonversi string timestamp SQL menjadi objek Carbon datetime.
    /**
     * Mengatur casting tipe data atribut basis data ke tipe data primitif PHP yang sesuai.
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

    // ==========================================================================================
    // 8. RELASI ELOQUENT INVERSE MANY-TO-ONE: PENGGUNA PELAPOR KERUSAKAN
    // ==========================================================================================
    // SYNTAX     : public function user(): BelongsTo
    // UNTUK APA  : Menghubungkan tiket kerusakan ini ke profil pengguna sivitas (`User`) pelapor.
    // CARA KERJA : Relasi Invers Belongs-To yang menghubungkan foreign key `damage_reports.user_id` ke `users.id`.
    /**
     * Mendapatkan data akun pengguna sivitas yang melaporkan kerusakan ini.
     * 
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // ==========================================================================================
    // 9. RELASI ELOQUENT INVERSE MANY-TO-ONE: FASILITAS / RUANGAN YANG RUSAK
    // ==========================================================================================
    // SYNTAX     : public function facility(): BelongsTo
    // UNTUK APA  : Menghubungkan tiket kerusakan ke data spesifik fasilitas fisik kampus (`Facility`).
    // CARA KERJA : Relasi Invers Belongs-To yang menghubungkan foreign key `damage_reports.facility_id` ke `facilities.id`.
    /**
     * Mendapatkan data fasilitas/ruangan kampus yang dilaporkan mengalami kerusakan.
     * 
     * @return BelongsTo
     */
    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class, 'facility_id');
    }

    // ==========================================================================================
    // 10. RELASI ELOQUENT INVERSE MANY-TO-ONE: PETUGAS TEKNISI PENANGGUNG JAWAB (HANDLER)
    // ==========================================================================================
    // SYNTAX     : public function handler(): BelongsTo
    // UNTUK APA  : Menghubungkan tiket kerusakan ke identitas petugas/teknisi (`User`) penanggung jawab.
    // CARA KERJA : Relasi Invers Belongs-To yang menghubungkan foreign key `damage_reports.handled_by` ke `users.id`.
    /**
     * Mendapatkan data teknisi/petugas sarpras yang menangani perbaikan fasilitas ini.
     * 
     * @return BelongsTo
     */
    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    // ==========================================================================================
    // 11. METHOD RELASI ALIAS: handledBy()
    // ==========================================================================================
    // SYNTAX     : public function handledBy(): BelongsTo
    // UNTUK APA  : Menyediakan alias yang ergonomis dan konsisten untuk relasi `handler()`.
    // CARA KERJA : Meneruskan pemanggilan secara langsung ke method `handler()`.
    /**
     * Alias method untuk relasi handler() guna fleksibilitas penamaan kode.
     * 
     * @return BelongsTo
     */
    public function handledBy(): BelongsTo
    {
        return $this->handler();
    }
}
