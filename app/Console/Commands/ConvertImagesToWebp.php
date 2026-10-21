<?php

namespace App\Console\Commands;

use App\Models\PortfolioItem;
use App\Services\PortfolioCache;
use App\Services\WebpImages;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

#[Signature('images:convert-webp {--public-only : Chỉ chuyển ảnh tĩnh trong public} {--uploads-only : Chỉ chuyển ảnh đã tải lên từ admin} {--archive-originals : Chuyển ảnh public gốc vào thư mục backup riêng}')]
#[Description('Chuyển ảnh JPG/PNG sang WebP và cập nhật ảnh tác phẩm đã tải lên, giữ lại bản gốc để khôi phục.')]
class ConvertImagesToWebp extends Command
{
    public function handle(WebpImages $images): int
    {
        if ($this->option('public-only') && $this->option('uploads-only')) {
            $this->error('Chỉ được chọn một trong hai tùy chọn --public-only hoặc --uploads-only.');

            return self::FAILURE;
        }

        $converted = 0;
        $failed = 0;
        if (! $this->option('uploads-only')) {
            $publicImages = is_dir(public_path('images')) ? File::allFiles(public_path('images')) : [];
            foreach ([...$publicImages, ...File::files(public_path())] as $file) {
                if (! in_array(strtolower($file->getExtension()), ['jpg', 'jpeg', 'png'], true)) {
                    continue;
                }
                try {
                    $source = $file->getPathname();
                    $destination = preg_replace('/\.(jpe?g|png)$/i', '.webp', $source);
                    if (! is_file($destination) || filemtime($destination) < $file->getMTime()) {
                        $this->writePublicImage($destination, $images->encode($source));
                    }
                    $size = @getimagesize($destination);
                    if ($size === false || $size[2] !== IMAGETYPE_WEBP) {
                        throw new RuntimeException('Tệp đích không phải WebP hợp lệ.');
                    }
                    if ($this->option('archive-originals')) {
                        $relative = str_replace('\\', '/', $file->getRelativePathname());
                        $backup = 'webp-originals/public/'.$relative;
                        if (Storage::disk('local')->exists($backup) && hash_file('sha256', Storage::disk('local')->path($backup)) !== hash_file('sha256', $source)) {
                            throw new RuntimeException('Đã có một bản gốc khác trong backup. Giữ nguyên ảnh để tránh ghi đè.');
                        }
                        if (! Storage::disk('local')->put($backup, File::get($source))) {
                            throw new RuntimeException('Không thể sao lưu ảnh gốc.');
                        }
                        File::delete($source);
                    }
                    $converted++;
                } catch (Throwable $exception) {
                    $failed++;
                    $this->error($file->getFilename().': '.$exception->getMessage());
                }
            }
        }

        if (! $this->option('public-only')) {
            PortfolioItem::where('image_path', 'like', 'portfolio/%')->eachById(function (PortfolioItem $item) use ($images, &$converted, &$failed): void {
                if (! preg_match('/\.(jpe?g|png)$/i', $item->image_path)) {
                    return;
                }
                $newPath = null;
                try {
                    $newPath = $images->storePath(Storage::disk('local')->path($item->image_path));
                    DB::transaction(fn (): bool => $item->update(['image_path' => $newPath]));
                    $converted++;
                } catch (Throwable $exception) {
                    if ($newPath !== null) {
                        WebpImages::delete($newPath);
                    }
                    $failed++;
                    $this->error('Tác phẩm #'.$item->id.': '.$exception->getMessage());
                }
            });
            PortfolioCache::flush();
        }

        $this->info('Đã chuyển '.$converted.' ảnh sang WebP. Lỗi: '.$failed.'.');

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }

    private function writePublicImage(string $destination, string $contents): void
    {
        $temporary = tempnam(dirname($destination), '.webp-');
        if ($temporary === false) {
            throw new RuntimeException('Không thể tạo tệp chuyển đổi.');
        }
        try {
            if (File::put($temporary, $contents) !== strlen($contents) || ! rename($temporary, $destination)) {
                throw new RuntimeException('Không thể lưu ảnh WebP.');
            }
        } finally {
            File::delete($temporary);
        }
    }
}
