<section class="relative overflow-hidden py-12 sm:py-20">
    <div class="absolute inset-0 -z-10">
        <div class="absolute top-0 right-0 h-96 w-96 rounded-full bg-emerald-300/30 dark:bg-emerald-600/20 blur-3xl"></div>
        <div class="absolute bottom-0 left-0 h-96 w-96 rounded-full bg-teal-300/30 dark:bg-teal-600/20 blur-3xl"></div>
    </div>

    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-emerald-100 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                </span>
                <span class="text-xs font-semibold text-emerald-700 dark:text-emerald-300 uppercase tracking-wider">{{ __('Give Monthly') }}</span>
            </div>
            <h1 class="mt-6 text-4xl sm:text-5xl font-bold text-gray-900 dark:text-white">
                {{ __('Give Monthly') }}
            </h1>
            <p class="mt-4 text-lg text-gray-600 dark:text-gray-300 max-w-3xl mx-auto">
                {{ __('Recurring support keeps campaigns stable and predictable. Your monthly impact compounds over time.') }}
            </p>
        </div>

        <div class="mt-10 grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl bg-white/90 dark:bg-gray-800/90 border border-gray-200 dark:border-gray-700 p-5 shadow-lg">
                <div class="text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Active Supporters') }}</div>
                <div class="mt-3 text-3xl font-bold text-emerald-600 dark:text-emerald-400">
                    {{ number_format($stats['active_count']) }}
                </div>
            </div>
            <div class="rounded-2xl bg-white/90 dark:bg-gray-800/90 border border-gray-200 dark:border-gray-700 p-5 shadow-lg">
                <div class="text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Monthly Impact (MRR)') }}</div>
                <div class="mt-3 text-3xl font-bold text-teal-600 dark:text-teal-400">
                    {{ $stats['currency'].' '.number_format($stats['mrr'] ?? 0, 2) }}
                </div>
            </div>
            <div class="rounded-2xl bg-white/90 dark:bg-gray-800/90 border border-gray-200 dark:border-gray-700 p-5 shadow-lg">
                <div class="text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Next 7 Days Forecast') }}</div>
                <div class="mt-3 text-3xl font-bold text-indigo-600 dark:text-indigo-400">
                    {{ $stats['currency'].' '.number_format($stats['next_seven_days'] ?? 0, 2) }}
                </div>
                <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                    {{ __('Expected recurring impact') }}
                </div>
            </div>
            <div class="rounded-2xl bg-white/90 dark:bg-gray-800/90 border border-gray-200 dark:border-gray-700 p-5 shadow-lg">
                <div class="text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Pause/Cancel Transparency') }}</div>
                <div class="mt-3 text-sm font-semibold text-gray-700 dark:text-gray-200">
                    {{ __('You can pause or cancel anytime.') }}
                </div>
                <div class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                    {{ __('Paused: :paused · Cancelled: :cancelled', ['paused' => number_format($stats['paused_count']), 'cancelled' => number_format($stats['cancelled_count'])]) }}
                </div>
            </div>
        </div>

        <div class="mt-10 grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2 rounded-3xl bg-white/90 dark:bg-gray-800/90 border border-gray-200 dark:border-gray-700 p-6 shadow-2xl">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Why monthly giving matters') }}</h2>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                    {{ __('Predictable support helps campaigns plan expenses, respond quickly, and serve communities consistently.') }}
                </p>
                <div class="mt-6 grid gap-4 sm:grid-cols-2">
                    <div class="rounded-2xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-100 dark:border-emerald-800 p-4">
                        <div class="text-sm font-semibold text-emerald-700 dark:text-emerald-300">{{ __('Stable Funding') }}</div>
                        <p class="mt-2 text-xs text-emerald-700/70 dark:text-emerald-200/70">
                            {{ __('Campaigns can budget better with reliable monthly commitments.') }}
                        </p>
                    </div>
                    <div class="rounded-2xl bg-teal-50 dark:bg-teal-900/20 border border-teal-100 dark:border-teal-800 p-4">
                        <div class="text-sm font-semibold text-teal-700 dark:text-teal-300">{{ __('Lower Fees Over Time') }}</div>
                        <p class="mt-2 text-xs text-teal-700/70 dark:text-teal-200/70">
                            {{ __('Fewer payment interruptions mean better efficiency.') }}
                        </p>
                    </div>
                    <div class="rounded-2xl bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-100 dark:border-indigo-800 p-4">
                        <div class="text-sm font-semibold text-indigo-700 dark:text-indigo-300">{{ __('Automatic Receipts') }}</div>
                        <p class="mt-2 text-xs text-indigo-700/70 dark:text-indigo-200/70">
                            {{ __('Get receipt history with every monthly payment.') }}
                        </p>
                    </div>
                    <div class="rounded-2xl bg-sky-50 dark:bg-sky-900/20 border border-sky-100 dark:border-sky-800 p-4">
                        <div class="text-sm font-semibold text-sky-700 dark:text-sky-300">{{ __('Full Control') }}</div>
                        <p class="mt-2 text-xs text-sky-700/70 dark:text-sky-200/70">
                            {{ __('Pause or cancel anytime, no hidden conditions.') }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="rounded-3xl bg-gradient-to-br from-emerald-600 to-teal-700 text-white p-6 shadow-2xl">
                <h3 class="text-lg font-semibold">{{ __('Start Monthly Giving') }}</h3>
                <p class="mt-2 text-sm text-emerald-100">
                    {{ __('Choose a campaign and make your support automatic.') }}
                </p>
                <a href="{{ route('web.campaigns') }}" wire:navigate class="mt-6 btn w-full bg-white/90 text-emerald-700 hover:bg-white">
                    {{ __('Choose a Campaign') }}
                    <x-icon name="o-arrow-right" class="w-4 h-4" />
                </a>
                <div class="mt-6 text-xs text-emerald-100">
                    {{ __('You can pause or cancel anytime from your account.') }}
                </div>
            </div>
        </div>
    </div>
</section>
