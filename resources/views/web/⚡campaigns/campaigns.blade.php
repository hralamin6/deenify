<div x-data="campaignList()" class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-purple-50 dark:from-gray-900 dark:via-gray-900 dark:to-indigo-950">
    {{-- Header is now in layout --}}
    
    {{-- Hero Section --}}
    <section class="relative overflow-hidden py-12 sm:py-16 bg-white dark:bg-gray-900">
        <div class="absolute inset-0 -z-10">
            <div class="absolute top-0 right-0 h-96 w-96 rounded-full bg-purple-300/20 dark:bg-purple-600/10 blur-3xl"></div>
            <div class="absolute bottom-0 left-0 h-96 w-96 rounded-full bg-indigo-300/20 dark:bg-indigo-600/10 blur-3xl"></div>
        </div>

        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-indigo-100 dark:bg-indigo-900/30 border border-indigo-200 dark:border-indigo-800 mb-6">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-indigo-500"></span>
                    </span>
                    <span class="text-xs font-semibold text-indigo-700 dark:text-indigo-300 uppercase tracking-wider">{{ __('Active Campaigns') }}</span>
                </div>
                
                <h1 class="text-4xl sm:text-5xl font-bold">
                    <span class="bg-gradient-to-r from-indigo-600 to-purple-600 dark:from-indigo-400 dark:to-purple-400 bg-clip-text text-transparent">
                        {{ __('Explore Campaigns') }}
                    </span>
                </h1>
                
                <p class="mt-4 text-lg text-gray-600 dark:text-gray-300 max-w-2xl mx-auto">
                    {{ __('Give directly to causes with transparent progress and impact.') }}
                </p>

                <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
                    <button @click="filtersOpen = !filtersOpen" 
                            class="btn btn-outline border-2 border-indigo-600 text-indigo-600 hover:bg-indigo-600 hover:text-white dark:border-indigo-400 dark:text-indigo-400">
                        <x-icon name="o-funnel" class="w-5 h-5" />
                        <span x-text="filtersOpen ? '{{ __('Hide Filters') }}' : '{{ __('Show Filters') }}'"></span>
                    </button>
                    <a href="{{ route('web.home') }}" wire:navigate class="btn btn-ghost">
                        <x-icon name="o-arrow-left" class="w-5 h-5" />
                        {{ __('Back Home') }}
                    </a>
                </div>
            </div>

            {{-- Filters --}}
            <div x-show="filtersOpen" 
                 x-cloak
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 -translate-y-3"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 -translate-y-3"
                 class="mt-8 rounded-3xl bg-white dark:bg-gray-800 p-6 shadow-xl border border-gray-200 dark:border-gray-700">
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <x-input wire:model.live.debounce.400ms="search" :label="__('Search')" placeholder="Search campaigns" icon="o-magnifying-glass" />
                    <x-select wire:model.live="status" :label="__('Status')" :options="[
                        ['id' => 'all', 'name' => __('All')],
                        ['id' => 'active', 'name' => __('Active')],
                        ['id' => 'closed', 'name' => __('Closed')],
                        ['id' => 'draft', 'name' => __('Draft')]
                    ]" icon="o-flag" />
                    <x-input wire:model.live.debounce.400ms="minGoal" type="number" :label="__('Min Goal (BDT)')" placeholder="0" icon="o-currency-bangladeshi" />
                    <x-input wire:model.live.debounce.400ms="maxGoal" type="number" :label="__('Max Goal (BDT)')" placeholder="500000" icon="o-currency-bangladeshi" />
                </div>
            </div>
        </div>
    </section>

    {{-- Campaigns Grid --}}
    <section class="py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @forelse($this->campaigns as $campaign)
                    @php
                        $raised = (float) ($campaign->paid_donations_sum ?? 0);
                        $goal = (float) ($campaign->goal_amount ?? 0);
                        $progress = $goal > 0 ? min(100, round(($raised / $goal) * 100)) : 0;
                    @endphp
                    <div class="group overflow-hidden rounded-3xl bg-white dark:bg-gray-800 shadow-lg hover:shadow-2xl transition-all duration-300 border border-gray-200 dark:border-gray-700 hover:-translate-y-1">
                        {{-- Image --}}
                        <div class="relative h-48 w-full overflow-hidden">
                            <img class="h-full w-full object-cover group-hover:scale-105 transition-transform duration-300" 
                                 src="{{ getImage($campaign, 'cover', 'thumb') }}" 
                                 alt="{{ $campaign->title }}" />
                            <div class="absolute top-4 left-4">
                                <span class="px-3 py-1 text-xs font-semibold rounded-full 
                                    {{ $campaign->status === 'active' ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300' }}">
                                    {{ ucfirst($campaign->status) }}
                                </span>
                            </div>
                            <div class="absolute top-4 right-4">
                                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-white/90 dark:bg-gray-900/90 text-gray-700 dark:text-gray-300">
                                    {{ $campaign->starts_at?->format('M j') ?? 'TBA' }}
                                </span>
                            </div>
                        </div>

                        {{-- Content --}}
                        <div class="p-6">
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white line-clamp-2 min-h-[3.5rem]">
                                {{ $campaign->title }}
                            </h3>
                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-300 line-clamp-2">
                                {{ Str::limit($campaign->description, 90) }}
                            </p>

                            {{-- Progress --}}
                            <div class="mt-4">
                                <div class="flex justify-between text-sm mb-2">
                                    <span class="text-gray-600 dark:text-gray-400">{{ __('Raised') }}</span>
                                    <span class="font-bold text-indigo-600 dark:text-indigo-400">৳{{ number_format($raised, 0) }}</span>
                                </div>
                                <div class="h-2.5 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                                    <div class="h-full rounded-full bg-gradient-to-r from-indigo-600 to-purple-600 transition-all duration-500" 
                                         style="width: {{ $progress }}%"></div>
                                </div>
                                <div class="flex justify-between text-xs mt-2 text-gray-500 dark:text-gray-400">
                                    <span>{{ __('Goal') }}: ৳{{ number_format($goal, 0) }}</span>
                                    <span class="font-semibold">{{ $progress }}%</span>
                                </div>
                            </div>

                            {{-- Footer --}}
                            <div class="mt-6 flex items-center justify-between">
                                <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                                    <x-icon name="o-users" class="w-4 h-4" />
                                    <span>{{ number_format($campaign->paid_donations_count ?? 0) }}</span>
                                </div>
                                <a href="{{ route('web.campaign', $campaign->slug) }}" 
                                   wire:navigate
                                   class="btn btn-sm bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white border-0 shadow-lg">
                                    {{ __('View Details') }}
                                    <x-icon name="o-arrow-right" class="w-4 h-4" />
                                </a>
                            </div>
                                <a href="{{ route('web.campaign', $campaign->slug) }}" 
                                   wire:navigate
                                   class="btn btn-sm bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white border-0 shadow-lg">
                                    {{ __('View Details') }}
                                    <x-icon name="o-arrow-right" class="w-4 h-4" />
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="sm:col-span-2 lg:col-span-3">
                        <div class="rounded-3xl border-2 border-dashed border-gray-300 dark:border-gray-700 bg-white/60 dark:bg-gray-800/60 p-12 text-center">
                            <x-icon name="o-magnifying-glass" class="w-16 h-16 mx-auto text-gray-400 dark:text-gray-600" />
                            <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-white">{{ __('No campaigns found') }}</h3>
                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Try adjusting your filters or search terms.') }}</p>
                        </div>
                    </div>
                @endforelse
            </div>

            {{-- Pagination --}}
            <div class="mt-8">
                {{ $this->campaigns->links() }}
            </div>
        </div>
    </section>
</div>

@script
<script>
    Alpine.data('campaignList', () => ({
        filtersOpen: false,
    }));
</script>
@endscript
