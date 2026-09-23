<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class InfluencerInquiry extends Model
{
    protected $guarded = [];
    
    public function influencer() {
        return $this->belongsTo(Influencer::class);
    }
}