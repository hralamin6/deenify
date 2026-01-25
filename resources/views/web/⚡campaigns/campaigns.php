<?php

use App\Models\Campaign;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Campaigns')] #[Layout('layouts.auth')] class extends Component
{
    use WithPagination;

    public $search = '';
    public $status = 'active';
    public $minGoal = null;
    public $maxGoal = null;
    public $perPage = 9;

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => 'active'],
        'minGoal' => ['except' => null],
        'maxGoal' => ['except' => null],
    ];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function updatedMinGoal(): void
    {
        $this->resetPage();
    }

    public function updatedMaxGoal(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function campaigns()
    {
        return Campaign::query()
            ->withSum(['donations as paid_donations_sum' => fn ($q) => $q->where('status', 'paid')], 'amount')
            ->withCount(['donations as paid_donations_count' => fn ($q) => $q->where('status', 'paid')])
            ->withSum('expenses', 'amount')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('title', 'like', '%'.$this->search.'%')
                      ->orWhere('description', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->status && $this->status !== 'all', function ($query) {
                $query->where('status', $this->status);
            })
            ->when($this->minGoal, function ($query) {
                $query->where('goal_amount', '>=', $this->minGoal);
            })
            ->when($this->maxGoal, function ($query) {
                $query->where('goal_amount', '<=', $this->maxGoal);
            })
            ->orderByDesc('starts_at')
            ->paginate($this->perPage)
            ->withQueryString();
    }
};
