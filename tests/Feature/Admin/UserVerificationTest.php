<?php

use App\Models\User;
use Spatie\Permission\Models\Role;

test('admin can view user management page with pending users', function () {
    $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    $admin->assignRole('admin');

    $pendingUser = User::factory()->create(['status' => 'pending']);

    $response = $this->actingAs($admin)->get('/admin/users');

    $response->assertStatus(200);
    $response->assertSee($pendingUser->name);
});

test('admin can verify pending user to active', function () {
    $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    $admin->assignRole('admin');

    $pendingUser = User::factory()->create(['status' => 'pending']);

    $response = $this->actingAs($admin)->post("/admin/users/{$pendingUser->id}/verify");

    $response->assertRedirect();
    $response->assertSessionHas('success');
    
    $pendingUser->refresh();
    $this->assertEquals('active', $pendingUser->status);
});

test('admin can reject pending user', function () {
    $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    $admin->assignRole('admin');

    $pendingUser = User::factory()->create(['status' => 'pending']);

    $response = $this->actingAs($admin)->post("/admin/users/{$pendingUser->id}/reject", [
        'reason' => 'invalid_ktm',
        'notes' => 'KTP buram'
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');
    
    $pendingUser->refresh();
    $this->assertEquals('rejected', $pendingUser->status);
    $this->assertStringContainsString('KTP buram', $pendingUser->rejection_reason);
});

test('non-admin cannot verify users', function () {
    $petugas = User::factory()->create(['role' => 'petugas', 'status' => 'active']);
    Role::firstOrCreate(['name' => 'petugas', 'guard_name' => 'web']);
    $petugas->assignRole('petugas');

    $pendingUser = User::factory()->create(['status' => 'pending']);

    $response = $this->actingAs($petugas)->post("/admin/users/{$pendingUser->id}/verify");

    $response->assertStatus(403);
});
