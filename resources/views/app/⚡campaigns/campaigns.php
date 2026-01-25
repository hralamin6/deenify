<?php

use App\Models\Campaign;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Mary\Traits\Toast;

new #[Title('Campaigns')] #[Layout('layouts.app')] class extends Component
{
    use Toast, WithFileUploads, WithPagination;

    public Collection $library;

    // Table/List Properties
    public $selectedRows = [];

    public $selectPageRows = false;

    public $itemPerPage = 10;

    public $orderBy = 'starts_at';

    public $orderDirection = 'desc';

    public $search = '';

    public $searchBy = 'title';

    public $itemStatus = null;

    // Form Properties
    public $title = '';

    public $slug = '';

    public $description = '';

    public $goal_amount = 0;

    public $status = 'draft';

    public $starts_at = null;

    public $ends_at = null;

    // File uploads
    public $photo;

    public $image_url = '';

    // Current model being edited
    public $campaign = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'itemStatus' => ['except' => null],
    ];

    public function mount(): void
    {
        $this->authorize('campaigns.view');
    }

    #[Computed]
    public function items()
    {
        return $this->data;
    }

    public function getDataProperty()
    {
        return Campaign::query()
            ->withSum(['donations as paid_donations_sum' => fn ($q) => $q->where('status', 'paid')], 'amount')
            ->withCount(['donations as paid_donations_count' => fn ($q) => $q->where('status', 'paid')])
            ->withSum('expenses', 'amount')
            ->where($this->searchBy, 'like', '%'.$this->search.'%')
            ->when($this->itemStatus, function ($query) {
                return $query->where('status', $this->itemStatus);
            })
            ->orderBy($this->orderBy, $this->orderDirection)
            ->paginate($this->itemPerPage)
            ->withQueryString();
    }

    protected function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'slug' => 'required|string|alpha_dash|unique:campaigns,slug,'.$this->campaign?->id,
            'description' => 'nullable|string',
            'goal_amount' => 'required|numeric|min:0',
            'status' => 'required|in:draft,active,closed',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date',
            'photo' => 'nullable|image|max:2048',
            'image_url' => 'nullable|url',
        ];
    }

    public function updatedTitle(): void
    {
        $this->slug = \Str::slug($this->title);
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
        $this->authorize('campaigns.create');

        $data = $this->validate();
        $data['created_by'] = auth()->id();

        $model = Campaign::create($data);

        $this->handleMediaUpload($model);

        $this->dispatch('dataAdded', dataId: "item-id-{$model->id}");
        $this->goToPage($this->getDataProperty()->lastPage());
        $this->success(__('Campaign created successfully!'));

        $this->resetData();
    }

    public function loadData(Campaign $campaign): void
    {
        $this->resetData();

        $this->title = $campaign->title;
        $this->slug = $campaign->slug;
        $this->description = $campaign->description;
        $this->goal_amount = $campaign->goal_amount;
        $this->status = $campaign->status;
        $this->starts_at = $campaign->starts_at?->format('Y-m-d\TH:i');
        $this->ends_at = $campaign->ends_at?->format('Y-m-d\TH:i');

        $this->campaign = $campaign;
    }

    public function editData(): void
    {
        $this->authorize('campaigns.edit');

        $data = $this->validate();
        $this->campaign->update($data);

        $this->handleMediaUpload($this->campaign);

        $this->dispatch('dataAdded', dataId: "item-id-{$this->campaign->id}");
        $this->success(__('Campaign updated successfully'));
        $this->resetData();
    }

    public function resetData(): void
    {
        $this->reset([
            'title', 'slug', 'description', 'goal_amount', 'status',
            'starts_at', 'ends_at', 'image_url', 'photo', 'campaign',
        ]);

        $this->status = 'draft';
        $this->goal_amount = 0;
    }

    public function deleteSingle(Campaign $campaign): void
    {
        $this->authorize('campaigns.delete');
        $campaign->delete();
        $this->success(__('Campaign deleted successfully'));
    }

    public function deleteMultiple(): void
    {
        $this->authorize('campaigns.delete');

        Campaign::whereIn('id', $this->selectedRows)->delete();

        $this->selectPageRows = false;
        $this->selectedRows = [];
        $this->success(__('Campaigns deleted successfully'));
    }

    public function changeStatus(Campaign $campaign): void
    {
        $this->authorize('campaigns.edit');

        $next = match ($campaign->status) {
            'draft' => 'active',
            'active' => 'closed',
            default => 'draft',
        };

        $campaign->update(['status' => $next]);

        $this->success(__('Status updated successfully'));
    }

    public function deleteMedia(Campaign $campaign, $key): void
    {
        $this->authorize('campaigns.edit');

        $media = $campaign->getMedia('cover');
        $media[$key]->delete();
        $this->success(__('Image deleted successfully'));
    }

    protected function handleMediaUpload($model): void
    {
        if ($this->image_url && checkImageUrl($this->image_url)) {
            $extension = pathinfo(parse_url($this->image_url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
            $model->addMediaFromUrl($this->image_url)
                ->usingFileName($model->id.'.'.$extension)
                ->toMediaCollection('cover');
        } elseif ($this->photo) {
            $model->addMedia($this->photo->getRealPath())
                ->usingFileName($model->id.'.'.$this->photo->extension())
                ->toMediaCollection('cover');
        }
    }
};
