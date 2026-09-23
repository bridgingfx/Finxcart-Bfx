<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorTierPayment extends Model
{
    protected $table = 'vendor_tier_payments';

    protected $fillable = [
        'vendor_tier_id',
        'billing_start_date',
        'billing_end_date',
        'amount_paid',
        'currency',
        'payment_method',
        'transaction_id',
        'status',
        'payment_status',
        'payment_meta',
        'paid_at',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'billing_start_date' => 'date',
        'billing_end_date' => 'date',
    ];

    public function vendorTier()
    {
        return $this->belongsTo(VendorTier::class, 'vendor_tier_id');
    }
}
