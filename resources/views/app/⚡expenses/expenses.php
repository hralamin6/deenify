<?php

use App\Models\Campaign;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Mary\Traits\Toast;

new #[Title('Expenses')] #[Layout('layouts.app')] class extends Component
{
    use Toast, WithFileUploads, WithPagination;

    public Collection $library;

    public $selectedRows = [];

    public $selectPageRows = false;

    public $itemPerPage = 10;

    public $orderBy = 'spent_at';

    public $orderDirection = 'desc';

    public $search = '';

    public $searchBy = 'description';

    public $itemStatus = null;

    public $campaignId = null;

    public $categoryId = null;

    public $amount = 0;

    public $spent_at = null;

    public $description = '';

    public $receipt;

    public $expense = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'itemStatus' => ['except' => null],
    ];

    public function mount(): void
    {
        $this->authorize('expenses.view');
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
    public function categories()
    {
        return ExpenseCategory::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function getDataProperty()
    {
        return Expense::query()
            ->with(['campaign', 'category'])
            ->where($this->searchBy, 'like', '%'.$this->search.'%')
            ->when($this->itemStatus, function ($query) {
                return $query->whereHas('campaign', function ($q) {
                    $q->where('status', $this->itemStatus);
                });
            })
            ->orderBy($this->orderBy, $this->orderDirection)
            ->paginate($this->itemPerPage)
            ->withQueryString();
    }

    protected function rules(): array
    {
        return [
            'campaignId' => 'required|exists:campaigns,id',
            'categoryId' => 'nullable|exists:expense_categories,id',
            'amount' => 'required|numeric|min:0',
            'spent_at' => 'nullable|date',
            'description' => 'nullable|string',
            'receipt' => 'nullable|file|max:4096',
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

    public function saveData(): void
    {
        $this->authorize('expenses.create');

        $data = $this->validate();

        $model = Expense::create([
            'campaign_id' => $data['campaignId'],
            'expense_category_id' => $data['categoryId'],
            'amount' => $data['amount'],
            'spent_at' => $data['spent_at'],
            'description' => $data['description'],
            'created_by' => auth()->id(),
        ]);

        $this->handleMediaUpload($model);

        $this->dispatch('dataAdded', dataId: "item-id-{$model->id}");
        $this->goToPage($this->getDataProperty()->lastPage());
        $this->success(__('Expense created successfully!'));

        $this->resetData();
    }

    public function loadData(Expense $expense): void
    {
        $this->resetData();

        $this->campaignId = $expense->campaign_id;
        $this->categoryId = $expense->expense_category_id;
        $this->amount = $expense->amount;
        $this->spent_at = $expense->spent_at?->format('Y-m-d\TH:i');
        $this->description = $expense->description;

        $this->expense = $expense;
    }

    public function editData(): void
    {
        $this->authorize('expenses.edit');

        $data = $this->validate();

        $this->expense->update([
            'campaign_id' => $data['campaignId'],
            'expense_category_id' => $data['categoryId'],
            'amount' => $data['amount'],
            'spent_at' => $data['spent_at'],
            'description' => $data['description'],
        ]);

        $this->handleMediaUpload($this->expense);

        $this->dispatch('dataAdded', dataId: "item-id-{$this->expense->id}");
        $this->success(__('Expense updated successfully'));
        $this->resetData();
    }

    public function resetData(): void
    {
        $this->reset([
            'campaignId', 'categoryId', 'amount', 'spent_at', 'description', 'receipt', 'expense',
        ]);

        $this->amount = 0;
    }

    public function deleteSingle(Expense $expense): void
    {
        $this->authorize('expenses.delete');
        $expense->delete();
        $this->success(__('Expense deleted successfully'));
    }

    public function deleteMultiple(): void
    {
        $this->authorize('expenses.delete');

        Expense::whereIn('id', $this->selectedRows)->delete();

        $this->selectPageRows = false;
        $this->selectedRows = [];
        $this->success(__('Expenses deleted successfully'));
    }

    public function deleteMedia(Expense $expense, $key): void
    {
        $this->authorize('expenses.edit');

        $media = $expense->getMedia('receipt');
        $media[$key]->delete();
        $this->success(__('Receipt deleted successfully'));
    }

    protected function handleMediaUpload($model): void
    {
        if ($this->receipt) {
            $model->addMedia($this->receipt->getRealPath())
                ->usingFileName($model->id.'.'.$this->receipt->extension())
                ->toMediaCollection('receipt');
        }
    }
};
