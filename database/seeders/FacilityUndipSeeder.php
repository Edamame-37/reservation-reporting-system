<?php
/**
 * NAMA FILE    : FacilityUndipSeeder.php
 * FUNGSI       : Seeder pengimpor 103 master data fasilitas dan ruangan Universitas Diponegoro
 * DESKRIPSI    : Membaca dataset CSV facilities_undip_lengkap.csv, melakukan transformasi data adaptif (sanitasi nama, pemetaan kategori, pemendekan floor_location, dan decoding equipment JSON), lalu menyimpan record secara idempotent ke tabel facilities.
 * CARA KERJA   : Membuka file CSV dengan fgetcsv, memetakan kategori ruang_kerja ke enum valid, membatasi panjang kolom sesuai batasan skema MySQL, dan mengeksekusi Facility::updateOrCreate() berdasarkan kode unik fasilitas.
 */

namespace Database\Seeders;

use App\Models\Facility;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class FacilityUndipSeeder extends Seeder
{
    /**
     * FUNCTION/PROCEDURE : run()
     * KEGUNAAN           : Mengeksekusi pembacaan dataset CSV dan penanaman 103 fasilitas UNDIP ke basis data.
     * CARA KERJA         : Memvalidasi ketersediaan file CSV, mengiterasi setiap baris, membersihkan atribut teks, dan menyimpan data fasilitas via Eloquent model.
     */
    public function run(): void
    {
        $csvPath = base_path('new data for database/facilities_undip_lengkap.csv');

        if (!File::exists($csvPath)) {
            $this->command->error("File dataset tidak ditemukan di: {$csvPath}");
            return;
        }

        $handle = fopen($csvPath, 'r');
        if ($handle === false) {
            $this->command->error("Gagal membuka file CSV: {$csvPath}");
            return;
        }

        // Baca baris header CSV
        $header = fgetcsv($handle);
        if (!$header) {
            $this->command->error("File CSV kosong atau header tidak valid.");
            fclose($handle);
            return;
        }

        // Bersihkan BOM (Byte Order Mark) jika ada pada header pertama
        $header[0] = preg_replace('/[\x{FEFF}]/u', '', $header[0]);

        $importedCount = 0;
        $updatedCount = 0;

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) !== count($header)) {
                continue;
            }

            $data = array_combine($header, $row);

            $code = trim($data['code'] ?? '');
            if (empty($code)) {
                continue;
            }

            // 1. Transformasi & Pemetaan Kategori
            $category = $this->adaptCategory($data['category'] ?? '', $data['name'] ?? '');

            // 2. Sanitasi Nama Fasilitas (maks 150 karakter)
            $name = $this->sanitizeName($data['name'] ?? '');

            // 3. Sanitasi Lokasi Lantai (maks 50 karakter)
            $floorLocation = $this->sanitizeFloorLocation($data['floor_location'] ?? null);

            // 4. Decode Daftar Peralatan (JSON Array)
            $equipmentRaw = $data['equipment'] ?? '[]';
            $equipment = json_decode($equipmentRaw, true);
            if (!is_array($equipment)) {
                $equipment = [];
            }

            // 5. Kapasitas & Status
            $capacity = (int) ($data['capacity'] ?? 0);
            $status = in_array($data['status'] ?? '', ['aktif', 'dalam perbaikan', 'nonaktif'], true)
                ? $data['status']
                : 'aktif';

            // 6. Path Gambar & Deskripsi
            $imagePath = trim($data['image_path'] ?? '');
            $description = trim($data['description'] ?? '');
            $building = trim($data['building'] ?? '');

            // 7. Simpan secara Idempotent (updateOrCreate berdasarkan code unik)
            $facility = Facility::updateOrCreate(
                ['code' => $code],
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

            if ($facility->wasRecentlyCreated) {
                $importedCount++;
            } else {
                $updatedCount++;
            }
        }

        fclose($handle);

        $this->command->info("Impor fasilitas UNDIP selesai: {$importedCount} data baru ditambahkan, {$updatedCount} data diperbarui.");
    }

    /**
     * FUNCTION/PROCEDURE : adaptCategory()
     * KEGUNAAN           : Memetakan kategori non-enum (ruang_kerja) ke nilai enum yang diizinkan skema database.
     * CARA KERJA         : Mengevaluasi kata kunci nama fasilitas jika berkategori ruang_kerja, lalu mengembalikan kategori enum yang valid.
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
     * FUNCTION/PROCEDURE : sanitizeName()
     * KEGUNAAN           : Membersihkan teks nama fasilitas dan membatasi panjangnya maksimal 150 karakter.
     * CARA KERJA         : Menghapus sufiks redundan (SIMLAB) dan memotong string secara aman jika melebihi 150 karakter.
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
     * FUNCTION/PROCEDURE : sanitizeFloorLocation()
     * KEGUNAAN           : Menyesuaikan panjang teks floor_location agar tidak melebihi kapasitas kolom (50 karakter).
     * CARA KERJA         : Menyederhanakan penamaan sayap ruang dan memotong string secara aman ke maksimal 50 karakter.
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
