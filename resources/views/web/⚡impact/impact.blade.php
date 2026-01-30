<section class="relative overflow-hidden py-12 sm:py-20">
    <div class="absolute inset-0 -z-10">
        <div class="absolute top-0 right-0 h-96 w-96 rounded-full bg-indigo-300/30 dark:bg-indigo-600/20 blur-3xl"></div>
        <div class="absolute bottom-0 left-0 h-96 w-96 rounded-full bg-emerald-300/30 dark:bg-emerald-600/20 blur-3xl"></div>
    </div>

    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-emerald-100 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                </span>
                <span class="text-xs font-semibold text-emerald-700 dark:text-emerald-300 uppercase tracking-wider">{{ __('Transparency') }}</span>
            </div>
            <h1 class="mt-6 text-4xl sm:text-5xl font-bold text-gray-900 dark:text-white">
                {{ __('Impact Dashboard') }}
            </h1>
            <p class="mt-4 text-lg text-gray-600 dark:text-gray-300 max-w-3xl mx-auto">
                {{ __('See how donations are raised, spent, and verified. Every number is tied to real activity in the system.') }}
            </p>
        </div>

        <div class="mt-10 grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl bg-white/90 dark:bg-gray-800/90 border border-gray-200 dark:border-gray-700 p-5 shadow-lg">
                <div class="text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Total Raised') }}</div>
                <div class="mt-3 text-3xl font-bold text-indigo-600 dark:text-indigo-400">
                    {{ $stats['currency'].' '.number_format($stats['total_raised'], 2) }}
                </div>
                <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                    {{ __('Lifetime paid donations') }}
                </div>
            </div>

            <div class="rounded-2xl bg-white/90 dark:bg-gray-800/90 border border-gray-200 dark:border-gray-700 p-5 shadow-lg">
                <div class="text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Net Impact') }}</div>
                <div class="mt-3 text-3xl font-bold {{ $stats['net_impact'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                    {{ $stats['currency'].' '.number_format($stats['net_impact'], 2) }}
                </div>
                <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                    {{ __('Donations minus expenses') }}
                </div>
            </div>

            <div class="rounded-2xl bg-white/90 dark:bg-gray-800/90 border border-gray-200 dark:border-gray-700 p-5 shadow-lg">
                <div class="text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Receipts Issued') }}</div>
                <div class="mt-3 text-3xl font-bold text-amber-600 dark:text-amber-400">
                    {{ number_format($stats['receipts_count']) }}
                </div>
                <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                    {{ __('Verified donation receipts') }}
                </div>
            </div>

            <div class="rounded-2xl bg-white/90 dark:bg-gray-800/90 border border-gray-200 dark:border-gray-700 p-5 shadow-lg">
                <div class="text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Expense Ratio') }}</div>
                <div class="mt-3 text-3xl font-bold text-sky-600 dark:text-sky-400">
                    {{ $stats['expense_ratio'] }}%
                </div>
                <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                    {{ __('This month spending vs donations') }}
                </div>
            </div>
        </div>

        <div class="mt-10 grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2 rounded-3xl bg-white/90 dark:bg-gray-800/90 border border-gray-200 dark:border-gray-700 p-6 shadow-2xl">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Monthly Trend (Last 6 Months)') }}</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Donations vs expenses over time') }}</p>
                    </div>
                    <div class="flex items-center gap-3 text-xs">
                        <span class="inline-flex items-center gap-2">
                            <span class="h-2 w-2 rounded-full bg-indigo-600"></span>
                            {{ __('Donations') }}
                        </span>
                        <span class="inline-flex items-center gap-2">
                            <span class="h-2 w-2 rounded-full bg-amber-500"></span>
                            {{ __('Expenses') }}
                        </span>
                    </div>
                </div>

                <div class="mt-6 space-y-4">
                    @foreach($monthlyTrend as $row)
                        <div>
                            <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                                <span class="font-semibold text-gray-700 dark:text-gray-200">{{ $row['label'] }}</span>
                                <span>
                                    {{ $stats['currency'].' '.number_format($row['donations'], 2) }}
                                    ·
                                    {{ $stats['currency'].' '.number_format($row['expenses'], 2) }}
                                </span>
                            </div>
                            <div class="mt-2 grid gap-2">
                                <progress max="100" value="{{ $row['donation_pct'] }}" class="progress progress-primary h-2 w-full"></progress>
                                <progress max="100" value="{{ $row['expense_pct'] }}" class="progress progress-warning h-2 w-full"></progress>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="rounded-3xl bg-gradient-to-br from-indigo-600 to-purple-700 text-white p-6 shadow-2xl">
                <h3 class="text-lg font-semibold">{{ __('This Month at a Glance') }}</h3>
                <p class="mt-1 text-sm text-indigo-100">{{ __('Live totals for the current month') }}</p>

                <div class="mt-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm">{{ __('Donations') }}</span>
                        <span class="text-base font-semibold">{{ $stats['currency'].' '.number_format($stats['month_donations'], 2) }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm">{{ __('Expenses') }}</span>
                        <span class="text-base font-semibold">{{ $stats['currency'].' '.number_format($stats['month_expenses'], 2) }}</span>
                    </div>
                    <div class="h-px bg-white/20"></div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm">{{ __('Net Impact') }}</span>
                        <span class="text-base font-semibold">{{ $stats['currency'].' '.number_format($stats['month_donations'] - $stats['month_expenses'], 2) }}</span>
                    </div>
                </div>

                <a href="{{ route('web.campaigns') }}" wire:navigate class="mt-6 btn w-full bg-white/90 text-indigo-700 hover:bg-white">
                    {{ __('Explore Campaigns') }}
                    <x-icon name="o-arrow-right" class="w-4 h-4" />
                </a>
            </div>
        </div>
    </div>
</section>
