<?php

namespace Database\Factories;

use App\Models\WorkshopEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WorkshopEvent>
 */
class WorkshopEventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'slug' => fake()->unique()->slug(),
            'title' => 'THE ART OF ELEVATION 2026',
            'tagline' => 'Elevate Your Beauty. Elevate Your Art.',
            'introduction' => 'Một ngày gặp gỡ nghệ thuật makeup tại Tokyo.',
            'venue' => 'Tokyo, Japan — Địa chỉ cụ thể sẽ được cập nhật.',
            'capacity' => 40,
            'ticket_price' => 5000,
            'registration_open' => true,
            'hero_image' => 'beauty-pro-technique.jpg',
            'glam_image' => '2aOboR6WhLoQQe6I8VzjFvxGVTBjIAHxaqrDAMNs.jpg',
            'bridal_image' => 'beauty-bridal-detail.jpg',
        ];
    }
}
