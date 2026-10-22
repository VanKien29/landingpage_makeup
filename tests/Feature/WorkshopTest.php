<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WorkshopEvent;
use App\Models\WorkshopRegistration;
use Database\Seeders\WorkshopSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class WorkshopTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_the_back_shows_free_event_western_glam_and_guest_schedule(): void
    {
        $this->seed(WorkshopSeeder::class);

        $this->get('/workshop')->assertOk()->assertSee('THE BACK')->assertSee('SỰ TRỞ LẠI')->assertSee('100% FREE')->assertSee('Western Glam')->assertSee('workshop-western-glam-01.webp')->assertSee('workshop-bridal-beauty-01.webp')->assertDontSee('Thai Glam')->assertSee('Bridal Beauty')->assertSee('10:30')->assertSee('14:30')->assertSee('20.12.2026')->assertSee('20/12/2026')->assertSee('0 / 60 học viên')->assertSee('〒135-0033東京都江東区深川2-2-3-1F')->assertSee('google.com/maps')->assertSee('spacemarket.com/spaces/fukagawagarage')->assertDontSee('Eurasian Glam')->assertDontSee('¥5,000')->assertDontSee('06:00')->assertDontSee('20:00');
        $this->get('/workshop/the-art-of-elevation-2026')->assertRedirectToRoute('workshop.show');
        $this->get('/workshop-admin/the-art-of-elevation-2026')->assertRedirect('/workshop-admin/the-back');
    }

    public function test_legacy_workshop_page_redirects_permanently_to_short_url(): void
    {
        $this->currentWorkshop();

        $this->get('/workshop/the-back')->assertStatus(301)->assertRedirectToRoute('workshop.show');
        $this->get('/workshop/does-not-exist')->assertNotFound();
        $this->get('/')->assertOk()->assertSee('href="'.route('workshop.show').'"', false)->assertDontSee('/workshop/the-back');
        $this->get('/workshop')->assertOk()->assertSee('action="'.route('workshop.register').'"', false);
    }

    public function test_short_workshop_url_is_not_found_without_a_configured_event(): void
    {
        $this->get('/workshop')->assertNotFound();
    }

    public function test_other_published_events_do_not_replace_the_configured_workshop(): void
    {
        WorkshopEvent::factory()->create(['slug' => 'different-event']);

        $this->get('/workshop')->assertNotFound();
        $this->get('/workshop/different-event')->assertNotFound();
    }

    public function test_registration_redirects_to_short_workshop_url_without_a_slug_query(): void
    {
        $event = $this->currentWorkshop();

        $response = $this->post('/workshop/register', $this->payload());

        $response->assertRedirect(route('workshop.show').'#registration')->assertSessionHas('workshop_success');
        $this->assertDatabaseHas('workshop_registrations', ['workshop_event_id' => $event->id, 'email' => 'minhanh@example.com', 'status' => 'pending']);
    }

    public function test_registration_from_previously_opened_slug_url_still_works(): void
    {
        $event = $this->currentWorkshop();

        $this->postJson('/workshop/the-back/register', $this->payload())->assertCreated()->assertJsonPath('status', 'pending');

        $this->assertDatabaseHas('workshop_registrations', ['workshop_event_id' => $event->id, 'email' => 'minhanh@example.com']);
    }

    public function test_seeder_rebrands_existing_event_without_losing_registrations_or_date(): void
    {
        $event = $this->currentWorkshop(['slug' => 'the-art-of-elevation-2026', 'title' => 'THE ART OF ELEVATION 2026', 'capacity' => 40, 'ticket_price' => 5000, 'event_date' => '2026-12-20']);
        $entry = WorkshopRegistration::factory()->for($event)->create();

        $this->seed(WorkshopSeeder::class);

        $this->assertDatabaseHas('workshop_events', ['id' => $event->id, 'slug' => 'the-back', 'title' => 'THE BACK', 'capacity' => 60, 'ticket_price' => 0]);
        $this->assertSame('2026-12-20', $event->fresh()->event_date->format('Y-m-d'));
        $this->assertSame($event->id, $entry->fresh()->workshop_event_id);
        $this->assertDatabaseCount('workshop_events', 1);
    }

    public function test_registration_is_one_free_pending_seat_and_ignores_privileged_fields(): void
    {
        $event = $this->currentWorkshop();

        $response = $this->postJson(route('workshop.register'), [...$this->payload(), 'quantity' => 60, 'unit_price' => 5000, 'status' => 'confirmed', 'checked_in_at' => now()->toIso8601String()]);

        $response->assertCreated()->assertJsonPath('status', 'pending')->assertJsonStructure(['message', 'reference']);
        $this->assertDatabaseHas('workshop_registrations', ['workshop_event_id' => $event->id, 'quantity' => 1, 'unit_price' => 0, 'status' => 'pending', 'experience' => 'enthusiast', 'attendance_confirmed' => true, 'confirmed_at' => null, 'checked_in_at' => null, 'paid_at' => null]);
        $this->assertSame(0, $event->fresh()->confirmed_tickets);
        $this->assertNotNull(WorkshopRegistration::first()->consented_at);
    }

    public function test_required_fields_attendance_and_consent_fail_without_storing_data(): void
    {
        $event = $this->currentWorkshop();

        $this->postJson(route('workshop.register'), [])->assertUnprocessable()->assertJsonValidationErrors(['name', 'email', 'phone', 'social', 'experience', 'attendance', 'agree'])->assertJsonPath('errors.agree.0', 'Vui lòng đồng ý với chính sách đăng ký và xử lý dữ liệu cá nhân.');

        $this->assertDatabaseCount('workshop_registrations', 0);
    }

    public function test_invalid_japanese_phone_email_and_experience_are_rejected(): void
    {
        $event = $this->currentWorkshop();

        $this->postJson(route('workshop.register'), [...$this->payload(), 'email' => 'invalid', 'phone' => '+84901234567', 'experience' => 'other'])->assertUnprocessable()->assertJsonValidationErrors(['email', 'phone', 'experience'])->assertJsonPath('errors.phone.0', 'Vui lòng nhập số điện thoại tại Nhật Bản (bắt đầu bằng 0 hoặc +81).');

        $this->assertDatabaseCount('workshop_registrations', 0);
    }

    public function test_email_and_japanese_phone_are_normalized_and_duplicate_email_is_rejected(): void
    {
        $event = $this->currentWorkshop();

        $this->postJson(route('workshop.register'), [...$this->payload(), 'email' => 'MINHANH@EXAMPLE.COM', 'phone' => '+81 80-9819-4619'])->assertCreated();
        $this->postJson(route('workshop.register'), $this->payload())->assertUnprocessable()->assertJsonValidationErrors('email');

        $this->assertDatabaseHas('workshop_registrations', ['email' => 'minhanh@example.com', 'phone' => '+818098194619']);
        $this->assertDatabaseCount('workshop_registrations', 1);
    }

    public function test_full_workshop_keeps_form_open_and_places_new_applicant_on_waitlist(): void
    {
        $event = $this->currentWorkshop(['confirmed_tickets' => 60]);

        $this->get(route('workshop.show'))->assertOk()->assertSee('Đang tiếp nhận danh sách chờ')->assertDontSee('<fieldset disabled', false);
        $this->postJson(route('workshop.register'), $this->payload())->assertCreated()->assertJsonPath('status', 'waitlisted');

        $this->assertDatabaseHas('workshop_registrations', ['workshop_event_id' => $event->id, 'status' => 'waitlisted', 'quantity' => 1]);
        $this->assertSame(60, $event->fresh()->confirmed_tickets);
    }

    public function test_manual_closure_rejects_registration_even_with_capacity(): void
    {
        $event = $this->currentWorkshop(['registration_open' => false]);

        $this->postJson(route('workshop.register'), $this->payload())->assertUnprocessable()->assertJsonValidationErrors('attendance');

        $this->assertDatabaseCount('workshop_registrations', 0);
    }

    public function test_workshop_registration_form_is_rate_limited_per_email_and_ip(): void
    {
        $event = $this->currentWorkshop();

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->postJson(route('workshop.register'), $this->payload());
        }

        $this->postJson(route('workshop.register'), $this->payload())->assertStatus(429);
    }

    public function test_attendance_confirmation_is_required_even_when_other_fields_are_valid(): void
    {
        $event = $this->currentWorkshop();

        $this->postJson(route('workshop.register'), [...$this->payload(), 'attendance' => false])->assertUnprocessable()->assertJsonValidationErrors('attendance');

        $this->assertDatabaseCount('workshop_registrations', 0);
    }

    public function test_admin_approves_once_without_bank_details_and_can_check_in_attendee(): void
    {
        $event = $this->currentWorkshop();
        $entry = WorkshopRegistration::factory()->for($event)->create();
        $url = route('workshop.registration.update', [$event->slug, $entry]);
        $this->actingAs($this->admin());

        $this->patch($url, ['action' => 'confirm'])->assertRedirect()->assertSessionHasNoErrors();
        $this->patch($url, ['action' => 'confirm'])->assertRedirect()->assertSessionHasNoErrors();
        $this->patch($url, ['action' => 'check-in'])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertSame(1, $event->fresh()->confirmed_tickets);
        $this->assertDatabaseHas('workshop_registrations', ['id' => $entry->id, 'status' => 'confirmed', 'paid_at' => null, 'bank_reference' => null]);
        $this->assertNotNull($entry->fresh()->checked_in_at);
        $this->assertNotNull($entry->fresh()->confirmed_at);
    }

    public function test_approval_rechecks_capacity_and_cannot_exceed_sixty_confirmed_attendees(): void
    {
        $event = $this->currentWorkshop(['confirmed_tickets' => 59]);
        $first = WorkshopRegistration::factory()->for($event)->create();
        $second = WorkshopRegistration::factory()->for($event)->create(['status' => 'waitlisted']);
        $this->actingAs($this->admin());

        $this->patch(route('workshop.registration.update', [$event->slug, $first]), ['action' => 'confirm'])->assertSessionHasNoErrors();
        $this->patch(route('workshop.registration.update', [$event->slug, $second]), ['action' => 'confirm'])->assertSessionHasErrors('action');

        $this->assertSame(60, $event->fresh()->confirmed_tickets);
        $this->assertSame('waitlisted', $second->fresh()->status);
    }

    public function test_cancellation_releases_seat_and_waitlisted_person_can_be_approved(): void
    {
        $event = $this->currentWorkshop(['confirmed_tickets' => 60]);
        $entry = WorkshopRegistration::factory()->for($event)->create(['status' => 'confirmed', 'confirmed_at' => now(), 'checked_in_at' => now()]);
        $waiting = WorkshopRegistration::factory()->for($event)->create(['status' => 'waitlisted']);
        $this->actingAs($this->admin());

        $this->patch(route('workshop.registration.update', [$event->slug, $entry]), ['action' => 'cancel'])->assertSessionHasNoErrors();
        $this->assertSame(59, $event->fresh()->confirmed_tickets);
        $this->assertDatabaseHas('workshop_registrations', ['id' => $entry->id, 'status' => 'cancelled', 'checked_in_at' => null, 'confirmed_at' => null]);
        $this->patch(route('workshop.registration.update', [$event->slug, $waiting]), ['action' => 'confirm'])->assertSessionHasNoErrors();

        $this->assertSame(60, $event->fresh()->confirmed_tickets);
        $this->assertSame('confirmed', $waiting->fresh()->status);
    }

    public function test_admin_can_waitlist_registration_and_unconfirmed_person_cannot_check_in(): void
    {
        $event = $this->currentWorkshop();
        $entry = WorkshopRegistration::factory()->for($event)->create();
        $this->actingAs($this->admin());

        $this->patch(route('workshop.registration.update', [$event->slug, $entry]), ['action' => 'check-in'])->assertSessionHasErrors('action');
        $this->patch(route('workshop.registration.update', [$event->slug, $entry]), ['action' => 'waitlist'])->assertSessionHasNoErrors();

        $this->assertSame(0, $event->fresh()->confirmed_tickets);
        $this->assertSame('waitlisted', $entry->fresh()->status);
        $this->assertNull($entry->fresh()->checked_in_at);
    }

    public function test_admin_routes_require_authentication_and_academy_permission(): void
    {
        $event = $this->currentWorkshop();
        $entry = WorkshopRegistration::factory()->for($event)->create();

        $this->get(route('workshop.admin', $event->slug))->assertRedirectToRoute('login');
        $this->get(route('workshop.export', $event->slug))->assertRedirectToRoute('login');
        $this->patch(route('workshop.visibility.update', $event->slug), ['is_published' => false])->assertRedirectToRoute('login');
        $this->actingAs(User::factory()->create());
        $this->get(route('workshop.admin', $event->slug))->assertForbidden();
        $this->get(route('workshop.export', $event->slug))->assertForbidden();
        $this->patch(route('workshop.registration.update', [$event->slug, $entry]), ['action' => 'confirm'])->assertForbidden();
        $this->patch(route('workshop.update', $event->slug), [])->assertForbidden();
        $this->patch(route('workshop.visibility.update', $event->slug), ['is_published' => false])->assertForbidden();
        $this->assertTrue($event->fresh()->is_published);
    }

    public function test_registration_from_another_event_cannot_be_changed(): void
    {
        $event = $this->currentWorkshop();
        $entry = WorkshopRegistration::factory()->create();

        $this->actingAs($this->admin())->patch(route('workshop.registration.update', [$event->slug, $entry]), ['action' => 'cancel'])->assertNotFound();

        $this->assertSame('pending', $entry->fresh()->status);
    }

    public function test_admin_sees_escaped_data_and_csv_neutralizes_spreadsheet_formulas(): void
    {
        $event = $this->currentWorkshop();
        WorkshopRegistration::factory()->for($event)->create(['name' => '<script>alert(1)</script>', 'social' => '=HYPERLINK("bad")', 'phone' => '+818098194619']);
        $this->actingAs($this->admin());

        $this->get(route('workshop.admin', $event->slug))->assertOk()->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)->assertDontSee('<script>alert(1)</script>', false)->assertSee('Tổng số đơn đăng ký')->assertSee('Đơn trong danh sách chờ')->assertSee('Sức chứa tối đa')->assertDontSee('Cài đặt sự kiện, hình ảnh');
        $response = $this->get(route('workshop.export', $event->slug))->assertOk()->assertDownload($event->slug.'-guests.csv');

        $csv = $response->streamedContent();
        $this->assertStringContainsString("'=HYPERLINK", $csv);
        $this->assertStringContainsString("'+818098194619", $csv);
        $this->assertStringNotContainsString('Đơn giá', $csv);
    }

    public function test_admin_can_update_event_but_cannot_lower_capacity_exceed_sixty_or_set_price(): void
    {
        $event = $this->currentWorkshop(['confirmed_tickets' => 5]);
        $data = $event->only(['title', 'tagline', 'introduction', 'venue', 'capacity', 'hero_image', 'glam_image', 'bridal_image']);
        $this->actingAs($this->admin());

        $this->patch(route('workshop.update', $event->slug), [...$data, 'event_date' => '2026-12-20', 'venue' => 'Tokyo Studio', 'registration_open' => false, 'ticket_price' => 5000])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('workshop_events', ['id' => $event->id, 'venue' => 'Tokyo Studio', 'registration_open' => false, 'ticket_price' => 0]);
        $this->patch(route('workshop.update', $event->slug), [...$data, 'registration_open' => true, 'capacity' => 4])->assertSessionHasErrors('capacity');
        $this->patch(route('workshop.update', $event->slug), [...$data, 'registration_open' => true, 'capacity' => 61])->assertSessionHasErrors('capacity');
        $this->patch(route('workshop.update', $event->slug), [...$data, 'registration_open' => true, 'hero_image' => 'https://example.com/tracker.jpg'])->assertSessionHasErrors('hero_image');
    }

    public function test_admin_can_update_capacity_from_the_registrations_screen(): void
    {
        $event = $this->currentWorkshop(['capacity' => 40, 'confirmed_tickets' => 3]);
        $this->actingAs($this->admin());

        $this->patch(route('workshop.capacity.update', $event->slug), ['capacity' => 25])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('workshop_events', ['id' => $event->id, 'capacity' => 25]);
        $this->patch(route('workshop.capacity.update', $event->slug), ['capacity' => 2])->assertSessionHasErrors('capacity');
        $this->assertSame(25, $event->fresh()->capacity);
    }

    public function test_admin_can_hide_workshop_from_public_page_and_navigation(): void
    {
        $event = $this->currentWorkshop(['slug' => 'the-back', 'is_published' => false]);
        $this->assertNotNull($event);

        $this->get(route('workshop.show'))->assertNotFound();
        $this->get('/workshop/the-back')->assertNotFound();
        $this->postJson(route('workshop.register'), $this->payload())->assertUnprocessable()->assertJsonValidationErrors('attendance');
    }

    public function test_admin_visibility_button_controls_workshop_homepage_invitation_and_navigation_without_losing_registrations(): void
    {
        $event = $this->currentWorkshop(['slug' => 'the-back', 'confirmed_tickets' => 1]);
        $entry = WorkshopRegistration::factory()->for($event)->create(['status' => 'confirmed']);
        $this->actingAs($this->admin());

        $this->get(route('workshop.admin', $event->slug))->assertOk()->assertSee('<h1>Workshop</h1>', false)->assertSee('Ẩn workshop')->assertDontSee('Đơn đăng ký workshop');
        $this->from(route('workshop.admin', $event->slug))->patch(route('workshop.visibility.update', $event->slug), ['is_published' => false, 'capacity' => 1, 'confirmed_tickets' => 0, 'title' => 'Unexpected change'])
            ->assertRedirectToRoute('workshop.admin', $event->slug)->assertSessionHasNoErrors();

        $this->get('/')->assertOk()->assertDontSee('id="workshop"', false)->assertDontSee('class="nav-workshop"', false);
        $this->get(route('workshop.show'))->assertNotFound();
        $this->get(route('workshop.admin', $event->slug))->assertOk()->assertSee('Hiện workshop')->assertSee($entry->name);
        $this->assertDatabaseHas('workshop_events', ['id' => $event->id, 'is_published' => false, 'capacity' => 60, 'confirmed_tickets' => 1, 'title' => 'THE BACK']);
        $this->assertModelExists($entry);

        $this->patch(route('workshop.visibility.update', $event->slug), ['is_published' => true])->assertSessionHasNoErrors();

        $this->get('/')->assertOk()->assertSee('id="workshop"', false)->assertSee('class="nav-workshop"', false);
        $this->get(route('workshop.show'))->assertOk();
        $this->get(route('workshop.admin', $event->slug))->assertOk()->assertSee('Ẩn workshop');
        $this->assertTrue($event->fresh()->is_published);
        $this->assertSame('confirmed', $entry->fresh()->status);
    }

    #[TestWith([[]])]
    #[TestWith([['is_published' => 'invalid']])]
    public function test_workshop_visibility_rejects_missing_or_invalid_status_without_changes(array $data): void
    {
        $event = $this->currentWorkshop();

        $this->actingAs($this->admin())->patch(route('workshop.visibility.update', $event->slug), $data)->assertSessionHasErrors('is_published');

        $this->assertTrue($event->fresh()->is_published);
    }

    /** @param array<string, mixed> $attributes */
    private function currentWorkshop(array $attributes = []): WorkshopEvent
    {
        return WorkshopEvent::factory()->create(['slug' => config('workshop.slug'), ...$attributes]);
    }

    private function admin(): User
    {
        return User::factory()->create(['email' => config('academy.admin_email')]);
    }

    private function payload(): array
    {
        return ['name' => 'Nguyễn Minh Anh', 'phone' => '08098194619', 'email' => 'minhanh@example.com', 'social' => 'facebook.com/minhanh', 'experience' => 'enthusiast', 'attendance' => '1', 'agree' => '1'];
    }
}
