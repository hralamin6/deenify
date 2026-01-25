<div class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-purple-50 dark:from-gray-900 dark:via-gray-900 dark:to-indigo-950">
    @php
        $raised = (float) ($campaign->paid_donations_sum ?? 0);
        $expense = (float) ($campaign->expenses_sum ?? 0);
        $goal = (float) ($campaign->goal_amount ?? 0);
        $progress = $goal > 0 ? min(100, round(($raised / $goal) * 100)) : 0;
        $balance = $raised - $expense;
    @endphp

    {{-- Hero Section --}}
    <section class="relative overflow-hidden bg-white dark:bg-gray-900 py-8">
        <div class="absolute inset-0 -z-10">
            <div class="absolute top-0 right-0 h-96 w-96 rounded-full bg-purple-300/20 dark:bg-purple-600/10 blur-3xl"></div>
            <div class="absolute bottom-0 left-0 h-96 w-96 rounded-full bg-indigo-300/20 dark:bg-indigo-600/10 blur-3xl"></div>
        </div>

        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
                <a href="{{ route('web.campaigns') }}" wire:navigate class="btn btn-ghost btn-sm">
                    <x-icon name="o-arrow-left" class="w-5 h-5" />
                    {{ __('All Campaigns') }}
                </a>
                <div class="flex items-center gap-2">
                    <span class="px-3 py-1 text-xs font-semibold rounded-full 
                        {{ $campaign->status === 'active' ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 border border-green-200 dark:border-green-800' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300' }}">
                        {{ ucfirst($campaign->status) }}
                    </span>
                    <span class="px-3 py-1 text-xs font-semibold rounded-full bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                        {{ $campaign->starts_at?->format('M j, Y') ?? 'TBA' }}
                    </span>
                </div>
            </div>

            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold">
                <span class="bg-gradient-to-r from-indigo-600 to-purple-600 dark:from-indigo-400 dark:to-purple-400 bg-clip-text text-transparent">
                    {{ $campaign->title }}
                </span>
            </h1>
            <p class="mt-4 text-lg text-gray-600 dark:text-gray-300 max-w-3xl">
                {{ $campaign->description }}
            </p>
        </div>
    </section>

    {{-- Main Content --}}
    <section class="py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-8 lg:grid-cols-3">
                {{-- Left Column - Main Content --}}
                <div class="lg:col-span-2 space-y-8">
                    {{-- Campaign Image & Progress --}}
                    <div class="overflow-hidden rounded-3xl bg-white dark:bg-gray-800 shadow-xl border border-gray-200 dark:border-gray-700">
                        <div class="relative h-64 sm:h-80 w-full overflow-hidden">
                            <img class="h-full w-full object-cover" 
                                 src="{{ getImage($campaign, 'cover', 'thumb') }}" 
                                 alt="{{ $campaign->title }}" />
                        </div>
                        
                        <div class="p-6 sm:p-8">
                            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">{{ __('Campaign Progress') }}</h2>
                            
                            {{-- Progress Bar --}}
                            <div class="mb-6">
                                <div class="flex justify-between text-sm mb-3">
                                    <span class="text-gray-600 dark:text-gray-400">{{ __('Raised') }}</span>
                                    <span class="font-bold text-2xl text-indigo-600 dark:text-indigo-400">৳{{ number_format($raised, 0) }}</span>
                                </div>
                                <div class="h-4 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                                    <div class="h-full rounded-full bg-gradient-to-r from-indigo-600 to-purple-600 transition-all duration-500" 
                                         style="width: {{ $progress }}%"></div>
                                </div>
                                <div class="flex justify-between text-sm mt-3 text-gray-600 dark:text-gray-400">
                                    <span>{{ __('Goal') }}: ৳{{ number_format($goal, 0) }}</span>
                                    <span class="font-bold text-indigo-600 dark:text-indigo-400">{{ $progress }}%</span>
                                </div>
                            </div>

                            {{-- Stats Grid --}}
                            <div class="grid gap-4 sm:grid-cols-3">
                                <div class="text-center p-4 rounded-2xl bg-gradient-to-br from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 border border-indigo-100 dark:border-indigo-800">
                                    <x-icon name="o-users" class="w-6 h-6 mx-auto text-indigo-600 dark:text-indigo-400 mb-2" />
                                    <p class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">{{ number_format($campaign->paid_donations_count ?? 0) }}</p>
                                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">{{ __('Donations') }}</p>
                                </div>
                                <div class="text-center p-4 rounded-2xl bg-gradient-to-br from-red-50 to-orange-50 dark:from-red-900/20 dark:to-orange-900/20 border border-red-100 dark:border-red-800">
                                    <x-icon name="o-arrow-trending-down" class="w-6 h-6 mx-auto text-red-600 dark:text-red-400 mb-2" />
                                    <p class="text-xl font-bold text-red-600 dark:text-red-400">৳{{ number_format($expense, 0) }}</p>
                                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">{{ __('Expenses') }}</p>
                                </div>
                                <div class="text-center p-4 rounded-2xl bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 border border-green-100 dark:border-green-800">
                                    <x-icon name="o-banknotes" class="w-6 h-6 mx-auto text-green-600 dark:text-green-400 mb-2" />
                                    <p class="text-xl font-bold text-green-600 dark:text-green-400">৳{{ number_format($balance, 0) }}</p>
                                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">{{ __('Balance') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Recent Donors --}}
                    <div class="rounded-3xl bg-white dark:bg-gray-800 p-6 sm:p-8 shadow-xl border border-gray-200 dark:border-gray-700">
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">{{ __('Recent Supporters') }}</h3>
                        <div class="grid gap-3 sm:grid-cols-2">
                            @forelse($donations as $donation)
                                <div class="flex items-center justify-between rounded-2xl bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 px-4 py-3 border border-indigo-100 dark:border-indigo-800">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-600 to-purple-600 flex items-center justify-center text-white font-bold">
                                            {{ substr($donation->donor_name, 0, 1) }}
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-gray-900 dark:text-white">{{ $donation->donor_name }}</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $donation->paid_at?->diffForHumans() }}</p>
                                        </div>
                                    </div>
                                    <p class="text-sm font-bold text-indigo-600 dark:text-indigo-400">৳{{ number_format($donation->amount, 0) }}</p>
                                </div>
                            @empty
                                <div class="sm:col-span-2 text-center py-8">
                                    <x-icon name="o-heart" class="w-12 h-12 mx-auto text-gray-400 dark:text-gray-600 mb-3" />
                                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('No donations yet. Be the first to support!') }}</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- Right Column - Sidebar --}}
                <div class="space-y-6">
                    {{-- Donate Card --}}
                    <div id="donate" class="sticky top-20 rounded-3xl bg-white dark:bg-gray-800 p-6 shadow-xl border border-gray-200 dark:border-gray-700">
                        <div class="text-center mb-6">
                            <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-indigo-600 to-purple-600 flex items-center justify-center mx-auto mb-4">
                                <x-icon name="o-heart" class="w-8 h-8 text-white" />
                            </div>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ __('Support This Campaign') }}</h3>
                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Your contribution helps us reach the goal faster.') }}</p>
                        </div>

                        <div class="space-y-3">
                            <button class="w-full btn bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white border-0 shadow-lg">
                                <x-icon name="o-heart" class="w-5 h-5" />
                                {{ __('Donate Now') }}
                            </button>
                            <button class="w-full btn btn-outline border-2 border-indigo-600 text-indigo-600 hover:bg-indigo-600 hover:text-white">
                                <x-icon name="o-share" class="w-5 h-5" />
                                {{ __('Share Campaign') }}
                            </button>
                        </div>

                        <div class="mt-6 p-4 rounded-2xl bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-100 dark:border-indigo-800">
                            <p class="text-xs text-indigo-700 dark:text-indigo-300 font-semibold mb-2">{{ __('Payment Methods') }}</p>
                            <div class="flex items-center gap-2 text-xs text-gray-600 dark:text-gray-400">
                                <x-icon name="o-check-circle" class="w-4 h-4 text-green-600" />
                                {{ __('bKash, Nagad, SSLCommerz') }}
                            </div>
                        </div>
                    </div>

                    {{-- Campaign Timeline --}}
                    <div class="rounded-3xl bg-white dark:bg-gray-800 p-6 shadow-xl border border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">{{ __('Campaign Timeline') }}</h3>
                        <div class="space-y-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                                    <x-icon name="o-calendar" class="w-5 h-5 text-green-600 dark:text-green-400" />
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Starts') }}</p>
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $campaign->starts_at?->format('M d, Y') ?? __('TBA') }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                                    <x-icon name="o-calendar" class="w-5 h-5 text-red-600 dark:text-red-400" />
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Ends') }}</p>
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $campaign->ends_at?->format('M d, Y') ?? __('TBA') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Mobile Sticky Footer --}}
    <div class="fixed bottom-0 left-0 right-0 z-40 md:hidden bg-white dark:bg-gray-900 border-t border-gray-200 dark:border-gray-800 p-4 shadow-lg">
        <div class="flex items-center justify-between gap-3">
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Goal') }}: ৳{{ number_format($goal, 0) }}</p>
                <p class="text-sm font-bold text-gray-900 dark:text-white">{{ $progress }}% {{ __('raised') }}</p>
            </div>
            <a href="#donate" class="btn btn-sm bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white border-0 shadow-lg">
                <x-icon name="o-heart" class="w-4 h-4" />
                {{ __('Donate') }}
            </a>
        </div>
    </div>
</div>
