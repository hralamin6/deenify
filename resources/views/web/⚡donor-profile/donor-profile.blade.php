<section class="relative overflow-hidden py-12 sm:py-20">
    <div class="absolute inset-0 -z-10">
        <div class="absolute top-0 right-0 h-96 w-96 rounded-full bg-indigo-300/30 dark:bg-indigo-600/20 blur-3xl"></div>
        <div class="absolute bottom-0 left-0 h-96 w-96 rounded-full bg-emerald-300/30 dark:bg-emerald-600/20 blur-3xl"></div>
    </div>

    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="rounded-3xl bg-white/90 dark:bg-gray-800/90 border border-gray-200 dark:border-gray-700 p-6 sm:p-8 shadow-2xl">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-center gap-4">
                    <img class="h-16 w-16 rounded-2xl object-cover ring-2 ring-indigo-200 dark:ring-indigo-900" src="{{ userImage($user) }}" alt="{{ $user->name }}" />
                    <div>
                        <div class="text-xs uppercase tracking-wider text-indigo-600 dark:text-indigo-400">{{ __('Donor Profile') }}</div>
                        <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $user->name }}</div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ $user->email }}</div>
                    </div>
                </div>
                <div class="flex flex-wrap gap-3">
                    <span class="badge badge-success badge-outline">{{ __('Active Supporter') }}</span>
                    <span class="badge badge-ghost">{{ __('Since :date', ['date' => $user->created_at?->format('M Y')]) }}</span>
                </div>
            </div>

            <div class="mt-8 grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-2xl bg-indigo-50/70 dark:bg-indigo-900/20 border border-indigo-100 dark:border-indigo-800 p-4">
                    <div class="text-xs text-indigo-600 dark:text-indigo-300 uppercase tracking-wider">{{ __('Total Donated') }}</div>
                    <div class="mt-2 text-2xl font-bold text-indigo-700 dark:text-indigo-300">
                        {{ $stats['currency'].' '.number_format($stats['total_donated'], 2) }}
                    </div>
                </div>
                <div class="rounded-2xl bg-emerald-50/70 dark:bg-emerald-900/20 border border-emerald-100 dark:border-emerald-800 p-4">
                    <div class="text-xs text-emerald-600 dark:text-emerald-300 uppercase tracking-wider">{{ __('Donations') }}</div>
                    <div class="mt-2 text-2xl font-bold text-emerald-700 dark:text-emerald-300">
                        {{ number_format($stats['donation_count']) }}
                    </div>
                </div>
                <div class="rounded-2xl bg-amber-50/70 dark:bg-amber-900/20 border border-amber-100 dark:border-amber-800 p-4">
                    <div class="text-xs text-amber-600 dark:text-amber-300 uppercase tracking-wider">{{ __('Average Donation') }}</div>
                    <div class="mt-2 text-2xl font-bold text-amber-700 dark:text-amber-300">
                        {{ $stats['currency'].' '.number_format($stats['avg_donation'] ?? 0, 2) }}
                    </div>
                </div>
                <div class="rounded-2xl bg-sky-50/70 dark:bg-sky-900/20 border border-sky-100 dark:border-sky-800 p-4">
                    <div class="text-xs text-sky-600 dark:text-sky-300 uppercase tracking-wider">{{ __('Campaigns Supported') }}</div>
                    <div class="mt-2 text-2xl font-bold text-sky-700 dark:text-sky-300">
                        {{ number_format($stats['campaigns_supported']) }}
                    </div>
                </div>
            </div>

            <div class="mt-6 grid grid-cols-1 gap-5 lg:grid-cols-3">
                <div class="lg:col-span-2 rounded-3xl bg-white/80 dark:bg-gray-900/40 border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('Giving Trend (6 months)') }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ __('Monthly donation totals') }}</div>
                        </div>
                        <span class="text-xs text-gray-400">{{ __('Recent') }}</span>
                    </div>
                    <div class="mt-5 space-y-3">
                        @foreach($monthlyTrend as $row)
                            <div>
                                <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                                    <span class="font-semibold text-gray-700 dark:text-gray-200">{{ $row['label'] }}</span>
                                    <span>{{ $stats['currency'].' '.number_format($row['total'], 2) }}</span>
                                </div>
                                <progress max="100" value="{{ $row['pct'] }}" class="progress progress-primary h-2 w-full mt-2"></progress>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="rounded-3xl bg-gradient-to-br from-indigo-600 to-purple-700 text-white p-6">
                    <h3 class="text-lg font-semibold">{{ __('Donor Snapshot') }}</h3>
                    <div class="mt-4 space-y-3 text-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-indigo-100">{{ __('Receipts Issued') }}</span>
                            <span class="font-semibold">{{ number_format($stats['receipts_issued']) }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-indigo-100">{{ __('Active Recurring') }}</span>
                            <span class="font-semibold">{{ number_format($stats['active_recurring']) }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-indigo-100">{{ __('First Donation') }}</span>
                            <span class="font-semibold">
                                {{ $stats['first_donation_at'] ? \Carbon\Carbon::parse($stats['first_donation_at'])->format('M d, Y') : '—' }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-indigo-100">{{ __('Last Donation') }}</span>
                            <span class="font-semibold">
                                {{ $stats['last_donation_at'] ? \Carbon\Carbon::parse($stats['last_donation_at'])->format('M d, Y') : '—' }}
                            </span>
                        </div>
                    </div>
                    <a href="{{ route('web.campaigns') }}" wire:navigate class="mt-6 btn w-full bg-white/90 text-indigo-700 hover:bg-white">
                        {{ __('Support a Campaign') }}
                        <x-icon name="o-arrow-right" class="w-4 h-4" />
                    </a>
                </div>
            </div>
        </div>

        <div class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2 rounded-3xl bg-white/90 dark:bg-gray-800/90 border border-gray-200 dark:border-gray-700 p-6 shadow-2xl">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Recent Donations') }}</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Latest paid donations') }}</p>
                    </div>
                    <x-icon name="o-receipt-refund" class="w-6 h-6 text-indigo-400" />
                </div>

                <div class="mt-6 space-y-4">
                    @forelse($recentDonations as $donation)
                        <div class="flex flex-col gap-3 rounded-2xl border border-gray-100 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-900/40 px-4 py-4 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <div class="text-sm font-semibold text-gray-900 dark:text-white">
                                    {{ $donation->campaign?->title ?? __('General Donation') }}
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $donation->paid_at?->format('M d, Y') ?? '—' }}
                                </div>
                                @if($donation->receipt)
                                    <span class="mt-2 inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-600 dark:text-emerald-400">
                                        <x-icon name="o-check-circle" class="w-3 h-3" />
                                        {{ __('Receipt issued') }}
                                    </span>
                                @endif
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="text-sm font-semibold text-indigo-600 dark:text-indigo-400">
                                    {{ $stats['currency'].' '.number_format((float) $donation->amount, 2) }}
                                </div>
                                @if($donation->campaign)
                                    <a href="{{ route('web.campaign', $donation->campaign->slug) }}" wire:navigate class="text-xs font-semibold text-gray-600 dark:text-gray-300 hover:text-indigo-600">
                                        {{ __('View campaign') }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('No donations yet.') }}</div>
                    @endforelse
                </div>
            </div>

            <div class="rounded-3xl bg-white/90 dark:bg-gray-800/90 border border-gray-200 dark:border-gray-700 p-6 shadow-2xl">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('About Donor') }}</h2>
                <div class="mt-4 space-y-3 text-sm text-gray-600 dark:text-gray-300">
                    @if($detail?->bio)
                        <p>{{ $detail->bio }}</p>
                    @endif
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span>{{ __('Phone') }}</span>
                            <span class="font-semibold">{{ $detail?->phone ?? '—' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span>{{ __('Occupation') }}</span>
                            <span class="font-semibold">{{ $detail?->occupation ?? '—' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span>{{ __('Location') }}</span>
                            <span class="font-semibold">
                                {{ collect([$detail?->union?->name, $detail?->upazila?->name, $detail?->district?->name, $detail?->division?->name])->filter()->join(', ') ?: '—' }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="mt-6">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('Social Links') }}</h3>
                    <div class="mt-3 flex flex-wrap gap-2 text-xs">
                        @foreach(['website' => 'Website', 'facebook' => 'Facebook', 'twitter' => 'Twitter', 'instagram' => 'Instagram', 'linkedin' => 'LinkedIn', 'youtube' => 'YouTube', 'github' => 'GitHub'] as $field => $label)
                            @if(! empty($detail?->$field))
                                <a href="{{ $detail->$field }}" target="_blank" rel="noopener" class="rounded-full border border-gray-200 dark:border-gray-700 px-3 py-1 text-gray-600 dark:text-gray-300 hover:border-indigo-300 hover:text-indigo-600">
                                    {{ __($label) }}
                                </a>
                            @endif
                        @endforeach
                        @if(! $detail)
                            <span class="text-xs text-gray-400">{{ __('No profile details yet.') }}</span>
                        @endif
                    </div>
                </div>

                <div class="mt-6">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('Recurring Plans') }}</h3>
                    <div class="mt-3 space-y-2 text-xs">
                        @forelse($recurringPlans as $plan)
                            <div class="flex items-center justify-between rounded-xl border border-gray-100 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-900/40 px-3 py-2">
                                <span class="font-medium text-gray-700 dark:text-gray-200">
                                    {{ $plan->campaign?->title ?? __('General') }}
                                </span>
                                <span class="text-indigo-600 dark:text-indigo-400">
                                    {{ $stats['currency'].' '.number_format((float) $plan->amount, 2) }}
                                </span>
                            </div>
                        @empty
                            <div class="text-xs text-gray-400">{{ __('No recurring plans yet.') }}</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
