<?php
/**
 * NAMA FILE    : User.php
 * FUNGSI       : Model Eloquent entitas Pengguna & Sivitas Kampus
 * DESKRIPSI    : Merepresentasikan akun pengguna, integrasi Spatie Role-Based Access Control, serta relasi ke tiket reservasi dan pelaporan.
 * CARA KERJA   : Berkomunikasi dengan tabel users, mengelola otorisasi hak akses, enkripsi sandi otomatis, dan riwayat permohonan.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'identity_number',
        'role',
        'department',
        'phone_number',
        'id_card_path',
        'status',
        'rejection_reason',
        'assignment_zone',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

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

    /**
     * FUNCTION/PROCEDURE : reservations()
     * KEGUNAAN           : Menarik seluruh riwayat permohonan reservasi yang diajukan oleh pengguna ini.
     * CARA KERJA         : Menghubungkan users.id ke reservations.user_id (Relasi One-to-Many).
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'user_id');
    }

    /**
     * FUNCTION/PROCEDURE : damageReports()
     * KEGUNAAN           : Menarik seluruh riwayat tiket pelaporan kerusakan yang diajukan oleh pengguna ini.
     * CARA KERJA         : Menghubungkan users.id ke damage_reports.user_id (Relasi One-to-Many).
     */
    public function damageReports(): HasMany
    {
        return $this->hasMany(DamageReport::class, 'user_id');
    }

    /**
     * FUNCTION/PROCEDURE : reviewedReservations()
     * KEGUNAAN           : Menarik daftar reservasi yang telah disetujui, ditolak, atau dibatalkan darurat oleh petugas ini.
     * CARA KERJA         : Menghubungkan users.id ke reservations.reviewed_by (Relasi One-to-Many Petugas).
     */
    public function reviewedReservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'reviewed_by');
    }

    /**
     * FUNCTION/PROCEDURE : handledReports()
     * KEGUNAAN           : Menarik daftar tiket kerusakan yang ditangani atau diselesaikan oleh petugas/teknisi ini.
     * CARA KERJA         : Menghubungkan users.id ke damage_reports.handled_by (Relasi One-to-Many Petugas).
     */
    public function handledReports(): HasMany
    {
        return $this->hasMany(DamageReport::class, 'handled_by');
    }
}
