<?php

use App\Models\Campaign;
use App\Models\Expense;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Title('Spending')]
#[Layout('layouts.auth')]
class extends Component
{
    public array $stats = [];

    public $topCategories = [];

    public $topCampaigns = [];

    public string $selectedMonth = '';

    public array $monthOptions = [];

    public array $monthlySeries = [];

    public $monthlyExpensesList = [];

    public function mount(): void
    {
        $this->loadData();
    }

    public function updatedSelectedMonth(): void
    {
        $this->loadData();
    }

    protected function loadData(): void
    {
        $now = now();
        $expenseDateColumn = DB::raw('COALESCE(expenses.spent_at, expenses.created_at)');

        $this->monthOptions = [];
        for ($i = 0; $i < 12; $i++) {
            $month = $now->copy()->subMonths($i);
            $key = $month->format('Y-m');
            $this->monthOptions[$key] = $month->format('M Y');
        }

        if ($this->selectedMonth === '' || ! isset($this->monthOptions[$this->selectedMonth])) {
            $this->selectedMonth = $now->format('Y-m');
        }

        $selected = $this->selectedMonth.'-01';
        $monthStart = \Carbon\Carbon::createFromFormat('Y-m-d', $selected)->startOfMonth();
        $monthEnd = \Carbon\Carbon::createFromFormat('Y-m-d', $selected)->endOfMonth();

        $monthlyExpenses = Expense::query()
            ->whereBetween($expenseDateColumn, [$monthStart, $monthEnd])
            ->sum('amount');

        $this->monthlyExpensesList = Expense::query()
            ->leftJoin('expense_categories', 'expense_categories.id', '=', 'expenses.expense_category_id')
            ->leftJoin('campaigns', 'campaigns.id', '=', 'expenses.campaign_id')
            ->whereBetween($expenseDateColumn, [$monthStart, $monthEnd])
            ->select([
                'expenses.id',
                'expenses.amount',
                'expenses.spent_at',
                'expenses.created_at',
                'expenses.description',
                'expense_categories.name as category_name',
                'campaigns.title as campaign_title',
            ])
            ->orderByDesc(DB::raw('COALESCE(expenses.spent_at, expenses.created_at)'))
            ->get();

        $uncategorizedExpenses = Expense::query()
            ->whereNull('expense_category_id')
            ->count();

        $this->topCategories = Expense::query()
            ->join('expense_categories', 'expense_categories.id', '=', 'expenses.expense_category_id')
            ->select('expense_categories.name', DB::raw('SUM(expenses.amount) as total'))
            ->groupBy('expense_categories.name')
            ->orderByDesc('total')
            ->take(6)
            ->get();

        $this->topCampaigns = Campaign::query()
            ->withSum('expenses', 'amount')
            ->orderByDesc('expenses_sum_amount')
            ->take(6)
            ->get(['id', 'title']);

        $seriesStart = $now->copy()->subMonths(11)->startOfMonth();
        $series = Expense::query()
            ->whereBetween($expenseDateColumn, [$seriesStart, $now])
            ->selectRaw("DATE_FORMAT(COALESCE(expenses.spent_at, expenses.created_at), '%Y-%m') as period, SUM(amount) as total")
            ->groupBy('period')
            ->orderBy('period')
            ->pluck('total', 'period');

        $this->monthlySeries = collect(array_reverse($this->monthOptions, true))
            ->map(function ($label, $key) use ($series) {
                return [
                    'key' => $key,
                    'label' => $label,
                    'total' => (float) ($series[$key] ?? 0),
                ];
            })
            ->values()
            ->toArray();

        $this->stats = [
            'currency' => setting('currency', 'BDT'),
            'monthly_expenses' => $monthlyExpenses,
            'uncategorized_expenses' => $uncategorizedExpenses,
        ];
    }
};
