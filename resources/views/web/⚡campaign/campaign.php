<?php

use App\Models\Campaign;
use App\Models\Donation;
use App\Models\ManualPaymentProof;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;

new #[Title('Campaign Details')] #[Layout('layouts.auth')] class extends Component
{
    use Toast, WithFileUploads;

    public Campaign $campaign;
    public $amount;
    public $donor_name;
    public $donor_email;
    public $donor_password;
    public bool $showDonateModal = false;
    public $donations = [];
    public $gateway = 'shurjopay'; // Default gateway
    
    // Manual payment fields
    public $transaction_id;
    public $currentPaymentAttempt;




    public function mount(string $slug): void
    {
        
        $this->campaign = Campaign::query()
            ->withSum(['donations as paid_donations_sum' => fn ($q) => $q->where('status', 'paid')], 'amount')
            ->withCount(['donations as paid_donations_count' => fn ($q) => $q->where('status', 'paid')])
            ->withSum('expenses', 'amount')
            ->where('slug', $slug)
            ->firstOrFail();

        $this->donations = Donation::query()
            ->where('campaign_id', $this->campaign->id)
            ->where('status', 'paid')
            ->orderByDesc('paid_at')
            ->limit(8)
            ->get();

        if (auth()->check()) {
            $this->donor_name = auth()->user()->name;
            $this->donor_email = auth()->user()->email;
        }

        // Check if we should show manual payment modal (after redirect)
        if (session()->has('show_manual_payment') && session()->has('manual_payment_attempt_id')) {
            $this->currentPaymentAttempt = session('manual_payment_attempt_id');
            $this->gateway = session('manual_payment_gateway');
            $this->amount = session('manual_payment_amount');
            $this->showDonateModal = true; // Keep modal open to show manual payment form
            
            // Clear the session data
            session()->forget(['show_manual_payment', 'manual_payment_attempt_id', 'manual_payment_gateway', 'manual_payment_amount']);
        }
    }

    public function donate()
    {
        // Validation rules depend on whether user is logged in
        $rules = [
            'amount' => 'required|numeric|min:10',
            'gateway' => 'required|in:shurjopay,aamarpay,bkash,nagad,rocket',
        ];

        // If user is not logged in, require name, email, and password
        if (!auth()->check()) {
            $rules['donor_name'] = 'required|string|max:255';
            $rules['donor_email'] = 'required|email|max:255';
            $rules['donor_password'] = 'required|string|min:8';
        }

        $this->validate($rules);

        // Initialize user variables
        $userId = auth()->id();
        $donorName = auth()->user()?->name;
        $donorEmail = auth()->user()?->email;
        $isNewLogin = false;

        // Handle guest user data and authentication preparation
        if (!auth()->check()) {
            $user = User::where('email', $this->donor_email)->first();

            if ($user) {
                // User exists - verify password
                if (!Hash::check($this->donor_password, $user->password)) {
                    $this->addError('donor_password', __('The password is incorrect.'));
                    return;
                }
            } else {
                // User doesn't exist - register new user
                $user = User::create([
                    'name' => $this->donor_name,
                    'email' => $this->donor_email,
                    'password' => Hash::make($this->donor_password),
                ]);
            }
            
            $userId = $user->id;
            $donorName = $user->name;
            $donorEmail = $user->email;
            $isNewLogin = true;
        }

        // At this point, we have a $userId (either auth or just found/created)
        $donation = Donation::create([
            'campaign_id' => $this->campaign->id,
            'user_id' => $userId,
            'donor_name' => $donorName,
            'donor_email' => $donorEmail,
            'amount' => $this->amount,
            'currency' => 'BDT',
            'status' => 'pending',
        ]);

        // Create payment attempt
        $paymentAttempt = \App\Models\PaymentAttempt::create([
            'donation_id' => $donation->id,
            'campaign_id' => $this->campaign->id,
            'user_id' => $userId,
            'amount' => $this->amount,
            'gateway' => $this->gateway,
            'status' => 'pending',
            'session_id' => session()->getId(),
            'initiated_at' => now(),
        ]);

        // Check if manual payment gateway
        if (in_array($this->gateway, ['bkash', 'nagad', 'rocket'])) {
            // Validate transaction ID immediately
            $this->validate(['transaction_id' => 'required|string|max:255']);

            // Finalize payment attempt in database BEFORE login
            $paymentAttempt->update([
                'status' => 'pending_verification',
                'provider_reference' => $this->transaction_id,
            ]);

            // Now login the user if they were a guest
            if ($isNewLogin) {
                auth()->loginUsingId($userId);
                
                // Store success message in session for after redirect
                // Using 'toast_success' to survive session regeneration
                session()->put('toast_success', __('Payment proof submitted! We will verify and confirm your donation soon.'));
                
                return redirect()->route('web.campaign', $this->campaign->slug);
            }

            // For already logged in users
            $this->showDonateModal = false;
            $this->reset(['transaction_id', 'amount', 'gateway']);
            $this->success(__('Payment proof submitted! We will verify and confirm your donation soon.'));
            return;
        }

        // Handle automated gateways...
        if ($isNewLogin) {
            auth()->loginUsingId($userId);
        }


        // Handle automated payment gateways
        if ($this->gateway === 'shurjopay') {
            return $this->processShurjoPay($paymentAttempt);
        } else {
            return $this->processAamarPay($paymentAttempt);
        }
    }

    public function submitManualPaymentProof()
    {
        $this->validate([
            'transaction_id' => 'required|string|max:255',
        ]);

        $paymentAttempt = \App\Models\PaymentAttempt::findOrFail($this->currentPaymentAttempt);

        // Update payment attempt with transaction ID
        $paymentAttempt->update([
            'status' => 'pending_verification',
            'provider_reference' => $this->transaction_id,
        ]);

        // Close modal and reset
        $this->showDonateModal = false;
        $this->reset(['transaction_id', 'currentPaymentAttempt', 'amount', 'gateway']);
        $this->success(__('Payment proof submitted! We will verify and confirm your donation soon.'));

    }

    protected function processShurjoPay(\App\Models\PaymentAttempt $paymentAttempt)
    {
        $shurjopay = app(\Raziul\Shurjopay\Gateway::class);
        $shurjopay->setCallbackUrl(route('payment.shurjopay.callback'), route('payment.shurjopay.cancel'));

        $orderId = 'SP' . time() . 'ID' . $paymentAttempt->id;

        $payload = [
            'amount' => $this->amount,
            'currency' => 'BDT',
            'order_id' => $orderId,
            'customer_name' => $this->donor_name,
            'customer_email' => $this->donor_email,
            'customer_phone' => '01700000000',
            'customer_address' => 'Dhaka',
            'customer_city' => 'Dhaka',
            'client_ip' => request()->ip(),
        ];

        $paymentAttempt->update(['provider_reference' => $orderId]);

        try {
            return $shurjopay->makePayment($payload);
        } catch (\Exception $e) {
            $this->addError('amount', __('Error: ') . $e->getMessage());
        }
    }

    protected function processAamarPay(\App\Models\PaymentAttempt $paymentAttempt)
    {
        $orderId = 'AP' . time() . 'ID' . $paymentAttempt->id;

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

        $aamarpay = new \Shipu\Aamarpay\Aamarpay($config);

        $paymentAttempt->update(['provider_reference' => $orderId]);

        try {
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
            ->currency('BDT')
            ->product('Donation for ' . $this->campaign->title);

            // Store payment data in session for the redirect view
            session()->put('aamarpay_payment', [
                'url' => $aamarpay->paymentUrl(),
                'fields' => $aamarpay->hiddenValue(),
            ]);

            // Redirect to a dedicated payment redirect route
            return redirect()->route('payment.aamarpay.redirect');
        } catch (\Exception $e) {
            $this->addError('amount', __('Error: ') . $e->getMessage());
        }
    }
};
