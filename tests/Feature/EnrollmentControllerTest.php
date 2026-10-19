<?php

namespace Tests\Feature;

use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class EnrollmentControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_valid_consultation_is_saved_without_accepting_privileged_fields(): void
    {
        $response = $this->postJson('/enrollment', ['name' => 'Nguyễn Hà', 'contact' => 'ha@example.com', 'message' => 'Tư vấn lịch học', 'agree' => '1', 'status' => 'enrolled', 'admin_notes' => 'Injected']);

        $response->assertOk()->assertJsonPath('success', true);
        $this->assertDatabaseHas('enrollments', ['name' => 'Nguyễn Hà', 'contact' => 'ha@example.com', 'message' => 'Tư vấn lịch học', 'status' => 'pending', 'admin_notes' => null, 'agreed_terms' => true]);
    }

    public function test_missing_fields_return_422_and_do_not_create_a_consultation(): void
    {
        $response = $this->postJson('/enrollment', []);

        $response->assertUnprocessable()->assertJsonValidationErrors(['name', 'contact', 'agree'])->assertJsonPath('message', 'Vui lòng nhập họ và tên của bạn.');
        $this->assertDatabaseCount('enrollments', 0);
    }

    public function test_missing_consent_returns_422_without_saving_contact_information(): void
    {
        $response = $this->postJson('/enrollment', ['name' => 'Nguyễn Hà', 'contact' => 'ha@example.com']);

        $response->assertUnprocessable()->assertJsonPath('message', 'Vui lòng xác nhận đồng ý với điều khoản và chính sách.');
        $this->assertDatabaseCount('enrollments', 0);
    }

    public function test_guests_cannot_read_contact_information(): void
    {
        $this->get('/admin/enrollments')->assertRedirectToRoute('login');
    }

    public function test_oversized_message_returns_a_vietnamese_error_without_saving(): void
    {
        $response = $this->postJson('/enrollment', ['name' => 'Nguyễn Hà', 'contact' => 'ha@example.com', 'agree' => '1', 'message' => str_repeat('a', 2001)]);

        $response->assertUnprocessable()->assertJsonPath('message', 'Lời nhắn không được vượt quá 2.000 ký tự.');
        $this->assertDatabaseCount('enrollments', 0);
    }

    public function test_non_admin_users_cannot_read_contact_information(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/admin/enrollments')->assertForbidden();
    }

    public function test_admin_sees_escaped_contact_information(): void
    {
        $admin = User::factory()->create(['email' => config('academy.admin_email')]);
        Enrollment::create(['name' => '<script>alert(1)</script>', 'contact' => '<img src=x onerror=alert(1)>', 'message' => '<iframe>test</iframe>', 'agreed_terms' => true]);

        $response = $this->actingAs($admin)->get('/admin/enrollments');

        $response->assertOk()->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)->assertDontSee('<script>alert(1)</script>', false)->assertSee('&lt;img', false)->assertDontSee('<img src=x', false)->assertSee('&lt;iframe&gt;', false);
    }
}
