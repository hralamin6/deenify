<?php

use App\Models\Campaign;
use App\Models\Donation;
use App\Models\DonationReceipt;
use App\Models\Expense;
use App\Models\PaymentAttempt;
use App\Models\RecurringPlan;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Component;

new
#[Layout('layouts.auth')]
class extends Component
{
    public ?Campaign $featuredCampaign = null;
    public $campaigns = [];
    public array $stats = [];
    public $recentReceipt = null;
    public $nextRecurring = null;
    public $recentDonors = [];
    public $topCampaigns = [];
    public array $paymentSplit = [];

    public function mount(): void
    {
        $this->featuredCampaign = Campaign::query()
            ->withSum(['donations as paid_donations_sum' => fn ($q) => $q->where('status', 'paid')], 'amount')
            ->withCount(['donations as paid_donations_count' => fn ($q) => $q->where('status', 'paid')])
            ->withSum('expenses', 'amount')
            ->where('status', 'active')
            ->orderByDesc('starts_at')
            ->first();

        $campaigns = Campaign::query()
            ->withSum(['donations as paid_donations_sum' => fn ($q) => $q->where('status', 'paid')], 'amount')
            ->withCount(['donations as paid_donations_count' => fn ($q) => $q->where('status', 'paid')])
            ->withSum('expenses', 'amount')
            ->where('status', 'active')
            ->orderByDesc('starts_at')
            ->limit(3)
            ->get();

        if ($campaigns->isEmpty()) {
            $campaigns = Campaign::query()
                ->withSum(['donations as paid_donations_sum' => fn ($q) => $q->where('status', 'paid')], 'amount')
                ->withCount(['donations as paid_donations_count' => fn ($q) => $q->where('status', 'paid')])
                ->withSum('expenses', 'amount')
                ->latest()
                ->limit(3)
                ->get();
        }

        $this->campaigns = $campaigns;

        $this->stats = [
            'paid_total' => Donation::where('status', 'paid')->sum('amount'),
            'expense_total' => Expense::sum('amount'),
            'failed_count' => Donation::where('status', 'failed')->count(),
            'paid_count' => Donation::where('status', 'paid')->count(),
            'pending_count' => Donation::where('status', 'pending')->count(),
            'active_campaigns' => Campaign::where('status', 'active')->count(),
            'total_campaigns' => Campaign::count(),
            'recurring_active' => RecurringPlan::where('status', 'active')->count(),
            'receipts_count' => DonationReceipt::count(),
        ];
        $this->stats['net_balance'] = $this->stats['paid_total'] - $this->stats['expense_total'];

        $this->recentReceipt = DonationReceipt::query()
            ->with(['donation.paymentAttempts' => fn ($q) => $q->latest()])
            ->latest('issued_at')
            ->first();

        $this->nextRecurring = RecurringPlan::query()
            ->where('status', 'active')
            ->whereNotNull('next_run_at')
            ->orderBy('next_run_at')
            ->first();

        $this->recentDonors = Donation::query()
            ->where('status', 'paid')
            ->latest('paid_at')
            ->limit(6)
            ->get();

        $this->topCampaigns = Campaign::query()
            ->withSum(['donations as paid_donations_sum' => fn ($q) => $q->where('status', 'paid')], 'amount')
            ->where('status', 'active')
            ->orderByDesc('paid_donations_sum')
            ->limit(3)
            ->get();

        $this->paymentSplit = PaymentAttempt::query()
            ->selectRaw('gateway, count(*) as count, sum(amount) as total')
            ->where('status', 'success')
            ->groupBy('gateway')
            ->get()
            ->mapWithKeys(function ($row) {
                return [$row->gateway => ['count' => (int) $row->count, 'total' => (float) $row->total]];
            })
            ->toArray();
    }
};
