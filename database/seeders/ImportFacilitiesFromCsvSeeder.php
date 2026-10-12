<?php

namespace Database\Seeders;

use App\Models\Facility;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class ImportFacilitiesFromCsvSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $csvPath = database_path('data/facilities_undip_lengkap.csv');

        if (!File::exists($csvPath)) {
            $this->command->error("File CSV tidak ditemukan di: {$csvPath}");
            return;
        }

        $handle = fopen($csvPath, 'r');
        if ($handle === false) {
            $this->command->error("Gagal membuka file CSV.");
            return;
        }

        // Baca baris header
        $header = fgetcsv($handle, 0, ',');
        if (!$header) {
            $this->command->error("Header CSV kosong.");
            fclose($handle);
            return;
        }

        // Bersihkan UTF-8 BOM jika ada
        $header[0] = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $header[0]);
        $header = array_map('trim', $header);

        $importedCount = 0;
        $updatedCount = 0;

        while (($row = fgetcsv($handle, 0, ',')) !== false) {
            if (count($row) < count($header)) {
                continue;
            }

            $data = array_combine($header, $row);

            $code = trim($data['code'] ?? '');
            if (empty($code)) {
                continue;
            }

            // Normalisasi image_path: hilangkan awalan /storage/ atau storage/
            $rawImagePath = trim($data['image_path'] ?? '');
            $cleanImagePath = preg_replace('#^/?storage/#', '', $rawImagePath);
            $cleanImagePath = ltrim($cleanImagePath, '/');

            // Normalisasi equipment JSON
            $rawEquipment = trim($data['equipment'] ?? '');
            $equipmentArray = json_decode($rawEquipment, true);
            if (!is_array($equipmentArray)) {
                $equipmentArray = [];
            }

            $facilityData = [
                'name'           => trim($data['name'] ?? ''),
                'source_origin'  => !empty($data['source_origin']) ? trim($data['source_origin']) : null,
                'category'       => trim($data['category'] ?? 'auditorium'),
                'building'       => trim($data['building'] ?? ''),
                'floor_location' => !empty($data['floor_location']) ? trim($data['floor_location']) : null,
                'capacity'       => (int) ($data['capacity'] ?? 0),
                'equipment'      => $equipmentArray,
                'description'    => !empty($data['description']) ? trim($data['description']) : null,
                'image_path'     => $cleanImagePath ?: null,
                'status'         => !empty($data['status']) ? trim($data['status']) : 'aktif',
            ];

            $facility = Facility::withTrashed()->where('code', $code)->first();

            if ($facility) {
                if ($facility->trashed()) {
                    $facility->restore();
                }
                $facility->update($facilityData);
                $updatedCount++;
            } else {
                Facility::create(array_merge(['code' => $code], $facilityData));
                $importedCount++;
            }
        }

        fclose($handle);

        $this->command->info("Selesai import data fasilitas!");
        $this->command->info("Baru ditambahkan: {$importedCount}");
        $this->command->info("Diperbarui: {$updatedCount}");
        $this->command->info("Total Fasilitas di Database saat ini: " . Facility::count());
    }
}
