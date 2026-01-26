# AamarPay Integration Guide

This document provides a comprehensive guide for the AamarPay payment gateway integration in the Deenify application.

## Overview

AamarPay is integrated alongside ShurjoPay as an alternative payment gateway option. Users can select their preferred gateway when making donations to campaigns.

## Package Used

- **Package**: `shipu/php-aamarpay-payment` v2.0.0
- **Repository**: https://github.com/Shipu/php-aamarpay-payment

## Configuration

### Environment Variables

Add the following to your `.env` file:

```dotenv
AAMARPAY_SANDBOX_MODE=true
AAMARPAY_STORE_ID=aamarpaytest
AAMARPAY_SIGNATURE_KEY=dbb74894e82415a2f7ff0ec3a97e4183
```

### Configuration File

Configuration is stored in `config/aamarpay.php`:

```php
return [
    'sandbox' => env('AAMARPAY_SANDBOX_MODE', true),
    'store_id' => env('AAMARPAY_STORE_ID', 'aamarpaytest'),
    'signature_key' => env('AAMARPAY_SIGNATURE_KEY', 'dbb74894e82415a2f7ff0ec3a97e4183'),
    'redirect_url' => [
        'success' => [
            'url' => '', // Set dynamically in the component
        ],
        'cancel' => [
            'url' => '', // Set dynamically in the component
        ],
    ],
];
```

**Note**: The package requires a specific config structure including `redirect_url` in the configuration array.

## Database Changes

### Migration

The payment gateway enum was updated to include 'aamarpay':

```php
Schema::table('payment_attempts', function (Blueprint $table) {
    $table->enum('gateway', ['bkash', 'nagad', 'sslcommerz', 'shurjopay', 'aamarpay'])->change();
});
```

## Routes

The following routes handle AamarPay callbacks:

```php
Route::post('payment/aamarpay/callback', [PaymentController::class, 'aamarpayCallback'])
    ->name('payment.aamarpay.callback');
    
Route::post('payment/aamarpay/cancel', [PaymentController::class, 'aamarpayCancel'])
    ->name('payment.aamarpay.cancel');
```

**Note**: AamarPay uses POST requests for callbacks, unlike ShurjoPay which uses GET.

## Payment Flow

### 1. Initiating Payment

When a user selects AamarPay as the payment gateway in the campaign donation modal:

```php
protected function processAamarPay(\App\Models\PaymentAttempt $paymentAttempt)
{
    $orderId = 'AP' . time() . 'ID' . $paymentAttempt->id;
    
    $config = [
        'store_id' => config('aamarpay.store_id'),
        'signature_key' => config('aamarpay.signature_key'),
        'sandbox' => config('aamarpay.sandbox'),
        'redirect_url' => [
            'success' => [
                'url' => route('payment.aamarpay.callback'),
            ],
            'cancel' => [
                'url' => route('payment.aamarpay.cancel'),
            ],
        ],
    ];

    $aamarpay = new \Shipu\Aamarpay\Aamarpay($config);

    $paymentAttempt->update(['provider_reference' => $orderId]);

    // Use fluent API to build payment request
    $aamarpay->customer([
        'cus_name' => $this->donor_name,
        'cus_email' => $this->donor_email,
        'cus_phone' => '01700000000',
        'cus_add1' => 'Dhaka',
        'cus_add2' => 'Dhaka',
        'cus_city' => 'Dhaka',
        'cus_country' => 'Bangladesh',
    ])
    ->transactionId($orderId)
    ->amount($this->amount)
    ->product('Donation for ' . $this->campaign->title);

    // Generate and return auto-submit form
    return response()->streamDownload(function() use ($aamarpay) {
        echo '<!DOCTYPE html>
        <html>
        <head><title>Redirecting to AamarPay...</title></head>
        <body>
        <form id="aamarpay-form" method="POST" action="' . $aamarpay->paymentUrl() . '">
            ' . $aamarpay->hiddenValue() . '
        </form>
        <script>document.getElementById("aamarpay-form").submit();</script>
        </body>
        </html>';
    }, 'redirect.html', ['Content-Type' => 'text/html']);
}
```

**Note**: AamarPay uses a fluent API pattern with method chaining. The payment is submitted via an auto-submit HTML form.

### 2. Handling Successful Payment

When AamarPay redirects back with a successful payment:

```php
public function aamarpayCallback(Request $request)
{
    $config = [
        'store_id' => config('aamarpay.store_id'),
        'signature_key' => config('aamarpay.signature_key'),
        'sandbox' => config('aamarpay.sandbox'),
        'redirect_url' => [
            'success' => [
                'url' => route('payment.aamarpay.callback'),
            ],
            'cancel' => [
                'url' => route('payment.aamarpay.cancel'),
            ],
        ],
    ];

    $aamarpay = new Aamarpay($config);

    $orderId = $request->mer_txnid;
    $paymentAttempt = PaymentAttempt::where('provider_reference', $orderId)->firstOrFail();
    $donation = $paymentAttempt->donation;

    // Validate the payment using the package's valid() method
    if ($aamarpay->valid($request, $paymentAttempt->amount)) {
        $paymentAttempt->update([
            'status' => 'success',
            'completed_at' => now(),
            'response_payload' => $request->all(),
        ]);

        $donation->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        return redirect()->route('web.campaign', $donation->campaign->slug)
            ->with('success', __('Donation successful! Thank you for your support.'));
    }
    
    // Handle failure...
}
```

