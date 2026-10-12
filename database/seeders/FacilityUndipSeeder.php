<?php

/**
 * ==============================================================================================
 * SEEDER BASIS DATA: PENGIMPOR DATASET RIIL FASILITAS & RUANGAN KAMPUS (ETL PIPELINE)
 * ==============================================================================================
 * Berkas: database/seeders/FacilityUndipSeeder.php
 * 
 * PERAN & FUNGSI UTAMA:
 * 1. Mengimplementasikan pola pemrosesan data ETL (Extract, Transform, Load) untuk membaca dataset
 *    eksternal `new data for database/facilities_undip_lengkap.csv` (100+ fasilitas riil universitas).
 * 2. Membersihkan Byte Order Mark (UTF-8 BOM) agar tidak merusak header CSV kolom pertama.
 * 3. Melakukan transformasi data adaptif:
 *    - Pemetaan kategori non-enum (misal: 'ruang_kerja') ke nilai valid enum basis data.
 *    - Pembersihan teks nama (sanitasi redundansi sistem luar) & pemotongan batas 150 karakter.
 *    - Pemangkasan teks lantai gedung agar mematuhi batasan skema VARCHAR(50).
 *    - Penguraian (decoding) string JSON peralatan menjadi array PHP.
 * 4. Menyimpan data secara aman dan idempoten menggunakan `Facility::updateOrCreate()` berbasis `code`.
 * ==============================================================================================
 */

namespace Database\Seeders;

