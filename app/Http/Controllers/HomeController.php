<?php

namespace App\Http\Controllers;

use App\Models\WorkshopEvent;
use App\Services\PortfolioCache;
use App\Services\Seo;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(Seo $seo): View
    {
        $portfolioItems = PortfolioCache::items();
        $portfolioCategories = PortfolioCache::categories();
        $workshop = WorkshopEvent::where('slug', config('workshop.slug'))->first();

        return view('welcome', ['seo' => $seo->home(), 'publicWorkshop' => $workshop, ...compact('portfolioItems', 'portfolioCategories', 'workshop')]);
    }
}
