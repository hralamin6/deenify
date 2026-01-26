<?php

namespace App\Http\Controllers;

use App\Models\PaymentAttempt;
use Illuminate\Http\Request;
use Raziul\Shurjopay\Gateway;
use Shipu\Aamarpay\Aamarpay;

class PaymentController extends Controller
{
    public function callback(Request $request)
    {
        $order_id = $request->order_id;
        $shurjopay = app(Gateway::class);

        try {
            $r = $shurjopay->verifyPayment($order_id)->toArray();

            $response = $shurjopay->verifyPayment($order_id);

            $paymentAttempt = PaymentAttempt::where('provider_reference', $r['customer_order_id'])->firstOrFail();
            $donation = $paymentAttempt->donation;

            if ($response->success()) {
                // Success
                $paymentAttempt->update([
                    'status' => 'success',
                    'completed_at' => now(),
                    'response_payload' => $response->toArray(),
                ]);

                $donation->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                ]);

                return redirect()->route('web.campaign', $donation->campaign->slug)
                    ->with('success', __('Donation successful! Thank you for your support.'));
            } else {
                // Failed
                $paymentAttempt->update([
                    'status' => 'failed',
                    'completed_at' => now(),
                    'response_payload' => $response->toArray(),
                ]);

                $donation->update(['status' => 'failed']);

                return redirect()->route('web.campaign', $donation->campaign->slug)
                    ->with('error', __('Donation failed: ').$response->message());
            }
        } catch (\Exception $e) {
            dd($e);

            //          return redirect()->route('web.home')
            //                ->with('error', __('An error occurred during payment verification.'));
        }
    }

    public function cancel(Request $request)
    {
        $order_id = $request->order_id;
        $paymentAttempt = PaymentAttempt::where('provider_reference', $order_id)->first();

        if ($paymentAttempt) {
            $paymentAttempt->update([
                'status' => 'cancelled',
                'completed_at' => now(),
            ]);

            $donation = $paymentAttempt->donation;
            $donation->update(['status' => 'cancelled']);

            return redirect()->route('web.campaign', $donation->campaign->slug)
                ->with('warning', __('Donation was cancelled.'));
        }

        return redirect()->route('web.home')->with('warning', __('Donation was cancelled.'));
    }

    public function aamarpayRedirect()
    {
        $paymentData = session()->get('aamarpay_payment');

        if (! $paymentData) {
            return redirect()->route('web.home')->with('error', __('Payment session expired.'));
        }

        session()->forget('aamarpay_payment');

        // Return HTML response that auto-submits to AamarPay
        return response()->make('
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="utf-8">
                <title>Redirecting...</title>
                <style>
                    body { font-family: system-ui, -apple-system, sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
                    .loader { text-align: center; color: white; }
                    .spinner { border: 4px solid rgba(255,255,255,0.3); border-top: 4px solid white; border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite; margin: 0 auto 20px; }
                    @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
                </style>
            </head>
            <body>
                <div class="loader">
                    <div class="spinner"></div>
                    <p>Redirecting to payment gateway...</p>
                </div>
                <form id="aamarpay-form" method="POST" action="'.$paymentData['url'].'" style="display:none;">
                    '.$paymentData['fields'].'
                </form>
                <script>document.getElementById("aamarpay-form").submit();</script>
            </body>
            </html>
        ')->header('Content-Type', 'text/html');
    }

    public function aamarpayCallback(Request $request)
    {
        // Log the incoming request for debugging
        \Log::info('AamarPay Callback Received', [
            'all_data' => $request->all(),
            'mer_txnid' => $request->mer_txnid ?? 'not set',
            'pay_status' => $request->pay_status ?? 'not set',
            'status' => $request->status ?? 'not set',
        ]);

        $config = [
            'store_id' => config('aamarpay.store_id'),
            'signature_key' => config('aamarpay.signature_key'),
            'sandbox' => config('aamarpay.sandbox'),
            'redirect_url' => [
                'success' => [
                    'route' => 'payment.aamarpay.callback',
                ],
                'cancel' => [
                    'route' => 'payment.aamarpay.cancel',
                ],
            ],
        ];

        $aamarpay = new Aamarpay($config);

        try {

            $orderId = $request->mer_txnid;
            
            if (!$orderId) {
                \Log::error('AamarPay: No mer_txnid in request');
                return redirect()->route('web.home')
                    ->with('error', __('Invalid payment response.'));
            }

            $paymentAttempt = PaymentAttempt::where('provider_reference', $orderId)->firstOrFail();
            $donation = $paymentAttempt->donation;

            \Log::info('AamarPay: Payment attempt found', [
                'payment_attempt_id' => $paymentAttempt->id,
                'expected_amount' => $paymentAttempt->amount,
                'received_amount' => $request->amount ?? 'not set',
            ]);

            // Custom validation since the package's valid() method expects 'status' field
            // which AamarPay doesn't send. We validate based on what AamarPay actually returns.
            $isPaymentSuccessful = (
                $request->pay_status === 'Successful' &&
                $request->amount == $paymentAttempt->amount &&
                $request->mer_txnid === $orderId
            );

            \Log::info('AamarPay: Validation check', [
                'pay_status' => $request->pay_status,
                'pay_status_match' => $request->pay_status === 'Successful',
                'amount_match' => $request->amount == $paymentAttempt->amount,
                'txnid_match' => $request->mer_txnid === $orderId,
                'is_valid' => $isPaymentSuccessful,
            ]);

            // Validate the payment response
            if ($isPaymentSuccessful) {

                $paymentAttempt->update([
                    'status' => 'success',
                    'completed_at' => now(),
                    'response_payload' => $request->all(),
                ]);

                $donation->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                ]);

                \Log::info('AamarPay: Payment successful', ['donation_id' => $donation->id]);

                // Re-authenticate the user if they were logged in before payment
                // This is necessary because cross-site POST requests don't include session cookies (SameSite=lax)
                if ($donation->user_id && ! auth()->check()) {
                    auth()->loginUsingId($donation->user_id);
                    session()->regenerate();
                }

                return redirect()->route('web.campaign', $donation->campaign->slug)
                    ->with('success', __('Donation successful! Thank you for your support.'));
            } else {
                \Log::warning('AamarPay: Payment validation failed', [
                    'request_data' => $request->all(),
                ]);

                $paymentAttempt->update([
                    'status' => 'failed',
                    'completed_at' => now(),
                    'response_payload' => $request->all(),
                ]);

                $donation->update(['status' => 'failed']);

                // Re-authenticate the user if they were logged in before payment
                if ($donation->user_id && ! auth()->check()) {
                    auth()->loginUsingId($donation->user_id);
                    session()->regenerate();
                }

                return redirect()->route('web.campaign', $donation->campaign->slug)
                    ->with('error', __('Donation failed. Please try again.'));
            }
        } catch (\Exception $e) {
            \Log::error('AamarPay Callback Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('web.home')
                ->with('error', __('An error occurred during payment verification.'));
        }
    }

    public function aamarpayCancel(Request $request)
    {
        $orderId = $request->mer_txnid;
        $paymentAttempt = PaymentAttempt::where('provider_reference', $orderId)->first();

        if ($paymentAttempt) {
            $paymentAttempt->update([
                'status' => 'cancelled',
                'completed_at' => now(),
            ]);

            $donation = $paymentAttempt->donation;
            $donation->update(['status' => 'cancelled']);

            // Re-authenticate the user if they were logged in before payment
            if ($donation->user_id && ! auth()->check()) {
                auth()->loginUsingId($donation->user_id);
                session()->regenerate();
            }

            return redirect()->route('web.campaign', $donation->campaign->slug)
                ->with('warning', __('Donation was cancelled.'));
        }

        return redirect()->route('web.home')->with('warning', __('Donation was cancelled.'));
    }
}
