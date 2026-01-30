<section class="relative overflow-hidden py-12 sm:py-20">
    <div class="absolute inset-0 -z-10">
        <div class="absolute top-0 right-0 h-96 w-96 rounded-full bg-sky-300/30 dark:bg-sky-600/20 blur-3xl"></div>
        <div class="absolute bottom-0 left-0 h-96 w-96 rounded-full bg-indigo-300/30 dark:bg-indigo-600/20 blur-3xl"></div>
    </div>

    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-sky-100 dark:bg-sky-900/30 border border-sky-200 dark:border-sky-800">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-sky-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-sky-500"></span>
                </span>
                <span class="text-xs font-semibold text-sky-700 dark:text-sky-300 uppercase tracking-wider">{{ __('Trust & Safety') }}</span>
            </div>
            <h1 class="mt-6 text-4xl sm:text-5xl font-bold text-gray-900 dark:text-white">
                {{ __('Trust & Safety') }}
            </h1>
            <p class="mt-4 text-lg text-gray-600 dark:text-gray-300 max-w-3xl mx-auto">
                {{ __('We prioritize secure payments, transparent verification, and reliable operations so donors feel confident.') }}
            </p>
        </div>

        <div class="mt-10 grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="rounded-3xl bg-white/90 dark:bg-gray-800/90 border border-gray-200 dark:border-gray-700 p-6 shadow-2xl">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Payment Success Rate') }}</div>
                        <div class="mt-2 text-3xl font-bold text-emerald-600 dark:text-emerald-400">
                            {{ $stats['payment_success_rate'] }}%
                        </div>
                    </div>
                    <x-icon name="o-shield-check" class="w-10 h-10 text-emerald-500/40" />
                </div>
                <div class="mt-4 text-xs text-gray-500 dark:text-gray-400">
                    {{ __('Last 30 days') }} · {{ __(':count attempts', ['count' => number_format($stats['payment_attempts_total'])]) }}
                </div>
                <progress max="100" value="{{ $stats['payment_success_rate'] }}" class="progress progress-success h-2 w-full mt-3"></progress>
            </div>

            <div class="rounded-3xl bg-white/90 dark:bg-gray-800/90 border border-gray-200 dark:border-gray-700 p-6 shadow-2xl">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Manual Verification') }}</div>
                        <div class="mt-2 text-3xl font-bold text-indigo-600 dark:text-indigo-400">
                            {{ $stats['manual_success_rate'] }}%
                        </div>
                    </div>
                    <x-icon name="o-clipboard-document-check" class="w-10 h-10 text-indigo-500/40" />
                </div>
                <div class="mt-4 grid grid-cols-3 gap-2 text-xs">
                    <div class="rounded-xl bg-emerald-50 dark:bg-emerald-900/20 px-3 py-2 text-center">
                        <div class="font-semibold text-emerald-600 dark:text-emerald-300">{{ number_format($stats['manual_verified']) }}</div>
                        <div class="text-emerald-600/70 dark:text-emerald-300/70">{{ __('Verified') }}</div>
                    </div>
                    <div class="rounded-xl bg-amber-50 dark:bg-amber-900/20 px-3 py-2 text-center">
                        <div class="font-semibold text-amber-600 dark:text-amber-300">{{ number_format($stats['manual_pending']) }}</div>
                        <div class="text-amber-600/70 dark:text-amber-300/70">{{ __('Pending') }}</div>
                    </div>
                    <div class="rounded-xl bg-rose-50 dark:bg-rose-900/20 px-3 py-2 text-center">
                        <div class="font-semibold text-rose-600 dark:text-rose-300">{{ number_format($stats['manual_rejected']) }}</div>
                        <div class="text-rose-600/70 dark:text-rose-300/70">{{ __('Rejected') }}</div>
                    </div>
                </div>
            </div>

            <div class="rounded-3xl bg-white/90 dark:bg-gray-800/90 border border-gray-200 dark:border-gray-700 p-6 shadow-2xl">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Operational Reliability') }}</div>
                        <div class="mt-2 text-lg font-semibold text-gray-900 dark:text-white">
                            {{ $stats['last_backup_at'] ? \Carbon\Carbon::parse($stats['last_backup_at'])->diffForHumans() : __('No backups yet') }}
                        </div>
                    </div>
                    <x-icon name="o-server" class="w-10 h-10 text-sky-500/40" />
                </div>
                <div class="mt-4 text-xs text-gray-500 dark:text-gray-400">
                    {{ __('Last successful backup') }}
                </div>
                <div class="mt-4 rounded-2xl bg-sky-50 dark:bg-sky-900/20 px-4 py-3 text-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-sky-700 dark:text-sky-300">{{ __('System integrity') }}</span>
                        <span class="font-semibold text-sky-700 dark:text-sky-300">{{ __('Verified') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-10 grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2 rounded-3xl bg-white/90 dark:bg-gray-800/90 border border-gray-200 dark:border-gray-700 p-6 shadow-2xl">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Notification Delivery Health') }}</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('How reliably we keep donors informed') }}</p>
                    </div>
                    <x-icon name="o-bell" class="w-6 h-6 text-gray-400" />
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div class="rounded-2xl bg-gray-50 dark:bg-gray-900/30 p-4">
                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ __('Sent') }}</div>
                        <div class="mt-2 text-xl font-semibold text-gray-900 dark:text-white">
                            {{ number_format($stats['notifications_total']) }}
                        </div>
                    </div>
                    <div class="rounded-2xl bg-gray-50 dark:bg-gray-900/30 p-4">
                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ __('Unread') }}</div>
                        <div class="mt-2 text-xl font-semibold text-amber-600 dark:text-amber-400">
                            {{ number_format($stats['notifications_unread']) }}
                        </div>
                    </div>
                    <div class="rounded-2xl bg-gray-50 dark:bg-gray-900/30 p-4">
                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ __('Read Rate') }}</div>
                        <div class="mt-2 text-xl font-semibold text-emerald-600 dark:text-emerald-400">
                            {{ $stats['notification_read_rate'] }}%
                        </div>
                    </div>
                </div>
                <progress max="100" value="{{ $stats['notification_read_rate'] }}" class="progress progress-success h-2 w-full mt-4"></progress>
            </div>

            <div class="rounded-3xl bg-gradient-to-br from-indigo-600 to-purple-700 text-white p-6 shadow-2xl">
                <h3 class="text-lg font-semibold">{{ __('Supported Gateways') }}</h3>
                <p class="mt-1 text-sm text-indigo-100">{{ __('Secure, monitored payment partners') }}</p>
                <div class="mt-5 flex flex-wrap gap-2">
                    @foreach($gateways as $gateway)
                        <span class="rounded-full bg-white/15 px-3 py-1 text-xs font-semibold uppercase tracking-wider">
                            {{ $gateway }}
                        </span>
                    @endforeach
                </div>
                <a href="{{ route('web.campaigns') }}" wire:navigate class="mt-6 btn w-full bg-white/90 text-indigo-700 hover:bg-white">
                    {{ __('Explore Campaigns') }}
                    <x-icon name="o-arrow-right" class="w-4 h-4" />
                </a>
            </div>
        </div>
    </div>
</section>
