<section class="relative overflow-hidden py-12 sm:py-20">
    <div class="absolute inset-0 -z-10">
        <div class="absolute top-0 right-0 h-96 w-96 rounded-full bg-amber-300/30 dark:bg-amber-600/20 blur-3xl"></div>
        <div class="absolute bottom-0 left-0 h-96 w-96 rounded-full bg-rose-300/30 dark:bg-rose-600/20 blur-3xl"></div>
    </div>

    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-amber-100 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-800">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
                </span>
                <span class="text-xs font-semibold text-amber-700 dark:text-amber-300 uppercase tracking-wider">{{ __('Community') }}</span>
            </div>
            <h1 class="mt-6 text-4xl sm:text-5xl font-bold text-gray-900 dark:text-white">
                {{ __('Donors') }}
            </h1>
            <p class="mt-4 text-lg text-gray-600 dark:text-gray-300 max-w-3xl mx-auto">
                {{ __('Meet the community powering every campaign. Top supporters are shown with privacy-first display.') }}
            </p>
        </div>

        <div class="mt-10 grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl bg-white/90 dark:bg-gray-800/90 border border-gray-200 dark:border-gray-700 p-5 shadow-lg">
                <div class="text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Total Donors') }}</div>
                <div class="mt-3 text-3xl font-bold text-amber-600 dark:text-amber-400">
                    {{ number_format($stats['total_donors']) }}
                </div>
            </div>
            <div class="rounded-2xl bg-white/90 dark:bg-gray-800/90 border border-gray-200 dark:border-gray-700 p-5 shadow-lg">
                <div class="text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('New This Month') }}</div>
                <div class="mt-3 text-3xl font-bold text-emerald-600 dark:text-emerald-400">
                    {{ number_format($stats['new_donors_month']) }}
                </div>
            </div>
            <div class="rounded-2xl bg-white/90 dark:bg-gray-800/90 border border-gray-200 dark:border-gray-700 p-5 shadow-lg">
                <div class="text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Repeat Donor Rate') }}</div>
                <div class="mt-3 text-3xl font-bold text-indigo-600 dark:text-indigo-400">
                    {{ $stats['repeat_rate'] }}%
                </div>
            </div>
            <div class="rounded-2xl bg-white/90 dark:bg-gray-800/90 border border-gray-200 dark:border-gray-700 p-5 shadow-lg">
                <div class="text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Average Donation') }}</div>
                <div class="mt-3 text-3xl font-bold text-sky-600 dark:text-sky-400">
                    {{ $stats['currency'].' '.number_format($stats['avg_donation'] ?? 0, 2) }}
                </div>
            </div>
        </div>

        <div class="mt-10 grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2 rounded-3xl bg-white/90 dark:bg-gray-800/90 border border-gray-200 dark:border-gray-700 p-6 shadow-2xl">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Top Donors') }}</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Opt-in / anonymized display') }}</p>
                    </div>
                    <x-icon name="o-trophy" class="w-6 h-6 text-amber-400" />
                </div>
                <div class="mt-6 space-y-4">
                    @forelse($topDonors as $index => $donor)
                        <div class="flex items-center justify-between rounded-2xl border border-gray-100 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-900/40 px-4 py-3">
                            <div class="flex items-center gap-3">
                                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-amber-100 text-amber-700 text-xs font-bold">
                                    {{ $index + 1 }}
                                </span>
                                <div>
                                    @if(! empty($donor['user_id']))
                                        <a href="{{ route('web.donor-profile', $donor['user_id']) }}" wire:navigate class="text-sm font-semibold text-gray-900 dark:text-white hover:text-amber-600">
                                            {{ $donor['label'] }}
                                        </a>
                                    @else
                                        <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ $donor['label'] }}</div>
                                    @endif
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ __(':count donations', ['count' => number_format($donor['count'])]) }}
                                    </div>
                                </div>
                            </div>
                            <div class="text-sm font-semibold text-emerald-600 dark:text-emerald-400">
                                {{ $stats['currency'].' '.number_format($donor['total'], 2) }}
                            </div>
                        </div>
                    @empty
                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('No donors yet.') }}</div>
                    @endforelse
                </div>
            </div>

            <div class="rounded-3xl bg-gradient-to-br from-amber-600 to-rose-600 text-white p-6 shadow-2xl">
                <h3 class="text-lg font-semibold">{{ __('Why donors return') }}</h3>
                <p class="mt-2 text-sm text-amber-100">
                    {{ __('We show where every taka goes, provide receipts, and keep donors updated with real progress.') }}
                </p>
                <ul class="mt-5 space-y-3 text-sm">
                    <li class="flex items-start gap-2">
                        <x-icon name="o-check-circle" class="w-4 h-4 mt-0.5" />
                        {{ __('Instant receipts after verification') }}
                    </li>
                    <li class="flex items-start gap-2">
                        <x-icon name="o-check-circle" class="w-4 h-4 mt-0.5" />
                        {{ __('Transparent spending updates') }}
                    </li>
                    <li class="flex items-start gap-2">
                        <x-icon name="o-check-circle" class="w-4 h-4 mt-0.5" />
                        {{ __('Secure gateways with tracking') }}
                    </li>
                </ul>
                <a href="{{ route('web.campaigns') }}" wire:navigate class="mt-6 btn w-full bg-white/90 text-amber-700 hover:bg-white">
                    {{ __('Support a Campaign') }}
                    <x-icon name="o-arrow-right" class="w-4 h-4" />
                </a>
            </div>
        </div>
    </div>
</section>
