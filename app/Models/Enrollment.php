<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'contact',
        'message',
        'agreed_terms',
        'status',
        'admin_notes',
        'ip_address',
    ];

    protected $casts = [
        'agreed_terms' => 'boolean',
    ];
}
