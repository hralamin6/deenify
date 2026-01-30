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
                <span class="text-xs font-semibold text-amber-700 dark:text-amber-300 uppercase tracking-wider">{{ __('Spending') }}</span>
            </div>
            <h1 class="mt-6 text-4xl sm:text-5xl font-bold text-gray-900 dark:text-white">
                {{ __('Spending Overview') }}
            </h1>
            <p class="mt-4 text-lg text-gray-600 dark:text-gray-300 max-w-3xl mx-auto">
                {{ __('Track how funds are allocated across categories and campaigns with clear accountability.') }}
            </p>
        </div>

        <div class="mt-10 grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl bg-white/90 dark:bg-gray-800/90 border border-gray-200 dark:border-gray-700 p-5 shadow-lg">
                <div class="text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Monthly Expenses') }}</div>
                <div class="mt-3 text-3xl font-bold text-amber-600 dark:text-amber-400">
                    {{ $stats['currency'].' '.number_format($stats['monthly_expenses'], 2) }}
                </div>
                <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">{{ __('Current month total') }}</div>
            </div>
            <div class="rounded-2xl bg-white/90 dark:bg-gray-800/90 border border-gray-200 dark:border-gray-700 p-5 shadow-lg">
                <div class="text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Uncategorized') }}</div>
                <div class="mt-3 text-3xl font-bold text-rose-600 dark:text-rose-400">
                    {{ number_format($stats['uncategorized_expenses']) }}
                </div>
                <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">{{ __('Items without category') }}</div>
            </div>
            <div class="rounded-2xl bg-gradient-to-br from-amber-600 to-orange-600 text-white p-5 shadow-lg sm:col-span-2">
                <div class="text-xs uppercase tracking-wider text-amber-100">{{ __('Accountability') }}</div>
                <div class="mt-3 text-xl font-semibold">
                    {{ __('Every expense is linked to a campaign or category.') }}
                </div>
                <div class="mt-3 text-sm text-amber-100">
                    {{ __('Uncategorized items are flagged for quick review and cleanup.') }}
                </div>
            </div>
        </div>

        <div class="mt-10 rounded-3xl bg-white/90 dark:bg-gray-800/90 border border-gray-200 dark:border-gray-700 p-6 shadow-2xl">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Monthly Expense History') }}</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Switch months to view totals') }}</p>
                </div>
                <div class="w-full sm:w-56">
                    <label class="text-xs text-gray-500 dark:text-gray-400">{{ __('Month') }}</label>
                    <select wire:model.live="selectedMonth" class="mt-1 w-full select select-bordered">
                        @foreach($monthOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mt-6 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($monthlySeries as $row)
                    <div class="rounded-2xl border border-gray-100 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-900/40 px-4 py-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $row['label'] }}</span>
                            <span class="text-sm font-semibold text-amber-600 dark:text-amber-400">
                                {{ $stats['currency'].' '.number_format($row['total'], 2) }}
                            </span>
                        </div>
                        @if($row['key'] === $selectedMonth)
                            <div class="mt-2 text-xs text-amber-600 dark:text-amber-400">{{ __('Selected') }}</div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        <div class="mt-10 rounded-3xl bg-white/90 dark:bg-gray-800/90 border border-gray-200 dark:border-gray-700 p-6 shadow-2xl">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Expense List') }}</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ __('All expenses for the selected month') }}
                    </p>
                </div>
                <div class="text-xs text-gray-500 dark:text-gray-400">
                    {{ $monthOptions[$selectedMonth] ?? '' }}
                </div>
            </div>

            <div class="mt-6 overflow-hidden rounded-2xl border border-gray-100 dark:border-gray-700">
                <div class="overflow-x-auto">
                    <table class="table w-full">
                        <thead class="bg-gray-50 dark:bg-gray-900/40 text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            <tr>
                                <th class="px-4 py-3">{{ __('Date') }}</th>
                                <th class="px-4 py-3">{{ __('Category') }}</th>
                                <th class="px-4 py-3">{{ __('Campaign') }}</th>
                                <th class="px-4 py-3">{{ __('Description') }}</th>
                                <th class="px-4 py-3 text-right">{{ __('Amount') }}</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm">
                            @forelse($monthlyExpensesList as $expense)
                                @php
                                    $expenseDate = $expense->spent_at ?? $expense->created_at;
                                @endphp
                                <tr class="border-t border-gray-100 dark:border-gray-700">
                                    <td class="px-4 py-3 text-gray-600 dark:text-gray-300">
                                        {{ optional($expenseDate)->format('M d, Y') ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="badge badge-ghost badge-sm">
                                            {{ $expense->category_name ?? __('Uncategorized') }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-gray-700 dark:text-gray-200">
                                        {{ $expense->campaign_title ?? __('General') }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-500 dark:text-gray-400">
                                        {{ $expense->description ?: '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-right font-semibold text-amber-600 dark:text-amber-400">
                                        {{ $stats['currency'].' '.number_format((float) $expense->amount, 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">
                                        {{ __('No expenses found for this month.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="mt-10 grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2 rounded-3xl bg-white/90 dark:bg-gray-800/90 border border-gray-200 dark:border-gray-700 p-6 shadow-2xl">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Top Spending Categories') }}</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Highest allocation categories') }}</p>
                    </div>
                    <x-icon name="o-chart-pie" class="w-6 h-6 text-amber-400" />
                </div>
                <div class="mt-6 space-y-3">
                    @forelse($topCategories as $category)
                        <div class="flex items-center justify-between rounded-2xl border border-gray-100 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-900/40 px-4 py-3">
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $category->name }}</span>
                            <span class="text-sm font-semibold text-amber-600 dark:text-amber-400">
                                {{ $stats['currency'].' '.number_format((float) $category->total, 2) }}
                            </span>
                        </div>
                    @empty
                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('No categorized expenses yet.') }}</div>
                    @endforelse
                </div>
            </div>

            <div class="rounded-3xl bg-white/90 dark:bg-gray-800/90 border border-gray-200 dark:border-gray-700 p-6 shadow-2xl">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Top Spending Campaigns') }}</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Where funds are used most') }}</p>
                    </div>
                    <x-icon name="o-flag" class="w-6 h-6 text-rose-400" />
                </div>
                <div class="mt-6 space-y-3">
                    @forelse($topCampaigns as $campaign)
                        <div class="flex items-center justify-between rounded-2xl border border-gray-100 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-900/40 px-4 py-3">
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $campaign->title }}</span>
                            <span class="text-sm font-semibold text-rose-600 dark:text-rose-400">
                                {{ $stats['currency'].' '.number_format((float) ($campaign->expenses_sum_amount ?? 0), 2) }}
                            </span>
                        </div>
                    @empty
                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('No expenses yet.') }}</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</section>
