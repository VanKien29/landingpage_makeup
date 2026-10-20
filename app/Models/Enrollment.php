<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    use HasFactory;

    public const StatusLabels = [
        'pending' => 'Chờ tư vấn',
        'contacted' => 'Đã liên hệ',
        'enrolled' => 'Đã nhập học',
        'cancelled' => 'Đã hủy',
    ];

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
