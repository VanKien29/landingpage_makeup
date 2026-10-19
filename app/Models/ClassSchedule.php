<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_name',
        'batch_code',
        'start_date',
        'schedule_time',
        'location',
        'slots_total',
        'slots_available',
        'status',
        'display_order',
    ];

    protected $casts = [
        'slots_total' => 'integer',
        'slots_available' => 'integer',
        'display_order' => 'integer',
    ];
}
