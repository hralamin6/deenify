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
                                <div class="flex items-center gap-4">
                                    <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                                        <x-icon name="o-users" class="w-4 h-4" />
                                        <span>{{ number_format($campaign->paid_donations_count ?? 0) }}</span>
                                    </div>
                                    
                                    <div class="relative" x-data="{ shareOpen: false }">
                                        <button @click="shareOpen = !shareOpen" 
                                                class="p-2 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-400 hover:text-indigo-500 transition-colors">
                                            <x-icon name="o-share" class="w-4 h-4" />
                                        </button>

                                        <div x-cloak x-show="shareOpen" 
                                             x-transition:enter="transition ease-out duration-200" 
                                             x-transition:enter-start="opacity-0 scale-95 translate-y-2" 
                                             x-transition:enter-end="opacity-100 scale-100 translate-y-0" 
                                             x-transition:leave="transition ease-in duration-150" 
                                             x-transition:leave-start="opacity-100 scale-100 translate-y-0" 
                                             x-transition:leave-end="opacity-0 scale-95 translate-y-2" 
                                             @click.outside="shareOpen = false" 
                                             class="absolute left-0 bottom-full mb-4 w-64 p-3 rounded-2xl border border-gray-200 dark:border-gray-700 bg-white/95 dark:bg-gray-800/95 backdrop-blur-xl shadow-2xl z-50">
                                            
                                            <div class="grid grid-cols-3 gap-2">
                                                @php
                                                    $shareUrl = route('web.campaign', $campaign->slug);
                                                    $shareTitle = $campaign->title;
                                                @endphp
                                                <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($shareUrl) }}" target="_blank" rel="noopener" class="flex flex-col items-center justify-center p-2 rounded-xl hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors group">
                                                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 group-hover:scale-110 transition-transform" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                                                </a>
                                                <a href="https://x.com/intent/tweet?url={{ urlencode($shareUrl) }}&text={{ urlencode($shareTitle) }}" target="_blank" rel="noopener" class="flex flex-col items-center justify-center p-2 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors group">
                                                    <svg class="w-5 h-5 text-gray-900 dark:text-white group-hover:scale-110 transition-transform" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.134l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                                                </a>
                                                <a href="https://wa.me/?text={{ urlencode($shareTitle) }}%20{{ urlencode($shareUrl) }}" target="_blank" rel="noopener" class="flex flex-col items-center justify-center p-2 rounded-xl hover:bg-emerald-50 dark:hover:bg-emerald-900/20 transition-colors group">
                                                    <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400 group-hover:scale-110 transition-transform" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                                                </a>
                                            </div>
                                            <button @click="navigator.clipboard.writeText('{{ $shareUrl }}'); $wire.success('{{ __('Link copied!') }}'); shareOpen = false;" class="mt-2 w-full py-1.5 rounded-lg border border-dashed border-gray-200 dark:border-gray-700 text-[10px] font-bold text-gray-500 hover:border-indigo-500 hover:text-indigo-500 transition-all flex items-center justify-center gap-1">
                                                <x-icon name="o-link" class="w-3 h-3" />
                                                {{ __('Copy Link') }}
                                            </button>
                                        </div>
                                    </div>
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
