<?php
/**
 * NAMA FILE    : ReportFeatureTest.php
 * FUNGSI       : Pengujian Otomatis Fitur USR-04 (Form Pelaporan Kerusakan Fasilitas)
 * DESKRIPSI    : Memvalidasi alur bisnis pengajuan tiket kerusakan sarpras, pembatasan upload foto maks 2MB (JPG/PNG), validasi deskripsi min 10 karakter, dan integritas penyimpanan data.
 * CARA KERJA   : Dieksekusi melalui framework Pest dengan isolasi transaksi basis data MySQL dan fake storage disk.
 */

use App\Models\DamageReport;
use App\Models\Facility;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    // 1. Inisialisasi atau temukan akun pengguna uji
    $this->user = User::firstOrCreate(
        ['email' => 'dimas@mahasiswa.ac.id'],
        [
            'name'            => 'Dimas Pratama',
            'password'        => bcrypt('password'),
            'identity_number' => '2110512044',
            'role'            => 'mahasiswa',
            'department'      => 'Informatika',
            'status'          => 'active',
        ]
    );

    // 2. Inisialisasi atau temukan fasilitas uji
    $this->facility = Facility::firstOrCreate(
        ['name' => 'Lab Komputasi Cloud & Jaringan'],
        [
            'code'            => 'FAC-LAB-01',
            'category'        => 'laboratorium',
            'building'        => 'Gedung C Lt. 2',
            'floor_location'  => 'Lantai 2 Ruang 204',
            'capacity'        => 35,
            'description'     => 'Laboratorium komputer spesifikasi tinggi.',
            'status'          => 'aktif',
        ]
    );

    Storage::fake('public');
});

test('USR-04: Form pelaporan kerusakan dapat diakses dan memuat data fasilitas aktif (TC-USR04-01)', function () {
    $response = $this->actingAs($this->user)->get(route('user.report-form'));

    $response->assertStatus(200);
    $response->assertSee('Pelaporan Kerusakan Sarana');
    $response->assertSee('Opsional');
    $response->assertSee('Wajib Diisi (Min. 10 karakter)');
    $response->assertSee($this->facility->name);
});

