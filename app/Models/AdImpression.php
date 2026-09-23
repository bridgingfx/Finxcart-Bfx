<?php
// app/Models/AdImpression.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdImpression extends Model
{
    use HasFactory;
    public $timestamps = false; // We only use 'created_at'
    protected $fillable = [
        'ad_id', 'ad_group_id', 'ip_address', 'user_agent', 'session_id', 'is_click',
    ];

    public function ad()
    {
        return $this->belongsTo(Ad::class);
    }
}
