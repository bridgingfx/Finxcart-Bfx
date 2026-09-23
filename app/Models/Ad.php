<?php
// app/Models/Ad.php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ad extends Model
{
    use HasFactory;
    protected $fillable = [
        'ad_group_id', 'name', 'type', 'content', 'image_path',
        'destination_url', 'is_active', 'start_date', 'end_date',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function adGroup()
    {
        return $this->belongsTo(AdGroup::class);
    }

    public function impressions()
    {
        return $this->hasMany(AdImpression::class);
    }

    public function clicks()
    {
        return $this->hasMany(AdImpression::class)->where('is_click', true);
    }

    /**
     * Get the ad's shortcode.
     */
    protected function shortcode(): Attribute
    {
        return Attribute::make(
            get: fn () => '[ad id="' . $this->id . '"]',
        );
    }
}
