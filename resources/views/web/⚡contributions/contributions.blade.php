<div class="min-h-screen bg-gradient-to-br from-emerald-50 via-white to-teal-50 dark:from-gray-900 dark:via-gray-900 dark:to-emerald-950">
    
    {{-- Hero Section --}}
    <section class="relative overflow-hidden py-12 sm:py-16 bg-white dark:bg-gray-900">
        <div class="absolute inset-0 -z-10">
            <div class="absolute top-0 right-0 h-96 w-96 rounded-full bg-emerald-300/20 dark:bg-emerald-600/10 blur-3xl"></div>
            <div class="absolute bottom-0 left-0 h-96 w-96 rounded-full bg-teal-300/20 dark:bg-teal-600/10 blur-3xl"></div>
        </div>

        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-emerald-100 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 mb-6">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    <span class="text-xs font-semibold text-emerald-700 dark:text-emerald-300 uppercase tracking-wider">{{ __('Impact & Progress') }}</span>
                </div>
                
                <h1 class="text-4xl sm:text-5xl font-bold">
                    <span class="bg-gradient-to-r from-emerald-600 to-teal-600 dark:from-emerald-400 dark:to-teal-400 bg-clip-text text-transparent">
                        {{ __('Our Contributions') }}
                    </span>
                </h1>
                
                <p class="mt-4 text-lg text-gray-600 dark:text-gray-300 max-w-2xl mx-auto">
                    {{ __('See how your generosity is being put to work. Real impacts, real stories.') }}
                </p>

                <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
                    <div class="max-w-md w-full">
                        <x-input 
                            wire:model.live.debounce.400ms="search" 
                            placeholder="{{ __('Search by title or location...') }}" 
                            icon="o-magnifying-glass"
                            class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm border-2 border-emerald-100 dark:border-emerald-900 focus:border-emerald-500 rounded-2xl shadow-sm" />
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Contributions Grid --}}
    <section class="py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                @forelse($this->contributions as $item)
                    <div class="group flex flex-col overflow-hidden rounded-[2.5rem] bg-white dark:bg-gray-800 shadow-xl hover:shadow-2xl transition-all duration-500 border border-gray-100 dark:border-gray-700 hover:-translate-y-2">
                        {{-- Image --}}
                        <div class="relative h-64 w-full overflow-hidden">
                            <img class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-110" 
                                 src="{{ getImage($item, 'cover', 'thumb') }}" 
                                 alt="{{ $item->title }}" />
                            
                            {{-- Overlay info --}}
                            <div class="absolute bottom-0 left-0 right-0 p-6 bg-gradient-to-t from-black/80 via-black/40 to-transparent">
                                <div class="flex items-center justify-between text-white">
                                    <div class="flex items-center gap-2">
                                        <x-icon name="o-calendar" class="w-4 h-4 opacity-80" />
                                        <span class="text-xs font-medium">{{ $item->date?->format('F d, Y') ?? 'TBA' }}</span>
                                    </div>
                                    @if($item->location)
                                        <div class="flex items-center gap-1">
                                            <x-icon name="o-map-pin" class="w-4 h-4 text-emerald-400" />
                                            <span class="text-xs font-semibold">{{ $item->location }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Content --}}
                        <div class="flex flex-col flex-1 p-8">
                            <div class="mb-4">
                                <h3 class="text-2xl font-bold text-gray-900 dark:text-white line-clamp-2 leading-tight group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">
                                    {{ $item->title }}
                                </h3>
                                <div class="mt-2 text-sm font-semibold text-emerald-600 dark:text-emerald-400 flex items-center gap-1">
                                    <x-icon name="o-currency-bangladeshi" class="w-4 h-4" />
                                    <span>৳ {{ number_format($item->amount, 0) }} {{ __('Deployed') }}</span>
                                </div>
                            </div>

                            <p class="text-gray-600 dark:text-gray-300 line-clamp-3 text-sm leading-relaxed flex-1">
                                {{ $item->description }}
                            </p>

                            <div class="mt-8 pt-6 border-t border-gray-100 dark:border-gray-700 flex items-center">
                                <a href="{{ route('web.contribution', $item->slug) }}" 
                                   wire:navigate
                                   class="text-sm font-bold text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 dark:hover:text-emerald-300 flex items-center gap-2 transition-all group/link">
                                    {{ __('Read Full Story') }}
                                    <x-icon name="o-arrow-right" class="w-4 h-4 transform group-hover/link:translate-x-1 transition-transform" />
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="sm:col-span-2 lg:col-span-3">
                        <div class="rounded-[3rem] border-2 border-dashed border-emerald-100 dark:border-emerald-900 bg-emerald-50/30 dark:bg-emerald-950/20 p-16 text-center">
                            <div class="relative inline-block">
                                <x-icon name="o-heart" class="w-20 h-20 mx-auto text-emerald-200 dark:text-emerald-800" />
                                <div class="absolute -top-2 -right-2">
                                    <x-icon name="o-magnifying-glass" class="w-8 h-8 text-emerald-500" />
                                </div>
                            </div>
                            <h3 class="mt-6 text-xl font-bold text-gray-900 dark:text-white">{{ __('No contributions found') }}</h3>
                            <p class="mt-2 text-gray-600 dark:text-gray-400 max-w-sm mx-auto">{{ __('We are working hard to make more impact. Check back soon for new stories.') }}</p>
                            @if($search)
                                <button wire:click="$set('search', '')" class="mt-8 btn btn-emerald btn-outline rounded-2xl">
                                    {{ __('Clear Search') }}
                                </button>
                            @endif
                        </div>
                    </div>
                @endforelse
            </div>

            {{-- Pagination --}}
            <div class="mt-12 flex justify-center">
                {{ $this->contributions->links() }}
            </div>
        </div>
    </section>
</div>
