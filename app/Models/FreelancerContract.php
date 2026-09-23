<?php

namespace App\Models;

use App\Enums\Freelancer\DeliveryStatus;
use App\Enums\Freelancer\VerdictStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FreelancerContract extends Model
{
    protected $fillable = [
        'customer_id',
        'seller_id',
        'freelancer_service_id',
        'freelancer_quote_request_id',
        'payment_request_id',
        'scope',
        'total_amount',
        'status',
        'delivery_status',
        'verdict_status',
        'rejection_reason',
        'completed_at',
        'cancelled_at',
        'delivered_at',
        'verdict_at',
        'cancellation_status',
        'cancellation_reason',
        'cancellation_admin_note',
        'cancellation_requested_at',
        'cancellation_decided_at',
    ];

    protected $casts = [
        'customer_id' => 'integer',
        'seller_id' => 'integer',
        'freelancer_service_id' => 'integer',
        'freelancer_quote_request_id' => 'integer',
        'total_amount' => 'decimal:2',
        'delivery_status' => DeliveryStatus::class,
        'verdict_status' => VerdictStatus::class,
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'delivered_at' => 'datetime',
        'verdict_at' => 'datetime',
        'cancellation_requested_at' => 'datetime',
        'cancellation_decided_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function freelancer(): BelongsTo
    {
        return $this->belongsTo(Seller::class, 'seller_id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(FreelancerService::class, 'freelancer_service_id');
    }

    public function milestones(): HasMany
    {
        return $this->hasMany(FreelancerContractMilestone::class)->orderBy('position');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(FreelancerContractMessage::class)->orderBy('created_at');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(FreelancerContractReview::class);
    }

    /**
     * Direct FK link for contracts purchased through a specific accepted
     * quote (see FreelancerOrderService::purchase()).
     */
    public function quoteRequest(): BelongsTo
    {
        return $this->belongsTo(FreelancerQuoteRequest::class, 'freelancer_quote_request_id');
    }

    /**
     * Fallback for contracts hired directly (no quote_request_id set,
     * including all pre-existing contracts): quote requests are correlated
     * by customer + seller + service instead of a foreign key.
     */
    public function quoteRequests(): HasMany
    {
        return $this->hasMany(FreelancerQuoteRequest::class, 'customer_id', 'customer_id')
            ->where('seller_id', $this->seller_id)
            ->where('freelancer_service_id', $this->freelancer_service_id);
    }

    public function isParticipant(string $type, int $id): bool
    {
        return ($type === 'customer' && $this->customer_id === $id)
            || ($type === 'seller' && $this->seller_id === $id);
    }
}
