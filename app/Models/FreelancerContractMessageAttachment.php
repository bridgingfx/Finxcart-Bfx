<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FreelancerContractMessageAttachment extends Model
{
    protected $fillable = [
        'message_id',
        'disk_path',
        'original_name',
        'mime_type',
        'size',
    ];

    protected $casts = [
        'message_id' => 'integer',
        'size' => 'integer',
    ];

    public function message(): BelongsTo
    {
        return $this->belongsTo(FreelancerContractMessage::class, 'message_id');
    }
}
