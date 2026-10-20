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
            'title' => 'THE BACK',
            'tagline' => 'A Grateful Return. A New Beginning.',
            'introduction' => 'Một ngày gặp gỡ nghệ thuật makeup tại Tokyo.',
            'venue' => 'Tokyo, Japan — Địa chỉ cụ thể sẽ được cập nhật.',
            'capacity' => 60,
            'ticket_price' => 0,
            'registration_open' => true,
            'hero_image' => 'founder-van-cuong-enhanced.webp',
            'glam_image' => 'workshop-western-glam-01.webp',
            'bridal_image' => 'workshop-bridal-beauty-01.webp',
        ];
    }
}
