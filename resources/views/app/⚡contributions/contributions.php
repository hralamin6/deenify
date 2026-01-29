<?php

use App\Models\Contribution;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Mary\Traits\Toast;

new #[Title('Contributions')] #[Layout('layouts.app')] class extends Component
{
    use Toast, WithFileUploads, WithPagination;

    public Collection $library;

    // Table/List Properties
    public $selectedRows = [];

    public $selectPageRows = false;

    public $itemPerPage = 10;

    public $orderBy = 'date';

    public $orderDirection = 'desc';

    public $search = '';

    public $searchBy = 'title';

    public $itemStatus = null;

    // Form Properties
    public $title = '';

    public $slug = '';

    public $description = '';

    public $amount = 0;

    public $date = null;

    public $location = '';

    public $status = 'published';

    // File uploads
    public $photo;

    public $photos = [];

    public $image_url = '';

    // Current model being edited
    public $contribution = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'itemStatus' => ['except' => null],
    ];

    public function mount(): void
    {
        $this->authorize('contributions.view');
        $this->date = now()->format('Y-m-d');
    }

    #[Computed]
    public function items()
    {
        return $this->data;
    }

    public function getDataProperty()
    {
        return Contribution::query()
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
            'slug' => 'required|string|alpha_dash|unique:contributions,slug,'.$this->contribution?->id,
            'description' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'location' => 'nullable|string|max:255',
            'status' => 'required|in:draft,published',
            'photo' => 'nullable|image|max:2048',
            'photos.*' => 'nullable|image|max:2048',
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
        $this->authorize('contributions.create');

        $data = $this->validate();
        
        $model = Contribution::create($data);

        $this->handleMediaUpload($model);

        $this->dispatch('dataAdded', dataId: "item-id-{$model->id}");
        $this->goToPage($this->getDataProperty()->lastPage());
        $this->success(__('Contribution created successfully!'));

        $this->resetData();
    }

    public function loadData(Contribution $contribution): void
    {
        $this->resetData();

        $this->title = $contribution->title;
        $this->slug = $contribution->slug;
        $this->description = $contribution->description;
        $this->amount = $contribution->amount;
        $this->status = $contribution->status;
        $this->date = $contribution->date?->format('Y-m-d');
        $this->location = $contribution->location;

        $this->contribution = $contribution;
    }

    public function editData(): void
    {
        $this->authorize('contributions.edit');

        $data = $this->validate();
        $this->contribution->update($data);

        $this->handleMediaUpload($this->contribution);

        $this->dispatch('dataAdded', dataId: "item-id-{$this->contribution->id}");
        $this->success(__('Contribution updated successfully'));
        $this->resetData();
    }

    public function resetData(): void
    {
        $this->reset([
            'title', 'slug', 'description', 'amount', 'status',
            'date', 'location', 'image_url', 'photo', 'photos', 'contribution',
        ]);

        $this->status = 'published';
        $this->amount = 0;
        $this->date = now()->format('Y-m-d');
    }

    public function deleteSingle(Contribution $contribution): void
    {
        $this->authorize('contributions.delete');
        $contribution->delete();
        $this->success(__('Contribution deleted successfully'));
    }

    public function deleteMultiple(): void
    {
        $this->authorize('contributions.delete');

        Contribution::whereIn('id', $this->selectedRows)->delete();

        $this->selectPageRows = false;
        $this->selectedRows = [];
        $this->success(__('Contributions deleted successfully'));
    }

    public function changeStatus(Contribution $contribution): void
    {
        $this->authorize('contributions.edit');

        $next = $contribution->status === 'draft' ? 'published' : 'draft';

        $contribution->update(['status' => $next]);

        $this->success(__('Status updated successfully'));
    }

    public function deleteMedia(Contribution $contribution, $key, $collection = 'gallery'): void
    {
        $this->authorize('contributions.edit');

        $media = $contribution->getMedia($collection);
        if (isset($media[$key])) {
            $media[$key]->delete();
            $this->success(__('Media deleted successfully'));
        }
    }

    protected function handleMediaUpload($model): void
    {
        if ($this->image_url && checkImageUrl($this->image_url)) {
            $extension = pathinfo(parse_url($this->image_url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
            $media = $model->addMediaFromUrl($this->image_url)
                ->usingFileName($model->id.'.'.$extension)
                ->toMediaCollection('cover');
            if ($media && file_exists($media->getPath())) {
                unlink($media->getPath());
            }
        } elseif ($this->photo) {
            $media = $model->addMedia($this->photo->getRealPath())
                ->usingFileName($model->id.'.'.$this->photo->extension())
                ->toMediaCollection('cover');
            if ($media && file_exists($media->getPath())) {
                unlink($media->getPath());
            }
        }

        if ($this->photos) {
            foreach ($this->photos as $p) {
                $media = $model->addMedia($p->getRealPath())
                    ->usingFileName($model->id.'_gallery_'.uniqid().'.'.$p->extension())
                    ->toMediaCollection('gallery');
                if ($media && file_exists($media->getPath())) {
                    unlink($media->getPath());
                }
            }
        }
    }
};