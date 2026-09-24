<?php
/**
 * NAMA FILE    : DemoReportSeeder.php
 * FUNGSI       : Seeder Data Uji Coba Laporan Reservasi & Kerusakan Periode Berjalan
 * DESKRIPSI    : Menyediakan transaksi permohonan reservasi ruang dan tiket pengaduan kerusakan untuk menguji Dasbor Statistik dan Unduh Laporan (ADM-04 / UR17).
 * CARA KERJA   : Memasukkan record contoh ke tabel reservations dan damage_reports pada bulan berjalan.
 */

namespace Database\Seeders;

use App\Models\DamageReport;
use App\Models\Facility;
use App\Models\Reservation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DemoReportSeeder extends Seeder
{
    /**
     * Jalankan penanaman data reservasi & kerusakan untuk pengujian ekspor laporan.
     */
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first() ?? User::first();
        $petugas = User::where('role', 'petugas')->first() ?? $admin;
        $mahasiswa = User::where('role', 'mahasiswa')->first() ?? $admin;
        $dosen = User::where('role', 'dosen')->first() ?? $mahasiswa;

        $facilities = Facility::all();
        if ($facilities->isEmpty()) {
            return;
        }

        $fac1 = $facilities->first();
        $fac2 = $facilities->skip(1)->first() ?? $fac1;
        $fac3 = $facilities->skip(2)->first() ?? $fac1;

        // Tahun dan bulan aktif (September 2026)
        $year = 2026;
        $month = 9;

        // 1. Reservasi Disetujui 1
        Reservation::firstOrCreate(
            ['ticket_code' => 'TKT-20260905-001'],
            [
                'user_id'            => $mahasiswa->id,
                'facility_id'        => $fac1->id,
                'reservation_date'   => Carbon::create($year, $month, 5)->toDateString(),
                'start_time'         => '08:00:00',
                'end_time'           => '12:00:00',
                'total_slots'        => 8,
                'purpose'            => 'Praktikum Komputasi Awan & Jaringan',
                'participants_count' => 40,
                'status'             => 'approved',
                'reviewed_by'        => $admin->id,
                'reviewed_at'        => Carbon::create($year, $month, 4, 10, 0, 0),
            ]
        );

        // 2. Reservasi Disetujui 2
        Reservation::firstOrCreate(
            ['ticket_code' => 'TKT-20260910-002'],
            [
                'user_id'            => $dosen->id,
                'facility_id'        => $fac2->id,
                'reservation_date'   => Carbon::create($year, $month, 10)->toDateString(),
                'start_time'         => '13:00:00',
                'end_time'           => '16:00:00',
                'total_slots'        => 6,
                'purpose'            => 'Kuliah Tamu Industri & AI',
                'participants_count' => 65,
                'status'             => 'approved',
                'reviewed_by'        => $admin->id,
                'reviewed_at'        => Carbon::create($year, $month, 8, 14, 0, 0),
            ]
        );

        // 3. Reservasi Disetujui 3
        Reservation::firstOrCreate(
            ['ticket_code' => 'TKT-20260915-003'],
            [
                'user_id'            => $mahasiswa->id,
                'facility_id'        => $fac3->id,
                'reservation_date'   => Carbon::create($year, $month, 15)->toDateString(),
                'start_time'         => '09:00:00',
                'end_time'           => '17:00:00',
                'total_slots'        => 16,
                'purpose'            => 'Latihan Gladi Bersih Ormawa Kampus',
                'participants_count' => 80,
                'status'             => 'approved',
                'reviewed_by'        => $admin->id,
                'reviewed_at'        => Carbon::create($year, $month, 12, 9, 30, 0),
            ]
        );

        // 4. Reservasi Ditolak
        Reservation::firstOrCreate(
            ['ticket_code' => 'TKT-20260918-004'],
            [
                'user_id'            => $mahasiswa->id,
                'facility_id'        => $fac1->id,
                'reservation_date'   => Carbon::create($year, $month, 18)->toDateString(),
                'start_time'         => '10:00:00',
                'end_time'           => '12:00:00',
                'total_slots'        => 4,
                'purpose'            => 'Rapat Internal Himpunan Mahasiswa',
                'participants_count' => 20,
                'status'             => 'rejected',
                'rejection_reason'   => 'Ruang sedang dalam jadwal pemeliharaan berkala',
                'reviewed_by'        => $admin->id,
                'reviewed_at'        => Carbon::create($year, $month, 17, 11, 0, 0),
            ]
        );

        // 5. Tiket Kerusakan Selesai 1
        DamageReport::firstOrCreate(
            ['report_code' => 'DMG-20260906-001'],
            [
                'user_id'            => $mahasiswa->id,
                'facility_id'        => $fac1->id,
                'category'           => 'AC & Pendingin Ruang',
                'description'        => 'AC sentral unit timur meneteskan air pendingin',
                'status'             => 'selesai',
                'resolution_note'    => 'Pipa pembuangan AC telah dibersihkan dan unit normal kembali',
                'handled_by'         => $petugas->id,
                'created_at'         => Carbon::create($year, $month, 6, 8, 30, 0),
                'resolved_at'        => Carbon::create($year, $month, 7, 14, 0, 0),
            ]
        );

        // 6. Tiket Kerusakan Selesai 2
        DamageReport::firstOrCreate(
            ['report_code' => 'DMG-20260912-002'],
            [
                'user_id'            => $dosen->id,
                'facility_id'        => $fac2->id,
                'category'           => 'Proyektor, Audio & AV',
                'description'        => 'Kabel HDMI proyektor panggung tidak menampilkan sinyal grafis',
                'status'             => 'selesai',
                'resolution_note'    => 'Kabel HDMI telah diganti dengan kabel baru',
                'handled_by'         => $petugas->id,
                'created_at'         => Carbon::create($year, $month, 12, 10, 0, 0),
                'resolved_at'        => Carbon::create($year, $month, 12, 15, 30, 0),
            ]
        );

        // 7. Tiket Kerusakan Diproses
        DamageReport::firstOrCreate(
            ['report_code' => 'DMG-20260920-003'],
            [
                'user_id'            => $mahasiswa->id,
                'facility_id'        => $fac3->id,
                'category'           => 'Kelistrikan / Stop Kontak',
                'description'        => 'Stop kontak panggung sisi timur longgar',
                'status'             => 'diproses',
                'handled_by'         => $petugas->id,
                'created_at'         => Carbon::create($year, $month, 20, 11, 0, 0),
            ]
        );
    }
}
