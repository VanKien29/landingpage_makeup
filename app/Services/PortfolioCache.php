<?php

namespace App\Services;

use App\Models\PortfolioCategory;
use App\Models\PortfolioItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

final class PortfolioCache
{
    public const ITEMS_KEY = 'public.portfolio.items.v2';

    public const CATEGORIES_KEY = 'public.portfolio.categories.v2';

    public static function items(): Collection
    {
        $attributes = Cache::remember(self::ITEMS_KEY, now()->addMinutes(10), fn (): array => PortfolioItem::where('is_featured', true)
            ->orderBy('display_order')->get()
            ->map(fn (PortfolioItem $item): array => $item->getAttributes())->all());

        return PortfolioItem::hydrate($attributes);
    }

    public static function categories(): Collection
    {
        $attributes = Cache::remember(self::CATEGORIES_KEY, now()->addMinutes(10), fn (): array => PortfolioCategory::where('is_active', true)
            ->orderBy('display_order')->orderBy('name')->get()
            ->map(fn (PortfolioCategory $category): array => $category->getAttributes())->all());

        return PortfolioCategory::hydrate($attributes);
    }

    public static function flush(): void
    {
        Cache::forget(self::ITEMS_KEY);
        Cache::forget(self::CATEGORIES_KEY);
    }
}
