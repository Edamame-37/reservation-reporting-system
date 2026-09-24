<?php
/**
 * NAMA FILE    : FacilityMasterTest.php
 * FUNGSI       : Pengujian fitur otomatis untuk modul Master Data Fasilitas (ADM-03 / US-16)
 * DESKRIPSI    : Menguji seluruh siklus CRUD fasilitas, validasi integritas form request, manipulasi berkas gambar, status operasional, dan soft delete.
 * CARA KERJA   : Menjalankan skenario HTTP request menggunakan Pest/PHPUnit, memanfaatkan Storage::fake('public') dan RefreshDatabase.
 */

use App\Models\Facility;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function createTestCoverImage(string $name = 'cover.jpg'): UploadedFile
{
    $pngBase64 = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';
    return UploadedFile::fake()->createWithContent($name, base64_decode($pngBase64));
}

beforeEach(function () {
    // Siapkan akun admin terotentikasi jika otentikasi diterapkan
    $this->admin = User::factory()->create([
        'role'   => 'admin',
        'status' => 'active',
    ]);
});

test('admin can view facility master page with datasets and statistics', function () {
    Facility::create([
        'code'           => 'TEST-A01',
        'name'           => 'Ruang Kuliah Uji A',
        'category'       => 'kelas',
        'building'       => 'Gedung A',
        'floor_location' => 'Lantai 1',
        'capacity'       => 50,
        'equipment'      => ['Proyektor', 'Whiteboard'],
        'status'         => 'aktif',
    ]);

    Facility::create([
        'code'           => 'TEST-B01',
        'name'           => 'Lab Komputer Uji B',
        'category'       => 'lab',
        'building'       => 'Gedung B',
        'floor_location' => 'Lantai 2',
        'capacity'       => 30,
        'equipment'      => ['PC', 'Switch'],
        'status'         => 'dalam perbaikan',
    ]);

    $response = $this->actingAs($this->admin)->get('/admin/facility-master');

    $response->assertOk();
    $response->assertViewIs('admin.facility-master');
    $response->assertViewHas('facilities');
    $response->assertViewHas('totalCount', 2);
    $response->assertViewHas('activeCount', 1);
    $response->assertViewHas('maintenanceCount', 1);
    $response->assertViewHas('totalCapacity', 80);
    $response->assertViewHas('buildingCount', 2);
});

test('admin can filter facilities by category or search query via json or web', function () {
    Facility::create([
        'code'     => 'CLS-101',
        'name'     => 'Smart Classroom Alpha',
        'category' => 'kelas',
        'building' => 'Gedung Utama',
        'capacity' => 40,
        'status'   => 'aktif',
    ]);

    Facility::create([
        'code'     => 'LAB-202',
        'name'     => 'Laboratorium Robotika',
        'category' => 'lab',
        'building' => 'Gedung Sains',
        'capacity' => 25,
        'status'   => 'aktif',
    ]);

    $response = $this->actingAs($this->admin)->getJson('/admin/facilities?category=lab');

    $response->assertOk();
    $data = $response->json('data.data');
    expect(count($data))->toBe(1);
    expect($data[0]['code'])->toBe('LAB-202');
});

test('admin can store a new facility with cover image and valid data', function () {
    Storage::fake('public');

    $file = createTestCoverImage('auditorium.jpg');

    $payload = [
        'name'           => 'Auditorium Garuda Baru',
        'code'           => 'AUD-G01',
        'category'       => 'auditorium',
        'building'       => 'Gedung Serbaguna',
        'floor_location' => 'Lantai 1',
        'capacity'       => 300,
        'equipment'      => ['Sound System 5000W', 'Lighting Stage'],
        'description'    => 'Venue representatif untuk orasi dan wisuda.',
        'cover_image'    => $file,
        'status'         => 'aktif',
    ];

    $response = $this->actingAs($this->admin)->post('/admin/facilities', $payload);

    $response->assertRedirect('/admin/facility-master');
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('facilities', [
        'name'     => 'Auditorium Garuda Baru',
        'code'     => 'AUD-G01',
        'category' => 'auditorium',
        'capacity' => 300,
        'status'   => 'aktif',
    ]);

    $facility = Facility::where('code', 'AUD-G01')->first();
    expect($facility->image_path)->not->toBeNull();
    Storage::disk('public')->assertExists($facility->image_path);
});

test('store facility rejects duplicate name and duplicate code', function () {
    Facility::create([
        'code'     => 'DUP-01',
        'name'     => 'Ruang Serbaguna Asli',
        'category' => 'auditorium',
        'building' => 'Gedung A',
        'capacity' => 100,
        'status'   => 'aktif',
    ]);

    // Uji coba duplikasi nama
    $responseName = $this->actingAs($this->admin)->post('/admin/facilities', [
        'name'     => 'Ruang Serbaguna Asli',
        'code'     => 'DUP-02',
        'category' => 'auditorium',
        'building' => 'Gedung A',
        'capacity' => 100,
    ]);
    $responseName->assertSessionHasErrors(['name']);

    // Uji coba duplikasi kode
    $responseCode = $this->actingAs($this->admin)->post('/admin/facilities', [
        'name'     => 'Ruang Serbaguna Beda',
        'code'     => 'DUP-01',
        'category' => 'auditorium',
        'building' => 'Gedung A',
        'capacity' => 100,
    ]);
    $responseCode->assertSessionHasErrors(['code']);
});

