<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'title',
        'message',
        'link',
        'reference_id',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];
}
