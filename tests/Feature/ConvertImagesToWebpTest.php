<?php

namespace Tests\Feature;

use App\Models\PortfolioItem;
use App\Services\WebpImages;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ConvertImagesToWebpTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_public_conversion_preserves_originals_in_private_backup_and_resolves_legacy_filenames(): void
    {
        Storage::fake('local');
        Storage::fake('conversion');
        $publicPath = Storage::disk('conversion')->path('public');
        File::ensureDirectoryExists($publicPath.'/images');
        $this->app->usePublicPath($publicPath);
        $source = UploadedFile::fake()->image('logo.png', 80, 40);
        File::copy($source->getPathname(), $publicPath.'/images/logo.png');
        File::copy($source->getPathname(), $publicPath.'/favicon.png');

        $this->artisan('images:convert-webp', ['--public-only' => true, '--archive-originals' => true])->assertSuccessful();

        $this->assertFileExists($publicPath.'/images/logo.webp');
        $this->assertFileDoesNotExist($publicPath.'/images/logo.png');
        Storage::disk('local')->assertExists('webp-originals/public/logo.png');
        $this->assertFileExists($publicPath.'/favicon.webp');
        $this->assertFileDoesNotExist($publicPath.'/favicon.png');
        Storage::disk('local')->assertExists('webp-originals/public/favicon.png');
        $this->assertSame('logo.webp', WebpImages::publicFilename('logo.png'));
        $this->assertSame([80, 40, IMAGETYPE_WEBP], array_slice(getimagesize($publicPath.'/images/logo.webp'), 0, 3));
        $this->artisan('images:convert-webp', ['--public-only' => true, '--archive-originals' => true])->assertSuccessful();
    }

    public function test_existing_uploaded_images_are_converted_once_without_losing_the_original(): void
    {
        Storage::fake('local');
        $source = UploadedFile::fake()->image('old.jpg', 80, 120);
        Storage::disk('local')->put('portfolio/old.jpg', file_get_contents($source->getPathname()));
        $item = PortfolioItem::factory()->create(['image_path' => 'portfolio/old.jpg']);

        $this->artisan('images:convert-webp', ['--uploads-only' => true])->assertSuccessful();

        $converted = $item->fresh()->image_path;
        $this->assertStringEndsWith('.webp', $converted);
        Storage::disk('local')->assertExists([$converted, 'portfolio/old.jpg']);
        $this->assertSame(IMAGETYPE_WEBP, getimagesize(Storage::disk('local')->path($converted))[2]);
        $this->artisan('images:convert-webp', ['--uploads-only' => true])->assertSuccessful();
        $this->assertSame($converted, $item->fresh()->image_path);
    }

    public function test_failed_upload_conversion_keeps_the_original_record_and_file(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('portfolio/broken.jpg', 'not an image');
        $item = PortfolioItem::factory()->create(['image_path' => 'portfolio/broken.jpg']);

        $this->artisan('images:convert-webp', ['--uploads-only' => true])->assertFailed();

        $this->assertSame('portfolio/broken.jpg', $item->fresh()->image_path);
        Storage::disk('local')->assertExists('portfolio/broken.jpg');
        $this->assertCount(1, Storage::disk('local')->allFiles('portfolio'));
    }
}
