<?php

use App\Models\Campaign;
use App\Models\Donation;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

new #[Title('Donations')] #[Layout('layouts.app')] class extends Component
{
    use Toast, WithPagination;

    public Collection $library;

    public $selectedRows = [];

    public $selectPageRows = false;

    public $itemPerPage = 10;

    public $orderBy = 'created_at';

    public $orderDirection = 'desc';

    public $search = '';

    public $itemStatus = null;

    public $campaignId = null;

    public $status = 'pending';

    public $paid_at = null;

    public $notes = '';

    public $donation = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'itemStatus' => ['except' => null],
        'campaignId' => ['except' => null],
    ];

    public function mount(): void
    {
        $this->authorize('donations.view');
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

    public function getDataProperty()
    {
        return Donation::query()
            ->with([
                'campaign:id,title',
                'user:id,name,email',
                'paymentAttempts' => fn ($query) => $query->latest()->limit(1),
            ])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('donor_name', 'like', '%'.$this->search.'%')
                        ->orWhere('donor_email', 'like', '%'.$this->search.'%')
                        ->orWhereHas('campaign', function ($campaign) {
                            $campaign->where('title', 'like', '%'.$this->search.'%');
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
            'status' => 'required|in:pending,paid,failed,cancelled',
            'paid_at' => 'nullable|date',
            'notes' => 'nullable|string',
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

    public function loadData(Donation $donation): void
    {
        $this->resetData();

        $donation->load(['campaign', 'user', 'paymentAttempts' => fn ($query) => $query->latest()->limit(1)]);

        $this->status = $donation->status;
        $this->paid_at = $donation->paid_at?->format('Y-m-d\TH:i');
        $this->notes = $donation->notes;

        $this->donation = $donation;
    }

    public function editData(): void
    {
        $this->authorize('donations.edit');

        $data = $this->validate();

        if ($data['status'] === 'paid' && empty($data['paid_at'])) {
            $data['paid_at'] = now();
        }

        if ($data['status'] !== 'paid') {
            $data['paid_at'] = null;
        }

        $this->donation->update($data);

        $this->dispatch('dataUpdated', dataId: "item-id-{$this->donation->id}");
        $this->success(__('Donation updated successfully'));
        $this->resetData();
    }

    public function resetData(): void
    {
        $this->reset(['status', 'paid_at', 'notes', 'donation']);
        $this->status = 'pending';
    }

    public function deleteSingle(Donation $donation): void
    {
        $this->authorize('donations.delete');
        $donation->delete();
        $this->success(__('Donation deleted successfully'));
    }

    public function deleteMultiple(): void
    {
        $this->authorize('donations.delete');

        Donation::whereIn('id', $this->selectedRows)->delete();

        $this->selectPageRows = false;
        $this->selectedRows = [];
        $this->success(__('Donations deleted successfully'));
    }
};
