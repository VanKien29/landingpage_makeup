<?php

namespace App\Models;

use Database\Factories\WorkshopRegistrationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkshopRegistration extends Model
{
    /** @use HasFactory<WorkshopRegistrationFactory> */
    use HasFactory;

    public const Pending = 'pending';

    public const Confirmed = 'confirmed';

    public const Cancelled = 'cancelled';

    protected $fillable = ['name', 'phone', 'email', 'social', 'quantity', 'notes'];

    protected $casts = ['quantity' => 'integer', 'unit_price' => 'integer', 'consented_at' => 'datetime', 'paid_at' => 'datetime', 'checked_in_at' => 'datetime'];

    public function workshopEvent(): BelongsTo
    {
        return $this->belongsTo(WorkshopEvent::class);
    }

    public function total(): int
    {
        return $this->quantity * $this->unit_price;
    }
}
