<?php

namespace App\Http\Controllers;

use App\Models\PortfolioItem;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $portfolioItems = PortfolioItem::where('is_featured', true)->orderBy('display_order')->get();

        return view('welcome', compact('portfolioItems'));
    }
}
