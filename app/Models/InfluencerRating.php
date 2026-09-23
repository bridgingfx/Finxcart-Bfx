<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InfluencerRating extends Model
{
    use HasFactory;

    protected $guarded = [];

    // Relationship: A rating belongs to a specific Influencer
    public function influencer()
    {
        return $this->belongsTo(Influencer::class);
    }

    // Relationship: A rating belongs to a User (Customer)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}