<?php
/**
 * NAMA FILE    : ReservationFeatureTest.php
 * FUNGSI       : Pengujian Otomatis Fitur USR-01 (Form Pengajuan Reservasi Ruangan Kampus)
 * DESKRIPSI    : Memvalidasi alur pemesanan ruang 3-tier (Gedung -> Lantai -> Ruang), validasi anti-bentrok jadwal (anti double-booking), dan batasan operasional 30 menit.
 * CARA KERJA   : Dieksekusi melalui framework Pest dengan isolasi database transaction.
 */

use App\Models\Facility;
use App\Models\Reservation;
use App\Models\User;
use Carbon\Carbon;

beforeEach(function () {
    // Pastikan terdapat data user dan fasilitas aktif
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
    $this->user->syncRoles(['pengguna']);

    // Ambil atau buat fasilitas terstruktur A104
    $this->roomA104 = Facility::firstOrCreate(
        ['code' => 'A104'],
        [
            'name'           => 'Ruang A104 (Ruang Kelas)',
            'category'       => 'kelas',
            'building'       => 'Gedung Kuliah Terpadu A',
            'floor_location' => 'Lantai 1',
            'capacity'       => 40,
            'equipment'      => ['AC Split 2PK', 'Proyektor HD & Screen'],
            'description'    => 'Ruang perkuliahan terstandarisasi di Gedung Kuliah Terpadu A Lantai 1.',
            'status'         => 'aktif',
        ]
    );
});

test('USR-01: Form pengajuan reservasi dapat diakses dan memuat data fasilitas (TC-USR01-01)', function () {
    $response = $this->actingAs($this->user)->get(route('user.reservation-form'));

    $response->assertStatus(200);
    $response->assertSee('Form Permohonan Reservasi Fasilitas');
    $response->assertSee('1. Gedung');
    $response->assertSee('2. Lantai');
    $response->assertSee('3. Ruangan');
    $response->assertSee('A104');
    $response->assertSee('Gedung Kuliah Terpadu A');
});

test('USR-01: Pengguna berhasil mengajukan reservasi ruangan A104 (TC-USR01-02)', function () {
    $date = Carbon::tomorrow()->format('Y-m-d');

    $payload = [
        'facility_id'        => $this->roomA104->id,
        'reservation_date'   => $date,
        'start_time'         => '09:00',
        'end_time'           => '11:00',
        'participants_count' => 35,
        'purpose'            => 'Kuliah Pengganti Pemrograman Web Lanjut Kelas A - Dosen Pengampu & Mahasiswa TI.',
    ];

    $response = $this->actingAs($this->user)->post(route('user.reservations.store'), $payload);

    $response->assertRedirect(route('user.reservation-history'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('reservations', [
        'user_id'          => $this->user->id,
        'facility_id'      => $this->roomA104->id,
        'reservation_date' => $date . ' 00:00:00',
        'start_time'       => '09:00',
        'end_time'         => '11:00',
        'status'           => 'pending',
    ]);
});

test('USR-01: Penolakan reservasi jika slot waktu jadwal bentrok dengan peminjaman approved (TC-USR01-03)', function () {
    $date = Carbon::tomorrow()->format('Y-m-d');

    // Buat reservasi yang telah disetujui (approved) pada jam 09:00 - 11:00
    Reservation::create([
        'ticket_code'        => 'TKT-' . date('Ymd') . '-APPR',
        'user_id'            => $this->user->id,
        'facility_id'        => $this->roomA104->id,
        'reservation_date'   => $date,
        'start_time'         => '09:00',
        'end_time'           => '11:00',
        'total_slots'        => 4,
        'purpose'            => 'Kegiatan sebelumnya yang sudah disetujui',
        'participants_count' => 30,
        'status'             => 'approved',
    ]);

    // Pengguna mencoba mengajukan di jam 10:00 - 12:00 (overlap)
    $payloadOverlap = [
        'facility_id'        => $this->roomA104->id,
        'reservation_date'   => $date,
        'start_time'         => '10:00',
        'end_time'           => '12:00',
        'participants_count' => 25,
        'purpose'            => 'Pengajuan bentrok pada rentang jam yang sama.',
    ];

    $response = $this->actingAs($this->user)->post(route('user.reservations.store'), $payloadOverlap);

    $response->assertSessionHasErrors('start_time');
});

test('USR-01: Penolakan jika waktu mulai lebih besar atau sama dengan waktu selesai (TC-USR01-04)', function () {
    $date = Carbon::tomorrow()->format('Y-m-d');

    $payload = [
        'facility_id'        => $this->roomA104->id,
        'reservation_date'   => $date,
        'start_time'         => '14:00',
        'end_time'           => '10:00', // Waktu selesai mendahului waktu mulai
        'participants_count' => 20,
        'purpose'            => 'Kegiatan dengan input waktu terbalik.',
    ];

    $response = $this->actingAs($this->user)->post(route('user.reservations.store'), $payload);

    $response->assertSessionHasErrors('end_time');
});

test('USR-01: Penolakan jika deskripsi tujuan kegiatan kurang dari 10 karakter (TC-USR01-05)', function () {
    $date = Carbon::tomorrow()->format('Y-m-d');

    $payload = [
        'facility_id'        => $this->roomA104->id,
        'reservation_date'   => $date,
        'start_time'         => '08:00',
        'end_time'           => '10:00',
        'participants_count' => 10,
        'purpose'            => 'Rapat', // Kurang dari 10 karakter
    ];

    $response = $this->actingAs($this->user)->post(route('user.reservations.store'), $payload);

    $response->assertSessionHasErrors('purpose');
});
