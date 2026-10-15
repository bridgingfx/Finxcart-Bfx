<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $shipment_id
 * @property int $order_detail_id
 * @property string $type
 * @property string|null $delivery_mode
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $delivered_at
 * @property int $sla_stage
 */
class ShipmentItem extends Model
{
    protected $fillable = [
        'shipment_id',
        'order_detail_id',
        'type',
        'delivery_mode',
        'status',
        'delivered_at',
        'sla_stage',
    ];

    protected $casts = [
        'shipment_id' => 'integer',
        'order_detail_id' => 'integer',
        'delivered_at' => 'datetime',
        'sla_stage' => 'integer',
    ];

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }

    public function orderDetail(): BelongsTo
    {
        return $this->belongsTo(OrderDetail::class);
    }
}
