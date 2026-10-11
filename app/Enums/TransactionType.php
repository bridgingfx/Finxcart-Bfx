<?php

namespace App\Enums;

use App\Enums\Freelancer\ServicePricingType;

/**
 * TransactionType — single source of truth for FinXCart's commercial model.
 *
 * The five brief transaction types, plus the legacy 6Valley product types that
 * are kept for backwards compatibility (cart, checkout, order pipelines and
 * the legacy Blade all branch on them — they are NOT removed, only named).
 *
 * Field evidence for each mapping rule (see forProduct / forFreelancerService):
 *  - download           <- products.product_type = 'digital'
 *                          (fulfillment variant: products.digital_product_type
 *                          'ready_product' = instant, 'ready_after_sell' = manual)
 *  - fixed_service      <- freelancer_services.pricing_type = 'fixed'
 *                          (App\Enums\Freelancer\ServicePricingType::Fixed)
 *  - custom_service     <- freelancer_services.pricing_type = 'quote'
 *                          (App\Enums\Freelancer\ServicePricingType::Quote,
 *                          goes through freelancer_quote_requests)
 *  - provider_directory <- products.product_type = 'broker'
 *                          (always products.not_sellable = 1, set by
 *                          ProductService for event/broker listings)
 *  - event              <- products.product_type = 'event'
 *                          (always products.not_sellable = 1; dates in
 *                          products.event_days, tickets via book_tickets_url)
 *  - physical           <- products.product_type = 'physical' (legacy 6Valley)
 *  - subscription       <- products.product_type = 'subscription' (legacy billing variant)
 *  - recurring          <- products.product_type = 'recurring' (legacy billing variant)
 *
 * products.transaction_type (nullable) is an explicit admin override: when set
 * to one of these values it wins over the derived mapping. NULL = derive.
 */
enum TransactionType: string
{
    case Download = 'download';
    case FixedService = 'fixed_service';
    case CustomService = 'custom_service';
    case ProviderDirectory = 'provider_directory';
    case Event = 'event';
    case Physical = 'physical';
    case Subscription = 'subscription';
    case Recurring = 'recurring';

    /**
     * Canonical type for a Product. Prefers the explicit
     * products.transaction_type override when it holds a known value.
     *
     * @param bool $ignoreOverride when true, derive purely from verified
     *                             fields (product_type etc.) — used by
     *                             presentation layers that must only reflect
     *                             verified fulfillment fields.
     */
    public static function forProduct(object $product, bool $ignoreOverride = false): self
    {
        if (!$ignoreOverride) {
            $override = $product->transaction_type ?? null;
            if (is_string($override) && $override !== '' && self::tryFrom($override)) {
                return self::from($override);
            }
        }

        return match ($product->product_type ?? 'physical') {
            'digital' => self::Download,
            'event' => self::Event,
            'broker' => self::ProviderDirectory,
            'subscription' => self::Subscription,
            'recurring' => self::Recurring,
            default => self::Physical,
        };
    }

    /**
     * Canonical type for a FreelancerService. The pricing_type enum is the
     * authoritative discriminator: 'fixed' = fixed-scope service sold at a
     * set price, 'quote' = custom service requiring a quote request first.
     */
    public static function forFreelancerService(object $service): self
    {
        $pricingType = $service->pricing_type ?? null;
        if ($pricingType instanceof ServicePricingType) {
            return $pricingType === ServicePricingType::Quote ? self::CustomService : self::FixedService;
        }

        return ((string) $pricingType) === ServicePricingType::Quote->value
            ? self::CustomService
            : self::FixedService;
    }

    /**
     * Types that can never be purchased through the cart/checkout pipeline.
     * They are informational listings with external actions (book tickets,
     * open account, contact provider) instead of buy buttons.
     */
    public static function nonPurchasable(): array
    {
        return [self::Event, self::ProviderDirectory];
    }

    public function isPurchasable(): bool
    {
        return !in_array($this, self::nonPurchasable(), true);
    }

    /**
     * The translate() key for the ONE accurate delivery promise per type.
     * Download needs the product row to distinguish instant vs manual file
     * delivery — use Product::deliveryPromiseKey() for that. Service
     * promises live on the freelancer side (delivery_time_days).
     */
    public function promiseKey(): ?string
    {
        return match ($this) {
            self::Event => 'event_dates',
            self::ProviderDirectory => 'on_provider_website',
            self::Subscription, self::Recurring => 'billed_on_recurring_basis',
            self::Physical => 'shipped_to_your_address',
            self::Download, self::FixedService, self::CustomService => null,
        };
    }
}
