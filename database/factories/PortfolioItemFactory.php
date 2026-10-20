<?php

namespace Database\Factories;

use App\Models\PortfolioItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<PortfolioItem> */
class PortfolioItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => 'Beauty Study',
            'category' => 'beauty',
            'image_path' => '2aOboR6WiZgtJaXsCt5LunyqWE9n4yTJ5JsJ9sJc.webp',
            'alt_text' => 'Tác phẩm beauty makeup',
            'display_order' => 1,
            'is_featured' => true,
        ];
    }
}
