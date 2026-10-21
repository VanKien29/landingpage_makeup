<?php

namespace App\Http\Controllers;

use App\Models\WorkshopEvent;
use App\Services\Seo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\View\View;

class SeoController extends Controller
{
    public function legacyWorkshop(Seo $seo, ?WorkshopEvent $workshop = null): RedirectResponse
    {
        if ($workshop !== null) {
            abort_unless($workshop->slug === config('workshop.slug') && $workshop->is_published, 404);
        }

        return redirect()->to($seo->url('workshop.show'), 301);
    }

    public function policies(Seo $seo): View
    {
        return view('policies', ['seo' => $seo->policies()]);
    }

    public function sitemap(Seo $seo): Response
    {
        abort_unless($seo->indexingEnabled(), 404);

        $urls = [$seo->url('home')];
        if (WorkshopEvent::where('slug', config('workshop.slug'))->where('is_published', true)->exists()) {
            $urls[] = $seo->url('workshop.show');
        }

        return response()->view('sitemap', compact('urls'))->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    public function robots(Seo $seo): Response
    {
        $content = "User-agent: *\n";
        $content .= $seo->indexingEnabled()
            ? "Disallow:\n\nSitemap: ".$seo->url('sitemap')."\n"
            : "Disallow: /\n";

        return response($content)->header('Content-Type', 'text/plain; charset=UTF-8');
    }
}
