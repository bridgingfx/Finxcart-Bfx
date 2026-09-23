<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Influencer extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function ratings()
    {
        return $this->hasMany(InfluencerRating::class);
    }

    public function inquiries()
    {
        return $this->hasMany(InfluencerInquiry::class);
    }
    
    public function updateRating()
    {
        $avg = $this->ratings()->avg('rating');
        $count = $this->ratings()->count();
        
        $this->update([
            'avg_rating' => $avg ? number_format($avg, 2) : 0.00,
            'rating_count' => $count
        ]);
    }

    // NEW: Helper to format numbers (e.g. 1.5M, 200K)
    public function formattedFollowers($column)
    {
        $num = $this->$column;
        
        if ($num >= 1000000) {
            return round($num / 1000000, 1) . 'M';
        }
        if ($num >= 1000) {
            return round($num / 1000, 1) . 'K';
        }
        
        return $num;
    }
}