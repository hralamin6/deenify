<?php

use App\Models\Contribution;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Contributions')] #[Layout('layouts.auth')] class extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 9;

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function contributions()
    {
        return Contribution::query()
            ->where('status', 'published')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('title', 'like', '%'.$this->search.'%')
                      ->orWhere('description', 'like', '%'.$this->search.'%')
                      ->orWhere('location', 'like', '%'.$this->search.'%');
                });
            })
            ->orderByDesc('date')
            ->paginate($this->perPage)
            ->withQueryString();
    }
};
