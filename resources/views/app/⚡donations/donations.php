<?php

use App\Models\Campaign;
use App\Models\Donation;
use App\Models\User;
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

    public $donationCampaignId = null;

    public $userId = null;

    public $status = 'pending';

    public $paid_at = null;

    public $notes = '';

    public $donor_name = '';

    public $donor_email = '';

    public $amount = 0;

    public $currency = 'BDT';

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

    #[Computed]
    public function users()
    {
        return User::query()
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
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
        $this->reset([
            'donationCampaignId', 'userId', 'donor_name', 'donor_email',
            'amount', 'currency', 'status', 'paid_at', 'notes', 'donation',
        ]);
        $this->status = 'pending';
        $this->currency = 'BDT';
        $this->amount = 0;
    }

    public function saveData(): void
    {
        $this->authorize('donations.create');

        if ($this->userId && (empty($this->donor_name) || empty($this->donor_email))) {
            $user = User::find($this->userId);
            $this->donor_name = $this->donor_name ?: $user?->name;
            $this->donor_email = $this->donor_email ?: $user?->email;
        }

        $data = $this->validate([
            'donationCampaignId' => 'required|exists:campaigns,id',
            'userId' => 'nullable|exists:users,id',
            'donor_name' => 'required|string|max:255',
            'donor_email' => 'nullable|email|max:255',
            'amount' => 'required|numeric|min:1',
            'currency' => 'required|string|max:3',
            'status' => 'required|in:pending,paid,failed,cancelled',
            'paid_at' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        if ($data['status'] === 'paid' && empty($data['paid_at'])) {
            $data['paid_at'] = now();
        }

        if ($data['status'] !== 'paid') {
            $data['paid_at'] = null;
        }

        $donation = Donation::create([
            'campaign_id' => $data['donationCampaignId'],
            'user_id' => $data['userId'],
            'donor_name' => $data['donor_name'],
            'donor_email' => $data['donor_email'],
            'amount' => $data['amount'],
            'currency' => $data['currency'],
            'status' => $data['status'],
            'paid_at' => $data['paid_at'],
            'notes' => $data['notes'],
        ]);

        $this->dispatch('dataAdded', dataId: "item-id-{$donation->id}");
        $this->resetPage();
        $this->success(__('Donation created successfully'));
        $this->resetData();
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

    public function sendReminder(Donation $donation): void
    {
        $this->authorize('donations.edit');

        if ($donation->status !== 'pending') {
            $this->error(__('Donation is not pending.'));
            return;
        }

        if (! $donation->user) {
            $this->error(__('Donation has no associated user.'));
            return;
        }

        // Check verification (load if not loaded, though check latest usually sufficient)
        $latestAttempt = $donation->paymentAttempts()->latest()->first();
        if ($latestAttempt?->status === 'pending_verification') {
            $this->error(__('Payment is pending verification.'));
            return;
        }

        if ($donation->recurring_plan_id) {
            // Recurring Donation Reminder
            $donation->user->notify(new \App\Notifications\RecurringDonationPendingNotification($donation, true));
        } else {
            // One-time Donation Reminder
            $donation->user->notify(new \App\Notifications\TaskNotification(
                title: __('Donation Reminder'),
                message: __('You have a pending donation for :campaign. Please complete your payment.', ['campaign' => $donation->campaign->title]),
                url: route('web.campaign', $donation->campaign->slug).'#donate',
                icon: 'o-bell',
                type: 'info'
            ));
        }

        $this->success(__('Reminder sent successfully'));
    }
};
