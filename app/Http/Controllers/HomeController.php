<?php

namespace App\Http\Controllers;

use App\Models\WorkshopEvent;
use App\Services\PortfolioCache;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $portfolioItems = PortfolioCache::items();
        $portfolioCategories = PortfolioCache::categories();
        $workshop = WorkshopEvent::where('slug', config('workshop.slug'))->first();

        return view('welcome', compact('portfolioItems', 'portfolioCategories', 'workshop'));
    }
}
