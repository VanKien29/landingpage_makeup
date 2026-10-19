<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WorkshopEvent;
use App\Models\WorkshopRegistration;
use Database\Seeders\WorkshopSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class WorkshopTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_workshop_shows_eurasian_concept_and_unannounced_date_without_internal_schedule(): void
    {
        $this->seed(WorkshopSeeder::class);

        $response = $this->get('/workshop/the-art-of-elevation-2026');

        $response->assertOk()->assertSee('Eurasian Glam')->assertSee('Bridal Beauty')->assertSee('Đang cập nhật')->assertSee('Còn 40 / 40 vé')->assertDontSee('Thai Glam')->assertDontSee('06:00')->assertDontSee('20:00');
    }

    public function test_registration_saves_pending_and_server_price_and_ignores_privileged_fields(): void
    {
        $event = WorkshopEvent::factory()->create();

        $response = $this->postJson(route('workshop.register', $event->slug), [...$this->payload(), 'status' => 'confirmed', 'unit_price' => 1, 'quantity' => 2, 'checked_in_at' => now()->toIso8601String()]);

        $response->assertCreated()->assertJsonStructure(['message', 'reference']);
        $this->assertDatabaseHas('workshop_registrations', ['workshop_event_id' => $event->id, 'quantity' => 2, 'status' => 'pending', 'unit_price' => 5000, 'checked_in_at' => null]);
        $this->assertSame(0, $event->fresh()->confirmed_tickets);
        $this->assertNotNull(WorkshopRegistration::first()->consented_at);
    }

    public function test_required_fields_and_consent_fail_without_storing_personal_data(): void
    {
        $event = WorkshopEvent::factory()->create();

        $this->postJson(route('workshop.register', $event->slug), [])->assertUnprocessable()->assertJsonValidationErrors(['name', 'email', 'phone', 'social', 'quantity', 'agree'])->assertJsonPath('errors.agree.0', 'Vui lòng đồng ý với chính sách đăng ký và xử lý dữ liệu cá nhân.');

        $this->assertDatabaseCount('workshop_registrations', 0);
    }

    public function test_invalid_contact_and_ticket_fields_fail_validation(): void
    {
        $event = WorkshopEvent::factory()->create();

        $this->postJson(route('workshop.register', $event->slug), [...$this->payload(), 'email' => 'invalid', 'phone' => '<script>', 'quantity' => 0, 'notes' => str_repeat('a', 2001)])
            ->assertUnprocessable()->assertJsonValidationErrors(['email', 'phone', 'quantity', 'notes']);

        $this->assertDatabaseCount('workshop_registrations', 0);
    }

    public function test_full_workshop_disables_form_and_rejects_new_registration(): void
    {
        $event = WorkshopEvent::factory()->create(['confirmed_tickets' => 40]);

        $this->get(route('workshop.show', $event->slug))->assertOk()->assertSee('Đã đóng đăng ký')->assertSee('<fieldset disabled', false);
        $this->postJson(route('workshop.register', $event->slug), $this->payload())->assertUnprocessable()->assertJsonValidationErrors(['quantity']);

        $this->assertDatabaseCount('workshop_registrations', 0);
    }

    public function test_manual_closure_rejects_registration_even_with_capacity(): void
    {
        $event = WorkshopEvent::factory()->create(['registration_open' => false]);

        $this->postJson(route('workshop.register', $event->slug), $this->payload())->assertUnprocessable();

        $this->assertDatabaseCount('workshop_registrations', 0);
    }

    public function test_request_cannot_exceed_remaining_seats(): void
    {
        $event = WorkshopEvent::factory()->create(['confirmed_tickets' => 39]);

        $this->postJson(route('workshop.register', $event->slug), [...$this->payload(), 'quantity' => 2])->assertUnprocessable()->assertJsonValidationErrors(['quantity']);

        $this->assertDatabaseCount('workshop_registrations', 0);
    }

    public function test_admin_can_confirm_payment_once_and_check_in_confirmed_guests(): void
    {
        $admin = $this->admin();
        $event = WorkshopEvent::factory()->create();
        $entry = WorkshopRegistration::factory()->for($event)->create(['quantity' => 2]);
        $url = route('workshop.registration.update', [$event->slug, $entry]);

        $this->actingAs($admin)->patch($url, ['action' => 'confirm', 'bank_reference' => 'BANK-123'])->assertRedirect()->assertSessionHasNoErrors();
        $this->patch($url, ['action' => 'confirm', 'bank_reference' => 'BANK-123'])->assertRedirect()->assertSessionHasNoErrors();
        $this->patch($url, ['action' => 'check-in'])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertSame(2, $event->fresh()->confirmed_tickets);
        $this->assertDatabaseHas('workshop_registrations', ['id' => $entry->id, 'status' => 'confirmed', 'bank_reference' => 'BANK-123']);
        $this->assertNotNull($entry->fresh()->checked_in_at);
    }

    public function test_capacity_is_rechecked_when_confirming_pending_registrations(): void
    {
        $admin = $this->admin();
        $event = WorkshopEvent::factory()->create(['confirmed_tickets' => 39]);
        $first = WorkshopRegistration::factory()->for($event)->create();
        $second = WorkshopRegistration::factory()->for($event)->create();

        $this->actingAs($admin)->patch(route('workshop.registration.update', [$event->slug, $first]), ['action' => 'confirm', 'bank_reference' => 'TX-1'])->assertSessionHasNoErrors();
        $this->patch(route('workshop.registration.update', [$event->slug, $second]), ['action' => 'confirm', 'bank_reference' => 'TX-2'])->assertSessionHasErrors('action');

        $this->assertSame(40, $event->fresh()->confirmed_tickets);
        $this->assertSame('pending', $second->fresh()->status);
    }

    public function test_cancellation_releases_capacity_and_resets_check_in(): void
    {
        $event = WorkshopEvent::factory()->create(['confirmed_tickets' => 40]);
        $entry = WorkshopRegistration::factory()->for($event)->create(['status' => 'confirmed', 'quantity' => 2, 'paid_at' => now(), 'checked_in_at' => now()]);

        $this->actingAs($this->admin())->patch(route('workshop.registration.update', [$event->slug, $entry]), ['action' => 'cancel'])->assertSessionHasNoErrors();

        $this->assertSame(38, $event->fresh()->confirmed_tickets);
        $this->assertDatabaseHas('workshop_registrations', ['id' => $entry->id, 'status' => 'cancelled', 'checked_in_at' => null]);
    }

    public function test_unpaid_registration_cannot_be_checked_in_and_confirmation_requires_bank_reference(): void
    {
        $event = WorkshopEvent::factory()->create();
        $entry = WorkshopRegistration::factory()->for($event)->create();
        $this->actingAs($this->admin());

        $this->patch(route('workshop.registration.update', [$event->slug, $entry]), ['action' => 'check-in'])->assertSessionHasErrors('action');
        $this->patch(route('workshop.registration.update', [$event->slug, $entry]), ['action' => 'confirm'])->assertSessionHasErrors('bank_reference');

        $this->assertSame(0, $event->fresh()->confirmed_tickets);
        $this->assertNull($entry->fresh()->checked_in_at);
    }

    public function test_admin_routes_require_authentication_and_academy_permission(): void
    {
        $event = WorkshopEvent::factory()->create();
        $entry = WorkshopRegistration::factory()->for($event)->create();

        $this->get(route('workshop.admin', $event->slug))->assertRedirectToRoute('login');
        $this->get(route('workshop.export', $event->slug))->assertRedirectToRoute('login');
        $this->actingAs(User::factory()->create());
        $this->get(route('workshop.admin', $event->slug))->assertForbidden();
        $this->get(route('workshop.export', $event->slug))->assertForbidden();
        $this->patch(route('workshop.registration.update', [$event->slug, $entry]), ['action' => 'confirm', 'bank_reference' => 'BAD'])->assertForbidden();
        $this->patch(route('workshop.update', $event->slug), [])->assertForbidden();
    }

    public function test_registration_from_another_event_cannot_be_changed(): void
    {
        $event = WorkshopEvent::factory()->create();
        $entry = WorkshopRegistration::factory()->create();

        $this->actingAs($this->admin())->patch(route('workshop.registration.update', [$event->slug, $entry]), ['action' => 'cancel'])->assertNotFound();

        $this->assertSame('pending', $entry->fresh()->status);
    }

    public function test_admin_sees_escaped_personal_data_and_csv_neutralizes_spreadsheet_formulas(): void
    {
        $event = WorkshopEvent::factory()->create();
        WorkshopRegistration::factory()->for($event)->create(['name' => '<script>alert(1)</script>', 'social' => '=HYPERLINK("bad")', 'phone' => '+818098194619']);
        $this->actingAs($this->admin());

        $this->get(route('workshop.admin', $event->slug))->assertOk()->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)->assertDontSee('<script>alert(1)</script>', false);
        $response = $this->get(route('workshop.export', $event->slug))->assertOk()->assertDownload($event->slug.'-guests.csv');

        $csv = $response->streamedContent();
        $this->assertStringContainsString("'=HYPERLINK", $csv);
        $this->assertStringContainsString("'+818098194619", $csv);
    }

    public function test_admin_can_update_event_but_cannot_lower_capacity_or_use_external_image(): void
    {
        $event = WorkshopEvent::factory()->create(['confirmed_tickets' => 5]);
        $data = $event->only(['title', 'tagline', 'introduction', 'venue', 'capacity', 'ticket_price', 'hero_image', 'glam_image', 'bridal_image']);
        $this->actingAs($this->admin());

        $this->patch(route('workshop.update', $event->slug), [...$data, 'event_date' => '2026-12-20', 'venue' => 'Tokyo Studio', 'registration_open' => false])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('workshop_events', ['id' => $event->id, 'venue' => 'Tokyo Studio', 'registration_open' => false]);
        $this->patch(route('workshop.update', $event->slug), [...$data, 'registration_open' => true, 'capacity' => 4])->assertSessionHasErrors('capacity');
        $this->patch(route('workshop.update', $event->slug), [...$data, 'registration_open' => true, 'hero_image' => 'https://example.com/tracker.jpg'])->assertSessionHasErrors('hero_image');
    }

    private function admin(): User
    {
        return User::factory()->create(['email' => config('academy.admin_email')]);
    }

    private function payload(): array
    {
        return ['name' => 'Nguyễn Minh Anh', 'phone' => '08098194619', 'email' => 'minhanh@example.com', 'social' => 'instagram.com/minhanh', 'quantity' => 1, 'agree' => '1'];
    }
}
