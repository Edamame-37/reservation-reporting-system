<?php

use Spatie\Permission\Models\Role;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Models\User;

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register and get pending status', function () {
    Storage::fake('public');
    
    // Pastikan role exist di database testing
    Role::firstOrCreate(['name' => 'pengguna', 'guard_name' => 'web']);

    $file = UploadedFile::fake()->create('ktm.pdf', 100, 'application/pdf');

    $response = $this->post('/register', [
        'name' => 'Siswa Baru',
        'identifier' => '2108561000',
        'role_type' => 'mahasiswa',
        'email' => 'siswa@kampus.ac.id',
        'password' => 'passwordaman',
        'password_confirmation' => 'passwordaman',
        'identity_proof' => $file,
    ]);

    // Memastikan user TIDAK login otomatis
    $this->assertGuest();
    
    // Memastikan dialihkan ke login dengan pesan sukses
    $response->assertRedirect(route('login', absolute: false));
    $response->assertSessionHas('success');

    // Memastikan data tersimpan sebagai pending
    $this->assertDatabaseHas('users', [
        'email' => 'siswa@kampus.ac.id',
        'status' => 'pending',
        'role' => 'mahasiswa'
    ]);

    $user = User::where('email', 'siswa@kampus.ac.id')->first();
    
    // Memastikan mendapat role Spatie 'pengguna'
    expect($user->hasRole('pengguna'))->toBeTrue();
    
    // Memastikan file tersimpan dengan benar
    Storage::disk('public')->assertExists($user->id_card_path);
});

test('registration fails on invalid inputs', function () {
    $response = $this->post('/register', [
        'name' => 'Test Gagal',
        'identifier' => '123',
        'role_type' => 'bukan_sivitas', // Error (not in:mahasiswa,dosen,staf)
        'email' => 'email_salah', // Error (not email format)
        'password' => 'pendek', // Error (min 8)
        'password_confirmation' => 'pendek',
        'identity_proof' => UploadedFile::fake()->create('bahaya.exe', 100, 'application/x-msdownload'), // Error (mimes)
    ]);

    $response->assertInvalid(['role_type', 'email', 'password', 'identity_proof']);
    $this->assertGuest();
});
