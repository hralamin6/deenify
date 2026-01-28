<?php

use App\Models\Campaign;
use App\Models\RecurringPlan;
use App\Models\User;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

new #[Title('Recurring Plans')] #[Layout('layouts.app')] class extends Component
{
    use Toast, WithPagination;

    public Collection $library;

    public $selectedRows = [];

    public $selectPageRows = false;

    public $itemPerPage = 10;

    public $orderBy = 'next_run_at';

    public $orderDirection = 'asc';

    public $search = '';

    public $itemStatus = null;

    public $campaignId = null;

    public $userId = null;

    public $planCampaignId = null;

    public $amount = 0;

    public $currency = 'BDT';

    public $interval = 'monthly';

    public $day_of_week = null;

    public $day_of_month = null;

    public $status = 'active';

    public $starts_at = null;

    public $ends_at = null;

    public $next_run_at = null;

    public $plan = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'itemStatus' => ['except' => null],
        'campaignId' => ['except' => null],
    ];

    public function mount(): void
    {
        $this->authorize('recurring-plans.view');
    }

    #[Computed]
    public function items()
    {
        return $this->data;
    }

    #[Computed]
    public function campaigns()
    {
        return Campaign::query()
            ->orderBy('title')
            ->get(['id', 'title']);
    }

    #[Computed]
    public function users()
    {
        return User::query()
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
    }

    public function getDataProperty()
    {
        return RecurringPlan::query()
            ->with([
                'campaign:id,title',
                'user:id,name,email',
            ])
            ->withCount('donations')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->whereHas('campaign', function ($campaign) {
                        $campaign->where('title', 'like', '%'.$this->search.'%');
                    })
                        ->orWhereHas('user', function ($user) {
                            $user->where('name', 'like', '%'.$this->search.'%')
                                ->orWhere('email', 'like', '%'.$this->search.'%');
                        });
                });
            })
            ->when($this->itemStatus, function ($query) {
                return $query->where('status', $this->itemStatus);
            })
            ->when($this->campaignId, function ($query) {
                return $query->where('campaign_id', $this->campaignId);
            })
            ->orderBy($this->orderBy, $this->orderDirection)
            ->paginate($this->itemPerPage)
            ->withQueryString();
    }

    protected function rules(): array
    {
        return [
            'userId' => 'required|exists:users,id',
            'planCampaignId' => 'required|exists:campaigns,id',
            'amount' => 'required|numeric|min:1',
            'currency' => 'required|string|max:3',
            'interval' => 'required|in:weekly,monthly',
            'day_of_week' => 'nullable|integer|min:0|max:6|required_if:interval,weekly',
            'day_of_month' => 'nullable|integer|min:1|max:31|required_if:interval,monthly',
            'status' => 'required|in:active,paused,cancelled',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date',
            'next_run_at' => 'nullable|date',
        ];
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedItemPerPage(): void
    {
        $this->resetPage();
    }

    public function updatedItemStatus(): void
    {
        $this->resetPage();
    }

    public function updatedCampaignId(): void
    {
        $this->resetPage();
    }

    public function updatedSelectPageRows($value): void
    {
        if ($value) {
            $this->selectedRows = $this->data->pluck('id')->map(function ($id) {
                return (string) $id;
            })->toArray();
        } else {
            $this->reset('selectedRows', 'selectPageRows');
        }
    }

    public function orderByDirection($field): void
    {
        if ($this->orderBy == $field) {
            $this->orderDirection = $this->orderDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->orderBy = $field;
            $this->orderDirection = 'asc';
        }
    }

    public function loadData(RecurringPlan $plan): void
    {
        $this->resetData();

        $this->userId = $plan->user_id;
        $this->planCampaignId = $plan->campaign_id;
        $this->amount = $plan->amount;
        $this->currency = $plan->currency;
        $this->interval = $plan->interval;
        $this->day_of_week = $plan->day_of_week;
        $this->day_of_month = $plan->day_of_month;
        $this->status = $plan->status;
        $this->starts_at = $plan->starts_at?->format('Y-m-d\TH:i');
        $this->ends_at = $plan->ends_at?->format('Y-m-d\TH:i');
        $this->next_run_at = $plan->next_run_at?->format('Y-m-d\TH:i');

        $this->plan = $plan;
    }

    public function editData(): void
    {
        $this->authorize('recurring-plans.edit');

        $data = $this->validate();

        $data['user_id'] = $data['userId'];
        $data['campaign_id'] = $data['planCampaignId'];
        unset($data['userId'], $data['planCampaignId']);

        if ($data['interval'] === 'weekly') {
            $data['day_of_month'] = null;
        }

        if ($data['interval'] === 'monthly') {
            $data['day_of_week'] = null;
        }

        $this->plan->update($data);

        $this->dispatch('dataUpdated', dataId: "item-id-{$this->plan->id}");
        $this->success(__('Recurring plan updated successfully'));
        $this->resetData();
    }

    public function resetData(): void
    {
        $this->reset([
            'userId', 'planCampaignId', 'amount', 'currency', 'interval',
            'day_of_week', 'day_of_month', 'status', 'starts_at', 'ends_at',
            'next_run_at', 'plan',
        ]);

        $this->currency = 'BDT';
        $this->interval = 'monthly';
        $this->status = 'active';
        $this->amount = 0;
    }

    public function saveData(): void
    {
        $this->authorize('recurring-plans.create');

        $data = $this->validate();

        $payload = [
            'user_id' => $data['userId'],
            'campaign_id' => $data['planCampaignId'],
            'amount' => $data['amount'],
            'currency' => $data['currency'],
            'interval' => $data['interval'],
            'day_of_week' => $data['day_of_week'],
            'day_of_month' => $data['day_of_month'],
            'status' => $data['status'],
            'starts_at' => $data['starts_at'],
            'ends_at' => $data['ends_at'],
            'next_run_at' => $data['next_run_at'],
        ];

        if ($payload['interval'] === 'weekly') {
            $payload['day_of_month'] = null;
        }

        if ($payload['interval'] === 'monthly') {
            $payload['day_of_week'] = null;
        }

        $plan = RecurringPlan::create($payload);

        $this->dispatch('dataAdded', dataId: "item-id-{$plan->id}");
        $this->resetPage();
        $this->success(__('Recurring plan created successfully'));
        $this->resetData();
    }

    public function deleteSingle(RecurringPlan $plan): void
    {
        $this->authorize('recurring-plans.delete');
        $plan->delete();
        $this->success(__('Recurring plan deleted successfully'));
    }

    public function deleteMultiple(): void
    {
        $this->authorize('recurring-plans.delete');

        RecurringPlan::whereIn('id', $this->selectedRows)->delete();

        $this->selectPageRows = false;
        $this->selectedRows = [];
        $this->success(__('Recurring plans deleted successfully'));
    }
};
