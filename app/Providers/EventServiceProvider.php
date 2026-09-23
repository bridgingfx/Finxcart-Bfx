<?php

namespace App\Providers;

use App\Events\AddFundToWalletEvent;
use App\Events\CashCollectEvent;
use App\Events\CustomerRegistrationEvent;
use App\Events\CustomerStatusUpdateEvent;
use App\Events\DeliverymanPasswordResetEvent;
use App\Events\DigitalProductDownloadEvent;
use App\Events\DigitalProductOtpVerificationEvent;
use App\Events\EmailVerificationEvent;
use App\Events\FreelancerKycStatusChangedEvent;
use App\Events\FreelancerQuoteRequestedEvent;
use App\Events\FreelancerQuoteRepliedEvent;
use App\Events\FreelancerQuoteAcceptedEvent;
use App\Events\FreelancerQuoteDeclinedEvent;
use App\Events\MaintenanceModeNotificationEvent;
use App\Events\OrderPlacedEvent;
use App\Events\PasswordResetEvent;
use App\Events\ChattingEvent;
use App\Events\OrderStatusEvent;
use App\Events\ProductRequestStatusUpdateEvent;
use App\Events\RefundEvent;
use App\Events\RequestProductRestockEvent;
use App\Events\RestockProductNotificationEvent;
use App\Events\SplitTransactionCompleteEvent;
use App\Events\TierThresholdReachedEvent;
use App\Events\VendorRegistrationEvent;
use App\Events\VendorRegistrationOtpEvent;
use App\Events\WithdrawStatusUpdateEvent;
use App\Listeners\AddFundToWalletListener;
use App\Listeners\CashCollectListener;
use App\Listeners\CustomerRegistrationListener;
use App\Listeners\CustomerStatusUpdateListener;
use App\Listeners\DeliverymanPasswordResetListener;
use App\Listeners\DigitalProductDownloadListener;
use App\Listeners\DigitalProductOtpVerificationListener;
use App\Listeners\EmailVerificationListener;
use App\Listeners\FreelancerKycStatusChangedListener;
use App\Listeners\FreelancerQuoteRequestedListener;
use App\Listeners\FreelancerQuoteRepliedListener;
use App\Listeners\FreelancerQuoteAcceptedListener;
use App\Listeners\FreelancerQuoteDeclinedListener;
use App\Listeners\MaintenanceModeNotificationListener;
use App\Listeners\OrderPlacedListener;
use App\Listeners\PasswordResetListener;
use App\Listeners\ChattingListener;
use App\Listeners\OrderStatusListener;
use App\Listeners\ProductRequestStatusUpdateListener;
use App\Listeners\RefundListener;
use App\Listeners\RequestProductRestockListener;
use App\Listeners\RestockProductNotificationListener;
use App\Listeners\SplitTransactionCompleteListener;
use App\Listeners\TierThresholdReachedListener;
use App\Listeners\VendorRegistrationListener;
use App\Listeners\VendorRegistrationOtpListener;
use App\Listeners\WithdrawStatusUpdateListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        AddFundToWalletEvent::class => [
            AddFundToWalletListener::class,
        ],
        DigitalProductOtpVerificationEvent::class => [
            DigitalProductOtpVerificationListener::class,
        ],
        DeliverymanPasswordResetEvent::class => [
            DeliverymanPasswordResetListener::class,
        ],
        EmailVerificationEvent::class => [
            EmailVerificationListener::class,
        ],
        PasswordResetEvent::class => [
            PasswordResetListener::class,
        ],
        OrderPlacedEvent::class => [
            OrderPlacedListener::class,
        ],
        OrderStatusEvent::class => [
            OrderStatusListener::class,
        ],
        ChattingEvent::class => [
            ChattingListener::class,
        ],
        RefundEvent::class => [
            RefundListener::class,
        ],
        VendorRegistrationEvent::class => [
            VendorRegistrationListener::class,
        ],
        VendorRegistrationOtpEvent::class => [
            VendorRegistrationOtpListener::class,
        ],
        CustomerRegistrationEvent::class => [
            CustomerRegistrationListener::class,
        ],
        CustomerStatusUpdateEvent::class => [
            CustomerStatusUpdateListener::class,
        ],
        WithdrawStatusUpdateEvent::class => [
            WithdrawStatusUpdateListener::class,
        ],
        CashCollectEvent::class => [
            CashCollectListener::class,
        ],
        ProductRequestStatusUpdateEvent::class => [
            ProductRequestStatusUpdateListener::class,
        ],
        DigitalProductDownloadEvent::class => [
            DigitalProductDownloadListener::class,
        ],
        MaintenanceModeNotificationEvent::class => [
            MaintenanceModeNotificationListener::class,
        ],
        RequestProductRestockEvent::class => [
            RequestProductRestockListener::class,
        ],
        RestockProductNotificationEvent::class => [
            RestockProductNotificationListener::class,
        ],
        FreelancerQuoteRequestedEvent::class => [
            FreelancerQuoteRequestedListener::class,
        ],

        FreelancerQuoteRepliedEvent::class => [
            FreelancerQuoteRepliedListener::class,
        ],

        FreelancerQuoteAcceptedEvent::class => [
            FreelancerQuoteAcceptedListener::class,
        ],

        FreelancerQuoteDeclinedEvent::class => [
            FreelancerQuoteDeclinedListener::class,
        ],

        FreelancerKycStatusChangedEvent::class => [
            FreelancerKycStatusChangedListener::class,
        ],
        TierThresholdReachedEvent::class => [
            TierThresholdReachedListener::class,
        ],
        SplitTransactionCompleteEvent::class => [
            SplitTransactionCompleteListener::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
