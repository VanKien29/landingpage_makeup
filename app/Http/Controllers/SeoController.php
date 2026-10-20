<?php

namespace App\Http\Controllers;

use App\Models\WorkshopEvent;
use App\Services\Seo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\View\View;

class SeoController extends Controller
{
    public function legacyWorkshop(Seo $seo): RedirectResponse
    {
        return redirect()->to($seo->url('workshop.show', ['workshop' => 'the-back']), 301);
    }

    public function policies(Seo $seo): View
    {
        return view('policies', ['seo' => $seo->policies()]);
    }

    public function sitemap(Seo $seo): Response
    {
        abort_unless($seo->indexingEnabled(), 404);

        $urls = [$seo->url('home')];
        foreach (WorkshopEvent::query()->where('is_published', true)->select('id', 'slug')->orderBy('id')->cursor() as $workshop) {
            if ($workshop->slug === 'the-art-of-elevation-2026') {
                continue;
            }
            $urls[] = $seo->url('workshop.show', ['workshop' => $workshop->slug]);
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
