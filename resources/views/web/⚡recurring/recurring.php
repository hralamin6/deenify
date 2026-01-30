<?php

use App\Models\RecurringPlan;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Title('Give Monthly')]
#[Layout('layouts.auth')]
class extends Component
{
    public array $stats = [];

    public function mount(): void
    {
        $now = now();
        $activeCount = RecurringPlan::query()->where('status', 'active')->count();

        $mrr = RecurringPlan::query()
            ->where('status', 'active')
            ->selectRaw("SUM(CASE WHEN `interval` = 'weekly' THEN amount * 4.33 ELSE amount END) as aggregate")
            ->value('aggregate');

        $nextSevenDays = RecurringPlan::query()
            ->where('status', 'active')
            ->whereNotNull('next_run_at')
            ->whereBetween('next_run_at', [$now, $now->copy()->addDays(7)])
            ->sum('amount');

        $pausedCount = RecurringPlan::query()->where('status', 'paused')->count();
        $cancelledCount = RecurringPlan::query()->where('status', 'cancelled')->count();

        $this->stats = [
            'currency' => setting('currency', 'BDT'),
            'active_count' => $activeCount,
            'mrr' => $mrr,
            'next_seven_days' => $nextSevenDays,
            'paused_count' => $pausedCount,
            'cancelled_count' => $cancelledCount,
        ];
    }
};
