<?php

namespace App\Http\Controllers\Payment_Methods;

use App\Models\PaymentRequest;
use App\Models\User;
use App\Traits\Processor;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Stripe\Checkout\Session;
use Stripe\Stripe;

class StripePaymentController extends Controller
{
    use Processor;

    private $config_values;
    private PaymentRequest $payment;

    public function __construct(PaymentRequest $payment)
    {
        $config = $this->payment_config('stripe', 'payment_config');
        if (!is_null($config) && $config->mode == 'live') {
            $this->config_values = json_decode($config->live_values);
        } elseif (!is_null($config) && $config->mode == 'test') {
            $this->config_values = json_decode($config->test_values);
        }
        $this->payment = $payment;
    }

    public function index(Request $request): View|Factory|JsonResponse|Application
    {
        $validator = Validator::make($request->all(), [
            'payment_id' => 'required|uuid'
        ]);

        if ($validator->fails()) {
            return response()->json($this->response_formatter(GATEWAYS_DEFAULT_400, null, $this->error_processor($validator)), 400);
        }

        $data = $this->payment::where(['id' => $request['payment_id']])->where(['is_paid' => 0])->first();
        if (!isset($data)) {
            return response()->json($this->response_formatter(GATEWAYS_DEFAULT_204), 200);
        }
        $config = $this->config_values;

        return view('payment.stripe', compact('data', 'config'));
    }

    public function payment_process_3d(Request $request): JsonResponse
    {
        $data = $this->payment::where(['id' => $request['payment_id']])->where(['is_paid' => 0])->first();
        if (!isset($data)) {
            return response()->json($this->response_formatter(GATEWAYS_DEFAULT_204), 200);
        }
        $payment_amount = $data['payment_amount'];

        // Get the user through the user_id stored in the payment table
        // $user = User::find($data['payer_id']);

        // if (!$user || empty($user->email)) {
        //     return response()->json([
        //         'message' => 'Customer email was not found.',
        //     ], 422);
        // }

        if (empty($this->config_values->api_key ?? null)) {
            \Log::error('[Stripe] payment_process_3d() missing/misconfigured Stripe API key for payment_id=' . $data->id);
            return response()->json($this->response_formatter(GATEWAYS_DEFAULT_204), 200);
        }

        header('Content-Type: application/json');
        $currency_code = $data->currency_code;

        if ($data['additional_data'] != null) {
            $business = json_decode($data['additional_data']);
            $business_name = $business->business_name ?? "my_business";
            $business_logo = $business->business_logo ??  url('/');
        } else {
            $business_name = "my_business";
            $business_logo = url('/');
        }

        try {
            Stripe::setApiKey($this->config_values->api_key);
            $checkout_session = Session::create([
                'payment_method_types' => ['card'],
                // 'customer_email' => $user->email ?? '',
                'line_items' => [[
                    'price_data' => [
                        'currency' => $currency_code ?? 'usd',
                        'unit_amount' => round($payment_amount, 2) * 100,
                        'product_data' => [
                            'name' => $business_name,
                            'images' => [$business_logo],
                        ],
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => url('/') . '/payment/stripe/success?session_id={CHECKOUT_SESSION_ID}&payment_id=' . $data->id,
                'cancel_url' => url()->previous(),
            ]);
        } catch (\Throwable $e) {
            \Log::error('[Stripe] payment_process_3d() checkout session creation failed: ' . $e->getMessage());
            return response()->json(['message' => translate('Payment_initialization_failed')], 422);
        }

        // 'url' lets the client do a plain redirect instead of calling the
        // deprecated Stripe.js stripe.redirectToCheckout(sessionId) API, which
        // newer Stripe.js versions reject — that was surfacing as a generic
        // "Payment initialization failed" error with no useful detail.
        return response()->json(['id' => $checkout_session->id, 'url' => $checkout_session->url]);
    }

    public function success(Request $request)
    {
        // A missing session_id, missing/misconfigured Stripe credentials, or an
        // invalid/expired session ID all previously threw an uncaught exception
        // here (500) instead of sending the customer to the normal failure page —
        // any of those are reachable just by revisiting or bookmarking this URL.
        if (!$request->filled('session_id') || !isset($this->config_values->api_key)) {
            return redirect()->route('payment-fail');
        }

        // Idempotency guard: the customer's browser can hit this success_url more than
        // once for the same payment (refresh, back/forward, double-tap on mobile). Once
        // is_paid is already 1, the order/shipment for this payment has already been
        // generated by the block below — re-running it against the same (still-cached)
        // cart created a second order and crashed on the shipments unique-key constraint.
        $alreadyPaid = $this->payment::where(['id' => $request['payment_id']])->where(['is_paid' => 1])->first();
        if (isset($alreadyPaid)) {
            return $this->payment_response($alreadyPaid, 'success');
        }

        try {
            Stripe::setApiKey($this->config_values->api_key);
            $session = Session::retrieve($request->get('session_id'));
        } catch (\Throwable $e) {
            \Log::error('[Stripe] success() session retrieval failed: ' . $e->getMessage());
            return redirect()->route('payment-fail');
        }

        if ($session->payment_status == 'paid' && $session->status == 'complete') {

            $updated = $this->payment::where(['id' => $request['payment_id']])->where(['is_paid' => 0])->update([
                'payment_method' => 'stripe',
                'is_paid' => 1,
                'transaction_id' => $session->payment_intent,
            ]);

            $data = $this->payment::where(['id' => $request['payment_id']])->first();

            if (!isset($data)) {
                return redirect()->route('payment-fail');
            }

            // $updated is 0 when a concurrent request already flipped is_paid to 1
            // between the guard check above and this update — skip the hook again here
            // so a race between two near-simultaneous requests still only fires it once.
            if ($updated && function_exists($data->success_hook)) {
                call_user_func($data->success_hook, $data);
            }

            return $this->payment_response($data,'success');
        }
        $payment_data = $this->payment::where(['id' => $request['payment_id']])->first();
        if (!isset($payment_data)) {
            return redirect()->route('payment-fail');
        }
        if (function_exists($payment_data->failure_hook)) {
            call_user_func($payment_data->failure_hook, $payment_data);
        }
        return $this->payment_response($payment_data,'fail');
    }
}
