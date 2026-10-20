<?php

namespace App\Models;

use App\Services\PortfolioCache;
use App\Services\WebpImages;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PortfolioItem extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::saved(function (): void {
            PortfolioCache::flush();
        });
        static::deleted(function (): void {
            PortfolioCache::flush();
        });
    }

    protected $fillable = [
        'title',
        'category',
        'image_path',
        'alt_text',
        'display_order',
        'is_featured',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'display_order' => 'integer',
    ];

    public function getImageUrlAttribute(): string
    {
        if (str_starts_with($this->image_path, 'portfolio/')) {
            return route('portfolio.image', ['portfolioItem' => $this, 'v' => substr(hash('sha256', $this->image_path), 0, 16)]);
        }
        if (str_starts_with($this->image_path, 'http')) {
            return $this->image_path;
        }

        return asset('images/'.WebpImages::publicFilename(ltrim($this->image_path, '/')));
    }

    public function getAdminImageUrlAttribute(): string
    {
        return str_starts_with($this->image_path, 'portfolio/')
            ? route('admin.portfolio.image', $this)
            : $this->image_url;
    }
}
