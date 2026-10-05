<?php

namespace Tests\Feature\Security;

use App\Models\User;

/**
 * Regression test untuk batas otorisasi yang SUDAH ditetapkan di kode.
 *
 * Sengaja TIDAK menguji hak role operator/supervisi atas konten: permission
 * matrix tersebut belum diputuskan (F-07) dan tidak boleh dikunci oleh test.
 */
class AuthorizationTest extends SecurityTestCase
{
    public function test_guest_is_redirected_from_admin_routes(): void
    {
        foreach (['/dashboard', '/news', '/announcements', '/ppid-requests', '/complaints', '/users', '/settings'] as $uri) {
            $this->get($uri)->assertRedirect('/login');
        }
    }

    public function test_guest_cannot_mutate_admin_resources(): void
    {
        $this->post('/news', [])->assertRedirect('/login');
        $this->delete('/news/bulk-delete', [])->assertRedirect('/login');
        $this->post('/users', [])->assertRedirect('/login');
    }

    public function test_public_registration_route_does_not_exist(): void
    {
        $this->get('/register')->assertNotFound();
        $this->post('/register', [])->assertNotFound();
    }

    public function test_non_superadmin_cannot_access_user_management(): void
    {
        $user = User::factory()->create(['role' => 'operator']);

        $this->actingAs($user)->get('/users')->assertForbidden();
        $this->actingAs($user)->get('/users/create')->assertForbidden();
        $this->actingAs($user)->post('/users', [
            'name' => 'X',
            'email' => 'x@example.com',
            'role' => 'operator',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertForbidden();

        $this->assertDatabaseMissing('users', ['email' => 'x@example.com']);
    }

    public function test_non_superadmin_cannot_change_site_logo(): void
    {
        $user = User::factory()->create(['role' => 'operator']);

        $this->actingAs($user)->delete(route('settings.logo.delete'))->assertForbidden();
    }

    public function test_superadmin_can_access_user_management(): void
    {
        $superadmin = User::factory()->create(['role' => 'superadmin']);

        $this->actingAs($superadmin)->get('/users')->assertOk();
    }

    public function test_api_write_endpoints_are_json_and_do_not_require_session(): void
    {
        $this->postJson('/api/complaints', [])
            ->assertStatus(422)
            ->assertHeaderMissing('Set-Cookie');
    }
}
