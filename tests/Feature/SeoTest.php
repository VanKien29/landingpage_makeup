<?php

namespace Tests\Feature;

use App\Models\PortfolioItem;
use App\Models\User;
use App\Models\WorkshopEvent;
use DOMDocument;
use DOMXPath;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class SeoTest extends TestCase
{
    use LazilyRefreshDatabase;

    private function production(string $environment = 'production'): void
    {
        PortfolioItem::query()->exists();
        $this->app->instance('env', $environment);
        config(['app.url' => 'https://academy.example', 'seo.indexing' => true, 'seo.canonical_redirects' => true]);
    }

    private function html(string $content): DOMXPath
    {
        $document = new DOMDocument;
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="UTF-8">'.$content);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        return new DOMXPath($document);
    }

    public function test_home_has_one_complete_metadata_set_and_query_free_canonical(): void
    {
        $this->production();

        $response = $this->get('https://academy.example/?utm_source=facebook&category=bridal&page=2');

        $response->assertOk()->assertHeaderMissing('X-Robots-Tag');
        $html = $this->html($response->getContent());
        foreach (['//title', '//meta[@name="description"]', '//meta[@name="robots"]', '//link[@rel="canonical"]', '//meta[@property="og:title"]', '//meta[@name="twitter:card"]', '//h1', '//script[@type="application/ld+json"]'] as $selector) {
            $this->assertSame(1, $html->query($selector)->length, $selector);
        }
        $this->assertSame('https://academy.example/', $html->evaluate('string(//link[@rel="canonical"]/@href)'));
        $this->assertSame('https://academy.example/', $html->evaluate('string(//meta[@property="og:url"]/@content)'));
        $this->assertSame('index, follow, max-image-preview:large', $html->evaluate('string(//meta[@name="robots"]/@content)'));
        $this->assertSame('vi', $html->evaluate('string(/html/@lang)'));
        $schema = json_decode($html->evaluate('string(//script[@type="application/ld+json"])'), true, flags: JSON_THROW_ON_ERROR);
        $this->assertSame(['Organization', 'WebSite', 'WebPage'], array_column($schema['@graph'], '@type'));
        $this->assertSame('https://academy.example/#organization', $schema['@graph'][0]['@id']);
        $this->assertSame(1, $html->query('//link[@rel="preload"][@as="image"]')->length);
        $this->assertSame($html->evaluate('string(//img[@fetchpriority="high"]/@srcset)'), $html->evaluate('string(//link[@rel="preload"][@as="image"]/@imagesrcset)'));
        $this->assertSame($html->evaluate('string(//img[@fetchpriority="high"]/@sizes)'), $html->evaluate('string(//link[@rel="preload"][@as="image"]/@imagesizes)'));
        $this->assertSame(0, $html->query('//img[@fetchpriority="high"][@loading="lazy"]')->length);
    }

    public function test_workshop_metadata_and_breadcrumb_follow_database_changes_without_stale_cache(): void
    {
        $this->production();
        $event = WorkshopEvent::factory()->create(['slug' => 'the-back', 'title' => 'THE BACK', 'introduction' => 'Buổi học makeup thực tế tại Tokyo.']);

        $response = $this->get('https://academy.example/workshop/the-back?utm_source=facebook');

        $response->assertOk()->assertHeaderMissing('X-Robots-Tag');
        $html = $this->html($response->getContent());
        $this->assertSame('Buổi học makeup thực tế tại Tokyo.', $html->evaluate('string(//meta[@name="description"]/@content)'));
        $this->assertSame('https://academy.example/workshop/the-back', $html->evaluate('string(//link[@rel="canonical"]/@href)'));
        $this->assertSame(1, $html->query('//h1')->length);
        $this->assertSame($html->evaluate('string(//img[@fetchpriority="high"]/@srcset)'), $html->evaluate('string(//link[@rel="preload"][@as="image"]/@imagesrcset)'));
        $schema = json_decode($html->evaluate('string(//script[@type="application/ld+json"])'), true, flags: JSON_THROW_ON_ERROR);
        $this->assertSame(['Organization', 'WebSite', 'WebPage', 'BreadcrumbList'], array_column($schema['@graph'], '@type'));
        $this->assertSame('https://academy.example/workshop/the-back', $schema['@graph'][3]['itemListElement'][1]['item']);
        $this->assertArrayNotHasKey('startDate', $schema['@graph'][2]);

        $event->update(['introduction' => 'Nội dung mới từ ban tổ chức.']);
        $updated = $this->get('https://academy.example/workshop/the-back')->assertOk();
        $this->assertSame('Nội dung mới từ ban tổ chức.', $this->html($updated->getContent())->evaluate('string(//meta[@name="description"]/@content)'));
    }

    public function test_dynamic_metadata_is_escaped_and_json_ld_cannot_close_its_script(): void
    {
        $this->production();
        $event = WorkshopEvent::factory()->create(['slug' => 'safe-workshop', 'title' => 'Beauty " & </script><script>alert(1)</script>', 'introduction' => 'Kỹ thuật "nền" & <img src=x onerror=alert(1)> makeup.']);

        $response = $this->get('https://academy.example/workshop/'.$event->slug)->assertOk();

        $response->assertDontSee('</script><script>alert(1)</script>', false)->assertDontSee('<img src=x onerror=alert(1)>', false);
        $html = $this->html($response->getContent());
        $this->assertSame('Kỹ thuật "nền" & makeup.', $html->evaluate('string(//meta[@name="description"]/@content)'));
        $schema = json_decode($html->evaluate('string(//script[@type="application/ld+json"])'), true, flags: JSON_THROW_ON_ERROR);
        $this->assertSame('https://schema.org', $schema['@context']);
        $this->assertSame(1, $html->query('//meta[@name="description"]')->length);
    }

    public function test_empty_workshop_copy_has_nonempty_fallback_metadata(): void
    {
        $this->production();
        $event = WorkshopEvent::factory()->create(['title' => ' ', 'introduction' => ' ']);

        $response = $this->get('https://academy.example/workshop/'.$event->slug)->assertOk();

        $html = $this->html($response->getContent());
        $this->assertStringContainsString('Private Makeup Workshop', $html->evaluate('string(//title)'));
        $this->assertStringContainsString('Western Glam', $html->evaluate('string(//meta[@name="description"]/@content)'));
    }

    public function test_sitemap_contains_only_canonical_public_pages_and_tracks_publication_changes(): void
    {
        $this->production();
        $event = WorkshopEvent::factory()->create(['slug' => 'the-back']);
        WorkshopEvent::factory()->create(['slug' => 'draft', 'is_published' => false]);
        WorkshopEvent::factory()->create(['slug' => 'the-art-of-elevation-2026']);

        $response = $this->get('https://academy.example/sitemap.xml');

        $response->assertOk()->assertHeader('Content-Type', 'application/xml; charset=UTF-8')->assertDontSee('lastmod');
        $xml = new DOMDocument;
        $this->assertTrue($xml->loadXML($response->getContent()));
        $locations = array_map(fn ($node): string => $node->textContent, iterator_to_array($xml->getElementsByTagName('loc')));
        $this->assertSame(['https://academy.example/', 'https://academy.example/workshop/the-back'], $locations);
        foreach ($locations as $location) {
            $this->get($location)->assertOk()->assertHeaderMissing('X-Robots-Tag');
        }

        $event->update(['is_published' => false]);
        $this->get('https://academy.example/sitemap.xml')->assertOk()->assertDontSee('/workshop/the-back');
        $this->get('https://academy.example/workshop/the-back')->assertNotFound()->assertHeader('X-Robots-Tag', 'noindex, follow');
        $event->update(['is_published' => true, 'slug' => 'new-public-event']);
        $this->get('https://academy.example/sitemap.xml')->assertOk()->assertSee('/workshop/new-public-event')->assertDontSee('/workshop/the-back');
    }

    public function test_production_robots_declares_sitemap_and_leaves_assets_and_noindex_pages_crawlable(): void
    {
        $this->production();

        $this->get('https://academy.example/robots.txt')->assertOk()->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->assertContent("User-agent: *\nDisallow:\n\nSitemap: https://academy.example/sitemap.xml\n");
    }

    #[TestWith(['local', true])]
    #[TestWith(['staging', true])]
    #[TestWith(['production', false])]
    public function test_nonproduction_or_disabled_indexing_blocks_crawling_and_marks_html_noindex(string $environment, bool $indexing): void
    {
        $this->production($environment);
        config(['seo.indexing' => $indexing]);
        WorkshopEvent::factory()->create(['slug' => 'the-back']);

        $response = $this->get('https://academy.example/')->assertOk()->assertHeader('X-Robots-Tag', 'noindex, follow');

        $this->assertSame('noindex, follow', $this->html($response->getContent())->evaluate('string(//meta[@name="robots"]/@content)'));
        $this->get('https://academy.example/robots.txt')->assertOk()->assertContent("User-agent: *\nDisallow: /\n");
        $this->get('https://academy.example/sitemap.xml')->assertNotFound()->assertHeader('X-Robots-Tag', 'noindex, follow');
        $this->get('https://academy.example/workshop/the-back')->assertOk()->assertHeader('X-Robots-Tag', 'noindex, follow');
    }

    #[TestWith(['http://www.academy.example/chinh-sach/?utm_source=fb', 'https://academy.example/chinh-sach?utm_source=fb'])]
    #[TestWith(['http://academy.example/', 'https://academy.example/'])]
    #[TestWith(['https://unexpected.example/', 'https://academy.example/'])]
    public function test_public_url_variants_redirect_in_one_hop_to_configured_origin(string $source, string $destination): void
    {
        $this->production();

        $response = $this->get($source);

        $response->assertStatus(301)->assertRedirect($destination);
        $this->get($destination)->assertOk();
    }

    public function test_legacy_workshop_redirects_permanently_straight_to_canonical_destination(): void
    {
        $this->production();
        WorkshopEvent::factory()->create(['slug' => 'the-back']);

        $this->get('http://www.academy.example/workshop/the-art-of-elevation-2026')->assertStatus(301)->assertRedirect('https://academy.example/workshop/the-back');
        $this->get('https://academy.example/workshop/the-back')->assertOk();
    }

    public function test_trusted_tls_proxy_does_not_cause_https_redirect_loop(): void
    {
        $this->production();
        config(['trustedproxy.proxies' => ['10.0.0.1']]);

        $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.1'])->withHeaders(['X-Forwarded-Proto' => 'https', 'X-Forwarded-Port' => '443'])
            ->get('http://academy.example/')->assertOk();
    }

    public function test_untrusted_forwarded_headers_cannot_bypass_canonical_https_redirect(): void
    {
        $this->production();

        $this->withHeaders(['X-Forwarded-Proto' => 'https'])->get('http://academy.example/')->assertStatus(301)->assertRedirect('https://academy.example/');
    }

    public function test_private_pages_drafts_and_errors_are_noindex_without_changing_auth_or_status(): void
    {
        $this->production();
        WorkshopEvent::factory()->create(['slug' => 'the-back']);

        $this->get('https://academy.example/admin/login')->assertOk()->assertHeader('X-Robots-Tag', 'noindex, follow');
        $this->get('https://academy.example/admin/portfolio?page=2')->assertRedirect()->assertHeader('X-Robots-Tag', 'noindex, follow');
        $this->get('https://academy.example/workshop-admin/the-back')->assertRedirect()->assertHeader('X-Robots-Tag', 'noindex, follow');
        $this->get('https://academy.example/chinh-sach')->assertOk()->assertHeader('X-Robots-Tag', 'noindex, follow')
            ->assertSee('Bản dự thảo chính sách.')->assertSee('name="robots" content="noindex, follow"', false);
        $this->get('https://academy.example/does-not-exist')->assertNotFound()->assertHeader('X-Robots-Tag', 'noindex, follow');
        $this->withoutMiddleware(PreventRequestForgery::class)
            ->postJson('https://academy.example/enrollment', [])->assertUnprocessable()->assertHeader('X-Robots-Tag', 'noindex, follow');

        $admin = User::factory()->create(['email' => config('academy.admin_email')]);
        $this->actingAs($admin)->get('https://academy.example/admin/portfolio')->assertOk()->assertHeader('X-Robots-Tag', 'noindex, follow');
        $visitor = User::factory()->create();
        $this->actingAs($visitor)->get('https://academy.example/admin/portfolio')->assertForbidden()->assertHeader('X-Robots-Tag', 'noindex, follow');
    }

    public function test_public_uploaded_images_remain_indexable_and_private_images_return_real_404(): void
    {
        $this->production();
        Storage::fake('local');
        $path = UploadedFile::fake()->image('work.webp')->store('portfolio', 'local');
        $item = PortfolioItem::factory()->create(['image_path' => $path]);

        $this->get('https://academy.example/tac-pham/'.$item->id.'/anh')->assertOk()->assertHeaderMissing('X-Robots-Tag');
        $item->update(['is_featured' => false]);
        $this->get('https://academy.example/tac-pham/'.$item->id.'/anh')->assertNotFound()->assertHeader('X-Robots-Tag', 'noindex, follow');
    }

    public function test_home_reuses_its_workshop_query_for_header(): void
    {
        WorkshopEvent::factory()->create(['slug' => config('workshop.slug')]);
        $workshopQueries = 0;
        DB::listen(function ($query) use (&$workshopQueries): void {
            if (str_contains($query->sql, 'workshop_events')) {
                $workshopQueries++;
            }
        });

        $this->get('/')->assertOk()->assertSee('nav-workshop', false);

        $this->assertSame(1, $workshopQueries);
    }

    public function test_error_500_keeps_its_status_and_has_noindex_header(): void
    {
        $this->production();
        Route::get('/_seo-test-error', function (): void {
            abort(500);
        });

        $this->get('https://academy.example/_seo-test-error')->assertStatus(500)->assertHeader('X-Robots-Tag', 'noindex, follow');
    }

    public function test_production_with_localhost_origin_cannot_be_indexed(): void
    {
        $this->production();
        config(['app.url' => 'http://localhost:8000', 'seo.canonical_redirects' => false]);

        $this->get('/')->assertOk()->assertHeader('X-Robots-Tag', 'noindex, follow');
        $this->get('/robots.txt')->assertContent("User-agent: *\nDisallow: /\n");
    }

    public function test_canonical_redirects_can_be_disabled_without_losing_canonical_metadata(): void
    {
        $this->production();
        config(['seo.canonical_redirects' => false]);

        $response = $this->get('http://www.academy.example/')->assertOk();

        $this->assertSame('https://academy.example/', $this->html($response->getContent())->evaluate('string(//link[@rel="canonical"]/@href)'));
    }
}
