<?php

/**
 * ==============================================================================================
 * SEEDER BASIS DATA: MASTER INVENTARIS FASILITAS & RUANGAN KAMPUS
 * ==============================================================================================
 * Berkas: database/seeders/FacilitySeeder.php
 * 
 * PERAN & FUNGSI UTAMA:
 * 1. Menginisialisasi katalog ruang kampus dengan data realistis untuk pengujian sistem CAVA.
 * 2. Mendaftarkan 6 fasilitas unggulan utama (Flagship Venues) dengan variasi kategori lengkap
 *    (Auditorium, Laboratorium Komputer, Smart Classroom, Aula Olahraga, Ruang Seminar, Ruang Senat).
 * 3. Menghasilkan puluhan ruang kelas terstandarisasi secara prosedural (Gedung A s.d. F, Lantai 1-3)
 *    menggunakan perulangan bersarang (`sprintf`) untuk simulasi skala lingkungan kampus nyata.
 * 4. Menerapkan `Facility::updateOrCreate()` agar seeder bersifat idempoten (dapat dijalankan
 *    berulang kali tanpa menghasilkan data duplikat atau merusak kode ruangan).
 * ==============================================================================================
 */

namespace Database\Seeders;

use App\Models\Facility;
use Illuminate\Database\Seeder;

class FacilitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * CARA KERJA & ALUR:
     * 1. Menyiapkan array data untuk 6 fasilitas unggulan representatif.
     * 2. Menjalankan algoritma procedural generator untuk memproduksi ruangan di Gedung A s.d. F.
     * 3. Melakukan sinkronisasi ke tabel `facilities` menggunakan `updateOrCreate()` berbasis kolom `code`.
     */
    public function run(): void
    {
        // ------------------------------------------------------------------------------------------
        // 1. FASILITAS UNGGULAN REPRESENTATIF (FLAGSHIP VENUES)
        // ------------------------------------------------------------------------------------------
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
                // Status 'dalam perbaikan' berguna sebagai data uji untuk memvalidasi pencegahan reservasi fasilitas yang terkunci:
                'status'         => 'dalam perbaikan',
            ],
        ];

        // ------------------------------------------------------------------------------------------
        // 2. GENERASI PROSEDURAL RUANG KELAS & LAB (GEDUNG A s.d. F)
        // ------------------------------------------------------------------------------------------
        // Metadata konfigurasi kluster gedung universitas:
        $buildings = [
            'A' => ['name' => 'Gedung Kuliah Terpadu A', 'category' => 'kelas', 'capacity' => 40],
            'B' => ['name' => 'Gedung Kuliah Bersama B', 'category' => 'kelas', 'capacity' => 45],
            'C' => ['name' => 'Gedung Lab Terpadu C', 'category' => 'lab', 'capacity' => 35],
            'D' => ['name' => 'Gedung Perkuliahan D', 'category' => 'kelas', 'capacity' => 50],
            'E' => ['name' => 'Gedung Pascasarjana E', 'category' => 'kelas', 'capacity' => 30],
            'F' => ['name' => 'Gedung Pusat Bahasa & Budaya F', 'category' => 'kelas', 'capacity' => 35],
        ];

        // Perulangan bertingkat 3 level (Gedung -> Lantai 1-3 -> Ruang 1-4 per lantai):
        foreach ($buildings as $bLetter => $bMeta) {
            for ($floor = 1; $floor <= 3; $floor++) {
                for ($roomNum = 1; $roomNum <= 4; $roomNum++) {
                    // Format kode ruangan (contoh: A101, A102, C203):
                    // - %s = huruf gedung ('A')
                    // - %d = angka lantai (1)
                    // - %02d = nomor ruangan 2 digit berpading nol ('01', '02', dst.)
                    $roomCode = sprintf('%s%d%02d', $bLetter, $floor, $roomNum);
                    
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

        // ------------------------------------------------------------------------------------------
        // 3. PENYIMPANAN DATA IDEMPOTEN KE BASIS DATA
        // ------------------------------------------------------------------------------------------
        foreach ($facilities as $facility) {
            // Facility::updateOrCreate(['code' => ...], $facility)
            // - Mencari apakah fasilitas dengan kode 'code' sudah pernah ada di database.
            // - Jika ada: Melakukan UPDATE atribut-atributnya agar tetap mutakhir.
            // - Jika belum ada: Melakukan INSERT data baru.
            // - Menjamin seeder dapat dijalankan berulang kali tanpa memicu galat "Duplicate entry 'AUD-H01'".
            Facility::updateOrCreate(['code' => $facility['code']], $facility);
        }
    }
}

