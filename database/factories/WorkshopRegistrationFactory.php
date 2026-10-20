<?php

namespace Database\Factories;

use App\Models\WorkshopEvent;
use App\Models\WorkshopRegistration;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WorkshopRegistration>
 */
class WorkshopRegistrationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'workshop_event_id' => WorkshopEvent::factory(),
            'reference' => 'BACK-'.strtoupper(fake()->unique()->bothify('??######')),
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'phone' => '08098194619',
            'social' => 'instagram.com/makeup.artist',
            'quantity' => 1,
            'unit_price' => 0,
            'experience' => 'enthusiast',
            'attendance_confirmed' => true,
            'consented_at' => now(),
        ];
    }
}