test('USR-04: Pengguna berhasil mengirim laporan kerusakan dengan foto bukti valid (TC-USR04-02)', function () {
    $fakeImage = UploadedFile::fake()->create('bukti_kerusakan.jpg', 500, 'image/jpeg'); // 500 KB < 2048 KB

    $payload = [
        'facility_id'      => $this->facility->id,
        'category'         => 'AC & Pendingin',
        'description'      => 'Unit AC di baris depan mati total dan mengeluarkan bau hangus.',
        'attachment_photo' => $fakeImage,
    ];

    $response = $this->actingAs($this->user)->post(route('user.reports.store'), $payload);

    $response->assertRedirect(route('user.report-history'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('damage_reports', [
        'user_id'     => $this->user->id,
        'facility_id' => $this->facility->id,
        'category'    => 'AC & Pendingin',
        'description' => 'Unit AC di baris depan mati total dan mengeluarkan bau hangus.',
        'status'      => 'baru',
    ]);
});

test('USR-04: Penolakan laporan jika ukuran berkas foto melebihi batas 2 MB (TC-USR04-03)', function () {
    $oversizedImage = UploadedFile::fake()->create('foto_besar.png', 3000, 'image/png'); // 3000 KB > 2048 KB

    $payload = [
        'facility_id'      => $this->facility->id,
        'category'         => 'Proyektor & Audio',
        'description'      => 'Kabel HDMI proyektor putus pada bagian konektor utama.',
        'attachment_photo' => $oversizedImage,
    ];

    $response = $this->actingAs($this->user)->post(route('user.reports.store'), $payload);

    $response->assertSessionHasErrors('attachment_photo');
});

test('USR-04: Penolakan laporan jika berkas yang diunggah bukan bertipe gambar (TC-USR04-04)', function () {
    $fakePdf = UploadedFile::fake()->create('dokumen.pdf', 500, 'application/pdf');

    $payload = [
        'facility_id'      => $this->facility->id,
        'category'         => 'Kelistrikan / Stop Kontak',
        'description'      => 'Stop kontak di dinding sebelah kiri korsleting.',
        'attachment_photo' => $fakePdf,
    ];

    $response = $this->actingAs($this->user)->post(route('user.reports.store'), $payload);

    $response->assertSessionHasErrors('attachment_photo');
});

test('USR-04: Penolakan laporan jika deskripsi terlalu pendek kurang dari 10 karakter (TC-USR04-05)', function () {
    $fakeImage = UploadedFile::fake()->create('bukti.jpg', 200, 'image/jpeg');

    $payload = [
        'facility_id'      => $this->facility->id,
        'category'         => 'Kebersihan',
        'description'      => 'rusak', // Hanya 5 karakter
        'attachment_photo' => $fakeImage,
    ];

    $response = $this->actingAs($this->user)->post(route('user.reports.store'), $payload);

    $response->assertSessionHasErrors('description');
});

test('USR-04: Penolakan laporan jika facility_id tidak terdaftar di sistem (TC-USR04-06)', function () {
    $fakeImage = UploadedFile::fake()->create('bukti.jpg', 200, 'image/jpeg');

    $payload = [
        'facility_id'      => 999999, // Tidak ada di DB
        'category'         => 'Lainnya',
        'description'      => 'Pintu ruangan tidak bisa dikunci dari dalam.',
        'attachment_photo' => $fakeImage,
    ];

    $response = $this->actingAs($this->user)->post(route('user.reports.store'), $payload);

    $response->assertSessionHasErrors('facility_id');
});

test('USR-04: Pengguna berhasil mengirim laporan tanpa memilih kategori / opsional (TC-USR04-07)', function () {
    $fakeImage = UploadedFile::fake()->create('bukti_kerusakan_opsional.jpg', 400, 'image/jpeg');

    $payload = [
        'facility_id'      => $this->facility->id,
        'category'         => null, // Sengaja dikosongkan oleh pengguna
        'description'      => 'Kaca jendela retak dan bergoyang saat tertiup angin kencang.',
        'attachment_photo' => $fakeImage,
    ];

    $response = $this->actingAs($this->user)->post(route('user.reports.store'), $payload);

    $response->assertRedirect(route('user.report-history'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('damage_reports', [
        'user_id'     => $this->user->id,
        'facility_id' => $this->facility->id,
        'category'    => 'Lainnya', // Otomatis fallback ke 'Lainnya'
        'description' => 'Kaca jendela retak dan bergoyang saat tertiup angin kencang.',
        'status'      => 'baru',
    ]);
});

test('USR-05: Pengguna dapat mengakses riwayat laporan dan melihat tiket miliknya (TC-USR05-01)', function () {
    $report = DamageReport::create([
        'report_code'        => 'RPT-20260924-TEST',
        'user_id'            => $this->user->id,
        'facility_id'        => $this->facility->id,
        'category'           => 'AC & Pendingin',
        'description'        => 'AC tidak dingin dan meneteskan air ke lantai.',
        'status'             => 'baru',
        'is_facility_locked' => false,
    ]);

    $response = $this->actingAs($this->user)->get(route('user.report-history'));

    $response->assertStatus(200);
    $response->assertSee('RPT-20260924-TEST');
    $response->assertSee('AC tidak dingin dan meneteskan air ke lantai.');
    $response->assertSee($this->facility->name);
    $response->assertSee('Laporan Baru');
});

test('USR-05: Pengguna tidak dapat melihat laporan milik pengguna lain (TC-USR05-02)', function () {
    $otherUser = User::firstOrCreate(
        ['email' => 'other_student@mahasiswa.ac.id'],
        [
            'name'            => 'Budi Mahasiswa',
            'password'        => bcrypt('password'),
            'identity_number' => '2110512999',
            'role'            => 'mahasiswa',
            'department'      => 'Sistem Informasi',
            'status'          => 'active',
        ]
    );

    $otherReport = DamageReport::create([
        'report_code'        => 'RPT-OTHER-SECRET',
        'user_id'            => $otherUser->id,
        'facility_id'        => $this->facility->id,
        'category'           => 'Kelistrikan / Stop Kontak',
        'description'        => 'Laporan rahasia pengguna lain.',
        'status'             => 'baru',
        'is_facility_locked' => false,
    ]);

    $response = $this->actingAs($this->user)->get(route('user.report-history'));

    $response->assertStatus(200);
    $response->assertDontSee('RPT-OTHER-SECRET');
    $response->assertDontSee('Laporan rahasia pengguna lain.');
});

test('USR-05: Pengguna dapat memfilter riwayat laporan berdasarkan tab status (TC-USR05-03)', function () {
    $reportBaru = DamageReport::create([
        'report_code'        => 'RPT-FILTER-BARU',
        'user_id'            => $this->user->id,
        'facility_id'        => $this->facility->id,
        'category'           => 'Proyektor & Audio',
        'description'        => 'Proyektor mati total di ruang lab.',
        'status'             => 'baru',
        'is_facility_locked' => false,
    ]);

    $reportSelesai = DamageReport::create([
        'report_code'        => 'RPT-FILTER-SELESAI',
        'user_id'            => $this->user->id,
        'facility_id'        => $this->facility->id,
        'category'           => 'Kebersihan',
        'description'        => 'Sampah menumpuk di dekat pintu masuk.',
        'status'             => 'selesai',
        'resolution_note'    => 'Sudah dibersihkan oleh tim kebersihan.',
        'is_facility_locked' => false,
    ]);

    // Request filter status = baru
    $responseBaru = $this->actingAs($this->user)->get(route('user.report-history', ['status' => 'baru']));
    $responseBaru->assertStatus(200);
    $responseBaru->assertSee('RPT-FILTER-BARU');
    $responseBaru->assertDontSee('RPT-FILTER-SELESAI');

    // Request filter status = selesai
    $responseSelesai = $this->actingAs($this->user)->get(route('user.report-history', ['status' => 'selesai']));
    $responseSelesai->assertStatus(200);
    $responseSelesai->assertSee('RPT-FILTER-SELESAI');
    $responseSelesai->assertSee('Sudah dibersihkan oleh tim kebersihan.');
    $responseSelesai->assertDontSee('RPT-FILTER-BARU');
});

test('USR-05: Pengguna dapat mencari riwayat laporan berdasarkan kata kunci (TC-USR05-04)', function () {
    $report1 = DamageReport::create([
        'report_code'        => 'RPT-SEARCH-KEY1',
        'user_id'            => $this->user->id,
        'facility_id'        => $this->facility->id,
        'category'           => 'Lainnya',
        'description'        => 'Engsel pintu ruangan patah dan berderit.',
        'status'             => 'diproses',
        'is_facility_locked' => false,
    ]);

    $report2 = DamageReport::create([
        'report_code'        => 'RPT-SEARCH-KEY2',
        'user_id'            => $this->user->id,
        'facility_id'        => $this->facility->id,
        'category'           => 'Jaringan & Internet',
        'description'        => 'Kabel LAN port 12 terputus.',
        'status'             => 'baru',
        'is_facility_locked' => false,
    ]);

    // Cari dengan kode tiket
    $resCode = $this->actingAs($this->user)->get(route('user.report-history', ['search' => 'KEY1']));
    $resCode->assertStatus(200);
    $resCode->assertSee('RPT-SEARCH-KEY1');
    $resCode->assertDontSee('RPT-SEARCH-KEY2');

    // Cari dengan teks deskripsi
    $resDesc = $this->actingAs($this->user)->get(route('user.report-history', ['search' => 'Kabel LAN']));
    $resDesc->assertStatus(200);
    $resDesc->assertSee('RPT-SEARCH-KEY2');
    $resDesc->assertDontSee('RPT-SEARCH-KEY1');
});

