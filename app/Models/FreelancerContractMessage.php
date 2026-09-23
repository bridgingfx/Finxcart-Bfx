<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FreelancerContractMessage extends Model
{
    protected $fillable = [
        'freelancer_contract_id',
        'sender_type',
        'sender_id',
        'body',
    ];

    protected $casts = [
        'freelancer_contract_id' => 'integer',
        'sender_id' => 'integer',
    ];

    public function contract(): BelongsTo
    {
        return $this->belongsTo(FreelancerContract::class, 'freelancer_contract_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(FreelancerContractMessageAttachment::class, 'message_id');
    }
}
