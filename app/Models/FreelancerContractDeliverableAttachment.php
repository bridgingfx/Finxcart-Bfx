<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FreelancerContractDeliverableAttachment extends Model
{
    protected $fillable = [
        'deliverable_id',
        'disk_path',
        'original_name',
        'mime_type',
        'size',
    ];

    protected $casts = [
        'deliverable_id' => 'integer',
        'size' => 'integer',
    ];

    public function deliverable(): BelongsTo
    {
        return $this->belongsTo(FreelancerContractDeliverable::class, 'deliverable_id');
    }
}
