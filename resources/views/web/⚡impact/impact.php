<?php

use App\Models\Donation;
use App\Models\DonationReceipt;
use App\Models\Expense;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Title('Impact')]
#[Layout('layouts.auth')]
class extends Component
{
    public array $stats = [];

    public array $monthlyTrend = [];

    public function mount(): void
    {
        $currency = setting('currency', 'BDT');
        $now = now();

        $totalRaised = Donation::query()
            ->where('status', 'paid')
            ->sum('amount');
        $totalExpenses = Expense::query()->sum('amount');
        $netImpact = $totalRaised - $totalExpenses;
        $receiptsCount = DonationReceipt::query()->count();

        $monthStart = $now->copy()->startOfMonth();
        $monthDonations = Donation::query()
            ->where('status', 'paid')
            ->whereBetween('paid_at', [$monthStart, $now])
            ->sum('amount');
        $monthExpenses = Expense::query()
            ->whereBetween(DB::raw('COALESCE(spent_at, created_at)'), [$monthStart, $now])
            ->sum('amount');
        $expenseRatio = $monthDonations > 0
            ? round(($monthExpenses / $monthDonations) * 100, 1)
            : 0;

        $this->stats = [
            'currency' => $currency,
            'total_raised' => $totalRaised,
            'net_impact' => $netImpact,
            'receipts_count' => $receiptsCount,
            'expense_ratio' => $expenseRatio,
            'month_donations' => $monthDonations,
            'month_expenses' => $monthExpenses,
        ];

        $startRange = $now->copy()->subMonths(5)->startOfMonth();

        $donationsByMonth = Donation::query()
            ->where('status', 'paid')
            ->whereNotNull('paid_at')
            ->whereBetween('paid_at', [$startRange, $now])
            ->selectRaw("DATE_FORMAT(paid_at, '%Y-%m') as period, SUM(amount) as total")
            ->groupBy('period')
            ->orderBy('period')
            ->pluck('total', 'period');

        $expensesByMonth = Expense::query()
            ->whereBetween(DB::raw('COALESCE(spent_at, created_at)'), [$startRange, $now])
            ->selectRaw("DATE_FORMAT(COALESCE(spent_at, created_at), '%Y-%m') as period, SUM(amount) as total")
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
            $maxValue = max($maxValue, (float) ($donationsByMonth[$key] ?? 0), (float) ($expensesByMonth[$key] ?? 0));
        }

        $trend = [];
        foreach ($months as $key => $label) {
            $donations = (float) ($donationsByMonth[$key] ?? 0);
            $expenses = (float) ($expensesByMonth[$key] ?? 0);
            $trend[] = [
                'label' => $label,
                'donations' => $donations,
                'expenses' => $expenses,
                'donation_pct' => $maxValue > 0 ? round(($donations / $maxValue) * 100) : 0,
                'expense_pct' => $maxValue > 0 ? round(($expenses / $maxValue) * 100) : 0,
            ];
        }

        $this->monthlyTrend = $trend;
    }
};
