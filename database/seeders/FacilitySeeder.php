<?php
/**
 * NAMA FILE    : FacilitySeeder.php
 * FUNGSI       : Seeder inisialisasi master inventaris fasilitas dan ruang kampus
 * DESKRIPSI    : Menanamkan 6 fasilitas representatif kampus yang tertera pada rancangan mockup katalog dan matriks jadwal.
 * CARA KERJA   : Mengiterasi data array fasilitas dan mengeksekusi Facility::firstOrCreate() dengan atribut JSON equipment.
 */

namespace Database\Seeders;

use App\Models\Facility;
use Illuminate\Database\Seeder;

class FacilitySeeder extends Seeder
{
    /**
     * FUNCTION/PROCEDURE : run()
     * KEGUNAAN           : Mengeksekusi penanaman data master fasilitas ke dalam tabel facilities.
     * CARA KERJA         : Menginput 6 data ruang lengkap dengan kapasitas, kategori, peralatan (JSON), dan status operasional.
     */
    public function run(): void
    {
        $facilities = [
            [
                'code'           => 'AUD-H01',
                'name'           => 'Auditorium Utama B.J. Habibie',
                'category'       => 'auditorium',
                'building'       => 'Gedung Rektorat (Lt. 1 & 2)',
                'floor_location' => 'Lantai 1 dan 2 Sayap Barat',
                'capacity'       => 450,
                'equipment'      => ['AC Central', 'Dual Laser Projector', 'Sound Yamaha 5000W', '8 Mic Wireless', 'Podium VIP'],
                'description'    => 'Auditorium utama universitas berstandar internasional untuk wisuda, seminar internasional, dan orasi ilmiah.',
                'status'         => 'aktif',
            ],
            [
                'code'           => 'LAB-C204',
                'name'           => 'Lab Komputasi Cloud & Jaringan',
                'category'       => 'lab',
                'building'       => 'Gedung Lab Terpadu C',
                'floor_location' => 'Lantai 2 Ruang C-204',
                'capacity'       => 45,
                'equipment'      => ['45 PC Core i7 RTX 4060', 'Gigabit Switch Cisco', 'AC Dual 2PK', 'Smart Display', 'Whiteboard'],
                'description'    => 'Laboratorium riset jaringan, cloud virtualization, dan praktikum mahasiswa informatika.',
                'status'         => 'aktif',
            ],
            [
                'code'           => 'CLS-B302',
                'name'           => 'Smart Classroom 302',
                'category'       => 'kelas',
                'building'       => 'Gedung Kuliah Bersama B',
                'floor_location' => 'Lantai 3 Ruang B-302',
                'capacity'       => 60,
                'equipment'      => ['Interactive Whiteboard', 'Video Conference Cam', 'Collab Desks', 'Audio Mic'],
                'description'    => 'Ruang kelas multimedia modern dengan meja kolaborasi ergonomis dan sistem video conference untuk kuliah hybrid.',
                'status'         => 'aktif',
            ],
            [
                'code'           => 'SPT-PKM01',
                'name'           => 'Aula Serbaguna & Olahraga PKM',
                'category'       => 'olahraga',
                'building'       => 'Pusat Kegiatan Mahasiswa (Lt. 1)',
                'floor_location' => 'Lantai 1 Gedung PKM',
                'capacity'       => 500,
                'equipment'      => ['Lapangan Futsal Vinyl', '2 Lapangan Badminton', 'Sound System', 'Ruang Ganti', 'Tribun'],
                'description'    => 'Fasilitas serbaguna untuk kegiatan ormawa kampus, turnamen olahraga antar fakultas, dan pameran kewirausahaan.',
                'status'         => 'aktif',
            ],
            [
                'code'           => 'SEM-A301',
                'name'           => 'Ruang Seminar Lantai 3',
                'category'       => 'kelas',
                'building'       => 'Gedung Kuliah Terpadu A',
                'floor_location' => 'Lantai 3 Ruang A-301',
                'capacity'       => 120,
                'equipment'      => ['Acoustic Wall Panel', 'Sound System', 'Wireless Mic', 'Dual Screen Projector'],
                'description'    => 'Ruang teater bertingkat untuk presentasi seminar skripsi, kuliah umum fakultas, dan lokakarya.',
                'status'         => 'aktif',
            ],
            [
                'code'           => 'RPT-SENAT',
                'name'           => 'Ruang Rapat Senat Akademik',
                'category'       => 'rapat',
                'building'       => 'Gedung Rektorat (Lt. 3)',
                'floor_location' => 'Lantai 3 Sayap Eksekutif',
                'capacity'       => 35,
                'equipment'      => ['Meja Oval Konferensi', 'Delegate Mic Units', 'Display LCD 85 Inch', 'Executive Chairs'],
                'description'    => 'Ruang sidang formal para pimpinan universitas dan dewan senat. Saat ini sedang dalam perbaikan tata suara.',
                'status'         => 'dalam perbaikan',
            ],
        ];

        // Master Ruangan Terstruktur Gedung A sampai F, Lantai 1 sampai 3, Ruang 101 sampai 104
        $buildings = [
            'A' => ['name' => 'Gedung Kuliah Terpadu A', 'category' => 'kelas', 'capacity' => 40],
            'B' => ['name' => 'Gedung Kuliah Bersama B', 'category' => 'kelas', 'capacity' => 45],
            'C' => ['name' => 'Gedung Lab Terpadu C', 'category' => 'lab', 'capacity' => 35],
            'D' => ['name' => 'Gedung Perkuliahan D', 'category' => 'kelas', 'capacity' => 50],
            'E' => ['name' => 'Gedung Pascasarjana E', 'category' => 'kelas', 'capacity' => 30],
            'F' => ['name' => 'Gedung Pusat Bahasa & Budaya F', 'category' => 'kelas', 'capacity' => 35],
        ];

        foreach ($buildings as $bLetter => $bMeta) {
            for ($floor = 1; $floor <= 3; $floor++) {
                for ($roomNum = 1; $roomNum <= 4; $roomNum++) {
                    $roomCode = sprintf('%s%d%02d', $bLetter, $floor, $roomNum); // Contoh: A101, A102, A103, A104
                    $facilities[] = [
                        'code'           => $roomCode,
                        'name'           => sprintf('Ruang %s (%s)', $roomCode, $bMeta['category'] === 'lab' ? 'Laboratorium' : 'Ruang Kelas'),
                        'category'       => $bMeta['category'],
                        'building'       => $bMeta['name'],
                        'floor_location' => sprintf('Lantai %d', $floor),
                        'capacity'       => $bMeta['capacity'],
                        'equipment'      => ['AC Split 2PK', 'Proyektor HD & Screen', 'Papan Tulis Whiteboard', 'Audio & Mic Speaker', 'Meja & Kursi Mahasiswa'],
                        'description'    => sprintf('Ruang perkuliahan terstandarisasi di %s Lantai %d dengan sarana multimedia lengkap.', $bMeta['name'], $floor),
                        'status'         => 'aktif',
                    ];
                }
            }
        }

        foreach ($facilities as $facility) {
            Facility::updateOrCreate(['code' => $facility['code']], $facility);
        }
    }
}
