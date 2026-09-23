<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FreelancerContractMilestone extends Model
{
    protected $fillable = [
        'freelancer_contract_id',
        'title',
        'amount',
        'status',
        'position',
        'submitted_at',
        'approved_at',
    ];

    protected $casts = [
        'freelancer_contract_id' => 'integer',
        'amount' => 'decimal:2',
        'position' => 'integer',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function contract(): BelongsTo
    {
        return $this->belongsTo(FreelancerContract::class, 'freelancer_contract_id');
    }

    public function deliverables(): HasMany
    {
        return $this->hasMany(FreelancerContractDeliverable::class, 'milestone_id')->orderBy('created_at');
    }
}
