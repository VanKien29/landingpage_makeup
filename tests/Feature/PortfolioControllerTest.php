<?php

namespace Tests\Feature;

use App\Models\PortfolioCategory;
use App\Models\PortfolioItem;
use App\Models\User;
use App\Services\PortfolioCache;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
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

    public function test_admin_can_upload_multiple_artworks_in_one_request(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['email' => config('academy.admin_email')]);

        $response = $this->actingAs($admin)->post('/admin/portfolio', [
            'title' => 'Editorial Set',
            'category' => 'creative',
            'images' => [UploadedFile::fake()->image('one.jpg'), UploadedFile::fake()->image('two.png')],
            'display_order' => 4,
        ]);

        $response->assertRedirectToRoute('admin.portfolio');
        $this->assertDatabaseCount('portfolio_items', 2);
        $this->assertCount(2, Storage::disk('local')->allFiles('portfolio'));
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

    public function test_admin_can_edit_artwork_metadata_and_hide_it_from_public_gallery(): void
    {
        $admin = User::factory()->create(['email' => config('academy.admin_email')]);
        $item = PortfolioItem::factory()->create(['category' => 'bridal', 'is_featured' => true]);

        $response = $this->actingAs($admin)->put(route('admin.portfolio.update', $item), [
            'title' => 'Updated Study',
            'category' => 'beauty',
            'alt_text' => 'Ảnh makeup cập nhật',
            'display_order' => 8,
        ]);

        $response->assertRedirectToRoute('admin.portfolio');
        $this->assertDatabaseHas('portfolio_items', ['id' => $item->id, 'title' => 'Updated Study', 'category' => 'beauty', 'is_featured' => false, 'display_order' => 8]);
    }

    public function test_multi_upload_saves_individual_metadata_for_each_image(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['email' => config('academy.admin_email')]);

        $response = $this->actingAs($admin)->post(route('admin.portfolio.store'), [
            'images' => [UploadedFile::fake()->image('one.jpg'), UploadedFile::fake()->image('two.jpg')],
            'items' => [
                ['title' => 'First Bridal', 'category' => 'bridal', 'alt_text' => 'First description', 'display_order' => 5, 'is_featured' => 1],
                ['title' => 'Second Beauty', 'category' => 'beauty', 'alt_text' => null, 'display_order' => 9, 'is_featured' => 0],
            ],
        ]);

        $response->assertRedirectToRoute('admin.portfolio');
        $this->assertDatabaseHas('portfolio_items', ['title' => 'First Bridal', 'category' => 'bridal', 'alt_text' => 'First description', 'display_order' => 5, 'is_featured' => true]);
        $this->assertDatabaseHas('portfolio_items', ['title' => 'Second Beauty', 'category' => 'beauty', 'alt_text' => 'Second Beauty', 'display_order' => 9, 'is_featured' => false]);
        $this->assertCount(2, Storage::disk('local')->allFiles('portfolio'));
    }

    public function test_upload_limit_rejects_more_than_twenty_images_without_saving_any(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['email' => config('academy.admin_email')]);

        $response = $this->actingAs($admin)->post(route('admin.portfolio.store'), [
            'title' => 'Too many images', 'category' => 'bridal', 'display_order' => 0,
            'images' => array_fill(0, 21, UploadedFile::fake()->image('photo.jpg')),
        ]);

        $response->assertSessionHasErrors(['images' => 'Mỗi lần chỉ được tải tối đa 20 ảnh.']);
        $this->assertDatabaseCount('portfolio_items', 0);
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_invalid_individual_metadata_rejects_the_entire_upload(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['email' => config('academy.admin_email')]);

        $response = $this->actingAs($admin)->post(route('admin.portfolio.store'), [
            'images' => [UploadedFile::fake()->image('one.jpg'), UploadedFile::fake()->image('two.jpg')],
            'items' => [
                ['title' => 'Valid', 'category' => 'bridal', 'display_order' => 1, 'is_featured' => 1],
                ['title' => '', 'category' => 'does-not-exist', 'display_order' => -1, 'is_featured' => 1],
            ],
        ]);

        $response->assertSessionHasErrors(['items.1.title', 'items.1.category', 'items.1.display_order']);
        $this->assertDatabaseCount('portfolio_items', 0);
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_upload_rejects_metadata_that_does_not_match_image_count(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['email' => config('academy.admin_email')]);

        $response = $this->actingAs($admin)->post(route('admin.portfolio.store'), [
            'images' => [UploadedFile::fake()->image('one.jpg'), UploadedFile::fake()->image('two.jpg')],
            'items' => [['title' => 'Missing second metadata', 'category' => 'bridal', 'display_order' => 1, 'is_featured' => 1]],
        ]);

        $response->assertSessionHasErrors('items');
        $this->assertDatabaseCount('portfolio_items', 0);
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_bulk_deletion_keeps_filters_and_returns_to_a_valid_page(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['email' => config('academy.admin_email')]);
        PortfolioItem::factory()->count(24)->create(['title' => 'Bridal keep', 'category' => 'bridal', 'is_featured' => false]);
        Storage::disk('local')->put('portfolio/last.jpg', 'image');
        $item = PortfolioItem::factory()->create(['title' => 'Bridal delete', 'category' => 'bridal', 'is_featured' => false, 'image_path' => 'portfolio/last.jpg']);
        $other = PortfolioItem::factory()->create(['title' => 'Unselected Beauty', 'category' => 'beauty']);
        $filters = ['search' => 'Bridal', 'category' => 'bridal', 'visibility' => 'hidden', 'sort' => 'newest', 'per_page' => 24, 'page' => 2];

        $response = $this->actingAs($admin)->post(route('admin.portfolio.bulk', $filters), ['action' => 'delete', 'ids' => [$item->id]]);

        $response->assertRedirect(route('admin.portfolio', [...$filters, 'page' => 1]));
        $this->assertModelMissing($item);
        $this->assertModelExists($other);
        Storage::disk('local')->assertMissing('portfolio/last.jpg');
    }

    public function test_bulk_visibility_changes_flush_the_public_cache(): void
    {
        $admin = User::factory()->create(['email' => config('academy.admin_email')]);
        $items = PortfolioItem::factory()->count(2)->create(['is_featured' => true]);
        PortfolioCache::items();
        $this->assertTrue(Cache::has(PortfolioCache::ITEMS_KEY));

        $response = $this->actingAs($admin)->post(route('admin.portfolio.bulk'), ['action' => 'hide', 'ids' => $items->modelKeys()]);

        $response->assertRedirectToRoute('admin.portfolio');
        $this->assertDatabaseCount('portfolio_items', 2);
        $this->assertSame(0, PortfolioItem::where('is_featured', true)->count());
        $this->assertFalse(Cache::has(PortfolioCache::ITEMS_KEY));
    }

    public function test_bulk_request_with_a_missing_id_does_not_delete_any_images(): void
    {
        $admin = User::factory()->create(['email' => config('academy.admin_email')]);
        $item = PortfolioItem::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.portfolio.bulk'), ['action' => 'delete', 'ids' => [$item->id, 999999]]);

        $response->assertSessionHasErrors('ids.1');
        $this->assertModelExists($item);
    }

    public function test_bulk_request_requires_a_selection_and_a_supported_action(): void
    {
        $admin = User::factory()->create(['email' => config('academy.admin_email')]);
        $item = PortfolioItem::factory()->create();

        $this->actingAs($admin)->post(route('admin.portfolio.bulk'), ['action' => 'delete'])->assertSessionHasErrors('ids');
        $this->post(route('admin.portfolio.bulk'), ['action' => 'invalid', 'ids' => [$item->id]])->assertSessionHasErrors('action');
        $this->assertModelExists($item);
    }

    public function test_guests_and_ordinary_users_cannot_bulk_modify_artwork_or_view_private_uploads(): void
    {
        $item = PortfolioItem::factory()->create(['is_featured' => false, 'image_path' => 'portfolio/hidden.jpg']);
        $this->post(route('admin.portfolio.bulk'), ['action' => 'delete', 'ids' => [$item->id]])->assertRedirectToRoute('login');
        $this->get(route('admin.portfolio.image', $item))->assertRedirectToRoute('login');
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('admin.portfolio.bulk'), ['action' => 'delete', 'ids' => [$item->id]])->assertForbidden();
        $this->actingAs($user)->get(route('admin.portfolio.image', $item))->assertForbidden();
        $this->assertModelExists($item);
    }

    public function test_admin_can_preview_a_hidden_upload_without_making_it_public(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('portfolio/hidden.jpg', 'image');
        $item = PortfolioItem::factory()->create(['is_featured' => false, 'image_path' => 'portfolio/hidden.jpg']);
        $admin = User::factory()->create(['email' => config('academy.admin_email')]);

        $this->actingAs($admin)->get(route('admin.portfolio.image', $item))->assertOk()->assertHeader('Cache-Control', 'no-store, private');
        $this->get(route('portfolio.image', $item))->assertNotFound();
    }

    public function test_search_category_and_visibility_filters_work_together(): void
    {
        $admin = User::factory()->create(['email' => config('academy.admin_email')]);
        PortfolioItem::factory()->create(['title' => 'Bridal Target', 'category' => 'bridal', 'is_featured' => false]);
        PortfolioItem::factory()->create(['title' => 'Wrong category', 'category' => 'beauty', 'is_featured' => false]);
        PortfolioItem::factory()->create(['title' => 'Bridal Visible', 'category' => 'bridal', 'is_featured' => true]);

        $response = $this->actingAs($admin)->get(route('admin.portfolio', ['search' => 'Bridal', 'category' => 'bridal', 'visibility' => 'hidden']));

        $response->assertOk()->assertSee('Bridal Target')->assertDontSee('Wrong category')->assertDontSee('Bridal Visible');
    }

    public function test_edit_and_delete_keep_the_current_search_and_category(): void
    {
        $admin = User::factory()->create(['email' => config('academy.admin_email')]);
        $item = PortfolioItem::factory()->create(['title' => 'Bridal original', 'category' => 'bridal']);
        $filters = ['search' => 'Bridal', 'category' => 'bridal'];

        $this->actingAs($admin)->put(route('admin.portfolio.update', ['portfolioItem' => $item, ...$filters]), ['title' => 'Bridal updated', 'category' => 'bridal', 'display_order' => 2])->assertRedirect(route('admin.portfolio', $filters));
        $this->delete(route('admin.portfolio.destroy', ['portfolioItem' => $item, ...$filters]))->assertRedirect(route('admin.portfolio', $filters));
        $this->assertModelMissing($item);
    }

    public function test_category_slug_update_keeps_existing_artwork_attached_and_deletion_is_blocked_when_in_use(): void
    {
        $admin = User::factory()->create(['email' => config('academy.admin_email')]);
        $category = PortfolioCategory::where('slug', 'bridal')->firstOrFail();
        $item = PortfolioItem::factory()->create(['category' => 'bridal']);

        $this->actingAs($admin)->put(route('admin.portfolio.categories.update', $category), ['name' => 'Bridal Beauty', 'slug' => 'bridal-beauty', 'display_order' => 10, 'is_active' => 1])->assertRedirectToRoute('admin.portfolio.categories');
        $this->assertDatabaseHas('portfolio_items', ['id' => $item->id, 'category' => 'bridal-beauty']);
        $this->actingAs($admin)->delete(route('admin.portfolio.categories.destroy', $category))->assertSessionHasErrors('category');
        $this->assertModelExists($category->fresh());
    }
}
