<?php

use App\Models\User;

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register and their status is pending without auto-login', function () {
    \Illuminate\Support\Facades\Storage::fake('public');
    \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'pengguna', 'guard_name' => 'web']);

    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'identifier' => '12345678',
        'role_type' => 'mahasiswa',
        'identity_proof' => \Illuminate\Http\UploadedFile::fake()->create('ktm.jpg', 100, 'image/jpeg'),
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    // Berdasarkan AUTH-01, pengguna di-redirect ke halaman login dengan pesan sukses (belum ada pesan sukses diuji, tapi pastikan redirect ke login)
    $response->assertRedirect(route('login', absolute: false));
    $response->assertSessionHas('success', 'Akun terdaftar, menunggu persetujuan Admin.');

    // Pastikan pengguna TIDAK login secara otomatis (Breeze default login, kita harus mematikannya)
    $this->assertGuest();

    $user = tap(User::where('email', 'test@example.com')->first(), function (User $user) {
        $this->assertEquals('pending', $user->status);
        $this->assertTrue($user->hasRole('pengguna'));
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($user->id_card_path);
    });
});
