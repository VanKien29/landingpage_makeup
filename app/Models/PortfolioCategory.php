<?php

namespace App\Models;

use App\Services\PortfolioCache;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PortfolioCategory extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'display_order', 'is_active'];

    protected $casts = ['display_order' => 'integer', 'is_active' => 'boolean'];

    protected static function booted(): void
    {
        static::saved(function (): void {
            PortfolioCache::flush();
        });
        static::deleted(function (): void {
            PortfolioCache::flush();
        });
    }

    public function portfolioItems(): HasMany
    {
        return $this->hasMany(PortfolioItem::class, 'category', 'slug');
    }
}
