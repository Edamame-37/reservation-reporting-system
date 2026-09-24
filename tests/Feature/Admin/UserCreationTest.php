<?php

use App\Models\User;
use Spatie\Permission\Models\Role;

test('admin can create petugas account', function () {
    $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'petugas', 'guard_name' => 'web']);
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->post('/admin/users', [
        'name' => 'Petugas Baru',
        'email' => 'petugas@kampus.ac.id',
        'password' => 'password123',
        'role' => 'petugas',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');
    
    $newUser = User::where('email', 'petugas@kampus.ac.id')->first();
    $this->assertNotNull($newUser);
    $this->assertEquals('active', $newUser->status);
    $this->assertTrue($newUser->hasRole('petugas'));
});

test('admin cannot create admin account', function () {
    $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->post('/admin/users', [
        'name' => 'Admin Bayangan',
        'email' => 'hacker@kampus.ac.id',
        'password' => 'password123',
        'role' => 'admin',
    ]);

    $response->assertSessionHasErrors(['role']);
    
    $newUser = User::where('email', 'hacker@kampus.ac.id')->first();
    $this->assertNull($newUser);
});

test('non-admin cannot create users', function () {
    $petugas = User::factory()->create(['role' => 'petugas', 'status' => 'active']);
    Role::firstOrCreate(['name' => 'petugas', 'guard_name' => 'web']);
    $petugas->assignRole('petugas');

    $response = $this->actingAs($petugas)->post('/admin/users', [
        'name' => 'Test',
        'email' => 'test@test.com',
        'password' => 'password',
        'role' => 'pengguna',
    ]);

    $response->assertStatus(403);
});
