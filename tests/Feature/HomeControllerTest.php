<?php

namespace Tests\Feature;

use App\Models\PortfolioItem;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class HomeControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_home_displays_vietnamese_course_information_and_only_visible_artworks(): void
    {
        PortfolioItem::factory()->create(['title' => 'Published Work']);
        PortfolioItem::factory()->create(['title' => 'Private Work', 'is_featured' => false]);

        $response = $this->get('/');

        $response->assertOk()->assertSee('Văn Cường')->assertSee('350.000')->assertSee('Published Work')->assertDontSee('Private Work');
    }

    public function test_home_escapes_artwork_titles_and_captions(): void
    {
        PortfolioItem::factory()->create(['title' => '<script>alert(1)</script>']);

        $response = $this->get('/');

        $response->assertOk()->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_policy_page_labels_unreviewed_terms_as_draft(): void
    {
        $response = $this->get('/chinh-sach');

        $response->assertOk()->assertSee('Bản dự thảo chính sách.')->assertSee('pháp luật Nhật Bản')->assertSee('245.000 JPY');
    }
}
