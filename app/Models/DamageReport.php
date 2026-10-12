<?php

/**
 * ====================================================================================================
 * SISTEM INFORMASI RESERVASI & PELAPORAN FASILITAS KAMPUS (CAVA)
 * MODUL 7: KONSOL ADMIN - DASBOR EKSEKUTIF & STATISTIK UTAMA (ADM-04 / US-17)
 * BERKAS: app/Models/DamageReport.php
 * ====================================================================================================
 * 
 * DESKRIPSI BERKAS:
 * Model Eloquent ini merepresentasikan entitas Tiket Laporan Kerusakan Sarana & Prasarana Kampus
 * (Damage Report / Facility Maintenance Ticket) pada sistem CAVA.
 * 
 * PERAN & ARSITEKTUR DALAM SISTEM CAVA:
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
 * ====================================================================================================
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class DamageReport
 * 
 * Mengelola interaksi data tiket kerusakan fasilitas pada tabel `damage_reports`.
 */
class DamageReport extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang terasosiasi dengan model ini di basis data MySQL.
     *
     * @var string
     */
    protected $table = 'damage_reports';

    /**
     * Daftar atribut yang diizinkan untuk pengisian massal (Mass Assignment Protection).
     * Mencegah kerentanan eksploitasi parameter HTTP request yang tidak terotorisasi.
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

    /**
     * Mengatur casting tipe data atribut basis data ke tipe data primitif PHP yang sesuai.
     * 
     * CARA KERJA & LOGIKA:
     * - 'is_facility_locked' => 'boolean': Mengonversi integer tinyint (0/1) dari MySQL menjadi boolean murni true/false.
     * - 'resolved_at' => 'datetime': Mengonversi string waktu timestamp menjadi objek Carbon datetime untuk manipulasi waktu.
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
     * ==========================================================================================
     * RELASI ELOQUENT: PENGGUNA PELAPOR KERUSAKAN
     * ==========================================================================================
     * 
     * KEGUNAAN   : Menarik profil identitas sivitas (nama, email, NIM/NIP) yang mengirimkan tiket aduan ini.
     * CARA KERJA : Relasi Invers Belongs-To yang menghubungkan kolom `damage_reports.user_id` ke `users.id`.
     * CONTOH     : `$damageReport->user->name;`
     * 
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * ==========================================================================================
     * RELASI ELOQUENT: FASILITAS / RUANGAN YANG RUSAK
     * ==========================================================================================
     * 
     * KEGUNAAN   : Mengambil metadata spesifik fasilitas (nama gedung, lokasi, lantai, kapasitas) yang rusak.
     * CARA KERJA : Relasi Invers Belongs-To yang menghubungkan `damage_reports.facility_id` ke `facilities.id`.
     * CONTOH     : `$damageReport->facility->name;`
     * 
     * @return BelongsTo
     */
    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class, 'facility_id');
    }

    /**
     * ==========================================================================================
     * RELASI ELOQUENT: PETUGAS TEKNISI PENANGGUNG JAWAB (HANDLER)
     * ==========================================================================================
     * 
     * KEGUNAAN   : Menarik identitas personil teknisi sarpras yang ditugaskan menangani atau meresolusi tiket.
     * CARA KERJA : Relasi Invers Belongs-To yang menghubungkan `damage_reports.handled_by` ke `users.id`.
     * CONTOH     : `$damageReport->handler->name;`
     * 
     * @return BelongsTo
     */
    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    /**
     * ==========================================================================================
     * METHOD RELASI ALIAS: handledBy()
     * ==========================================================================================
     * 
     * KEGUNAAN   : Menyediakan alias yang ergonomis dan konsisten untuk relasi `handler()`.
     * CARA KERJA : Meneruskan pemanggilan secara langsung ke method `handler()`.
     * 
     * @return BelongsTo
     */
    public function handledBy(): BelongsTo
    {
        return $this->handler();
    }
}
