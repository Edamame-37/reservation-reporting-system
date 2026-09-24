<?php

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create([
        'status' => 'active',
        'role' => 'mahasiswa'
    ]);

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

test('users can not authenticate if status is pending', function () {
    $user = User::factory()->create([
        'status' => 'pending',
        'role' => 'mahasiswa'
    ]);

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertGuest();
    $response->assertSessionHasErrors(['email' => 'Akun Anda masih berstatus pending dan menunggu verifikasi dari Admin.']);
});

test('users can not authenticate if status is rejected', function () {
    $user = User::factory()->create([
        'status' => 'rejected',
        'role' => 'mahasiswa'
    ]);

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertGuest();
    $response->assertSessionHasErrors(['email' => 'Pendaftaran akun Anda ditolak oleh Admin.']);
});

test('active user with admin role is redirected to admin dashboard', function () {
    $user = User::factory()->create([
        'status' => 'active',
        'role' => 'admin'
    ]);
    
    // factory()->configure() will assign 'admin' spatie role if we configure it,
    // wait, our configure method currently only assigns 'pengguna'.
    // let's explicitly assign role here to be safe if configure doesn't handle 'admin' yet.
    $user->syncRoles(['admin']);

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('admin.dashboard', absolute: false));
});

test('active user with petugas role is redirected to petugas dashboard', function () {
    $user = User::factory()->create([
        'status' => 'active',
        'role' => 'petugas'
    ]);

    $user->syncRoles(['petugas']);

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('petugas.dashboard', absolute: false));
});

test('active user with mahasiswa role is redirected to user dashboard', function () {
    $user = User::factory()->create([
        'status' => 'active',
        'role' => 'mahasiswa' // Ini akan otomatis disinkronkan dengan 'pengguna' di factory configure
    ]);

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('user.dashboard', absolute: false));
});

test('users can logout', function () {
    $user = User::factory()->create([
        'status' => 'active',
        'role' => 'mahasiswa'
    ]);

    $response = $this->actingAs($user)->post('/logout');

    $this->assertGuest();
    $response->assertRedirect('/');
});
