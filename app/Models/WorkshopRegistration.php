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

    public const Waitlisted = 'waitlisted';

    public const ExperienceLabels = ['studying' => 'Đang học makeup', 'professional' => 'Đang làm nghề makeup', 'enthusiast' => 'Yêu thích makeup'];

    protected $fillable = ['name', 'phone', 'email', 'social', 'experience'];

    protected $casts = ['quantity' => 'integer', 'unit_price' => 'integer', 'attendance_confirmed' => 'boolean', 'consented_at' => 'datetime', 'confirmed_at' => 'datetime', 'paid_at' => 'datetime', 'checked_in_at' => 'datetime'];

    public function workshopEvent(): BelongsTo
    {
        return $this->belongsTo(WorkshopEvent::class);
    }

    public function total(): int
    {
        return $this->quantity * $this->unit_price;
    }
}
