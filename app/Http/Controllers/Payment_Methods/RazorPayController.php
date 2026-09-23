<?php

namespace App\Http\Controllers\Payment_Methods;

use App\Models\PaymentRequest;
use App\Models\User;
use App\Traits\Processor;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log; // Import Log facade
use Illuminate\Support\Facades\Validator;
use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError; // Import exception

class RazorPayController extends Controller
{
    use Processor;

    private PaymentRequest $payment;
    private $user;

    public function __construct(PaymentRequest $payment, User $user)
    {
        $config = $this->payment_config('razor_pay', 'payment_config');
        $razor = false;
        if (!is_null($config) && $config->mode == 'live') {
            $razor = json_decode($config->live_values);
        } elseif (!is_null($config) && $config->mode == 'test') {
            $razor = json_decode($config->test_values);
        }

        if ($razor) {
            $config = array(
                'api_key' => $razor->api_key,
                'api_secret' => $razor->api_secret
            );
            Config::set('razor_config', $config);
        }

        $this->payment = $payment;
        $this->user = $user;
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
        $payer = json_decode($data['payer_information']);

        if ($data['additional_data'] != null) {
            $business = json_decode($data['additional_data']);
            $business_name = $business->business_name ?? "my_business";
            $business_logo = $business->business_logo ?? url('/');
        } else {
            $business_name = "my_business";
            $business_logo = url('/');
        }

        return view('payment.razor-pay', compact('data', 'payer', 'business_logo', 'business_name'));
    }

    /**
     * THIS FUNCTION IS LIKELY DEPRECATED if you use verifyPayment and payment_capture=1
     * This function attempts to manually capture a payment.
     * However, your createOrder function sets 'payment_capture' => 1, which means
     * payments are *auto-captured* by Razorpay.
     * Calling capture() on an already-captured payment can cause errors.
     * You should likely be using the verifyPayment() method instead.
     */
    public function payment(Request $request): JsonResponse|Redirector|RedirectResponse|Application
    {
        $input = $request->all();
        $api = new Api(config('razor_config.api_key'), config('razor_config.api_secret'));
        
        // Log a warning that this deprecated method is being called
        Log::warning('RazorPayController@payment (deprecated) was called.', ['payment_id' => $input['razorpay_payment_id'] ?? null]);

        try {
            $payment = $api->payment->fetch($input['razorpay_payment_id']);

            // Check if payment is already captured
            if ($payment['status'] == 'captured') {
                // Payment is already auto-captured, just update DB and return success
                $this->payment::where(['id' => $request['payment_id']])->update([
                    'payment_method' => 'razor_pay',
                    'is_paid' => 1,
                    'transaction_id' => $input['razorpay_payment_id'],
                ]);
                
                $data = $this->payment::where(['id' => $request['payment_id']])->first();
                if (isset($data) && function_exists($data->success_hook)) {
                    call_user_func($data->success_hook, $data);
                }
                return $this->payment_response($data, 'success');
            } else {
                // If for some reason it wasn't captured, capture the FULL amount
                $response = $api->payment->fetch($input['razorpay_payment_id'])->capture(array('amount' => $payment['amount'])); // Do NOT subtract fee
                
                $this->payment::where(['id' => $request['payment_id']])->update([
                    'payment_method' => 'razor_pay',
                    'is_paid' => 1,
                    'transaction_id' => $input['razorpay_payment_id'],
                ]);

                $data = $this->payment::where(['id' => $request['payment_id']])->first();
                if (isset($data) && function_exists($data->success_hook)) {
                    call_user_func($data->success_hook, $data);
                }
                return $this->payment_response($data, 'success');
            }

        } catch (\Exception $e) {
            // Log the error
            Log::error('RazorPayController@payment Error: ' . $e->getMessage(), ['request' => $input]);
            
            $payment_data = $this->payment::where(['id' => $request['payment_id']])->first();
            if (isset($payment_data) && function_exists($payment_data->failure_hook)) {
                call_user_func($payment_data->failure_hook, $payment_data);
            }
            return $this->payment_response($payment_data, 'fail');
        }
    }

    public function callback(Request $request): JsonResponse|Redirector|RedirectResponse|Application
    {
        $input = $request->all();
        $data_id = base64_decode($request?->payment_data);
        $payment_data = $this->payment::where(['id' => $data_id])->first();
        if (count($input) && !empty($input['razorpay_payment_id'])) {
            if (isset($payment_data) && function_exists($payment_data->success_hook)) {
                $payment_data->payment_method = 'razor_pay';
                $payment_data->is_paid = 1;
                $payment_data->transaction_id = $input['razorpay_payment_id'];
                $payment_data->save();
                call_user_func($payment_data->success_hook, $payment_data);
                return $this->payment_response($payment_data, 'success');
            }
        }
        return $this->payment_response($payment_data, 'fail');
    }

