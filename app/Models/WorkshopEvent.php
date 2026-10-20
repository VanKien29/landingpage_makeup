<?php

namespace App\Models;

use Database\Factories\WorkshopEventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkshopEvent extends Model
{
    /** @use HasFactory<WorkshopEventFactory> */
    use HasFactory;

    protected $fillable = ['slug', 'title', 'tagline', 'introduction', 'event_date', 'venue', 'ticket_price', 'capacity', 'registration_open', 'is_published', 'hero_image', 'glam_image', 'bridal_image', 'payment_instructions'];

    protected $casts = ['event_date' => 'date', 'ticket_price' => 'integer', 'capacity' => 'integer', 'confirmed_tickets' => 'integer', 'registration_open' => 'boolean', 'is_published' => 'boolean'];

    public function registrations(): HasMany
    {
        return $this->hasMany(WorkshopRegistration::class);
    }

    public function remainingTickets(): int
    {
        return max(0, $this->capacity - $this->confirmed_tickets);
    }

    public function acceptsRegistrations(): bool
    {
        return $this->registration_open;
    }
}
