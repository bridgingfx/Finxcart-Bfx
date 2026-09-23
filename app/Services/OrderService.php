<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Shipment;
use App\Models\ShipmentItem;
use Illuminate\Support\Collection;

class OrderService
{
    public function __construct()
    {
    }

    public function getPOSOrderData(int|string $orderId, array $cart, float $amount, float $paidAmount, string $paymentType, string $addedBy, int $userId): array
    {
        return [
            'id' => $orderId,
            'customer_id' => $userId,
            'customer_type' => 'customer',
            'payment_status' => 'paid',
            'order_status' => 'delivered',
            'seller_id' => $addedBy == 'seller' ? auth('seller')->id() : auth('admin')->id(),
            'seller_is' => $addedBy,
            'payment_method' => $paymentType,
            'order_type' => 'POS',
            'checked' => 1,
            'extra_discount' => $cart['ext_discount'] ?? 0,
            'extra_discount_type' => $cart['ext_discount_type'] ?? null,
            'order_amount' => currencyConverter(amount: $amount),
            'paid_amount' => currencyConverter(amount: $paidAmount),
            'discount_amount' => $cart['coupon_discount'] ?? 0,
            'coupon_code' => $cart['coupon_code'] ?? null,
            'discount_type' => (isset($cart['coupon_code']) && $cart['coupon_code']) ? 'coupon_discount' : NULL,
            'coupon_discount_bearer' => $cart['coupon_bearer'] ?? 'inhouse',
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function getCheckIsOrderOnlyDigital(object $order): bool
    {
        $isOrderOnlyDigital = true;
        if ($order->orderDetails) {
            foreach ($order->orderDetails as $detail) {
                $product = json_decode($detail->product_details);
                if (isset($product->product_type) && $product->product_type == 'physical') {
                    $isOrderOnlyDigital = false;
                }
            }
        }
        return $isOrderOnlyDigital;
    }

    /**
     * Create the shipment (+ shipment_items) for one vendor's order, once its
     * order_details rows exist. Called once per generate_order() invocation, i.e.
     * once per vendor in the cart.
     */
    public function createShipmentsForOrder(Order $sourceOrder, Collection $orderDetails): ?Shipment
    {
        if ($orderDetails->isEmpty()) {
            return null;
        }

        // Defense in depth: callers (e.g. payment gateway success callbacks) are
        // expected to guard against re-running order generation for an already-paid
        // payment, but a genuine race between two near-simultaneous requests could
        // still reach here twice for the same order before either guard commits —
        // source_order_id is unique per shipment, so return the existing one instead
        // of attempting a second insert that would crash on the unique constraint.
        $existingShipment = Shipment::where('source_order_id', $sourceOrder->id)->first();
        if ($existingShipment) {
            return $existingShipment;
        }

        $hasDigital = $orderDetails->contains(fn ($detail) => $detail->product_type === 'digital');
        $hasPhysical = $orderDetails->contains(fn ($detail) => ($detail->product_type ?? 'physical') !== 'digital');
        $physicalStatus = $hasPhysical ? ($sourceOrder->order_status ?? 'pending') : null;

        $shipment = Shipment::create([
            'order_id' => $sourceOrder->parent_order_id ?? $sourceOrder->id,
            'source_order_id' => $sourceOrder->id,
            'vendor_id' => $sourceOrder->seller_id,
            'vendor_type' => $sourceOrder->seller_is,
            'has_digital' => $hasDigital,
            'has_physical' => $hasPhysical,
            'digital_status' => $hasDigital ? 'pending' : null,
            'physical_status' => $physicalStatus,
            'status' => 'processing',
        ]);

        foreach ($orderDetails as $detail) {
            $isDigital = $detail->product_type === 'digital';
            $deliveryMode = null;
            $status = $isDigital ? 'pending' : ($physicalStatus ?? 'pending');
            $deliveredAt = null;

            if ($isDigital) {
                // Snapshot the product's delivery_mode at checkout time so later
                // product edits never change a past order's fulfillment.
                $productSnapshot = json_decode($detail->product_details);
                $deliveryMode = $productSnapshot->delivery_mode ?? 'manual';
                if ($deliveryMode === 'auto') {
                    $status = 'delivered';
                    $deliveredAt = now();
                }
            }

            ShipmentItem::create([
                'shipment_id' => $shipment->id,
                'order_detail_id' => $detail->id,
                'type' => $isDigital ? 'digital' : 'physical',
                'delivery_mode' => $deliveryMode,
                'status' => $status,
                'delivered_at' => $deliveredAt,
            ]);
        }

        if ($hasDigital) {
            $allDigitalDelivered = !$shipment->items()->where('type', 'digital')->where('status', '!=', 'delivered')->exists();
            $shipment->digital_status = $allDigitalDelivered ? 'delivered' : 'pending';
            $shipment->digital_delivered_at = $allDigitalDelivered ? now() : null;
            $shipment->save();
        }

        // An all-auto-digital (or otherwise already fully delivered) shipment
        // completes itself right here, inside the checkout transaction.
        $this->refreshShipmentCompletion($shipment->fresh());

        return $shipment->fresh();
    }

    /**
     * Vendor (or admin, when $vendorId is null) marks a single digital line item
     * delivered. $vendorId, when given, must own the shipment or the call is rejected.
     */
    public function markDigitalItemDelivered(int $orderDetailId, ?int $vendorId = null): bool
    {
        $item = ShipmentItem::with('shipment')
            ->where('order_detail_id', $orderDetailId)
            ->where('type', 'digital')
            ->first();

        if (!$item || !$item->shipment) {
            return false;
        }

        // Auto items are delivered by the system at checkout; a vendor (or admin)
        // can never mark them manually.
        if ($item->delivery_mode !== 'manual') {
            return false;
        }

        if ($vendorId !== null && (int) $item->shipment->vendor_id !== $vendorId) {
            return false;
        }

        if ($item->status !== 'delivered') {
            $item->update(['status' => 'delivered', 'delivered_at' => now()]);
        }

        $this->refreshDigitalShipmentStatus($item->shipment);

        return true;
    }

    /**
     * Keep the shipment's physical status (+ its physical shipment_items) in sync
     * whenever the vendor/admin updates the underlying order's order_status.
     */
    public function syncPhysicalShipmentStatus(Order $sourceOrder, string $newStatus): void
    {
        $shipment = Shipment::where('source_order_id', $sourceOrder->id)->first();
        if (!$shipment || !$shipment->has_physical) {
            return;
        }

        $isDelivered = $newStatus === 'delivered';
        $shipment->physical_status = $newStatus;
        $shipment->physical_delivered_at = $isDelivered ? now() : null;
        $shipment->save();

        $shipment->items()->where('type', 'physical')->update([
            'status' => $newStatus,
            'delivered_at' => $isDelivered ? now() : null,
        ]);

        $this->refreshShipmentCompletion($shipment->fresh());
    }

    private function refreshDigitalShipmentStatus(Shipment $shipment): void
    {
        if ($shipment->has_digital) {
            $allDelivered = !$shipment->items()->where('type', 'digital')->where('status', '!=', 'delivered')->exists();
            $shipment->digital_status = $allDelivered ? 'delivered' : 'pending';
            $shipment->digital_delivered_at = $allDelivered ? ($shipment->digital_delivered_at ?? now()) : null;
            $shipment->save();
        }

        $this->refreshShipmentCompletion($shipment->fresh());
    }

    /**
     * A shipment is complete once every type it actually contains is delivered.
     * Recomputes the main order's fulfillment_status from all its shipments.
     */
    public function refreshShipmentCompletion(Shipment $shipment): void
    {
        $digitalDone = !$shipment->has_digital || $shipment->digital_status === 'delivered';
        $physicalDone = !$shipment->has_physical || $shipment->physical_status === 'delivered';
        $isComplete = $digitalDone && $physicalDone;

        if ($isComplete && $shipment->status !== 'completed') {
            $shipment->update(['status' => 'completed', 'delivered_at' => now()]);
        } elseif (!$isComplete && $shipment->status !== 'processing') {
            $shipment->update(['status' => 'processing', 'delivered_at' => null]);
        }

        $this->refreshOrderFulfillmentStatus($shipment->order_id);
    }

    private function refreshOrderFulfillmentStatus(int $mainOrderId): void
    {
        $statuses = Shipment::where('order_id', $mainOrderId)->pluck('status');
        if ($statuses->isEmpty()) {
            return;
        }

        if ($statuses->every(fn ($status) => $status === 'completed')) {
            $fulfillmentStatus = 'completed';
        } elseif ($statuses->contains('completed')) {
            $fulfillmentStatus = 'partially_delivered';
        } else {
            $fulfillmentStatus = 'processing';
        }

        Order::where('id', $mainOrderId)->update([
            'fulfillment_status' => $fulfillmentStatus,
            'fulfillment_completed_at' => $fulfillmentStatus === 'completed' ? now() : null,
        ]);
    }
}
