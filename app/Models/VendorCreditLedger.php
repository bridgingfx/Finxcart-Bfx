<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorCreditLedger extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'seller_id',
        'type',
        'credits',
        'balance_after',
        'related_type',
        'related_id',
        'description',
        'expires_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'credits' => 'integer',
        'balance_after' => 'integer',
        'expires_at' => 'datetime',
    ];

    /**
     * Ledger entry types.
     */
    public const TYPE_GRANT = 'grant';
    public const TYPE_PURCHASE = 'purchase';
    public const TYPE_SPEND = 'spend';
    public const TYPE_REFUND = 'refund';
    public const TYPE_EXPIRE = 'expire';

    /**
     * Get the seller (vendor) this ledger entry belongs to.
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class, 'seller_id');
    }
}
