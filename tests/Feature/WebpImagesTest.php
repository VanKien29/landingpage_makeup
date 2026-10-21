<?php

namespace Tests\Feature;

use App\Services\WebpImages;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class WebpImagesTest extends TestCase
{
    #[TestWith(['jpg'])]
    #[TestWith(['png'])]
    #[TestWith(['webp'])]
    public function test_supported_uploads_become_real_webp_without_changing_dimensions(string $extension): void
    {
        Storage::fake('local');
        $upload = UploadedFile::fake()->image('artwork.'.$extension, 160, 240);

        $path = (new WebpImages)->storeUpload($upload, 'image');

        Storage::disk('local')->assertExists($path);
        $this->assertStringEndsWith('.webp', $path);
        $size = getimagesize(Storage::disk('local')->path($path));
        $this->assertSame([160, 240, IMAGETYPE_WEBP], array_slice($size, 0, 3));
    }

    public function test_transparent_logo_retains_its_alpha_channel(): void
    {
        Storage::fake('local');
        $upload = UploadedFile::fake()->image('logo.png', 16, 16);
        $image = imagecreatetruecolor(16, 16);
        imagealphablending($image, false);
        imagesavealpha($image, true);
        imagefill($image, 0, 0, imagecolorallocatealpha($image, 0, 0, 0, 127));
        imagefilledrectangle($image, 5, 5, 10, 10, imagecolorallocatealpha($image, 255, 255, 255, 0));
        imagepng($image, $upload->getPathname());

        $path = (new WebpImages)->storeUpload($upload, 'image');

        $converted = imagecreatefromwebp(Storage::disk('local')->path($path));
        $this->assertSame(127, imagecolorsforindex($converted, imagecolorat($converted, 0, 0))['alpha']);
        $this->assertSame(0, imagecolorsforindex($converted, imagecolorat($converted, 8, 8))['alpha']);

        $preview = (new WebpImages)->encode($upload->getPathname(), 8);
        $this->assertSame([8, 8, IMAGETYPE_WEBP], array_slice(getimagesizefromstring($preview), 0, 3));
        $resized = imagecreatefromstring($preview);
        $this->assertSame(127, imagecolorsforindex($resized, imagecolorat($resized, 0, 0))['alpha']);
    }

    public function test_invalid_image_is_rejected_before_any_file_is_saved(): void
    {
        Storage::fake('local');
        $upload = UploadedFile::fake()->createWithContent('invalid.jpg', 'invalid image bytes');

        try {
            (new WebpImages)->storePath($upload->getPathname());
            $this->fail('An invalid image must not be stored.');
        } catch (InvalidArgumentException $exception) {
            $this->assertSame('Không đọc được ảnh. Vui lòng chọn ảnh JPG, PNG hoặc WebP hợp lệ.', $exception->getMessage());
        }

        Storage::disk('local')->assertEmpty();
    }

    public function test_jpeg_exif_orientation_is_applied_before_metadata_is_removed(): void
    {
        Storage::fake('local');
        $upload = UploadedFile::fake()->image('portrait.jpg', 40, 20);
        $exif = "Exif\0\0II".pack('vVvvvVVV', 42, 8, 1, 274, 3, 1, 6, 0);
        $jpeg = file_get_contents($upload->getPathname());
        file_put_contents($upload->getPathname(), substr($jpeg, 0, 2)."\xFF\xE1".pack('n', strlen($exif) + 2).$exif.substr($jpeg, 2));

        $path = (new WebpImages)->storeUpload($upload, 'image');

        $this->assertSame([20, 40, IMAGETYPE_WEBP], array_slice(getimagesize(Storage::disk('local')->path($path)), 0, 3));
    }
}
