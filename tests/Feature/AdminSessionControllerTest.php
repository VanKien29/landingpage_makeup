<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class AdminSessionControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_admin_can_sign_in_with_valid_credentials(): void
    {
        $admin = User::factory()->create(['email' => config('academy.admin_email'), 'password' => 'test-password']);

        $response = $this->post('/admin/login', ['email' => $admin->email, 'password' => 'test-password']);

        $response->assertRedirectToRoute('admin.portfolio');
        $this->assertAuthenticatedAs($admin);
    }

    public function test_wrong_password_does_not_authenticate(): void
    {
        $admin = User::factory()->create(['email' => config('academy.admin_email'), 'password' => 'test-password']);

        $response = $this->post('/admin/login', ['email' => $admin->email, 'password' => 'wrong-password']);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    public function test_ordinary_user_cannot_authenticate_as_admin(): void
    {
        $user = User::factory()->create(['password' => 'test-password']);

        $response = $this->post('/admin/login', ['email' => $user->email, 'password' => 'test-password']);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    public function test_logout_invalidates_authentication(): void
    {
        $admin = User::factory()->create(['email' => config('academy.admin_email')]);

        $response = $this->actingAs($admin)->post('/admin/logout');

        $response->assertRedirectToRoute('home');
        $this->assertGuest();
    }
}
