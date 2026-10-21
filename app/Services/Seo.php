<?php

namespace App\Services;

use App\Models\WorkshopEvent;
use Illuminate\Support\Str;

class Seo
{
    public function indexingEnabled(): bool
    {
        $host = parse_url(config('app.url'), PHP_URL_HOST);

        return app()->isProduction() && config('seo.indexing') && $host
            && ! in_array($host, ['localhost', '127.0.0.1', '::1', '[::1]'], true);
    }

    /** @param array<string, mixed> $parameters */
    public function url(string $route, array $parameters = []): string
    {
        return rtrim(config('app.url'), '/').route($route, $parameters, false);
    }

    public function image(string $path): string
    {
        return rtrim(config('app.asset_url') ?: config('app.url'), '/').'/'.ltrim($path, '/');
    }

    /** @return array<string, mixed> */
    public function home(): array
    {
        return $this->metadata(config('seo.title'), config('seo.description'), $this->url('home'));
    }

    /** @return array<string, mixed> */
    public function workshop(WorkshopEvent $workshop): array
    {
        $name = $this->text($workshop->title, 80) ?: 'Private Makeup Workshop';
        $title = $name.' | Workshop Makeup tại Tokyo — Văn Cường';
        $description = $this->text($workshop->introduction ?? '', 180)
            ?: 'Private Makeup Workshop tại Tokyo cùng Văn Cường Makeup & Academy. Khám phá Western Glam và Bridal Beauty, lịch trình và thông tin đăng ký.';
        $canonical = $this->url('workshop.show');
        $seo = $this->metadata($title, $description, $canonical);
        $seo['image'] = $this->image('images/'.config('workshop.banner_image'));
        $seo['image_alt'] = 'Private Makeup Workshop — '.$name;
        $seo['schema']['@graph'][] = [
            '@type' => 'BreadcrumbList', '@id' => $canonical.'#breadcrumb',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Trang chủ', 'item' => $this->url('home')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => $name, 'item' => $canonical],
            ],
        ];
        $seo['schema']['@graph'][2]['breadcrumb'] = ['@id' => $canonical.'#breadcrumb'];

        return $seo;
    }

    /** @return array<string, mixed> */
    public function policies(): array
    {
        return $this->metadata(
            'Nội quy & chính sách đào tạo | Văn Cường Makeup & Academy',
            'Bản dự thảo nội quy, lịch học, học phí, hỗ trợ học lại và thông tin đăng ký tư vấn của Văn Cường Makeup & Academy tại Nhật Bản.',
            $this->url('policies'), false,
        );
    }

    /** @return array<string, mixed> */
    private function metadata(string $title, string $description, string $canonical, bool $index = true): array
    {
        $home = $this->url('home');
        $name = config('seo.name');

        return [
            'title' => $title, 'description' => $description, 'canonical' => $canonical,
            'robots' => $index && $this->indexingEnabled() ? 'index, follow, max-image-preview:large' : 'noindex, follow',
            'image' => $this->image(config('seo.image')), 'image_alt' => $name,
            'schema' => ['@context' => 'https://schema.org', '@graph' => [
                ['@type' => 'Organization', '@id' => $home.'#organization', 'name' => $name, 'url' => $home,
                    'logo' => ['@type' => 'ImageObject', 'url' => $this->image('images/van-cuong-logo-transparent.webp')],
                    'sameAs' => [config('workshop.facebook')]],
                ['@type' => 'WebSite', '@id' => $home.'#website', 'url' => $home, 'name' => $name,
                    'inLanguage' => 'vi', 'publisher' => ['@id' => $home.'#organization']],
                ['@type' => 'WebPage', '@id' => $canonical.'#webpage', 'url' => $canonical,
                    'name' => $title, 'description' => $description, 'inLanguage' => 'vi',
                    'isPartOf' => ['@id' => $home.'#website'], 'about' => ['@id' => $home.'#organization']],
            ]],
        ];
    }

    private function text(string $value, int $limit): string
    {
        return Str::limit(trim(preg_replace('/\s+/u', ' ', strip_tags($value)) ?? ''), $limit);
    }
}
