<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommissionLedger extends Model
{
    protected $table = 'commission_ledger';

    protected $fillable = [
        'seller_id',
        'order_id',
        'reference_type',
        'reference_id',
        'gross_amount',
        'company_share_amount',
        'service_charge_amount',
        'net_vendor_payout',
        'currency',
        'platform_rate',
    ];

    protected $casts = [
        'gross_amount' => 'decimal:4',
        'company_share_amount' => 'decimal:4',
        'service_charge_amount' => 'decimal:4',
        'net_vendor_payout' => 'decimal:4',
        'platform_rate' => 'decimal:2',
    ];

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class, 'seller_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(FreelancerContract::class, 'reference_id');
    }
}
