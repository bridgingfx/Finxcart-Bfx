<?php
// app/Models/AdGroup.php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdGroup extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'slug', 'description', 'is_active'];

    public function ads()
    {
        return $this->hasMany(Ad::class);
    }

    public function impressions()
    {
        return $this->hasMany(AdImpression::class);
    }

    /**
     * Get the ad group's shortcode.
     */
    protected function shortcode(): Attribute
    {
        return Attribute::make(
            get: fn () => '[ad group="' . $this->slug . '"]',
        );
    }
}
