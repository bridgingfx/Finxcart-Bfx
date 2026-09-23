<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FreelancerContractDeliverable extends Model
{
    protected $fillable = [
        'milestone_id',
        'seller_id',
        'note',
    ];

    protected $casts = [
        'milestone_id' => 'integer',
        'seller_id' => 'integer',
    ];

    public function milestone(): BelongsTo
    {
        return $this->belongsTo(FreelancerContractMilestone::class, 'milestone_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(FreelancerContractDeliverableAttachment::class, 'deliverable_id');
    }
}
