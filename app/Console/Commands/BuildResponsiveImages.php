<?php

namespace App\Console\Commands;

use App\Models\PortfolioItem;
use App\Services\ResponsiveImages;
use App\Services\WebpImages;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Throwable;

#[Signature('images:build-responsive {--public-only : Chỉ tạo ảnh tĩnh, không truy cập database}')]
#[Description('Tạo ảnh WebP theo kích thước hiển thị; giữ ảnh gốc cho chế độ xem chi tiết.')]
class BuildResponsiveImages extends Command
{
    public function handle(WebpImages $images): int
    {
        $directory = public_path('images/responsive');
        File::ensureDirectoryExists($directory);
        $manifest = [];
        $failed = 0;
        foreach (File::allFiles(public_path('images')) as $file) {
            $filename = str_replace('\\', '/', $file->getRelativePathname());
            if ($file->getExtension() !== 'webp' || str_starts_with($filename, 'responsive/') || preg_match('/-(640|960)\.webp$/', $filename)) {
                continue;
            }
            try {
                $size = @getimagesize($file->getPathname());
                if ($size === false || $size[2] !== IMAGETYPE_WEBP) {
                    throw new RuntimeException('Ảnh WebP không hợp lệ.');
                }
                $entry = ['width' => $size[0], 'height' => $size[1], 'variants' => []];
                $revision = substr(hash_file('sha256', $file->getPathname()), 0, 12);
                foreach (ResponsiveImages::WIDTHS as $width) {
                    if ($width >= $size[0]) {
                        continue;
                    }
                    $variant = 'responsive/'.pathinfo($filename, PATHINFO_FILENAME).'-'.$revision.'-'.$width.'.webp';
                    if (! is_file(public_path('images/'.$variant))) {
                        $this->writeAtomically(public_path('images/'.$variant), $images->encode($file->getPathname(), $width));
                    }
                    $entry['variants'][$width] = $variant;
                }
                $manifest[$filename] = $entry;
            } catch (Throwable $exception) {
                $failed++;
                $this->error($filename.': '.$exception->getMessage());
            }
        }
        if ($failed > 0) {
            return self::FAILURE;
        }
        $this->writeAtomically($directory.'/manifest.json', json_encode($manifest, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        if (! $this->option('public-only')) {
            PortfolioItem::where('image_path', 'like', 'portfolio/%')->eachById(function (PortfolioItem $item) use ($images, &$failed): void {
                try {
                    $images->preparePreviews($item->image_path);
                } catch (Throwable $exception) {
                    $failed++;
                    $this->error('Tác phẩm #'.$item->id.': '.$exception->getMessage());
                }
            });
        }
        $this->info('Đã tạo ảnh responsive cho '.count($manifest).' ảnh tĩnh. Lỗi: '.$failed.'.');

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }

    private function writeAtomically(string $path, string $contents): void
    {
        $temporary = tempnam(dirname($path), '.responsive-');
        if ($temporary === false) {
            throw new RuntimeException('Không thể tạo tệp ảnh responsive.');
        }
        try {
            if (File::put($temporary, $contents) !== strlen($contents) || ! rename($temporary, $path)) {
                throw new RuntimeException('Không thể lưu tệp ảnh responsive.');
            }
        } finally {
            File::delete($temporary);
        }
    }
}
