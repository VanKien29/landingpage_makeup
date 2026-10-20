<?php

namespace App\Models;

use App\Services\WebpImages;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentJourney extends Model
{
    use HasFactory;

    protected $fillable = [
        'image_path',
        'caption',
        'display_order',
    ];

    protected $casts = [
        'display_order' => 'integer',
    ];

    public function getImageUrlAttribute(): string
    {
        if (str_starts_with($this->image_path, 'http')) {
            return $this->image_path;
        }

        return asset('images/'.WebpImages::publicFilename(ltrim($this->image_path, '/')));
    }
}
