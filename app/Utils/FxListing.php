<?php

namespace App\Utils;

use App\Enums\TransactionType;

/**
 * FxListing — verified transaction-type mapping for the fx catalog redesign.
 *
 * Maps ONLY verified backend fields (product_type, digital_product_type,
 * not_sellable, event_*, *_url). Never invents services, prices, badges or
 * delivery promises. Missing facts surface as "Not provided".
 */
class FxListing
{
    /**
     * @param object $product Product model (array or object access)
     * @return array{key:string,tag:string,delivery:?string,buyable:bool,attrs:array,primary:array}
     */
    public static function type(object $product): array
    {
        // Key resolution delegates to the canonical business-model mapping
        // (App\Enums\TransactionType via Product::transactionType()) WITH the
        // products.transaction_type override respected: the override is the
        // vendor/admin's own explicit classification of the listing, so
        // honoring it is what keeps the tag, delivery promise and buy actions
        // truthful. Ignoring it re-labeled fixed-scope services as
        // "Physical product" with "Shipped to your address" — a false
        // delivery claim. This match only translates canonical keys into this
        // catalog's display vocabulary.
        $canonical = TransactionType::forProduct($product)->value;

        $pt = match ($canonical) {
            'download' => 'digital',
            'event' => 'event',
            'provider_directory' => 'broker',
            'subscription' => 'subscription',
            'recurring' => 'recurring',
            'fixed_service' => 'fixed_service',
            'custom_service' => 'custom_service',
            default => 'physical',
        };
        $notProvided = translate('not_provided');

        $result = match ($pt) {
            'digital' => self::digital($product),
            'event' => [
                'key' => 'event',
                'tag' => translate('event'),
                'delivery' => !empty($product->event_days)
                    ? translate('event_dates') . ': ' . $product->event_days
                    : null,
                'buyable' => false,
                'attrs' => [
                    [translate('event_dates'), !empty($product->event_days) ? $product->event_days : $notProvided],
                    [translate('tickets'), !empty($product->book_tickets_url) ? translate('booking_available') : translate('see_website')],
                ],
                'primary' => ['label' => translate('view_details'), 'kind' => 'details', 'url' => null],
            ],
            'broker' => [
                'key' => 'directory',
                'tag' => translate('provider_directory'),
                'delivery' => null,
                'buyable' => false,
                'attrs' => [
                    [translate('listing_type'), translate('provider_directory')],
                    [translate('more_info'), translate('on_provider_website')],
                ],
                'primary' => [
                    'label' => translate('view_provider'),
                    'kind' => 'external',
                    'url' => $product->open_account_url ?: ($product->contact_broker_url ?: $product->external_url),
                ],
            ],
            'subscription', 'recurring' => [
                'key' => $pt,
                'tag' => $pt === 'subscription' ? translate('subscription') : translate('recurring'),
                'delivery' => translate('billed_on_recurring_basis'),
                'buyable' => !$product->not_sellable,
                'attrs' => [
                    [translate('billing'), translate('recurring_billing')],
                    [translate('delivery_model'), translate('billed_on_recurring_basis')],
                ],
                'primary' => ['label' => translate('view_details'), 'kind' => 'details', 'url' => null],
            ],
            'fixed_service' => [
                'key' => 'fixed_service',
                'tag' => translate('fixed_scope_service'),
                // No invented timeline: the scope and delivery terms live in
                // the listing details, written by the provider.
                'delivery' => translate('delivered_per_listing_terms'),
                'buyable' => !$product->not_sellable,
                'attrs' => [
                    [translate('service_type'), translate('fixed_scope_service')],
                    [translate('delivery_model'), translate('delivered_per_listing_terms')],
                ],
                'primary' => ['label' => translate('buy'), 'kind' => 'drawer', 'url' => null],
            ],
            'custom_service' => [
                'key' => 'custom_service',
                'tag' => translate('custom_service'),
                // Quote-based: no fixed buy-now semantics through the cart.
                'delivery' => translate('quote_before_purchase'),
                'buyable' => false,
                'attrs' => [
                    [translate('service_type'), translate('custom_service')],
                    [translate('pricing'), translate('by_quote')],
                ],
                'primary' => ['label' => translate('request_quote'), 'kind' => 'details', 'url' => null],
            ],
            default => [ // physical + any unknown fallback
                'key' => 'physical',
                'tag' => translate('physical_product'),
                'delivery' => translate('shipped_to_your_address'),
                'buyable' => !$product->not_sellable,
                'attrs' => [
                    [translate('delivery_model'), translate('shipped_to_your_address')],
                    [translate('availability'), self::stockLabel($product)],
                ],
                'primary' => ['label' => translate('view_details'), 'kind' => 'details', 'url' => null],
            ],
        };
        // Expose the canonical transaction type for compare enforcement so
        // fulfillment variants (download vs download_manual) stay comparable.
        $result['canonical'] = $canonical;
        return $result;
    }

    protected static function digital(object $product): array
    {
        // ONE accurate delivery promise — canonical source is
        // Product::deliveryPromiseKey(), derived from the verified
        // digital_product_type flag (never a hardcoded template string):
        //   ready_product    => instant_download_after_payment (files pre-uploaded)
        //   ready_after_sell => file_sent_after_order_processed (admin uploads later)
        // Fallback re-derives from the same verified flag for non-Product objects.
        $key = method_exists($product, 'deliveryPromiseKey') ? $product->deliveryPromiseKey() : null;
        if (!is_string($key) || $key === '') {
            $key = (($product->digital_product_type ?? null) === 'ready_product')
                ? 'instant_download_after_payment'
                : 'file_sent_after_order_processed';
        }
        $promise = translate($key);
        $ready = ($product->digital_product_type ?? null) === 'ready_product';

        return [
            'key' => $ready ? 'download' : 'download_manual',
            'tag' => translate('download'),
            'delivery' => $promise,
            'buyable' => !$product->not_sellable,
            'attrs' => [
                [translate('delivery_model'), $promise],
                [translate('access'), translate('from_your_orders')],
            ],
            'primary' => ['label' => translate('buy'), 'kind' => 'drawer', 'url' => null],
        ];
    }

    protected static function stockLabel(object $product): string
    {
        $stock = (int)($product->current_stock ?? 0);
        if ($stock <= 0) {
            return translate('out_of_stock');
        }
        if ($stock < 10) {
            return translate('low_stock');
        }
        return translate('in_stock');
    }

    /**
     * Provider/seller display name. Never invents: falls back to company name
     * for in-house products, "Not provided" when nothing is known.
     */
    public static function providerName(object $product): string
    {
        $shopName = $product->seller->shop->name ?? null;
        if ($shopName) {
            return $shopName;
        }
        if (($product->added_by ?? null) === 'admin') {
            return getWebConfig(name: 'company_name') ?? translate('in_house');
        }
        return translate('not_provided');
    }

    /** Price block for cards: [priceHtml, oldPriceHtml|null] using the same pricing pipeline as checkout. */
    public static function priceStrings(object $product): array
    {
        if ($product->not_sellable) {
            return [null, null];
        }
        $price = getProductPriceByType(product: $product, type: 'discounted_unit_price', result: 'string');
        $old = null;
        if (getProductPriceByType(product: $product, type: 'discount', result: 'value') > 0) {
            $old = webCurrencyConverter(amount: $product->unit_price);
        }
        $unit = $product->unit ?? null;
        if ($unit && in_array($product->product_type, ['subscription', 'recurring'], true)) {
            $price .= ' / ' . $unit;
        }
        return [$price, $old];
    }
}