**Note**: The package provides a `valid()` method, but it expects a `status` field set to 'VALID' which AamarPay doesn't send in their callback. Instead, we use custom validation that checks:
- `pay_status === 'Successful'`
- Amount matches the expected amount
- Transaction ID matches

```php
$isPaymentSuccessful = (
    $request->pay_status === 'Successful' &&
    $request->amount == $paymentAttempt->amount &&
    $request->mer_txnid === $orderId
);

if ($isPaymentSuccessful) {
    // Payment successful
}
```

### 3. Handling Cancelled Payment

When a user cancels the payment or it fails:

```php
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

        return redirect()->route('web.campaign', $donation->campaign->slug)
            ->with('warning', __('Donation was cancelled.'));
    }

    return redirect()->route('web.home')->with('warning', __('Donation was cancelled.'));
}
```

## User Interface

### Gateway Selection

The donation modal presents both payment gateways as radio button options:

```blade
<div class="space-y-3">
    <label class="text-sm font-semibold text-gray-900 dark:text-white">
        {{ __('Select Payment Gateway') }}
    </label>
    
    <label class="flex items-center gap-3 p-4 rounded-2xl border-2 cursor-pointer...">
        <input type="radio" wire:model="gateway" value="shurjopay" class="radio radio-primary">
        <div class="flex-1">
            <p class="text-sm font-bold">{{ __('ShurjoPay') }}</p>
            <p class="text-xs">{{ __('bKash, Nagad, Cards & more') }}</p>
        </div>
        <img src="https://shurjopay.com.bd/shurjopay-logo.png" alt="ShurjoPay" />
    </label>

    <label class="flex items-center gap-3 p-4 rounded-2xl border-2 cursor-pointer...">
        <input type="radio" wire:model="gateway" value="aamarpay" class="radio radio-primary">
        <div class="flex-1">
            <p class="text-sm font-bold">{{ __('AamarPay') }}</p>
            <p class="text-xs">{{ __('All major payment methods') }}</p>
        </div>
        <img src="https://aamarpay.com/images/logo.png" alt="AamarPay" />
    </label>
</div>
```

## Testing

### Sandbox Credentials

The default sandbox credentials are already configured:

- **Store ID**: `aamarpaytest`
- **Signature Key**: `dbb74894e82415a2f7ff0ec3a97e4183`
- **Sandbox Mode**: `true`

### Testing Flow

1. Visit a campaign page
2. Click "Donate Now"
3. Fill in donation details
4. Select "AamarPay" as the payment gateway
5. Click "Proceed to Pay"
6. You'll be redirected to AamarPay's payment page
7. Use test credentials to complete payment
8. Verify successful callback handling

## Production Setup

When going to production:

1. Obtain real AamarPay merchant credentials from https://aamarpay.com
2. Update `.env` with production credentials:
   ```dotenv
   AAMARPAY_SANDBOX_MODE=false
   AAMARPAY_STORE_ID=your_store_id
   AAMARPAY_SIGNATURE_KEY=your_signature_key
   ```
3. Test thoroughly in production environment

## Key Differences: AamarPay vs ShurjoPay

| Feature | AamarPay | ShurjoPay |
|---------|----------|-----------|
| Callback Method | POST | GET |
| Order ID Prefix | AP{timestamp}ID{id} | SP{timestamp}ID{id} |
| Success Field | `pay_status` === 'Successful' | `success()` method |
| Order ID Field | `mer_txnid` | `customer_order_id` |

## Troubleshooting

### Common Issues

1. **Payment not completing**: Check callback URLs are publicly accessible
2. **Signature mismatch**: Verify signature key is correct
3. **Sandbox mode not working**: Ensure `AAMARPAY_SANDBOX_MODE=true`

### Logging

Payment responses are stored in `payment_attempts.response_payload` for debugging.

## Security Considerations

1. **Signature Verification**: The package automatically verifies payment signatures
2. **CSRF Protection**: Callback routes should handle CSRF appropriately
3. **Order ID Uniqueness**: Timestamps + IDs ensure unique transaction identifiers
4. **SSL Required**: Production must use HTTPS for callback URLs

## Support

- **AamarPay Documentation**: https://aamarpay.com/developer
- **Package Repository**: https://github.com/Shipu/php-aamarpay-payment
- **Package Issues**: https://github.com/Shipu/php-aamarpay-payment/issues