use App\Models\Facility;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class FacilityUndipSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * CARA KERJA & ALUR:
     * 1. Mengecek keberadaan berkas CSV sumber di direktori proyek.
     * 2. Membuka stream baca baris-per-baris (`fopen`, `fgetcsv`) untuk efisiensi memori RAM (O(1) memory).
     * 3. Melakukan iterasi seluruh baris, mentransformasikan nilai kolom, dan menyimpannya ke tabel `facilities`.
     * 4. Menampilkan statistik jumlah baris baru dan baris yang diperbarui di konsol CLI.
     */
    public function run(): void
    {
        // Lokasi path absolut dataset CSV fasilitas:
        $csvPath = base_path('new data for database/facilities_undip_lengkap.csv');

        // Validasi fisik keberadaan file dataset:
        if (!File::exists($csvPath)) {
            $this->command->error("File dataset tidak ditemukan di: {$csvPath}");
            return;
        }

        // Membuka file stream mode read-only ('r'):
        // Penggunaan resource stream (fopen) jauh lebih hemat memori dibanding file_get_contents(),
        // karena hanya memuat 1 baris ke RAM pada setiap iterasi.
        $handle = fopen($csvPath, 'r');
        if ($handle === false) {
            $this->command->error("Gagal membuka file CSV: {$csvPath}");
            return;
        }

        // Membaca baris pertama sebagai Header Kolom:
        $header = fgetcsv($handle);
        if (!$header) {
            $this->command->error("File CSV kosong atau header tidak valid.");
            fclose($handle);
            return;
        }

        // Membersihkan karakter tak kasat mata UTF-8 BOM (\x{FEFF}):
        // Banyak file CSV hasil ekspor Microsoft Excel menambahkan BOM di awal baris yang dapat
        // menyebabkan array key $header[0] menjadi "?code" alih-alih "code".
        $header[0] = preg_replace('/[\x{FEFF}]/u', '', $header[0]);

        // Penghitung statistik data yang diimpor:
        $importedCount = 0;
        $updatedCount = 0;

        // Membaca baris CSV satu per satu hingga akhir berkas (EOF):
        while (($row = fgetcsv($handle)) !== false) {
            // Validasi integritas kolom: Lewati baris jika jumlah kolom tidak cocok dengan header.
            if (count($row) !== count($header)) {
                continue;
            }

            // array_combine: Menggabungkan nama header sebagai array key dan nilai baris sebagai array value.
            $data = array_combine($header, $row);

            $code = trim($data['code'] ?? '');
            if (empty($code)) {
                continue;
            }

            // --------------------------------------------------------------------------------------
            // TAHAP TRANSFORMASI DATA (DATA TRANSFORMATION)
            // --------------------------------------------------------------------------------------
            
            // 1. Transformasi & Pemetaan Kategori ke ENUM yang Valid:
            $category = $this->adaptCategory($data['category'] ?? '', $data['name'] ?? '');

            // 2. Sanitasi Nama Fasilitas (maksimal 150 karakter):
            $name = $this->sanitizeName($data['name'] ?? '');

            // 3. Sanitasi Lokasi Lantai (maksimal 50 karakter sesuai skema SQL):
            $floorLocation = $this->sanitizeFloorLocation($data['floor_location'] ?? null);

            // 4. Decode Daftar Peralatan (JSON string -> array PHP):
            $equipmentRaw = $data['equipment'] ?? '[]';
            $equipment = json_decode($equipmentRaw, true);
            if (!is_array($equipment)) {
                $equipment = [];
            }

            // 5. Kapasitas & Status Operasional:
            $capacity = (int) ($data['capacity'] ?? 0);
            $status = in_array($data['status'] ?? '', ['aktif', 'dalam perbaikan', 'nonaktif'], true)
                ? $data['status']
                : 'aktif';

            // 6. Path Gambar, Deskripsi, & Gedung:
            $imagePath = trim($data['image_path'] ?? '');
            $description = trim($data['description'] ?? '');
            $building = trim($data['building'] ?? '');

            // --------------------------------------------------------------------------------------
            // TAHAP PEMUATAN DATA (DATA LOADING - IDEMPOTENT)
            // --------------------------------------------------------------------------------------
            $facility = Facility::updateOrCreate(
                ['code' => $code], // Kunci pencarian unik
                [
                    'name'           => $name,
                    'category'       => $category,
                    'building'       => $building,
                    'floor_location' => $floorLocation,
                    'capacity'       => $capacity,
                    'equipment'      => $equipment,
                    'description'    => $description,
                    'image_path'     => $imagePath,
                    'status'         => $status,
                ]
            );

            // Menghitung statistik eksekusi:
            if ($facility->wasRecentlyCreated) {
                $importedCount++;
            } else {
                $updatedCount++;
            }
        }

        // Menutup stream file untuk membebaskan file descriptor sistem operasi:
        fclose($handle);

        $this->command->info("Impor fasilitas UNDIP selesai: {$importedCount} data baru ditambahkan, {$updatedCount} data diperbarui.");
    }

    /**
     * Memetakan kategori non-enum (misal: 'ruang_kerja') ke nilai enum yang diizinkan skema database.
     * 
     * CARA KERJA & ALUR:
     * - Skema database hanya mengizinkan: 'auditorium', 'lab', 'kelas', 'olahraga', 'rapat'.
     * - Jika CSV berisi 'ruang_kerja', fungsi ini menganalisis nama ruangan untuk menentukan
     *   apakah ruangan tersebut berupa aula ('auditorium'), ruang rapat ('rapat'), atau kelas ('kelas').
     * 
     * @param  string  $category
     * @param  string  $name
     * @return string
     */
    protected function adaptCategory(string $category, string $name): string
    {
        $category = strtolower(trim($category));

        if ($category === 'ruang_kerja') {
            if (stripos($name, 'hall') !== false || stripos($name, 'teater') !== false) {
                return 'auditorium';
            }
            if (stripos($name, 'rapat') !== false) {
                return 'rapat';
            }
            return 'kelas';
        }

        $validCategories = ['auditorium', 'lab', 'kelas', 'olahraga', 'rapat'];
        return in_array($category, $validCategories, true) ? $category : 'kelas';
    }

    /**
     * Membersihkan teks nama fasilitas dan membatasi panjangnya maksimal 150 karakter.
     * 
     * CARA KERJA & ALUR:
     * - Menghilangkan string redundan eksternal seperti "(Akses via SIMLAB...)".
     * - Merapikan spasi ganda (`preg_replace('/\s+/', ' ', ...)`).
     * - Memotong string multibyte (`mb_substr`) ke 147 karakter + "..." jika melebihi panjang 150 karakter.
     * 
     * @param  string  $name
     * @return string
     */
    protected function sanitizeName(string $name): string
    {
        $clean = preg_replace('/\s*\(\s*Akses via SIMLAB.*?\)/i', '', $name);
        $clean = preg_replace('/\s+/', ' ', trim((string) $clean));

        if (mb_strlen($clean) > 150) {
            $clean = mb_substr($clean, 0, 147) . '...';
        }

        return $clean;
    }

    /**
     * Menyesuaikan panjang teks floor_location agar tidak melebihi kapasitas kolom VARCHAR(50).
     * 
     * CARA KERJA & ALUR:
     * - Menyederhanakan frasa panjang (contoh: mengganti 'Sayap ' dengan string kosong).
     * - Memastikan panjang teks tidak memicu SQL Error 1406 (Data too long for column).
     * 
     * @param  string|null  $floor
     * @return string|null
     */
    protected function sanitizeFloorLocation(?string $floor): ?string
    {
        if ($floor === null || trim($floor) === '') {
            return null;
        }

        $clean = str_ireplace('Sayap ', '', $floor);
        $clean = preg_replace('/\s+/', ' ', trim($clean));

        if (mb_strlen($clean) > 50) {
            $clean = mb_substr($clean, 0, 50);
        }

        return $clean;
    }
}

