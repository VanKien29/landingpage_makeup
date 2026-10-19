<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PortfolioItem extends Model
{
    use HasFactory;

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
            return route('portfolio.image', $this);
        }
        if (str_starts_with($this->image_path, 'http')) {
            return $this->image_path;
        }

        return asset('images/'.ltrim($this->image_path, '/'));
    }
}
