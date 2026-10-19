<?php

namespace Tests\Feature;

use App\Models\PortfolioItem;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PortfolioControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_guests_cannot_upload_artwork(): void
    {
        $this->post('/admin/portfolio')->assertRedirectToRoute('login');
        $this->assertDatabaseCount('portfolio_items', 0);
    }

    public function test_non_admin_users_cannot_delete_artwork(): void
    {
        $user = User::factory()->create();
        $item = PortfolioItem::factory()->create();

        $response = $this->actingAs($user)->delete(route('admin.portfolio.destroy', $item));

        $response->assertForbidden();
        $this->assertModelExists($item);
    }

    public function test_admin_can_upload_an_image_and_persist_gallery_metadata(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['email' => config('academy.admin_email')]);

        $response = $this->actingAs($admin)->post('/admin/portfolio', ['title' => 'Bridal Study', 'category' => 'bridal', 'image' => UploadedFile::fake()->image('bridal.jpg', 800, 1000), 'alt_text' => 'Trang điểm cô dâu', 'display_order' => 3]);

        $response->assertRedirectToRoute('admin.portfolio');
        $this->assertDatabaseHas('portfolio_items', ['title' => 'Bridal Study', 'category' => 'bridal', 'alt_text' => 'Trang điểm cô dâu', 'display_order' => 3, 'is_featured' => true]);
        Storage::disk('local')->assertExists(PortfolioItem::firstOrFail()->image_path);
    }

    public function test_invalid_uploads_are_rejected_without_files_or_records(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['email' => config('academy.admin_email')]);

        $response = $this->actingAs($admin)->post('/admin/portfolio', ['title' => 'Unsafe', 'category' => 'unknown', 'image' => UploadedFile::fake()->create('script.svg', 2, 'image/svg+xml'), 'display_order' => -1]);

        $response->assertSessionHasErrors(['category', 'image', 'display_order']);
        $this->assertDatabaseCount('portfolio_items', 0);
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_admin_deleting_an_uploaded_artwork_removes_the_file_and_record(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['email' => config('academy.admin_email')]);
        Storage::disk('local')->put('portfolio/test.jpg', 'image content');
        $item = PortfolioItem::factory()->create(['image_path' => 'portfolio/test.jpg']);

        $response = $this->actingAs($admin)->delete(route('admin.portfolio.destroy', $item));

        $response->assertRedirectToRoute('admin.portfolio');
        $this->assertModelMissing($item);
        Storage::disk('local')->assertMissing('portfolio/test.jpg');
    }

    public function test_visible_uploaded_image_can_be_served_with_nosniff_header(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('portfolio/test.jpg', 'image content');
        $item = PortfolioItem::factory()->create(['image_path' => 'portfolio/test.jpg']);

        $response = $this->get(route('portfolio.image', $item));

        $response->assertOk()->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    public function test_hidden_upload_cannot_be_read_by_public_url(): void
    {
        $item = PortfolioItem::factory()->create(['image_path' => 'portfolio/private.jpg', 'is_featured' => false]);

        $this->get(route('portfolio.image', $item))->assertNotFound();
    }
}
