<?php

namespace App\Http\Controllers\Payment_Methods;

use App\Models\PaymentRequest;
use App\Services\NowPaymentsService;
use App\Traits\Processor;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class NowpaymentsController extends Controller
{
    use Processor;

    private PaymentRequest $payment;

    public function __construct(
        PaymentRequest $payment,
        private readonly NowPaymentsService $nowPaymentsService,
    ) {
        $this->payment = $payment;
    }

    /**
     * Redirect to NOWPayments checkout page.
     */
    public function index(Request $request): View|RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'payment_id' => 'required|uuid',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }

        $payment = PaymentRequest::where('id', $request->payment_id)
            ->where('is_paid', 0)
            ->firstOrFail();

        try {
            $apiKey = $this->nowPaymentsService->getApiKey();
            if (empty($apiKey)) {
                throw new \Exception('NOWPayments API credentials are not configured.');
            }

            $response = Http::withHeaders([
                'x-api-key' => $apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->nowPaymentsService->getInvoiceEndpoint(), [
                'price_amount' => $payment->payment_amount,
                'price_currency' => $payment->currency_code,
                'order_id' => $payment->id,
                'ipn_callback_url' => url('/payment/now-payments/webhook'),
                'success_url' => url('/payment/now-payments/payment') . '?payment_id=' . md5($payment->id) . '&status=success',
                'cancel_url' => url('/payment/now-payments/payment') . '?payment_id=' . md5($payment->id) . '&status=cancel',
            ]);

            $result = $response->json();

            if (!isset($result['invoice_url'])) {
                Log::error('NOWPayments response error', $result ?? []);
                return redirect()->back()->with('error', $result['message'] ?? 'Could not generate invoice.');
            }

            return view('payment.now-pay', [
                'invoice_url' => $result['invoice_url'],
                'payment' => $payment,
            ]);
        } catch (\Exception $e) {
            Log::error('NOWPayments API Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Something went wrong while connecting to payment gateway.');
        }
    }

    /**
     * Generate a payment request.
     */
    public function payment(Request $request): JsonResponse|RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'payment_id' => 'required|uuid',
        ]);

        if ($validator->fails()) {
            return response()->json($this->response_formatter(GATEWAYS_DEFAULT_400, null, $this->error_processor($validator)), 400);
        }

        $data = $this->payment::where(['id' => $request['payment_id'], 'is_paid' => 0])->first();

        if (!$data) {
            return response()->json($this->response_formatter(GATEWAYS_DEFAULT_204), 200);
        }

        $amount = $data->payment_amount;
        $currency = 'USD';
        $paymentId = $data->id;

        $success_url = url('/payment/now-payments/payment') . '?payment_id=' . md5($paymentId) . '&status=success';
        $cancel_url = url('/payment/now-payments/payment') . '?payment_id=' . md5($paymentId) . '&status=cancel';

        $payload = [
            'price_amount' => $amount,
            'price_currency' => $currency,
            'order_id' => $paymentId,
            'success_url' => $success_url,
            'cancel_url' => $cancel_url,
            'ipn_callback_url' => url('/payment/now-payments/webhook'),
        ];

        try {
            $apiKey = $this->nowPaymentsService->getApiKey();
            if (empty($apiKey)) {
                throw new \Exception('NOWPayments API credentials are not configured.');
            }

            $response = Http::withOptions([
                'verify' => false,
                'timeout' => 10,
                'connect_timeout' => 10,
            ])->withHeaders([
                'Content-Type' => 'application/json',
                'x-api-key' => $apiKey,
            ])->post($this->nowPaymentsService->getInvoiceEndpoint(), $payload);

            $nowpaymentResult = $response->json();

            if (isset($nowpaymentResult['invoice_url'])) {
                return redirect()->away($nowpaymentResult['invoice_url']);
            }

            return response()->json($this->response_formatter(GATEWAYS_DEFAULT_500, null, [
                'error' => $nowpaymentResult['message'] ?? 'Error generating payment invoice',
            ]), 500);

        } catch (\Exception $e) {
            Log::error('NOWPayments Payment Error: ' . $e->getMessage());
            return response()->json($this->response_formatter(GATEWAYS_DEFAULT_500, null, [
                'error' => $e->getMessage(),
            ]), 500);
        }
    }

    /**
     * Handle redirect from NOWPayments after payment attempt.
     */
    public function handlePaymentStatus(Request $request): View|RedirectResponse|JsonResponse|Redirector|Application
    {
        $payment_id_hash = $request->get('payment_id');
        $status = $request->get('status');

        if (!$payment_id_hash || !$status) {
            Log::warning('Payment status callback missing parameters', [
                'payment_id' => $payment_id_hash,
                'status' => $status,
            ]);
            return $this->payment_response(null, 'fail');
        }

        $payment = $this->payment::whereRaw('MD5(id) = ?', [$payment_id_hash])->first();

        if (!$payment) {
            return $this->payment_response(null, 'fail');
        }

        if ($status === 'cancel') {
            if ($payment->failure_hook && function_exists($payment->failure_hook)) {
                call_user_func($payment->failure_hook, $payment);
            }

            return $this->payment_response($payment, 'fail');
        }

        if ($status === 'success') {
            if ($payment->is_paid) {
                return $this->payment_response($payment, 'success');
            }

            return view('payment.now-payments-processing', [
                'payment_id_hash' => $payment_id_hash,
            ]);
        }

        return $this->payment_response($payment, 'fail');
    }

    /**
     * Poll payment status while waiting for webhook confirmation.
     */
    public function checkStatus(Request $request): JsonResponse
    {
        $payment_id_hash = $request->get('payment_id');

        if (!$payment_id_hash) {
            return response()->json(['is_paid' => false], 400);
        }

        $payment = $this->payment::whereRaw('MD5(id) = ?', [$payment_id_hash])->first();

        if (!$payment) {
            return response()->json(['is_paid' => false], 404);
        }

        return response()->json(['is_paid' => (bool) $payment->is_paid]);
    }

    /**
     * IPN webhook from NOWPayments.
     */
    public function webhook(Request $request): JsonResponse
    {
        if (!$this->nowPaymentsService->verifyIpnSignature($request)) {
            Log::warning('NowPayments IPN: invalid signature');
            return response()->json(['message' => 'Invalid signature.'], 401);
        }

        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            return response()->json(['status' => 'ignored'], 200);
        }

        Log::info('NOWPayments webhook received', $data);

        $orderId = $data['order_id'] ?? null;
        $paymentStatus = $data['payment_status'] ?? '';
        $payment = $orderId
            ? $this->payment::where('id', $orderId)->first()
            : null;

        if (!$payment) {
            return response()->json(['status' => 'payment_not_found'], 200);
        }

        if ($payment->is_paid) {
            return response()->json(['status' => 'already_paid'], 200);
        }

        if (in_array($paymentStatus, ['finished', 'partially_paid'], true)) {
            $actuallyPaid = $data['actually_paid'] ?? $payment->payment_amount;

            $payment->update([
                'is_paid' => 1,
                'payment_method' => 'now_payments',
                'transaction_id' => (string) ($data['payment_id'] ?? 'nowpay-' . uniqid()),
                'payment_amount' => (float) $actuallyPaid,
            ]);

            $payment->refresh();

            if ($payment->success_hook && function_exists($payment->success_hook)) {
                call_user_func($payment->success_hook, $payment);
            }

            return response()->json(['status' => 'success'], 200);
        }

        if ($paymentStatus === 'failed') {
            if ($payment->failure_hook && function_exists($payment->failure_hook)) {
                call_user_func($payment->failure_hook, $payment);
            }

            return response()->json(['status' => 'failed'], 200);
        }

        if (in_array($paymentStatus, ['refunded', 'expired'], true)) {
            Log::info('NOWPayments payment ended without success', [
                'order_id' => $orderId,
                'payment_status' => $paymentStatus,
            ]);

            if ($payment->failure_hook && function_exists($payment->failure_hook)) {
                call_user_func($payment->failure_hook, $payment);
            }

            return response()->json(['status' => $paymentStatus], 200);
        }

        return response()->json(['status' => 'ignored'], 200);
    }

    /**
     * Payment cancel handler.
     */
    public function cancel(Request $request): JsonResponse|Redirector|RedirectResponse|Application
    {
        $payment_data = $this->payment::where(['id' => $request['payment_id']])->first();
        return $this->payment_response($payment_data, 'fail');
    }
}
