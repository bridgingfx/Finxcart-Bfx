<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Broker extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function reviews()
    {
        return $this->hasMany(BrokerReview::class);
    }

    // Helper: Calculate WikiFX Style Score
    public function calculateSystemScore()
    {
        // Logic: License is 50%, Software 30%, Stability 20%
        $total = ($this->score_license * 0.5) + 
                 ($this->score_software * 0.3) + 
                 ($this->score_stability * 0.2);
        
        $this->update(['system_rating' => number_format($total, 2)]);
    }

    // Helper: Recalculate User Ratings (Only Approved Reviews)
    public function updateUserRating()
    {
        $approvedReviews = $this->reviews()->where('is_approved', 1);
        $avg = $approvedReviews->avg('rating');
        $count = $approvedReviews->count();

        $this->update([
            'user_rating' => $avg ? number_format($avg, 2) : 0.00,
            'review_count' => $count
        ]);
    }
}