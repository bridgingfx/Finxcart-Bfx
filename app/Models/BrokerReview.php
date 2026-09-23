<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BrokerReview extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function broker()
    {
        return $this->belongsTo(Broker::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class); // Adjust if your user model is Customer
    }
}