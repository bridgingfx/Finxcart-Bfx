<?php

namespace App\Models;

use App\Enums\Freelancer\QuoteStatus;
use App\Traits\StorageTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class FreelancerQuoteRequest extends Model
{
    use StorageTrait;

    protected $fillable = [
        'customer_id',
        'seller_id',
        'freelancer_service_id',
        'description',
        'attachment',
        'delivery_preference',
        'custom_delivery_text',
        'budget',
        'status',
        'quoted_price',
        'quoted_delivery_days',
        'reply_message',
        'replied_at',
        'accepted_at',
        'declined_at',
    ];

    protected $casts = [
        'customer_id' => 'integer',
        'seller_id' => 'integer',
        'freelancer_service_id' => 'integer',
        'attachment' => 'array',
        'budget' => 'decimal:2',
        'quoted_price' => 'decimal:2',
        'quoted_delivery_days' => 'integer',
        'status' => QuoteStatus::class,
        'replied_at' => 'datetime',
        'accepted_at' => 'datetime',
        'declined_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class, 'seller_id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(FreelancerService::class, 'freelancer_service_id');
    }

    public function contract(): HasOne
    {
        return $this->hasOne(FreelancerContract::class, 'freelancer_quote_request_id');
    }

    public function getAttachmentFullUrlAttribute(): array
    {
        $files = [];
        foreach ($this->attachment ?? [] as $fileName) {
            $files[] = $this->storageLink('freelancer-quotes', $fileName, 'public');
        }

        return $files;
    }

    public function getDeliveryLabelAttribute(): string
    {
        return match ($this->delivery_preference) {
            '24_hours' => translate('24_hours'),
            '3_days' => translate('3_days'),
            '7_days' => translate('7_days'),
            'custom' => $this->custom_delivery_text ?: translate('custom'),
            default => translate('not_specified'),
        };
    }
}
