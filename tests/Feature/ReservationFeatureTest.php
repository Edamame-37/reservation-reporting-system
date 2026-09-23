<?php

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

    $this->facility = Facility::firstOrCreate(
        ['code' => 'AUD-H01'],
        [
            'name'           => 'Auditorium Utama B.J. Habibie',
            'category'       => 'auditorium',
            'building'       => 'Gedung Rektorat (Lt. 1 & 2)',
            'capacity'       => 450,
            'status'         => 'aktif',
        ]
    );
});

test('USR-01: Halaman formulir reservasi dapat diakses dan menampilkan fasilitas aktif', function () {
    $response = $this->actingAs($this->user)->get(route('user.reservation-form'));

    $response->assertStatus(200);
    $response->assertSee('Auditorium Utama B.J. Habibie');
    $response->assertSee('Pilih Fasilitas');
});

test('USR-01: Pengguna berhasil mengajukan reservasi dengan data valid (TC-USR01-01)', function () {
    $tomorrow = Carbon::tomorrow()->format('Y-m-d');

    $payload = [
        'facility_id'        => $this->facility->id,
        'reservation_date'   => $tomorrow,
        'start_time'         => '09:00',
        'end_time'           => '11:00',
        'participants_count' => 50,
        'purpose'            => 'Seminar Nasional Web Development dan Cloud Architecture',
    ];

    $response = $this->actingAs($this->user)->post(route('user.reservations.store'), $payload);

    $response->assertRedirect(route('user.reservation-history'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('reservations', [
        'user_id'          => $this->user->id,
        'facility_id'      => $this->facility->id,
        'reservation_date' => $tomorrow,
        'start_time'       => '09:00',
        'end_time'         => '11:00',
        'status'           => 'pending',
    ]);
});

test('USR-01: Validasi menolak waktu di luar jam operasional (07:00 - 20:00 WIB)', function () {
    $tomorrow = Carbon::tomorrow()->format('Y-m-d');

    // Jam mulai sebelum 07:00
    $payloadEarly = [
        'facility_id'      => $this->facility->id,
        'reservation_date' => $tomorrow,
        'start_time'       => '06:30',
        'end_time'         => '08:00',
        'purpose'          => 'Kegiatan subuh mahasiswa',
    ];

    $responseEarly = $this->actingAs($this->user)->post(route('user.reservations.store'), $payloadEarly);
    $responseEarly->assertSessionHasErrors('start_time');

    // Jam selesai sesudah 20:00
    $payloadLate = [
        'facility_id'      => $this->facility->id,
        'reservation_date' => $tomorrow,
        'start_time'       => '19:00',
        'end_time'         => '21:00',
        'purpose'          => 'Kegiatan malam hari',
    ];

    $responseLate = $this->actingAs($this->user)->post(route('user.reservations.store'), $payloadLate);
    $responseLate->assertSessionHasErrors('end_time');
});

test('USR-01: Validasi menolak menit yang bukan kelipatan 30 menit', function () {
    $tomorrow = Carbon::tomorrow()->format('Y-m-d');

    $payload = [
        'facility_id'      => $this->facility->id,
        'reservation_date' => $tomorrow,
        'start_time'       => '08:15',
        'end_time'         => '09:45',
        'purpose'          => 'Latihan paduan suara mahasiswa',
    ];

    $response = $this->actingAs($this->user)->post(route('user.reservations.store'), $payload);
    $response->assertSessionHasErrors(['start_time', 'end_time']);
});

test('USR-01: Validasi menolak bentrok jadwal dengan reservasi yang sudah approved (TC-USR01-07)', function () {
    $targetDate = Carbon::now()->addDays(5)->format('Y-m-d');

    // 1. Buat reservasi existing yang sudah disetujui (09:00 - 12:00)
    Reservation::create([
        'ticket_code'        => 'TKT-TEST-0001',
        'user_id'            => $this->user->id,
        'facility_id'        => $this->facility->id,
        'reservation_date'   => $targetDate,
        'start_time'         => '09:00',
        'end_time'           => '12:00',
        'total_slots'        => 6,
        'purpose'            => 'Acara Resmi Dies Natalis',
        'status'             => 'approved',
    ]);

    // 2. Coba ajukan reservasi kedua yang bertabrakan (10:00 - 13:00)
    $conflictPayload = [
        'facility_id'      => $this->facility->id,
        'reservation_date' => $targetDate,
        'start_time'       => '10:00',
        'end_time'         => '13:00',
        'purpose'          => 'Latihan Tari Organisasi',
    ];

    $response = $this->actingAs($this->user)->post(route('user.reservations.store'), $conflictPayload);
    $response->assertSessionHasErrors('start_time');
});

test('USR-02: Dasbor riwayat reservasi memuat data milik pengguna (TC-USR02-01)', function () {
    $response = $this->actingAs($this->user)->get(route('user.reservation-history'));

    $response->assertStatus(200);
    $response->assertSee('Riwayat Lengkap Permohonan Reservasi');
});

test('USR-02: Dasbor riwayat dapat disaring berdasarkan status dan pencarian (TC-USR02-03, TC-USR02-04)', function () {
    // 1. Filter status
    $responsePending = $this->actingAs($this->user)->get(route('user.reservation-history', ['status' => 'pending']));
    $responsePending->assertStatus(200);

    // 2. Pencarian kata kunci
    $responseSearch = $this->actingAs($this->user)->get(route('user.reservation-history', ['search' => 'Auditorium']));
    $responseSearch->assertStatus(200);
});
