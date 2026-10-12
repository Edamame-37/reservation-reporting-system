<?php

/**
 * ====================================================================================================
 * NAMA FILE    : DemoReportSeeder.php
 * LOKASI       : database/seeders/DemoReportSeeder.php
 * FUNGSI       : Seeder Data Sampel Tiket Pelaporan Kerusakan & Transaksi Peminjaman Ruangan
 * MODUL CAVA   : 12. Portal Pengguna: Pelaporan Kerusakan Fasilitas & Pelacakan Tiket (USR-04 & USR-05)
 *                (Serta Digunakan untuk Pengujian Dasbor & Ekspor Laporan ADM-04)
 * ====================================================================================================
 * 
 * ARSITEKTUR & PERAN DALAM SISTEM:
 * 1. Data Dummy Realistis untuk Pengujian Modul Pelaporan:
 *    - Seeder ini menanamkan data contoh laporan kerusakan fisik (AC bocor, kabel HDMI putus, stop kontak longgar)
 *      lengkap dengan transisi status: 'baru', 'diproses', dan 'selesai'.
 *    - Mendukung verifikasi alur tindak lanjut teknisi: pengisian `resolution_note`, waktu penanganan (`resolved_at`),
 *      dan keterikatan penanggung jawab (`handled_by`).
 * 
 * 2. Idempoten (firstOrCreate):
 *    - Menggunakan metode `firstOrCreate` berbasis `ticket_code` dan `report_code` sehingga aman dieksekusi
 *      berulang kali tanpa menimbulkan duplikasi baris data.
 * 
 * ====================================================================================================
 */

namespace Database\Seeders;

// Mengimpor Model Eloquent yang berinteraksi dalam seeder
use App\Models\DamageReport;
use App\Models\Facility;
use App\Models\Reservation;
use App\Models\User;

// Mengimpor Carbon untuk pembuatan tanggal dan waktu yang terstandardisasi
use Carbon\Carbon;

// Mengimpor Base Class Seeder Laravel
use Illuminate\Database\Seeder;

/**
 * Class DemoReportSeeder
 * 
 * Bertanggung jawab menginjeksi data sampel tiket permohonan reservasi dan pengaduan kerusakan sarana.
 */
class DemoReportSeeder extends Seeder
{
    /**
     * Mengeksekusi penanaman data dummy ke dalam tabel reservations dan damage_reports.
     * 
     * @return void
     */
    public function run(): void
    {
        // 1. Mengidentifikasi akun pengguna representatif sistem berdasarkan peran (role)
        $admin     = User::where('role', 'admin')->first() ?? User::first();
        $petugas   = User::where('role', 'petugas')->first() ?? $admin;
        $mahasiswa = User::where('role', 'mahasiswa')->orWhere('role', 'pengguna')->first() ?? $admin;
        $dosen     = User::where('role', 'dosen')->first() ?? $mahasiswa;

        // 2. Mengambil master data fasilitas kampus; hentikan jika belum ada fasilitas yang ditanam
        $facilities = Facility::all();
        if ($facilities->isEmpty()) {
            return;
        }

        // Mengambil tiga fasilitas pertama sebagai target sampel transaksi
        $fac1 = $facilities->first();
        $fac2 = $facilities->skip(1)->first() ?? $fac1;
        $fac3 = $facilities->skip(2)->first() ?? $fac1;

        // Menetapkan periode tahun dan bulan aktif untuk data pengujian (September 2026)
        $year  = 2026;
        $month = 9;

        // ==========================================================================================
        // SEKSI 1: SAMPEL TRANSAKSI RESERVASI RUANGAN KAMPUS
        // ==========================================================================================

        // Sampel 1: Reservasi Berstatus 'approved' (Praktikum Mahasiswa)
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

        // Sampel 2: Reservasi Berstatus 'approved' (Kuliah Tamu Dosen)
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

        // Sampel 3: Reservasi Berstatus 'approved' (Kegiatan Organisasi Kampus)
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

        // Sampel 4: Reservasi Berstatus 'rejected' (Disertai Alasan Penolakan Petugas)
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
                'rejection_reason'   => 'Ruang sedang dalam jadwal pemeliharaan berkala sarpras.',
                'reviewed_by'        => $admin->id,
                'reviewed_at'        => Carbon::create($year, $month, 17, 11, 0, 0),
            ]
        );

        // ==========================================================================================
        // SEKSI 2: SAMPEL TIKET PENGADUAN KERUSAKAN FASILITAS (USR-04 & USR-05)
        // ==========================================================================================

        // Sampel 5: Tiket Kerusakan Selesai (AC & Pendingin Ruang)
        DamageReport::firstOrCreate(
            ['report_code' => 'DMG-20260906-001'],
            [
                'user_id'            => $mahasiswa->id,
                'facility_id'        => $fac1->id,
                'category'           => 'AC & Pendingin Ruang',
                'description'        => 'AC sentral unit timur meneteskan air pendingin ke lantai ruang lab.',
                'status'             => 'selesai',
                'resolution_note'    => 'Pipa pembuangan AC telah dibersihkan teknisi dan sirkulasi pendingin kembali normal.',
                'handled_by'         => $petugas->id,
                'created_at'         => Carbon::create($year, $month, 6, 8, 30, 0),
                'resolved_at'        => Carbon::create($year, $month, 7, 14, 0, 0),
            ]
        );

        // Sampel 6: Tiket Kerusakan Selesai (Audio & Proyektor)
        DamageReport::firstOrCreate(
            ['report_code' => 'DMG-20260912-002'],
            [
                'user_id'            => $dosen->id,
                'facility_id'        => $fac2->id,
                'category'           => 'Proyektor, Audio & AV',
                'description'        => 'Kabel HDMI proyektor panggung tidak menampilkan sinyal visual ke layar proyektor.',
                'status'             => 'selesai',
                'resolution_note'    => 'Kabel HDMI transmisi utama telah diganti dengan kabel optik baru oleh staf TIK sarpras.',
                'handled_by'         => $petugas->id,
                'created_at'         => Carbon::create($year, $month, 12, 10, 0, 0),
                'resolved_at'        => Carbon::create($year, $month, 12, 15, 30, 0),
            ]
        );

        // Sampel 7: Tiket Kerusakan Sedang Diproses (Kelistrikan)
        DamageReport::firstOrCreate(
            ['report_code' => 'DMG-20260920-003'],
            [
                'user_id'            => $mahasiswa->id,
                'facility_id'        => $fac3->id,
                'category'           => 'Kelistrikan / Stop Kontak',
                'description'        => 'Stop kontak panggung sisi timur longgar dan berpotensi memicu korsleting listrik.',
                'status'             => 'diproses',
                'handled_by'         => $petugas->id,
                'created_at'         => Carbon::create($year, $month, 20, 11, 0, 0),
            ]
        );
    }
}
