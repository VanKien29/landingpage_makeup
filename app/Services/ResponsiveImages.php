<?php

namespace App\Services;

final class ResponsiveImages
{
    public const WIDTHS = [480, 960, 1440];

    /** @return array{src: string, srcset: string, width: int, height: int} */
    public static function attributes(string $filename): array
    {
        $filename = WebpImages::publicFilename($filename);
        $manifest = self::manifest();
        $entry = $manifest[$filename] ?? null;
        $source = asset('images/'.$filename);
        if ($entry === null) {
            return ['src' => $source, 'srcset' => '', 'width' => 0, 'height' => 0];
        }
        $candidates = [];
        foreach ($entry['variants'] as $width => $path) {
            $candidates[] = asset('images/'.$path).' '.$width.'w';
        }
        $candidates[] = $source.' '.$entry['width'].'w';

        return [
            'src' => isset($entry['variants'][960]) ? asset('images/'.$entry['variants'][960]) : $source,
            'srcset' => implode(', ', $candidates),
            'width' => $entry['width'],
            'height' => $entry['height'],
        ];
    }

    /** @return array<string, array{width: int, height: int, variants: array<int, string>}> */
    private static function manifest(): array
    {
        return once(function (): array {
            $path = public_path('images/responsive/manifest.json');

            return is_file($path) ? json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR) : [];
        });
    }
}