test('admin can update facility data and old cover image is removed when replaced', function () {
    Storage::fake('public');

    // Buat file lama
    $oldFile = createTestCoverImage('old_cover.jpg');
    $oldPath = $oldFile->store('facilities', 'public');

    $facility = Facility::create([
        'code'        => 'UPD-01',
        'name'        => 'Ruang Seminar Lama',
        'category'    => 'kelas',
        'building'    => 'Gedung Kuliah B',
        'capacity'    => 50,
        'image_path'  => $oldPath,
        'status'      => 'aktif',
    ]);

    Storage::disk('public')->assertExists($oldPath);

    // Kirim file baru pengganti
    $newFile = createTestCoverImage('new_cover.jpg');

    $response = $this->actingAs($this->admin)->put("/admin/facilities/{$facility->id}", [
        'name'        => 'Ruang Seminar Terbarukan',
        'code'        => 'UPD-01', // Kode sama tidak boleh error (Rule::ignore)
        'category'    => 'kelas',
        'building'    => 'Gedung Kuliah B (Renovasi)',
        'capacity'    => 75,
        'cover_image' => $newFile,
        'status'      => 'aktif',
    ]);

    $response->assertRedirect('/admin/facility-master');
    $response->assertSessionHas('success');

    $facility->refresh();
    expect($facility->name)->toBe('Ruang Seminar Terbarukan');
    expect($facility->capacity)->toBe(75);

    // Pastikan file lama terhapus dari disk untuk efisiensi penyimpanan
    Storage::disk('public')->assertMissing($oldPath);
    // Pastikan file baru tersimpan
    Storage::disk('public')->assertExists($facility->image_path);
});

test('admin can soft delete a facility and verify status becomes nonaktif', function () {
    $facility = Facility::create([
        'code'     => 'DEL-01',
        'name'     => 'Ruang Yang Akan Dinonaktifkan',
        'category' => 'kelas',
        'building' => 'Gedung Lama',
        'capacity' => 20,
        'status'   => 'aktif',
    ]);

    $response = $this->actingAs($this->admin)->delete("/admin/facilities/{$facility->id}");

    $response->assertRedirect('/admin/facility-master');
    $response->assertSessionHas('success');

    // Pastikan status menjadi nonaktif
    $softDeleted = Facility::withTrashed()->find($facility->id);
    expect($softDeleted->status)->toBe('nonaktif');
    expect($softDeleted->trashed())->toBeTrue();
    $this->assertSoftDeleted('facilities', ['id' => $facility->id]);
});

test('admin can toggle facility operational status between aktif and dalam perbaikan', function () {
    $facility = Facility::create([
        'code'     => 'TOG-01',
        'name'     => 'Ruang Rapat Eksekutif',
        'category' => 'rapat',
        'building' => 'Gedung Rektorat',
        'capacity' => 20,
        'status'   => 'aktif',
    ]);

    // Kunci ruangan (aktif -> dalam perbaikan)
    $response1 = $this->actingAs($this->admin)->post("/admin/facilities/{$facility->id}/toggle");
    $response1->assertRedirect('/admin/facility-master');

    $facility->refresh();
    expect($facility->status)->toBe('dalam perbaikan');

    // Buka kunci kembali (dalam perbaikan -> aktif)
    $response2 = $this->actingAs($this->admin)->post("/admin/facilities/{$facility->id}/toggle");
    $response2->assertRedirect('/admin/facility-master');

    $facility->refresh();
    expect($facility->status)->toBe('aktif');
});

test('save endpoint alias handles modal submit for both create and update', function () {
    // 1. Simpan Baru via /admin/facilities/save
    $responseCreate = $this->actingAs($this->admin)->post('/admin/facilities/save', [
        'name'     => 'Aula Fleksibel 01',
        'code'     => 'AUL-F01',
        'category' => 'auditorium',
        'building' => 'Gedung Aula',
        'capacity' => 150,
        'status'   => 'aktif',
    ]);

    $responseCreate->assertRedirect('/admin/facility-master');
    $created = Facility::where('code', 'AUL-F01')->first();
    expect($created)->not->toBeNull();

    // 2. Pembaruan via /admin/facilities/save dengan menyertakan hidden ID
    $responseUpdate = $this->actingAs($this->admin)->post('/admin/facilities/save', [
        'id'       => $created->id,
        'name'     => 'Aula Fleksibel Terpadu 01',
        'code'     => 'AUL-F01',
        'category' => 'auditorium',
        'building' => 'Gedung Aula Timur',
        'capacity' => 200,
        'status'   => 'aktif',
    ]);

    $responseUpdate->assertRedirect('/admin/facility-master');
    $created->refresh();
    expect($created->name)->toBe('Aula Fleksibel Terpadu 01');
    expect($created->capacity)->toBe(200);
});