    public function cancel(Request $request): JsonResponse|Redirector|RedirectResponse|Application
    {
        $payment_data = $this->payment::where(['id' => $request['payment_id']])->first();
        return $this->payment_response($payment_data, 'fail');
    }

    public function createOrder(Request $request): JsonResponse|Redirector|RedirectResponse|Application
    {
        $request->validate([
            'payment_amount' => 'required', // Validate as required, but sanitize before using
            'currency_code' => 'required|string'
        ]);

        try {
            $api = new Api(config('razor_config.api_key'), config('razor_config.api_secret'));

            // === FIX: SANITIZE THE INPUT AMOUNT ===
            // 1. Get the raw amount from the request (e.g., "12,000" or "12,000.00")
            $raw_amount = $request['payment_amount'];

            // 2. Remove all commas and spaces
            $sanitized_amount_string = str_replace([',', ' '], '', $raw_amount);

            // 3. Convert the clean string to a float, then round to 2 decimal places
            $rounded_amount = round(floatval($sanitized_amount_string), 2);

            // 4. Multiply by 100 and cast to integer for Razorpay
            $amount_in_cents = (int)($rounded_amount * 100);
            
            // Log the conversion for debugging
            Log::info('Razorpay createOrder conversion', [
                'raw' => $raw_amount,
                'sanitized' => $sanitized_amount_string,
                'rounded' => $rounded_amount,
                'final_cents' => $amount_in_cents
            ]);

            // 5. Use the sanitized integer amount
            $razorpayOrder = $api->order->create([
                'receipt' => 'order_' . uniqid(),
                'amount' => $amount_in_cents, // Use the sanitized amount
                'currency' => $request['currency_code'],
                'payment_capture' => 1 // Auto-capture payment
            ]);

            return response()->json([
                'status' => true,
                'payment_request_id' => $request['payment_request_id'],
                'order_id' => $razorpayOrder['id'],
                'amount' => $razorpayOrder['amount'], // This will now be the correct integer
                'currency' => $razorpayOrder['currency']
            ]);

        } catch (\Exception $exception) {
            Log::error('Razorpay createOrder Exception: ' . $exception->getMessage(), ['request' => $request->all()]);
            return response()->json([
                'status' => false,
                'message' => $exception->getMessage()
            ]);
        }
    }

    public function verifyPayment(Request $request): JsonResponse|Redirector|RedirectResponse|Application
    {
        $api = new Api(config('razor_config.api_key'), config('razor_config.api_secret'));

        try {
            // Verify payment signature
            $api->utility->verifyPaymentSignature([
                'razorpay_order_id' => $request['order_id'],
                'razorpay_payment_id' => $request['payment_id'],
                'razorpay_signature' => $request['signature']
            ]);

            // Signature is valid. Now fetch payment to double-check status.
            $payment = $api->payment->fetch($request['payment_id']);

            if ($payment && isset($payment['status']) && $payment['status'] == 'captured') {
                $this->payment::where(['id' => $request['payment_request_id']])->update([
                    'payment_method' => 'razor_pay',
                    'is_paid' => 1,
                    'transaction_id' => $request['payment_id'],
                ]);
                $data = $this->payment::where(['id' => $request['payment_request_id']])->first();
                if (isset($data) && function_exists($data->success_hook)) {
                    call_user_func($data->success_hook, $data);
                }
                return $this->payment_response($data, 'success');
            } else {
                // Payment status is not 'captured' (e.g., 'failed', 'created')
                Log::warning('Razorpay verifyPayment: Signature was valid but payment not captured.', ['payment_id' => $request['payment_id'], 'status' => $payment['status'] ?? 'unknown']);
                throw new \Exception('Payment status not "captured"');
            }

        } catch (SignatureVerificationError $e) {
            // This is the "Invalid Signature" error!
            Log::error('Razorpay verifyPayment: INVALID SIGNATURE.', [
                'message' => $e->getMessage(),
                'order_id' => $request['order_id'],
                'payment_id' => $request['payment_id']
            ]);
            $paymentData = $this->payment::where(['id' => $request['payment_request_id']])->first();
            if (isset($paymentData) && function_exists($paymentData->failure_hook)) {
                call_user_func($paymentData->failure_hook, $paymentData);
            }
            return $this->payment_response($paymentData, 'fail');

        } catch (\Exception $e) {
            // Other errors (e.g., payment fetch failed, DB error)
            Log::error('Razorpay verifyPayment: General Error.', [
                'message' => $e->getMessage(),
                'payment_id' => $request['payment_id']
            ]);
            $paymentData = $this->payment::where(['id' => $request['payment_request_id']])->first();
            if (isset($paymentData) && function_exists($paymentData->failure_hook)) {
                call_user_func($paymentData->failure_hook, $paymentData);
            }
            return $this->payment_response($paymentData, 'fail');
        }
    }
}