<?php

namespace App\Services;

use App\Models\PortfolioCategory;
use App\Models\PortfolioItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

final class PortfolioCache
{
    public const ITEMS_KEY = 'public.portfolio.items.v1';

    public const CATEGORIES_KEY = 'public.portfolio.categories.v1';

    public static function items(): Collection
    {
        return Cache::remember(self::ITEMS_KEY, now()->addMinutes(10), fn (): Collection => PortfolioItem::where('is_featured', true)->orderBy('display_order')->get());
    }

    public static function categories(): Collection
    {
        return Cache::remember(self::CATEGORIES_KEY, now()->addMinutes(10), fn (): Collection => PortfolioCategory::where('is_active', true)->orderBy('display_order')->orderBy('name')->get());
    }

    public static function flush(): void
    {
        Cache::forget(self::ITEMS_KEY);
        Cache::forget(self::CATEGORIES_KEY);
    }
}
