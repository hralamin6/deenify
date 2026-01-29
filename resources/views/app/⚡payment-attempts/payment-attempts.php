<?php

use App\Models\PaymentAttempt;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

new #[Title('Payment Attempts')] #[Layout('layouts.app')] class extends Component
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

    public $gateway = null;

    public $status = 'pending_verification';

    public $provider_reference = '';

    public $completed_at = null;

    public $paymentAttempt = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'itemStatus' => ['except' => null],
        'gateway' => ['except' => null],
    ];

    public function mount(): void
    {
        $this->authorize('payment-attempts.view');
    }

    #[Computed]
    public function items()
    {
        return $this->data;
    }

    public function getDataProperty()
    {
        return PaymentAttempt::query()
            ->with([
                'donation.campaign:id,title',
                'donation.user:id,name,email',
            ])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('provider_reference', 'like', '%'.$this->search.'%')
                        ->orWhereHas('donation', function ($donation) {
                            $donation->where('donor_name', 'like', '%'.$this->search.'%')
                                ->orWhere('donor_email', 'like', '%'.$this->search.'%')
                                ->orWhereHas('campaign', function ($campaign) {
                                    $campaign->where('title', 'like', '%'.$this->search.'%');
                                });
                        });
                });
            })
            ->when($this->itemStatus, function ($query) {
                return $query->where('status', $this->itemStatus);
            })
            ->when($this->gateway, function ($query) {
                return $query->where('gateway', $this->gateway);
            })
            ->orderBy($this->orderBy, $this->orderDirection)
            ->paginate($this->itemPerPage)
            ->withQueryString();
    }

    protected function rules(): array
    {
        return [
            'status' => 'required|in:initiated,pending,pending_verification,success,failed,cancelled',
            'provider_reference' => 'nullable|string|max:255',
            'completed_at' => 'nullable|date',
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

    public function updatedGateway(): void
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

    public function loadData(PaymentAttempt $paymentAttempt): void
    {
        $this->resetData();

        $paymentAttempt->load(['donation.campaign', 'donation.user']);

        $this->status = $paymentAttempt->status;
        $this->provider_reference = $paymentAttempt->provider_reference;
        $this->completed_at = $paymentAttempt->completed_at?->format('Y-m-d\TH:i');

        $this->paymentAttempt = $paymentAttempt;
    }

    public function editData(): void
    {
        $this->authorize('payment-attempts.edit');

        $data = $this->validate();

        $isFinal = in_array($data['status'], ['success', 'failed', 'cancelled'], true);

        if ($isFinal && empty($data['completed_at'])) {
            $data['completed_at'] = now();
        }

        if (! $isFinal) {
            $data['completed_at'] = null;
        }

        $this->paymentAttempt->update($data);

        $donation = $this->paymentAttempt->donation;
        if ($donation) {
            $donationData = match ($data['status']) {
                'success' => [
                    'status' => 'paid',
                    'paid_at' => $donation->paid_at ?? $data['completed_at'] ?? now(),
                ],
                'failed' => [
                    'status' => 'failed',
                    'paid_at' => null,
                ],
                'cancelled' => [
                    'status' => 'cancelled',
                    'paid_at' => null,
                ],
                default => [
                    'status' => 'pending',
                    'paid_at' => null,
                ],
            };

            $donation->update($donationData);
        }

        $this->dispatch('dataUpdated', dataId: "item-id-{$this->paymentAttempt->id}");
        $this->success(__('Payment attempt updated successfully'));
        $this->resetData();
    }

    public function resetData(): void
    {
        $this->reset(['status', 'provider_reference', 'completed_at', 'paymentAttempt']);
        $this->status = 'pending_verification';
    }

    public function deleteSingle(PaymentAttempt $paymentAttempt): void
    {
        $this->authorize('payment-attempts.delete');
        $paymentAttempt->delete();
        $this->success(__('Payment attempt deleted successfully'));
    }

    public function deleteMultiple(): void
    {
        $this->authorize('payment-attempts.delete');

        PaymentAttempt::whereIn('id', $this->selectedRows)->delete();

        $this->selectPageRows = false;
        $this->selectedRows = [];
        $this->success(__('Payment attempts deleted successfully'));
    }
    public function verifyPayment(PaymentAttempt $paymentAttempt): void
    {
        $this->authorize('payment-attempts.edit');

        $now = now();

        $paymentAttempt->update([
            'status' => 'success',
            'completed_at' => $now,
        ]);

        $donation = $paymentAttempt->donation;
        if ($donation) {
            $donation->update([
                'status' => 'paid',
                'paid_at' => $now,
            ]);

            // Notify user
            if ($donation->user) {
                $donation->user->notify(new \App\Notifications\TaskNotification(
                    title: __('Payment Verified'),
                    message: __('Your donation of :amount :currency has been verified successfully.', ['amount' => $donation->amount, 'currency' => $donation->currency]),
                    url: route('web.campaign', $donation->campaign->slug),
                    icon: 'o-check-circle',
                    type: 'success'
                ));
            }
        }

        $this->success(__('Payment verified successfully'));
    }
};
