<?php

use App\Models\Campaign;
use App\Models\Donation;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Campaign Details')] #[Layout('layouts.auth')] class extends Component
{
    public Campaign $campaign;
    public $donations = [];

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
    }
};
