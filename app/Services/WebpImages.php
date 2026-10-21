<?php

namespace App\Services;

use GdImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

final class WebpImages
{
    public function encode(string $source, ?int $maxWidth = null): string
    {
        if ($maxWidth !== null && $maxWidth < 1) {
            throw new InvalidArgumentException('Chiều rộng ảnh phải lớn hơn 0.');
        }
        if (! function_exists('imagewebp') || ! (imagetypes() & IMG_WEBP)) {
            throw new RuntimeException('Máy chủ cần bật PHP GD có hỗ trợ WebP để chuyển đổi ảnh.');
        }

        $size = @getimagesize($source);
        if ($size === false || ! in_array($size[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP], true)) {
            throw new InvalidArgumentException('Không đọc được ảnh. Vui lòng chọn ảnh JPG, PNG hoặc WebP hợp lệ.');
        }
        if ($size[0] * $size[1] > 16000000) {
            throw new InvalidArgumentException('Ảnh vượt quá 16 megapixel. Vui lòng giảm kích thước trước khi tải lên.');
        }

        $image = match ($size[2]) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($source),
            IMAGETYPE_PNG => @imagecreatefrompng($source),
            IMAGETYPE_WEBP => @imagecreatefromwebp($source),
        };
        if (! $image instanceof GdImage) {
            throw new InvalidArgumentException('Không đọc được nội dung ảnh. Vui lòng chọn một ảnh khác.');
        }

        if ($size[2] === IMAGETYPE_JPEG && function_exists('exif_read_data')) {
            $orientation = (int) ((@exif_read_data($source))['Orientation'] ?? 1);
            if (in_array($orientation, [2, 4, 5, 7], true)) {
                imageflip($image, in_array($orientation, [4, 5], true) ? IMG_FLIP_VERTICAL : IMG_FLIP_HORIZONTAL);
            }
            $angle = match ($orientation) {
                3 => 180,
                5, 6, 7 => -90,
                8 => 90,
                default => 0,
            };
            if ($angle !== 0) {
                $rotated = imagerotate($image, $angle, 0);
                if (! $rotated instanceof GdImage) {
                    throw new InvalidArgumentException('Không thể xử lý hướng ảnh. Vui lòng chọn một ảnh khác.');
                }
                $image = $rotated;
            }
        }

        imagepalettetotruecolor($image);
        imagealphablending($image, false);
        imagesavealpha($image, true);
        if ($maxWidth !== null && imagesx($image) > $maxWidth) {
            $height = max(1, (int) round(imagesy($image) * $maxWidth / imagesx($image)));
            $resized = imagecreatetruecolor($maxWidth, $height);
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            imagefill($resized, 0, 0, imagecolorallocatealpha($resized, 0, 0, 0, 127));
            imagecopyresampled($resized, $image, 0, 0, 0, 0, $maxWidth, $height, imagesx($image), imagesy($image));
            $image = $resized;
        }
        ob_start();
        try {
            $success = imagewebp($image, null, $maxWidth === null ? 88 : 78);
            $contents = ob_get_contents();
        } finally {
            ob_end_clean();
        }
        $encodedSize = is_string($contents) ? @getimagesizefromstring($contents) : false;
        if (! $success || $encodedSize === false || $encodedSize[2] !== IMAGETYPE_WEBP) {
            throw new RuntimeException('Không thể tạo ảnh WebP. Vui lòng thử lại.');
        }

        return $contents;
    }

    public function storeUpload(UploadedFile $upload, string $field): string
    {
        try {
            return $this->storePath($upload->getPathname());
        } catch (InvalidArgumentException|RuntimeException $exception) {
            throw ValidationException::withMessages([$field => $exception->getMessage()]);
        }
    }

    public function storePath(string $source): string
    {
        $contents = $this->encode($source);
        $path = 'portfolio/'.Str::uuid().'.webp';
        try {
            if (! Storage::disk('local')->put($path, $contents)) {
                throw new RuntimeException('Không thể lưu ảnh WebP. Vui lòng kiểm tra quyền ghi thư mục storage.');
            }
            $this->preparePreviews($path);
        } catch (Throwable $exception) {
            self::delete($path);
            throw $exception;
        }

        return $path;
    }

    public function preparePreviews(string $path): void
    {
        $source = Storage::disk('local')->path($path);
        $size = @getimagesize($source);
        if ($size === false) {
            throw new InvalidArgumentException('Không đọc được ảnh để tạo bản xem trước.');
        }
        foreach (ResponsiveImages::WIDTHS as $width) {
            if ($width >= $size[0]) {
                continue;
            }
            $preview = self::previewPath($path, $width);
            if (! Storage::disk('local')->exists($preview) && ! Storage::disk('local')->put($preview, $this->encode($source, $width))) {
                throw new RuntimeException('Không thể lưu ảnh xem trước.');
            }
        }
    }

    public static function previewPath(string $path, int $width): string
    {
        return 'portfolio/previews/'.pathinfo($path, PATHINFO_FILENAME).'/'.$width.'.webp';
    }

    /** @param string|array<int, string> $paths */
    public static function delete(string|array $paths): void
    {
        foreach ((array) $paths as $path) {
            Storage::disk('local')->delete([$path, ...array_map(fn (int $width): string => self::previewPath($path, $width), ResponsiveImages::WIDTHS)]);
        }
    }

    public static function publicFilename(string $filename): string
    {
        $webp = preg_replace('/\.(jpe?g|png)$/i', '.webp', $filename);

        return $webp !== $filename && is_file(public_path('images/'.$webp)) ? $webp : $filename;
    }
}
