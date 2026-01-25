<?php

use App\Models\ExpenseCategory;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

new #[Title('Expense Categories')] #[Layout('layouts.app')] class extends Component
{
    use Toast, WithPagination;

    public Collection $library;

    public $selectedRows = [];

    public $selectPageRows = false;

    public $itemPerPage = 10;

    public $orderBy = 'name';

    public $orderDirection = 'asc';

    public $search = '';

    public $searchBy = 'name';

    public $itemStatus = null;

    public $name = '';

    public $description = '';

    public $is_active = true;

    public $category = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'itemStatus' => ['except' => null],
    ];

    public function mount(): void
    {
        $this->authorize('expense-categories.view');
    }

    #[Computed]
    public function items()
    {
        return $this->data;
    }

    public function getDataProperty()
    {
        return ExpenseCategory::query()
            ->withCount('expenses')
            ->where($this->searchBy, 'like', '%'.$this->search.'%')
            ->when(! is_null($this->itemStatus), function ($query) {
                return $query->where('is_active', $this->itemStatus === 'active');
            })
            ->orderBy($this->orderBy, $this->orderDirection)
            ->paginate($this->itemPerPage)
            ->withQueryString();
    }

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:expense_categories,name,'.$this->category?->id,
            'description' => 'nullable|string',
            'is_active' => 'boolean',
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
        $this->authorize('expense-categories.create');

        $data = $this->validate();

        $model = ExpenseCategory::create($data);

        $this->dispatch('dataAdded', dataId: "item-id-{$model->id}");
        $this->goToPage($this->getDataProperty()->lastPage());
        $this->success(__('Expense category created successfully!'));

        $this->resetData();
    }

    public function loadData(ExpenseCategory $category): void
    {
        $this->resetData();

        $this->name = $category->name;
        $this->description = $category->description;
        $this->is_active = (bool) $category->is_active;

        $this->category = $category;
    }

    public function editData(): void
    {
        $this->authorize('expense-categories.edit');

        $data = $this->validate();
        $this->category->update($data);

        $this->dispatch('dataAdded', dataId: "item-id-{$this->category->id}");
        $this->success(__('Expense category updated successfully'));
        $this->resetData();
    }

    public function resetData(): void
    {
        $this->reset(['name', 'description', 'is_active', 'category']);
        $this->is_active = true;
    }

    public function deleteSingle(ExpenseCategory $category): void
    {
        $this->authorize('expense-categories.delete');
        $category->delete();
        $this->success(__('Expense category deleted successfully'));
    }

    public function deleteMultiple(): void
    {
        $this->authorize('expense-categories.delete');

        ExpenseCategory::whereIn('id', $this->selectedRows)->delete();

        $this->selectPageRows = false;
        $this->selectedRows = [];
        $this->success(__('Expense categories deleted successfully'));
    }

    public function toggleActive(ExpenseCategory $category): void
    {
        $this->authorize('expense-categories.edit');

        $category->update(['is_active' => ! $category->is_active]);
        $this->success(__('Status updated successfully'));
    }
};
