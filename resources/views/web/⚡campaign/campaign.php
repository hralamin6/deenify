<?php

use App\Models\Campaign;
use App\Models\Donation;
use App\Models\Expense;
use App\Models\RecurringPlan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;
use misterspelik\LaravelPdf\Facades\Pdf;

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

    public $expenses = [];

    public $gateway = 'shurjopay'; // Default gateway

    public $recurring_amount = 1000;

    public $recurring_interval = 'monthly';

    public $recurring_day_of_week = 1;

    public $recurring_day_of_month = 1;

    public $recurringPaymentPlanId = null;

    public $pendingDonationId = null;

    // Manual payment fields
    public $transaction_id;

    public $currentPaymentAttempt;

    public function showToast(): void
    {
        if (session()->has('toast_success')) {
            $this->success(session('toast_success'));
            session()->forget('toast_success');
        }
        if (session()->has('toast_error')) {
            $this->error(session('toast_error'));
            session()->forget('toast_error');
        }
    }

    public function hydrate()
    {
        if (isset($this->campaign)) {
            $this->campaign->loadSum(['donations as paid_donations_sum' => fn ($q) => $q->where('status', 'paid')], 'amount');
            $this->campaign->loadCount(['donations as paid_donations_count' => fn ($q) => $q->where('status', 'paid')]);
            $this->campaign->loadSum('expenses', 'amount');
        }
    }

    public function mount(string $slug): void
    {

        $this->campaign = Campaign::query()
            ->withSum(['donations as paid_donations_sum' => fn ($q) => $q->where('status', 'paid')], 'amount')
            ->withCount(['donations as paid_donations_count' => fn ($q) => $q->where('status', 'paid')])
            ->withSum('expenses', 'amount')
            ->where('slug', $slug)
            ->firstOrFail();
        // dd($this->campaign);
        $this->donations = Donation::query()
            ->where('campaign_id', $this->campaign->id)
            ->where('status', 'paid')
            ->orderByDesc('paid_at')
            ->limit(8)
            ->get();

        $this->expenses = Expense::query()
            ->with('category')
            ->where('campaign_id', $this->campaign->id)
            ->orderByDesc('spent_at')
            ->get();

        if (auth()->check()) {
            $this->donor_name = auth()->user()->name;
            $this->donor_email = auth()->user()->email;
        }
    }

    public function donate()
    {
        // Validation rules depend on whether user is logged in
        $rules = [
            'amount' => 'required|numeric|min:10',
            'gateway' => 'required|in:shurjopay,aamarpay,bkash,nagad,rocket',
        ];

        // If manual payment, validate transaction ID immediately
        if (in_array($this->gateway, ['bkash', 'nagad', 'rocket'])) {
            $rules['transaction_id'] = 'required|string|max:255';
        }

        // If user is not logged in, require name, email, and password
        if (! auth()->check()) {
            $rules['donor_name'] = 'required|string|max:255';
            $rules['donor_email'] = 'required|email:rfc,dns|max:255';
            $rules['donor_password'] = 'required|string|min:8';
        }

        $this->validate($rules);

        // Initialize user variables
        $userId = auth()->id();
        $donorName = auth()->user()?->name;
        $donorEmail = auth()->user()?->email;
        $isNewLogin = false;

        // Handle guest user data and authentication preparation
        if (! auth()->check()) {
            $user = User::where('email', $this->donor_email)->first();

            if ($user) {
                // User exists - verify password
                if (! Hash::check($this->donor_password, $user->password)) {
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
                $user->assignRole('user');
            }

            $userId = $user->id;
            $donorName = $user->name;
            $donorEmail = $user->email;
            $isNewLogin = true;
        }

        if ($this->pendingDonationId) {
            if (! auth()->check()) {
                $this->error(__('Please log in to make a payment.'));

                return;
            }

            $donation = Donation::query()
                ->where('id', $this->pendingDonationId)
                ->where('campaign_id', $this->campaign->id)
                ->where('user_id', $userId)
                ->where('status', 'pending')
                ->firstOrFail();

            $this->amount = (float) $donation->amount;
            $this->recurringPaymentPlanId = $donation->recurring_plan_id;
        } else {
            // At this point, we have a $userId (either auth or just found/created)
            $donation = Donation::create([
                'campaign_id' => $this->campaign->id,
                'user_id' => $userId,
                'recurring_plan_id' => $this->recurringPaymentPlanId,
                'donor_name' => $donorName,
                'donor_email' => $donorEmail,
                'amount' => $this->amount,
                'currency' => 'BDT',
                'status' => 'pending',
            ]);
        }

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

        $this->recurringPaymentPlanId = null;
        $this->pendingDonationId = null;

        // Check if manual payment gateway
        if (in_array($this->gateway, ['bkash', 'nagad', 'rocket'])) {

            // Finalize payment attempt in database BEFORE login
            $paymentAttempt->update([
                'status' => 'pending_verification',
                'provider_reference' => $this->transaction_id,
            ]);

            // Notify admins
            try {
                $admins = User::role('admin')->get();
                \Illuminate\Support\Facades\Notification::send($admins, new \App\Notifications\TaskNotification(
                    title: 'Payment Verification Needed',
                    message: "A new manual payment of {$this->amount} BDT requires verification.",
                    url: route('app.payment-attempts', ['search' => $this->transaction_id]),
                    icon: 'o-currency-dollar',
                    type: 'info'
                ));
            } catch (\Exception $e) {
                // Ignore notification errors
            }
            // Now login the user if they were a guest
            if ($isNewLogin) {
                auth()->loginUsingId($userId);

                // Store success message in session for after redirect
                // Using 'toast_success' to survive session regeneration
                session()->put('toast_success', __('Payment proof submitted! We will verify and confirm your donation soon.'));

                return redirect()->route('web.campaign', $this->campaign->slug);
            }
            $this->userDonations();

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

    public function createRecurringPlan(): void
    {
        if (! auth()->check()) {
            $this->error(__('Please log in to start a recurring plan.'));

            return;
        }

        $data = $this->validate([
            'recurring_amount' => 'required|numeric|min:10',
            'recurring_interval' => 'required|in:weekly,monthly',
            'recurring_day_of_week' => 'nullable|integer|min:0|max:6|required_if:recurring_interval,weekly',
            'recurring_day_of_month' => 'nullable|integer|min:1|max:31|required_if:recurring_interval,monthly',
        ]);

        $now = now();
        $nextRun = $now->copy();

        if ($data['recurring_interval'] === 'weekly') {
            $days = [
                Carbon::SUNDAY,
                Carbon::MONDAY,
                Carbon::TUESDAY,
                Carbon::WEDNESDAY,
                Carbon::THURSDAY,
                Carbon::FRIDAY,
                Carbon::SATURDAY,
            ];
            $targetDay = $days[$data['recurring_day_of_week']] ?? Carbon::MONDAY;
            $nextRun = $now->copy()->next($targetDay);
            $data['recurring_day_of_month'] = null;
        }

        if ($data['recurring_interval'] === 'monthly') {
            $day = (int) ($data['recurring_day_of_month'] ?? 1);
            $initialDay = min($day, $now->daysInMonth);
            $candidate = $now->copy()->day($initialDay);

            if ($candidate->isSameDay($now) || $candidate->isPast()) {
                $nextMonth = $now->copy()->addMonth();
                $candidateDay = min($day, $nextMonth->daysInMonth);
                $candidate = $nextMonth->day($candidateDay);
            }

            $nextRun = $candidate;
            $data['recurring_day_of_week'] = null;
        }

        RecurringPlan::create([
            'user_id' => auth()->id(),
            'campaign_id' => $this->campaign->id,
            'amount' => $data['recurring_amount'],
            'currency' => 'BDT',
            'interval' => $data['recurring_interval'],
            'day_of_week' => $data['recurring_day_of_week'],
            'day_of_month' => $data['recurring_day_of_month'],
            'status' => 'active',
            'starts_at' => $now,
            'next_run_at' => $nextRun,
        ]);

        $this->reset(['recurring_amount', 'recurring_interval', 'recurring_day_of_week', 'recurring_day_of_month']);
        $this->recurring_interval = 'monthly';
        $this->recurring_day_of_week = 1;
        $this->recurring_day_of_month = 1;
        $this->recurring_amount = 1000;

        $this->success(__('Recurring plan started successfully.'));
    }

    public function payPendingRecurringDonation(int $donationId): void
    {
        if (! auth()->check()) {
            $this->error(__('Please log in to make a payment.'));

            return;
        }

        $donation = Donation::query()
            ->where('id', $donationId)
            ->where('campaign_id', $this->campaign->id)
            ->where('user_id', auth()->id())
            ->where('status', 'pending')
            ->firstOrFail();

        $this->amount = (float) $donation->amount;
        $this->recurringPaymentPlanId = $donation->recurring_plan_id;
        $this->pendingDonationId = $donation->id;
        $this->gateway = 'shurjopay';
        $this->showDonateModal = true;
    }

    #[Computed]
    public function userRecurringPlan()
    {
        if (! auth()->check()) {
            return null;
        }

        return RecurringPlan::query()
            ->where('campaign_id', $this->campaign->id)
            ->where('user_id', auth()->id())
            ->whereIn('status', ['active', 'paused'])
            ->latest()
            ->first();
    }

    #[Computed]
    public function recurringPlanDonations()
    {
        $plan = $this->userRecurringPlan;

        if (! $plan) {
            return collect();
        }

        return Donation::query()
            ->where('recurring_plan_id', $plan->id)
            ->where('campaign_id', $this->campaign->id)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();
    }

    #[Computed]
    public function pendingRecurringDonation()
    {
        $plan = $this->userRecurringPlan;

        if (! $plan || ! auth()->check()) {
            return null;
        }

        return Donation::query()
            ->where('recurring_plan_id', $plan->id)
            ->where('campaign_id', $this->campaign->id)
            ->where('user_id', auth()->id())
            ->where('status', 'pending')
            ->latest('created_at')
            ->first();
    }

    #[Computed]
    public function upcomingRecurringDates()
    {
        $plan = $this->userRecurringPlan;

        if (! $plan || ! $plan->next_run_at) {
            return collect();
        }

        $dates = collect();
        $current = $plan->next_run_at->copy();

        for ($i = 0; $i < 3; $i++) {
            $dates->push($current->copy());

            if ($plan->interval === 'weekly') {
                $current = $current->copy()->addWeek();

                continue;
            }

            $day = (int) ($plan->day_of_month ?? 1);
            $next = $current->copy()->addMonth();
            $current = $next->day(min($day, $next->daysInMonth));
        }

        return $dates;
    }


    protected function processShurjoPay(\App\Models\PaymentAttempt $paymentAttempt)
    {
        $shurjopay = app(\Raziul\Shurjopay\Gateway::class);
        $shurjopay->setCallbackUrl(route('payment.shurjopay.callback'), route('payment.shurjopay.cancel'));

        $orderId = 'SP'.time().'ID'.$paymentAttempt->id;

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
            $this->addError('amount', __('Error: ').$e->getMessage());
        }
    }

    protected function processAamarPay(\App\Models\PaymentAttempt $paymentAttempt)
    {
        $orderId = 'AP'.time().'ID'.$paymentAttempt->id;

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
                ->product('Donation for '.$this->campaign->title);

            // Store payment data in session for the redirect view
            session()->put('aamarpay_payment', [
                'url' => $aamarpay->paymentUrl(),
                'fields' => $aamarpay->hiddenValue(),
            ]);

            // Redirect to a dedicated payment redirect route
            return redirect()->route('payment.aamarpay.redirect');
        } catch (\Exception $e) {
            $this->addError('amount', __('Error: ').$e->getMessage());
        }
    }

    public function downloadInvoice(int $donationId)
    {
        if (! auth()->check()) {
            abort(403);
        }

        $donation = Donation::query()
            ->with(['campaign', 'paymentAttempts' => fn ($q) => $q->latest()])
            ->where('id', $donationId)
            ->where('campaign_id', $this->campaign->id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $attempt = $donation->paymentAttempts->first();
        $invoiceNumber = 'INV-'.$donation->id.'-'.$donation->created_at?->format('Ymd');
        $filename = 'invoice_'.$donation->id.'.pdf';

        $data = [
            'donation' => $donation,
            'campaign' => $donation->campaign,
            'paymentGateway' => $attempt?->gateway ? strtoupper($attempt->gateway) : null,
            'transactionId' => $attempt?->provider_reference,
            'attemptStatus' => $attempt?->status,
            'invoiceNumber' => $invoiceNumber,
            'issuedAt' => now(),
            'brandName' => config('app.name'),
            'brandEmail' => config('mail.from.address'),
        ];

        $pdfContent = Pdf::loadView('pdf.donation-invoice', $data)->output();

        return response()->streamDownload(function () use ($pdfContent) {
            echo $pdfContent;
        }, $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    #[Computed]
    public function userDonations()
    {
        if (! auth()->check()) {
            return collect();
        }

        return Donation::query()
            ->where('campaign_id', $this->campaign->id)
            ->where('user_id', auth()->id())
            ->orderByDesc('created_at')
            ->get();
    }
};
