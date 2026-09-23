<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FreelancerContractReview extends Model
{
    protected $fillable = [
        'freelancer_contract_id',
        'reviewer_type',
        'reviewer_id',
        'reviewee_type',
        'reviewee_id',
        'rating',
        'body',
    ];

    protected $casts = [
        'freelancer_contract_id' => 'integer',
        'reviewer_id' => 'integer',
        'reviewee_id' => 'integer',
        'rating' => 'integer',
    ];

    public function contract(): BelongsTo
    {
        return $this->belongsTo(FreelancerContract::class, 'freelancer_contract_id');
    }
}
