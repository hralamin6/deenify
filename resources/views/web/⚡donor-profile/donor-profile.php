<?php

use App\Models\Donation;
use App\Models\DonationReceipt;
use App\Models\RecurringPlan;
use App\Models\User;
use App\Models\UserDetail;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Title('Donor Profile')]
#[Layout('layouts.auth')]
class extends Component
{
    public User $user;

    public ?UserDetail $detail = null;

    public array $stats = [];

    public $recentDonations = [];

    public $recurringPlans = [];

    public array $monthlyTrend = [];

    public function mount(User $user): void
    {
        $this->user = $user;
        $this->detail = UserDetail::query()
            ->with(['division', 'district', 'upazila', 'union'])
            ->where('user_id', $user->id)
            ->first();

        $paidDonations = Donation::query()
            ->where('status', 'paid')
            ->where('user_id', $user->id);

        $totalDonated = (clone $paidDonations)->sum('amount');
        $donationCount = (clone $paidDonations)->count();
        $avgDonation = (clone $paidDonations)->avg('amount');
        $firstDonationAt = (clone $paidDonations)->min('paid_at');
        $lastDonationAt = (clone $paidDonations)->max('paid_at');
        $campaignsSupported = (clone $paidDonations)->distinct('campaign_id')->count('campaign_id');
        $receiptsIssued = DonationReceipt::query()
            ->whereHas('donation', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->count();

        $activeRecurring = RecurringPlan::query()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->count();

        $this->stats = [
            'currency' => setting('currency', 'BDT'),
            'total_donated' => $totalDonated,
            'donation_count' => $donationCount,
            'avg_donation' => $avgDonation,
            'first_donation_at' => $firstDonationAt,
            'last_donation_at' => $lastDonationAt,
            'campaigns_supported' => $campaignsSupported,
            'receipts_issued' => $receiptsIssued,
            'active_recurring' => $activeRecurring,
        ];

        $this->recentDonations = Donation::query()
            ->with(['campaign', 'receipt'])
            ->where('status', 'paid')
            ->where('user_id', $user->id)
            ->orderByDesc('paid_at')
            ->limit(10)
            ->get();

        $this->recurringPlans = RecurringPlan::query()
            ->with('campaign')
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $now = now();
        $startRange = $now->copy()->subMonths(5)->startOfMonth();
        $series = Donation::query()
            ->where('status', 'paid')
            ->where('user_id', $user->id)
            ->whereBetween('paid_at', [$startRange, $now])
            ->selectRaw("DATE_FORMAT(paid_at, '%Y-%m') as period, SUM(amount) as total")
            ->groupBy('period')
            ->orderBy('period')
            ->pluck('total', 'period');

        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = $now->copy()->subMonths($i);
            $key = $month->format('Y-m');
            $months[$key] = $month->format('M Y');
        }

        $maxValue = 0;
        foreach ($months as $key => $label) {
            $maxValue = max($maxValue, (float) ($series[$key] ?? 0));
        }

        $trend = [];
        foreach ($months as $key => $label) {
            $total = (float) ($series[$key] ?? 0);
            $trend[] = [
                'label' => $label,
                'total' => $total,
                'pct' => $maxValue > 0 ? round(($total / $maxValue) * 100) : 0,
            ];
        }

        $this->monthlyTrend = $trend;
    }
};
