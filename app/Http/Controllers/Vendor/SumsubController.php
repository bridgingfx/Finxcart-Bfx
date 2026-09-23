<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Seller;
use App\Models\VendorVerification;
use App\Traits\FileManagerTrait;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class SumsubController extends Controller
{
    use FileManagerTrait;

    private const API_BASE_URL = 'https://api.sumsub.com';

    public function index(): View
    {
        $seller = auth('seller')->user();
        $kycMethod = getWebConfig('kyc_method') ?? 'manual';
        $verification = $seller?->vendorVerification;

        return view('vendor-views.kyc.index', compact('seller', 'kycMethod', 'verification'));
    }

    public function getAccessToken(): JsonResponse
    {
        $seller = auth('seller')->user();

        if (!$seller) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        if ((getWebConfig('kyc_method') ?? 'manual') !== 'sumsub') {
            return response()->json(['message' => 'Sumsub KYC is not enabled.'], 422);
        }

        $credentials = $this->credentials();

        if (!$credentials['app_token'] || !$credentials['secret_key']) {
            return response()->json(['message' => 'Sumsub credentials are not configured.'], 422);
        }

        $userId = $this->externalUserId((int) $seller->id);
        $levelName = $credentials['flow_name'] ?: 'basic-kyc';
        $path = '/resources/accessTokens/sdk';
        $query = http_build_query([
            'userId' => $userId,
            'levelName' => $levelName,
            'ttlInSecs' => 600,
        ], '', '&', PHP_QUERY_RFC3986);
        $urlPath = $path . '?' . $query;

        $response = Http::withHeaders($this->signedHeaders('POST', $urlPath, '', $credentials))
            ->acceptJson()
            ->send('POST', self::API_BASE_URL . $urlPath);

        if (!$response->successful()) {
            Log::error('Sumsub access token request failed', [
                'seller_id' => $seller->id,
                'status' => $response->status(),
                'body' => $response->json() ?? $response->body(),
            ]);

            return response()->json(['message' => 'Unable to start Sumsub verification.'], 502);
        }

        $this->markVerificationStarted($seller, $userId, $response->json('applicantId'));

        return response()->json([
            'token' => $response->json('token'),
            'userId' => $userId,
        ]);
    }

    public function requestManualReview(Request $request): RedirectResponse
    {
        $seller = auth('seller')->user();
        $verification = $seller?->vendorVerification;
        $companyVerified = ($seller?->status ?? null) === 'approved'
            || ($verification?->status ?? null) === 'approved';

        if ((getWebConfig('kyc_method') ?? 'manual') !== 'manual') {
            ToastMagic::error(translate('Manual_payout_KYC_is_not_enabled'));
            return back();
        }

        if (!$companyVerified) {
            ToastMagic::error(translate('Complete_company_profile_verification_first'));
            return back();
        }

        $request->validate([
            'payout_kyc_document' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf,webp', 'max:10240'],
            'payout_kyc_note' => ['nullable', 'string', 'max:1000'],
        ]);

        if (!$verification) {
            $verification = VendorVerification::create(['seller_id' => $seller->id]);
        }

        if (!empty($verification->payout_kyc_document)) {
            $this->delete('vendor-verifications/' . $verification->payout_kyc_document);
        }

        $document = $this->fileUpload(
            dir: 'vendor-verifications/',
            format: $this->safeMimeExtension($request->file('payout_kyc_document')),
            file: $request->file('payout_kyc_document')
        );

        $verification->update([
            'payout_kyc_document' => $document,
            'payout_kyc_note' => $request->input('payout_kyc_note'),
            'payout_kyc_submitted_at' => now(),
        ]);

        $seller->update(['kyc_status' => 'pending']);

        ToastMagic::success(translate('Payout_KYC_review_request_sent'));
        return back();
    }

    public function webhook(Request $request): JsonResponse
    {
        $credentials = $this->credentials();

        if (!$this->validWebhookSignature($request, $credentials['secret_key'])) {
            Log::warning('Invalid Sumsub webhook signature');
            return response()->json(['message' => 'Invalid signature.'], 401);
        }

        $payload = $request->json()->all();

        $eventId = ($payload['applicantId'] ?? '')
            . '_' . data_get($payload, 'reviewResult.reviewAnswer', '')
            . '_' . ($payload['createdAt'] ?? '');

        $cacheKey = 'sumsub_event_' . hash('sha256', $eventId);
        if (Cache::has($cacheKey)) {
            return response()->json(['message' => 'already_processed'], 200);
        }
        Cache::put($cacheKey, true, now()->addHours(24));

        $externalUserId = (string) ($payload['externalUserId'] ?? '');
        $sellerId = $this->sellerIdFromExternalUserId($externalUserId);

        if (!$sellerId) {
            return response()->json(['message' => 'Unknown vendor.'], 404);
        }

        $seller = Seller::find($sellerId);

        if (!$seller) {
            return response()->json(['message' => 'Vendor not found.'], 404);
        }

        $reviewStatus = (string) ($payload['reviewStatus'] ?? '');
        $reviewAnswer = (string) data_get($payload, 'reviewResult.reviewAnswer', '');
        $verificationStatus = match (true) {
            $reviewAnswer === 'GREEN' => 'approved',
            $reviewAnswer === 'RED' => 'rejected',
            default => 'pending',
        };

        DB::transaction(function () use ($seller, $payload, $reviewStatus, $verificationStatus) {
            $sellerUpdate = [];

            if (Schema::hasColumn('sellers', 'kyc_status')) {
                $sellerUpdate['kyc_status'] = $verificationStatus;
            }

            if ($sellerUpdate) {
                $seller->update($sellerUpdate);
            }

            $seller->vendorVerification?->update([
                'sumsub_applicant_id' => $payload['applicantId'] ?? null,
                'sumsub_review_status' => $reviewStatus ?: null,
            ]);
        });

        return response()->json(['message' => 'Webhook processed.']);
    }

    private function credentials(): array
    {
        return [
            'app_token' => trim((string) getWebConfig('sumsub_app_token')),
            'secret_key' => trim((string) getWebConfig('sumsub_secret_key')),
            'flow_name' => trim((string) (getWebConfig('sumsub_flow_name') ?: 'basic-kyc')),
        ];
    }

    private function signedHeaders(string $method, string $urlPath, string $body, array $credentials): array
    {
        $timestamp = (string) time();
        $signature = hash_hmac('sha256', $timestamp . strtoupper($method) . $urlPath . $body, $credentials['secret_key']);

        return [
            'X-App-Token' => $credentials['app_token'],
            'X-App-Access-Ts' => $timestamp,
            'X-App-Access-Sig' => $signature,
        ];
    }

    private function validWebhookSignature(Request $request, string $secretKey): bool
    {
        $signature = (string) $request->header('X-Payload-Digest');
        $algorithm = strtolower((string) $request->header('X-Payload-Digest-Alg', 'HMAC_SHA256_HEX'));

        if (!$signature || !$secretKey) {
            return false;
        }

        $hashAlgorithm = str_contains($algorithm, '512') ? 'sha512' : 'sha256';
        $expectedSignature = hash_hmac($hashAlgorithm, $request->getContent(), $secretKey);

        return hash_equals(strtolower($signature), strtolower($expectedSignature));
    }

    private function markVerificationStarted(Seller $seller, string $userId, ?string $applicantId): void
    {
        DB::transaction(function () use ($seller, $userId, $applicantId) {
            if (Schema::hasColumn('sellers', 'kyc_status') && $seller->kyc_status !== 'approved') {
                $seller->update(['kyc_status' => 'pending']);
            }

            $seller->vendorVerification?->update([
                'sumsub_applicant_id' => $applicantId ?: $userId,
                'sumsub_review_status' => 'pending',
            ]);
        });
    }

    private function externalUserId(int $sellerId): string
    {
        return 'vendor_' . $sellerId;
    }

    private function sellerIdFromExternalUserId(string $externalUserId): ?int
    {
        if (!Str::startsWith($externalUserId, 'vendor_')) {
            return null;
        }

        $sellerId = (int) Str::after($externalUserId, 'vendor_');

        return $sellerId > 0 ? $sellerId : null;
    }

    private function safeMimeExtension(\Illuminate\Http\UploadedFile $file): string
    {
        $allowed = [
            'image/jpeg'      => 'jpg',
            'image/png'       => 'png',
            'image/webp'      => 'webp',
            'application/pdf' => 'pdf',
        ];
        $mime = $file->getMimeType();
        return $allowed[$mime] ?? 'bin';
    }
}
