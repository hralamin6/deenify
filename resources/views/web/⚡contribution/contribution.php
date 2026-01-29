<?php

use App\Models\Contribution;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

new #[Title('Contribution Details')] #[Layout('layouts.auth')] class extends Component
{
    use Toast;

    public Contribution $contribution;

    public function mount(string $slug): void
    {
        $this->contribution = Contribution::query()
            ->where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();
    }

    #[\Livewire\Attributes\Computed]
    public function similarContributions()
    {
        return Contribution::query()
            ->where('status', 'published')
            ->where('id', '!=', $this->contribution->id)
            ->latest('date')
            ->limit(3)
            ->get();
    }
};
