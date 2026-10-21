<?php

namespace Tests\Feature;

use App\Models\PortfolioItem;
use App\Models\User;
use App\Services\WebpImages;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class ResponsiveImagesTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_static_image_build_preserves_originals_and_publishes_real_sized_webp_files(): void
    {
        Storage::fake('responsive-public');
        $publicPath = Storage::disk('responsive-public')->path('public');
        File::ensureDirectoryExists($publicPath.'/images');
        $this->app->usePublicPath($publicPath);
        $source = UploadedFile::fake()->image('photo.webp', 1600, 800);
        File::copy($source->getPathname(), $publicPath.'/images/photo.webp');
        $originalHash = hash_file('sha256', $source->getPathname());

        $this->artisan('images:build-responsive', ['--public-only' => true])->assertSuccessful();

        $manifest = json_decode(File::get($publicPath.'/images/responsive/manifest.json'), true);
        $this->assertSame($originalHash, hash_file('sha256', $publicPath.'/images/photo.webp'));
        $this->assertSame(1600, $manifest['photo.webp']['width']);
        $this->assertSame(800, $manifest['photo.webp']['height']);
        foreach ([480 => 240, 960 => 480, 1440 => 720] as $width => $height) {
            $path = $publicPath.'/images/'.$manifest['photo.webp']['variants'][$width];
            $this->assertSame([$width, $height, IMAGETYPE_WEBP], array_slice(getimagesize($path), 0, 3));
        }
        $this->artisan('images:build-responsive', ['--public-only' => true])->assertSuccessful();
        $this->assertSame($manifest, json_decode(File::get($publicPath.'/images/responsive/manifest.json'), true));
    }

    public function test_upload_previews_are_served_at_the_requested_size_and_the_lightbox_keeps_the_original(): void
    {
        Storage::fake('local');
        $path = (new WebpImages)->storeUpload(UploadedFile::fake()->image('large.jpg', 2000, 1000), 'image');
        $item = PortfolioItem::factory()->create(['image_path' => $path]);

        $response = $this->get($item->image_url.'&w=480');

        $response->assertOk()->assertHeader('Content-Type', 'image/webp')->assertHeader('Cache-Control', 'max-age=604800, public');
        $this->assertSame([480, 240, IMAGETYPE_WEBP], array_slice(getimagesize($response->baseResponse->getFile()->getPathname()), 0, 3));
        $this->assertSame([2000, 1000, IMAGETYPE_WEBP], array_slice(getimagesize(Storage::disk('local')->path($path)), 0, 3));
        $this->get('/')->assertOk()->assertSee('data-src="'.$item->image_url.'"', false)->assertSee('w=480', false)->assertSee(' 1440w', false);
    }

    public function test_small_uploads_are_not_upscaled_and_existing_images_without_previews_still_work(): void
    {
        Storage::fake('local');
        $path = (new WebpImages)->storeUpload(UploadedFile::fake()->image('small.jpg', 200, 100), 'image');
        $item = PortfolioItem::factory()->create(['image_path' => $path]);

        $response = $this->get($item->image_url.'&w=480');

        $response->assertOk();
        $this->assertSame([200, 100], array_slice(getimagesize($response->baseResponse->getFile()->getPathname()), 0, 2));
        $this->assertCount(1, Storage::disk('local')->allFiles('portfolio'));
        $this->assertSame('', $item->image_srcset);
    }

    #[TestWith(['w=999'])]
    #[TestWith(['w[]=480'])]
    #[TestWith(['w=../../secret'])]
    public function test_unknown_preview_sizes_cannot_trigger_arbitrary_image_processing(string $query): void
    {
        Storage::fake('local');
        $path = (new WebpImages)->storeUpload(UploadedFile::fake()->image('photo.jpg'), 'image');
        $item = PortfolioItem::factory()->create(['image_path' => $path]);

        $this->get($item->image_url.'&'.$query)->assertNotFound();
    }

    public function test_hidden_artwork_previews_are_not_public(): void
    {
        Storage::fake('local');
        $path = (new WebpImages)->storeUpload(UploadedFile::fake()->image('photo.jpg', 800, 1000), 'image');
        $item = PortfolioItem::factory()->create(['image_path' => $path, 'is_featured' => false]);

        $this->get($item->image_url.'&w=480')->assertNotFound();
    }

    public function test_replacing_and_deleting_artwork_removes_its_previews_and_refreshes_the_gallery(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['email' => config('academy.admin_email')]);
        $path = (new WebpImages)->storeUpload(UploadedFile::fake()->image('old.jpg', 1600, 800), 'image');
        $item = PortfolioItem::factory()->create(['image_path' => $path]);
        $oldPreviewUrl = $item->image_url.'&w=480';
        $this->get('/')->assertOk();

        $this->actingAs($admin)->put(route('admin.portfolio.update', $item), ['title' => 'Updated work', 'category' => 'bridal', 'image' => UploadedFile::fake()->image('new.png', 1600, 800), 'display_order' => 1, 'is_featured' => true])->assertRedirect();

        $newPath = $item->fresh()->image_path;
        Storage::disk('local')->assertMissing([$path, WebpImages::previewPath($path, 480), WebpImages::previewPath($path, 960), WebpImages::previewPath($path, 1440)]);
        Storage::disk('local')->assertExists(WebpImages::previewPath($newPath, 480));
        $this->get('/')->assertOk()->assertSee('Updated work')->assertDontSee($oldPreviewUrl);
        $this->delete(route('admin.portfolio.destroy', $item))->assertRedirect();
        $this->assertModelMissing($item);
        Storage::disk('local')->assertEmpty();
    }

    public function test_existing_uploads_can_gain_previews_without_changing_their_original_file_or_record(): void
    {
        Storage::fake('local');
        Storage::fake('responsive-public');
        $publicPath = Storage::disk('responsive-public')->path('public');
        File::ensureDirectoryExists($publicPath.'/images');
        $this->app->usePublicPath($publicPath);
        $source = UploadedFile::fake()->image('old.jpg', 1200, 800);
        Storage::disk('local')->put('portfolio/old.jpg', File::get($source->getPathname()));
        $item = PortfolioItem::factory()->create(['image_path' => 'portfolio/old.jpg']);
        $originalHash = hash_file('sha256', $source->getPathname());

        $this->artisan('images:build-responsive')->assertSuccessful();

        $this->assertSame('portfolio/old.jpg', $item->fresh()->image_path);
        $this->assertSame($originalHash, hash_file('sha256', Storage::disk('local')->path('portfolio/old.jpg')));
        Storage::disk('local')->assertExists(['portfolio/previews/old/480.webp', 'portfolio/previews/old/960.webp']);
    }

    public function test_failed_static_image_build_keeps_the_previous_manifest(): void
    {
        Storage::fake('responsive-public');
        $publicPath = Storage::disk('responsive-public')->path('public');
        File::ensureDirectoryExists($publicPath.'/images/responsive');
        $this->app->usePublicPath($publicPath);
        File::put($publicPath.'/images/responsive/manifest.json', '{"previous":true}');
        File::put($publicPath.'/images/broken.webp', 'not an image');

        $this->artisan('images:build-responsive', ['--public-only' => true])->assertFailed();

        $this->assertSame('{"previous":true}', File::get($publicPath.'/images/responsive/manifest.json'));
    }
}
