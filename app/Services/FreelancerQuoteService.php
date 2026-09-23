<?php

namespace App\Services;

use App\Enums\Freelancer\QuoteStatus;
use App\Events\FreelancerQuoteAcceptedEvent;
use App\Events\FreelancerQuoteDeclinedEvent;
use App\Events\FreelancerQuoteRepliedEvent;
use App\Events\FreelancerQuoteRequestedEvent;
use App\Models\FreelancerQuoteRequest;
use App\Models\FreelancerService;
use App\Models\Seller;
use App\Traits\FileManagerTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class FreelancerQuoteService
{
    use FileManagerTrait;

    public function __construct(private readonly FreelancerAuditLogService $auditLog)
    {
    }

    /**
     * @param array{description: string, delivery_preference: string, custom_delivery_text?: ?string, budget?: ?float} $data
     * @param \Illuminate\Http\UploadedFile[] $attachmentFiles
     */
    public function requestQuote(int $customerId, int $serviceId, array $data, array $attachmentFiles = []): FreelancerQuoteRequest
    {
        $service = FreelancerService::where('id', $serviceId)->where('is_active', true)->firstOrFail();
        $seller = Seller::where('id', $service->seller_id)
            ->where('seller_type', 'freelancer')
            ->where('status', 'approved')
            ->firstOrFail();

        return DB::transaction(function () use ($service, $seller, $customerId, $data, $attachmentFiles) {
            $attachments = [];
            foreach ($attachmentFiles as $file) {
                $attachments[] = $this->fileUpload(
                    dir: 'freelancer-quotes/',
                    format: $file->getClientOriginalExtension(),
                    file: $file,
                );
            }

            $quote = FreelancerQuoteRequest::create([
                'customer_id' => $customerId,
                'seller_id' => $seller->id,
                'freelancer_service_id' => $service->id,
                'description' => $data['description'],
                'attachment' => $attachments,
                'delivery_preference' => $data['delivery_preference'],
                'custom_delivery_text' => ($data['delivery_preference'] ?? null) === 'custom' ? ($data['custom_delivery_text'] ?? null) : null,
                'budget' => $data['budget'] ?? null,
                'status' => QuoteStatus::Pending,
            ]);

            $this->auditLog->log(
                actorType: 'customer',
                actorId: $customerId,
                subject: $quote,
                action: 'quote_requested',
                before: null,
                after: ['status' => $quote->status->value],
            );

            try {
                event(new FreelancerQuoteRequestedEvent($quote));
            } catch (\Throwable $e) {
                Log::error('[FreelancerQuoteService] requestQuote event failed: ' . $e->getMessage());
            }

            return $quote;
        });
    }

    /**
     * @param array{quoted_price: float|string, quoted_delivery_days: int, reply_message: string} $data
     */
    public function sendQuote(int $quoteId, int $sellerId, array $data): FreelancerQuoteRequest
    {
        return DB::transaction(function () use ($quoteId, $sellerId, $data) {
            $quote = FreelancerQuoteRequest::where('id', $quoteId)
                ->where('seller_id', $sellerId)
                ->lockForUpdate()
                ->firstOrFail();

            $this->assertTransition($quote->status, QuoteStatus::Quoted);
            $before = ['status' => $quote->status->value];

            $quote->update([
                'quoted_price' => $data['quoted_price'],
                'quoted_delivery_days' => $data['quoted_delivery_days'],
                'reply_message' => $data['reply_message'],
                'status' => QuoteStatus::Quoted,
                'replied_at' => now(),
            ]);

            $this->auditLog->log(
                actorType: 'seller',
                actorId: $sellerId,
                subject: $quote,
                action: 'quote_sent',
                before: $before,
                after: ['status' => $quote->status->value],
            );

            try {
                event(new FreelancerQuoteRepliedEvent($quote));
            } catch (\Throwable $e) {
                Log::error('[FreelancerQuoteService] sendQuote event failed: ' . $e->getMessage());
            }

            return $quote;
        });
    }

    public function acceptQuote(int $quoteId, int $customerId): FreelancerQuoteRequest
    {
        return DB::transaction(function () use ($quoteId, $customerId) {
            $quote = FreelancerQuoteRequest::where('id', $quoteId)
                ->where('customer_id', $customerId)
                ->lockForUpdate()
                ->firstOrFail();

            $this->assertTransition($quote->status, QuoteStatus::Accepted);
            $before = ['status' => $quote->status->value];

            $quote->update(['status' => QuoteStatus::Accepted, 'accepted_at' => now()]);

            $this->auditLog->log(
                actorType: 'customer',
                actorId: $customerId,
                subject: $quote,
                action: 'quote_accepted',
                before: $before,
                after: ['status' => $quote->status->value],
            );

            try {
                event(new FreelancerQuoteAcceptedEvent($quote));
            } catch (\Throwable $e) {
                Log::error('[FreelancerQuoteService] acceptQuote event failed: ' . $e->getMessage());
            }

            return $quote;
        });
    }

    public function declineQuote(int $quoteId, int $customerId): FreelancerQuoteRequest
    {
        return DB::transaction(function () use ($quoteId, $customerId) {
            $quote = FreelancerQuoteRequest::where('id', $quoteId)
                ->where('customer_id', $customerId)
                ->lockForUpdate()
                ->firstOrFail();

            $this->assertTransition($quote->status, QuoteStatus::Declined);
            $before = ['status' => $quote->status->value];

            $quote->update(['status' => QuoteStatus::Declined, 'declined_at' => now()]);

            $this->auditLog->log(
                actorType: 'customer',
                actorId: $customerId,
                subject: $quote,
                action: 'quote_declined',
                before: $before,
                after: ['status' => $quote->status->value],
            );

            try {
                event(new FreelancerQuoteDeclinedEvent($quote));
            } catch (\Throwable $e) {
                Log::error('[FreelancerQuoteService] declineQuote event failed: ' . $e->getMessage());
            }

            return $quote;
        });
    }

    private function assertTransition(QuoteStatus $current, QuoteStatus $next): void
    {
        if (!$current->canTransitionTo($next)) {
            throw new RuntimeException("Cannot move a quote from \"{$current->value}\" to \"{$next->value}\".");
        }
    }
}
