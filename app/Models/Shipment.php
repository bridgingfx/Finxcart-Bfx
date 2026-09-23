<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $order_id
 * @property int $source_order_id
 * @property int $vendor_id
 * @property string $vendor_type
 * @property bool $has_digital
 * @property bool $has_physical
 * @property string|null $digital_status
 * @property \Illuminate\Support\Carbon|null $digital_delivered_at
 * @property string|null $physical_status
 * @property \Illuminate\Support\Carbon|null $physical_delivered_at
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $delivered_at
 */
class Shipment extends Model
{
    protected $fillable = [
        'order_id',
        'source_order_id',
        'vendor_id',
        'vendor_type',
        'has_digital',
        'has_physical',
        'digital_status',
        'digital_delivered_at',
        'physical_status',
        'physical_delivered_at',
        'status',
        'delivered_at',
    ];

    protected $casts = [
        'order_id' => 'integer',
        'source_order_id' => 'integer',
        'vendor_id' => 'integer',
        'has_digital' => 'boolean',
        'has_physical' => 'boolean',
        'digital_delivered_at' => 'datetime',
        'physical_delivered_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function sourceOrder(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'source_order_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ShipmentItem::class);
    }
}
