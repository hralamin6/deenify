@section('title', $campaign->title)
@section('image', getImage($campaign, 'cover', 'thumb'))
@section('description', Str::limit(strip_tags($campaign->description), 333))
@php
        $hero = $campaign->getFirstMediaUrl('postImages', 'avatar');
        $words = str_word_count(strip_tags($campaign->description ?? ''));
        $shareUrl = urlencode(request()->fullUrl());
        $shareText = urlencode($campaign->title);
        $raised = (float) ($campaign->paid_donations_sum ?? 0);
        $expense = (float) ($campaign->expenses_sum_amount ?? 0);
        $goal = (float) ($campaign->goal_amount ?? 0);
        $progress = $goal > 0 ? min(100, round(($raised / $goal) * 100)) : 0;
        $balance = $raised - $expense;

    @endphp
<div class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-purple-50 dark:from-gray-900 dark:via-gray-900 dark:to-indigo-950" x-data="{open:false, progress: {{ $progress }}}"
        x-init="
            $nextTick(() => {
                $wire.showToast()
            })
        ">

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
                                         x-bind:style="{ width: progress + '%' }"></div>
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
                        <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
                            <h3 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Recent Supporters') }}</h3>
                            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                                {{ number_format($campaign->paid_donations_count ?? 0) }} {{ __('Supporters') }}
                            </span>
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            @forelse($donations as $donation)
                                <div class="rounded-2xl border border-indigo-100 dark:border-indigo-800 bg-gradient-to-r from-indigo-50/80 via-sky-50/60 to-purple-50/80 dark:from-indigo-900/20 dark:via-sky-900/10 dark:to-purple-900/20 p-4 sm:p-5 shadow-sm transition-all hover:shadow-md">
                                    <div class="flex items-start justify-between gap-4">
                                        <div class="flex items-start gap-3">
                                            <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-indigo-600 to-purple-600 flex items-center justify-center text-white font-bold">
                                                {{ substr($donation->donor_name, 0, 1) }}
                                            </div>
                                            <div>
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <p class="text-sm sm:text-base font-bold text-gray-900 dark:text-white">{{ $donation->donor_name }}</p>
                                                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-full uppercase tracking-tighter
                                                        {{ $donation->status === 'paid' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300' }}">
                                                        {{ $donation->status }}
                                                    </span>
                                                </div>
                                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                                    {{ __('Paid') }}: {{ $donation->paid_at?->format('M d, Y · h:i A') ?? '-' }}
                                                </p>
                                                <p class="mt-1 text-[11px] text-gray-500 dark:text-gray-400">
                                                    {{ __('Reference') }}: #{{ $donation->id }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-lg font-bold text-indigo-600 dark:text-indigo-400">৳{{ number_format($donation->amount, 0) }}</p>
                                            @if($donation->gateway)
                                                <p class="mt-1 text-[10px] font-semibold uppercase text-gray-500 dark:text-gray-400">
                                                    {{ $donation->gateway }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="sm:col-span-2 text-center py-8">
                                    <x-icon name="o-heart" class="w-12 h-12 mx-auto text-gray-400 dark:text-gray-600 mb-3" />
                                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('No donations yet. Be the first to support!') }}</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    {{-- Campaign Expenses --}}
                    <div class="rounded-3xl bg-white dark:bg-gray-800 p-6 sm:p-8 shadow-xl border border-gray-200 dark:border-gray-700">
                        <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
                            <h3 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Campaign Expenses') }}</h3>
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">{{ __('Total') }}</span>
                                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 border border-red-200 dark:border-red-800">
                                    ৳{{ number_format($expense, 0) }}
                                </span>
                            </div>
                        </div>
                        <div class="grid gap-4">
                            @forelse($expenses as $expenseItem)
                                @php
                                    $receiptUrl = getImage($expenseItem, 'receipt', null, null);
                                    $hasReceipt = $receiptUrl && !str_contains($receiptUrl, 'placehold.co');
                                @endphp
                                <div class="group rounded-2xl border border-gray-100 dark:border-gray-700 bg-gradient-to-r from-rose-50/70 via-amber-50/50 to-orange-50/70 dark:from-rose-900/10 dark:via-amber-900/10 dark:to-orange-900/10 p-4 sm:p-5 shadow-sm transition-all hover:shadow-md">
                                    <div class="flex flex-wrap items-start justify-between gap-4">
                                        <div class="flex items-start gap-4">
                                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/70 dark:bg-gray-900/40 border border-white/60 dark:border-gray-700">
                                                <x-icon name="o-receipt-refund" class="h-6 w-6 text-rose-600 dark:text-rose-400" />
                                            </div>
                                            <div>
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <p class="text-sm sm:text-base font-bold text-gray-900 dark:text-white">
                                                        {{ $expenseItem->category?->name ?? __('Uncategorized') }}
                                                    </p>
                                                    <span class="px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide rounded-full bg-white/70 dark:bg-gray-900/40 text-gray-600 dark:text-gray-300 border border-white/60 dark:border-gray-700">
                                                        {{ $expenseItem->spent_at?->format('M d, Y') ?? __('Date not set') }}
                                                    </span>
                                                </div>
                                                @if($expenseItem->description)
                                                    <p class="mt-1 text-xs sm:text-sm text-gray-600 dark:text-gray-300">
                                                        {{ $expenseItem->description }}
                                                    </p>
                                                @endif
                                                <p class="mt-2 text-[11px] text-gray-500 dark:text-gray-400">
                                                    {{ __('Recorded') }}: {{ $expenseItem->created_at?->diffForHumans() ?? '-' }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-lg font-bold text-rose-600 dark:text-rose-400">৳{{ number_format($expenseItem->amount, 0) }}</p>
                                            @if($hasReceipt)
                                                <div class="mt-2 flex flex-col items-end gap-1">
                                                    <a href="{{ $receiptUrl }}" target="_blank" class="text-[11px] font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">
                                                        {{ __('View Receipt') }}
                                                    </a>
                                                    <a href="{{ $receiptUrl }}" download class="text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 hover:underline">
                                                        {{ __('Download Receipt') }}
                                                    </a>
                                                </div>
                                            @else
                                                <span class="mt-2 inline-flex items-center gap-1 text-[11px] font-semibold text-gray-500 dark:text-gray-400">
                                                    <x-icon name="o-minus-circle" class="h-4 w-4" />
                                                    {{ __('No receipt uploaded') }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-10">
                                    <x-icon name="o-receipt-percent" class="w-12 h-12 mx-auto text-gray-400 dark:text-gray-600 mb-3" />
                                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('No expenses reported yet.') }}</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- Right Column - Sidebar --}}
                <div class="space-y-6">
                    {{-- Donate Card --}}
                    <div id="donate" class="rounded-3xl bg-white dark:bg-gray-800 p-6 shadow-xl border border-gray-200 dark:border-gray-700">
                        <div class="text-center mb-6">
                            <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-indigo-600 to-purple-600 flex items-center justify-center mx-auto mb-4">
                                <x-icon name="o-heart" class="w-8 h-8 text-white" />
                            </div>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ __('Support This Campaign') }}</h3>
                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Your contribution helps us reach the goal faster.') }}</p>
                        </div>

                        <div class="space-y-3">
                            <button wire:click="$set('showDonateModal', true)" class="w-full btn bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white border-0 shadow-lg">
                                <x-icon name="o-heart" class="w-5 h-5" />
                                {{ __('Donate Now') }}
                            </button>
                            <button @click="open=!open" @keydown.escape.window="open=false"  class="w-full btn btn-outline border-2 border-indigo-600 text-indigo-600 hover:bg-indigo-600 hover:text-white">
                                <x-icon name="o-share" class="w-5 h-5" />
                                {{ __('Share Campaign') }}
                            </button>
                        </div>


                        <div class="relative">
                            <div x-cloak x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95 translate-y-2" x-transition:enter-end="opacity-100 scale-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100 translate-y-0" x-transition:leave-end="opacity-0 scale-95 translate-y-2" @click.outside="open=false" class="absolute right-0 bottom-full mb-4 w-72 p-3 rounded-2xl border border-gray-200 dark:border-gray-700 bg-white/90 dark:bg-gray-800/90 backdrop-blur-xl shadow-2xl z-50">
                                <div class="px-2 pb-2 mb-2 border-b border-gray-100 dark:border-gray-700">
                                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">{{ __('Spread the Word') }}</p>
                                </div>
                                <div class="grid grid-cols-2 gap-2">
                                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ $shareUrl }}" target="_blank" rel="noopener" class="flex flex-col items-center justify-center p-3 rounded-xl bg-blue-50 dark:bg-blue-900/20 hover:bg-blue-100 dark:hover:bg-blue-900/40 transition-colors group">
                                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400 group-hover:scale-110 transition-transform" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                                        <span class="mt-1 text-[10px] font-bold text-blue-600 dark:text-blue-400">Facebook</span>
                                    </a>
                                    <a href="https://wa.me/?text={{ $shareText }}%20{{ $shareUrl }}" target="_blank" rel="noopener" class="flex flex-col items-center justify-center p-3 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 hover:bg-emerald-100 dark:hover:bg-emerald-900/40 transition-colors group">
                                        <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400 group-hover:scale-110 transition-transform" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                                        <span class="mt-1 text-[10px] font-bold text-emerald-600 dark:text-emerald-400">WhatsApp</span>
                                    </a>
                                    <a href="https://x.com/intent/tweet?url={{ $shareUrl }}&text={{ $shareText }}" target="_blank" rel="noopener" class="flex flex-col items-center justify-center p-3 rounded-xl bg-gray-50 dark:bg-gray-700/50 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors group">
                                        <svg class="w-6 h-6 text-gray-900 dark:text-white group-hover:scale-110 transition-transform" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.134l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                                        <span class="mt-1 text-[10px] font-bold text-gray-600 dark:text-gray-400">X (Twitter)</span>
                                    </a>
                                    <button type="button" @click="
                                        if (navigator.share) {
                                            navigator.share({ title: decodeURIComponent('{{ $shareText }}'), url: decodeURIComponent('{{ $shareUrl }}') }).catch(()=>{});
                                        } else {
                                            navigator.clipboard.writeText(decodeURIComponent('{{ $shareUrl }}'));
                                            alert('Link copied to clipboard!');
                                        }
                                    " class="flex flex-col items-center justify-center p-3 rounded-xl bg-purple-50 dark:bg-purple-900/20 hover:bg-purple-100 dark:hover:bg-purple-900/40 transition-colors group">
                                        <x-icon name="o-share" class="w-6 h-6 text-purple-600 dark:text-purple-400 group-hover:scale-110 transition-transform" />
                                        <span class="mt-1 text-[10px] font-bold text-purple-600 dark:text-purple-400">{{ __('System Share') }}</span>
                                    </button>
                                </div>
                                <button @click="navigator.clipboard.writeText(decodeURIComponent('{{ $shareUrl }}')); alert('Link copied!');" class="mt-3 w-full py-2.5 px-4 rounded-xl border-2 border-dashed border-gray-200 dark:border-gray-700 hover:border-indigo-300 dark:hover:border-indigo-700 flex items-center justify-center gap-2 transition-all">
                                    <x-icon name="o-link" class="w-4 h-4 text-indigo-600 dark:text-indigo-400" />
                                    <span class="text-xs font-bold text-gray-600 dark:text-gray-400">{{ __('Copy Campaign Link') }}</span>
                                </button>
                            </div>
                        </div>

                        {{-- My Donations Section --}}
                        @auth
                            @if($this->userDonations->isNotEmpty())
                                <div class="mt-6 rounded-3xl bg-white dark:bg-gray-800 p-6 shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden relative">
                                    <div class="absolute top-0 right-0 p-4 opacity-5">
                                        <x-icon name="o-heart" class="w-20 h-20" />
                                    </div>
                                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                                        <x-icon name="o-sparkles" class="w-5 h-5 text-amber-500" />
                                        {{ __('My Contribution') }}
                                    </h3>
                                    <div class="space-y-3">
                                        @foreach($this->userDonations as $donation)
                                            <div class="p-3 rounded-2xl bg-indigo-50/50 dark:bg-indigo-900/10 border border-indigo-100/50 dark:border-indigo-800/50 flex items-center justify-between group hover:bg-white dark:hover:bg-gray-700 transition-all">
                                                <div>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $donation->created_at->format('M d, Y') }}</p>
                                                    <p class="text-sm font-bold text-gray-900 dark:text-white">৳{{ number_format($donation->amount, 0) }}</p>
                                                </div>
                                                <div class="flex flex-col items-end">
                                                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-full uppercase tracking-tighter
                                                        {{ $donation->status === 'paid' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300' }}">
                                                        {{ $donation->status }}
                                                    </span>
                                                    @if($donation->gateway)
                                                        <span class="text-[9px] text-gray-400 mt-1 uppercase">{{ $donation->gateway }}</span>
                                                    @endif
                                                    <button type="button"
                                                            wire:click="downloadInvoice({{ $donation->id }})"
                                                            wire:loading.attr="disabled"
                                                            wire:target="downloadInvoice"
                                                            class="mt-2 inline-flex items-center gap-1 text-[10px] font-semibold text-indigo-600 dark:text-indigo-300 hover:underline">
                                                        <x-icon name="o-document-text" class="w-3 h-3" />
                                                        {{ __('Download Invoice') }}
                                                    </button>
                                                </div>
                                            </div>
                                        @endforeach
                                        <div class="pt-2 text-center">
                                            <p class="text-[10px] text-gray-500 dark:text-gray-400 italic">
                                                {{ __('Total given') }}: <span class="font-bold text-indigo-600 dark:text-indigo-400">৳{{ number_format($this->userDonations->where('status', 'paid')->sum('amount'), 0) }}</span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endauth

                        <div class="mt-6 p-4 rounded-2xl bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-100 dark:border-indigo-800">
                            <p class="text-xs text-indigo-700 dark:text-indigo-300 font-semibold mb-2">{{ __('Payment Methods') }}</p>
                            <div class="space-y-1">
                                <div class="flex items-center gap-2 text-xs text-gray-600 dark:text-gray-400">
                                    <x-icon name="o-check-circle" class="w-4 h-4 text-green-600" />
                                    {{ __('ShurjoPay - bKash, Nagad, Cards') }}
                                </div>
                                <div class="flex items-center gap-2 text-xs text-gray-600 dark:text-gray-400">
                                    <x-icon name="o-check-circle" class="w-4 h-4 text-green-600" />
                                    {{ __('AamarPay - All major methods') }}
                                </div>
                                <div class="flex items-center gap-2 text-xs text-gray-600 dark:text-gray-400">
                                    <x-icon name="o-check-circle" class="w-4 h-4 text-pink-600" />
                                    {{ __('bKash - Manual Send Money') }}
                                </div>
                                <div class="flex items-center gap-2 text-xs text-gray-600 dark:text-gray-400">
                                    <x-icon name="o-check-circle" class="w-4 h-4 text-orange-600" />
                                    {{ __('Nagad - Manual Send Money') }}
                                </div>
                                <div class="flex items-center gap-2 text-xs text-gray-600 dark:text-gray-400">
                                    <x-icon name="o-check-circle" class="w-4 h-4 text-purple-600" />
                                    {{ __('Rocket - Manual Send Money') }}
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Recurring Support Card --}}
                    <div class="rounded-3xl bg-white dark:bg-gray-800 p-6 shadow-xl border border-gray-200 dark:border-gray-700">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-500 flex items-center justify-center">
                                <x-icon name="o-arrow-path" class="w-6 h-6 text-white" />
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ __('Start Monthly/Weekly Support') }}</h3>
                                <p class="text-xs text-gray-600 dark:text-gray-400">{{ __('Keep this campaign funded on schedule.') }}</p>
                            </div>
                        </div>

                        @auth
                            @php
                                $plan = $this->userRecurringPlan;
                                $statusClass = $plan ? match ($plan->status) {
                                    'active' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300',
                                    'paused' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
                                    default => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
                                } : null;
                            @endphp

                            @if($plan)
                                <div class="space-y-4">
                                    <div class="rounded-2xl border border-emerald-100 dark:border-emerald-800 bg-emerald-50/70 dark:bg-emerald-900/10 p-4">
                                        <div class="flex items-start justify-between gap-4">
                                            <div>
                                                <p class="text-xs uppercase tracking-wide text-emerald-700 dark:text-emerald-300">{{ __('Subscription') }}</p>
                                                <p class="mt-1 text-lg font-bold text-gray-900 dark:text-white">৳{{ number_format($plan->amount, 0) }} <span class="text-sm text-gray-500 dark:text-gray-400">/ {{ ucfirst($plan->interval) }}</span></p>
                                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                                    {{ __('Started') }}: {{ $plan->starts_at?->format('M d, Y') ?? '-' }}
                                                </p>
                                            </div>
                                            <span class="px-2.5 py-1 text-[10px] font-bold rounded-full uppercase tracking-wide {{ $statusClass }}">
                                                {{ $plan->status }}
                                            </span>
                                        </div>
                                        <div class="mt-3 flex flex-wrap items-center gap-2 text-xs text-gray-600 dark:text-gray-400">
                                            <span class="inline-flex items-center gap-1 rounded-full bg-white/70 dark:bg-gray-900/40 px-2 py-1 border border-white/60 dark:border-gray-700">
                                                <x-icon name="o-calendar" class="w-4 h-4 text-emerald-600 dark:text-emerald-400" />
                                                {{ $plan->interval === 'weekly' ? __('Weekly') : __('Monthly') }}
                                            </span>
                                            <span class="inline-flex items-center gap-1 rounded-full bg-white/70 dark:bg-gray-900/40 px-2 py-1 border border-white/60 dark:border-gray-700">
                                                <x-icon name="o-clock" class="w-4 h-4 text-emerald-600 dark:text-emerald-400" />
                                                {{ $plan->next_run_at?->format('M d, Y') ?? __('Next run TBD') }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="rounded-2xl border border-gray-200 dark:border-gray-700 p-4">
                                        <h4 class="text-sm font-bold text-gray-900 dark:text-white mb-3">{{ __('Upcoming Donations') }}</h4>
                                        @php
                                            $pendingDonation = $this->pendingRecurringDonation;
                                            $nextDate = $this->upcomingRecurringDates->first();
                                        @endphp
                                        <div class="space-y-2">
                                            @if($pendingDonation)
                                                <div class="flex items-center justify-between rounded-xl bg-amber-50 dark:bg-amber-900/10 px-3 py-2 border border-amber-100 dark:border-amber-800">
                                                    <span class="text-xs text-amber-700 dark:text-amber-300">{{ __('Pending since') }} {{ $pendingDonation->created_at?->format('M d, Y') }}</span>
                                                    <span class="text-xs font-semibold text-amber-700 dark:text-amber-300">৳{{ number_format($pendingDonation->amount, 0) }}</span>
                                                </div>
                                                <button type="button"
                                                        wire:click="payPendingRecurringDonation({{ $pendingDonation->id }})"
                                                        class="w-full btn btn-sm bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white border-0">
                                                    <x-icon name="o-credit-card" class="w-4 h-4" />
                                                    {{ __('Pay Pending Donation') }}
                                                </button>
                                            @elseif($nextDate)
                                                <div class="flex items-center justify-between rounded-xl bg-gray-50 dark:bg-gray-900/30 px-3 py-2">
                                                    <span class="text-xs text-gray-600 dark:text-gray-400">{{ $nextDate->format('M d, Y') }}</span>
                                                    <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400">৳{{ number_format($plan->amount, 0) }}</span>
                                                </div>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('A pending donation will be created on the due date.') }}</p>
                                            @else
                                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Upcoming dates will appear after the next run is scheduled.') }}</p>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="rounded-2xl border border-gray-200 dark:border-gray-700 p-4">
                                        <h4 class="text-sm font-bold text-gray-900 dark:text-white mb-3">{{ __('Plan Donations') }}</h4>
                                        <div class="space-y-2">
                                            @forelse($this->recurringPlanDonations as $donation)
                                                @php
                                                    $donationStatusClass = match ($donation->status) {
                                                        'paid' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300',
                                                        'pending' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
                                                        'failed' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300',
                                                        'cancelled' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
                                                        default => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
                                                    };
                                                @endphp
                                                <div class="flex items-center justify-between rounded-xl bg-gray-50 dark:bg-gray-900/30 px-3 py-2">
                                                    <div>
                                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $donation->created_at?->format('M d, Y') }}</p>
                                                        <p class="text-xs font-semibold text-gray-800 dark:text-gray-200">৳{{ number_format($donation->amount, 0) }}</p>
                                                    </div>
                                                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-full uppercase tracking-wide {{ $donationStatusClass }}">
                                                        {{ $donation->status }}
                                                    </span>
                                                </div>
                                            @empty
                                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('No recurring donations yet.') }}</p>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>
                            @else
                                <form wire:submit.prevent="createRecurringPlan" class="space-y-4">
                                    <div class="grid gap-3">
                                        <x-input
                                            wire:model="recurring_amount"
                                            :label="__('Amount (BDT)')"
                                            type="number"
                                            icon="o-currency-bangladeshi"
                                            hint="{{ __('Minimum ৳10') }}" />
                                    </div>

                                    <div x-data="{ interval: @entangle('recurring_interval').live }" class="grid gap-3">
                                        <div class="grid grid-cols-2 gap-2">
                                            <label class="flex items-center gap-2 p-3 rounded-xl border-2 cursor-pointer transition-all"
                                                :class="interval === 'monthly' ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-900/20' : 'border-gray-200 dark:border-gray-700 hover:border-emerald-300'">
                                                <input type="radio" x-model="interval" value="monthly" class="radio radio-success radio-sm hidden">
                                                <div>
                                                    <p class="text-sm font-bold text-gray-900 dark:text-white">{{ __('Monthly') }}</p>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Same day each month') }}</p>
                                                </div>
                                            </label>
                                            <label class="flex items-center gap-2 p-3 rounded-xl border-2 cursor-pointer transition-all"
                                                :class="interval === 'weekly' ? 'border-teal-500 bg-teal-50 dark:bg-teal-900/20' : 'border-gray-200 dark:border-gray-700 hover:border-teal-300'">
                                                <input type="radio" x-model="interval" value="weekly" class="radio radio-success radio-sm hidden">
                                                <div>
                                                    <p class="text-sm font-bold text-gray-900 dark:text-white">{{ __('Weekly') }}</p>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Pick a weekday') }}</p>
                                                </div>
                                            </label>
                                        </div>

                                        <div x-show="interval === 'monthly'" x-cloak>
                                            <x-input
                                                wire:model="recurring_day_of_month"
                                                :label="__('Day of Month')"
                                                type="number"
                                                min="1"
                                                max="31"
                                                icon="o-calendar-days" />
                                        </div>

                                        <div x-show="interval === 'weekly'" x-cloak>
                                            <x-select
                                                wire:model="recurring_day_of_week"
                                                :label="__('Day of Week')"
                                                icon="o-calendar"
                                                :options="[
                                                    ['id' => 0, 'name' => __('Sunday')],
                                                    ['id' => 1, 'name' => __('Monday')],
                                                    ['id' => 2, 'name' => __('Tuesday')],
                                                    ['id' => 3, 'name' => __('Wednesday')],
                                                    ['id' => 4, 'name' => __('Thursday')],
                                                    ['id' => 5, 'name' => __('Friday')],
                                                    ['id' => 6, 'name' => __('Saturday')]
                                                ]" />
                                        </div>
                                    </div>

                                    <button type="submit" class="w-full btn bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white border-0 shadow-lg">
                                        <x-icon name="o-arrow-path" class="w-5 h-5" />
                                        {{ __('Start Recurring Support') }}
                                    </button>
                                </form>
                            @endif
                        @endauth

                        @guest
                            <div class="rounded-2xl border border-dashed border-gray-200 dark:border-gray-700 p-4 text-center">
                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Log in to start a recurring plan and track it anytime.') }}</p>
                                <a href="{{ route('login') }}" class="mt-3 inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold">
                                    <x-icon name="o-arrow-right-on-rectangle" class="w-4 h-4" />
                                    {{ __('Log in to continue') }}
                                </a>
                            </div>
                        @endguest
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
            <button wire:click="$set('showDonateModal', true)" class="btn btn-sm bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white border-0 shadow-lg">
                <x-icon name="o-heart" class="w-4 h-4" />
                {{ __('Donate') }}
            </button>
        </div>
    </div>

    {{-- Donate Modal --}}
    <x-modal wire:model="showDonateModal" :title="__('Support') . ' ' . $campaign->title" class="backdrop-blur">
        <div class="space-y-4">
            <div class="grid gap-3">
                @php
                    $isPendingPayment = ! empty($pendingDonationId);
                @endphp
                <x-input wire:model="amount" :label="__('Amount (BDT)')" type="number" icon="o-currency-bangladeshi"
                         hint="{{ $isPendingPayment ? __('Amount is locked for this pending donation.') : __('Minimum ৳10') }}"
                         :readonly="$isPendingPayment" />
                @if($isPendingPayment)
                    <div class="rounded-xl border border-emerald-100 dark:border-emerald-800 bg-emerald-50/70 dark:bg-emerald-900/20 px-3 py-2 text-xs text-emerald-700 dark:text-emerald-300">
                        {{ __('This payment will settle your pending recurring donation.') }}
                    </div>
                @endif

                @guest
                    <x-input wire:model="donor_name" :label="__('Your Name')" icon="o-user" />
                    <x-input wire:model="donor_email" :label="__('Email')" type="email" icon="o-envelope" />
                    <x-input wire:model="donor_password" :label="__('Password')" type="password" icon="o-lock-closed"
                        hint="{{ __('Login or create account (min 8 chars)') }}" />
                @endguest
            </div>

            {{-- Payment Gateway Selection --}}
            <div x-data="{ currentGateway: @entangle('gateway').live }" class="space-y-4">
                {{-- Automated Gateways Section --}}
                <div class="space-y-2">
                    <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide px-1">{{ __('Automated Payment') }}</div>

                    <div class="grid grid-cols-2 gap-2">
                        <label class="flex items-center gap-2 p-3 rounded-xl border-2 cursor-pointer transition-all"
                            :class="currentGateway === 'shurjopay' ? 'border-primary bg-primary/5' : 'border-gray-200 dark:border-gray-700 hover:border-primary/50'">
                            <input type="radio" x-model="currentGateway" value="shurjopay" class="radio radio-primary radio-sm hidden">
                            <div class="flex-1">
                                <p class="text-sm font-bold text-gray-900 dark:text-white">{{ __('ShurjoPay') }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('bKash, Nagad, Cards') }}</p>
                            </div>
                        </label>

                        <label class="flex items-center gap-2 p-3 rounded-xl border-2 cursor-pointer transition-all"
                            :class="currentGateway === 'aamarpay' ? 'border-primary bg-primary/5' : 'border-gray-200 dark:border-gray-700 hover:border-primary/50'">
                            <input type="radio" x-model="currentGateway" value="aamarpay" class="radio radio-primary radio-sm hidden">
                            <div class="flex-1">
                                <p class="text-sm font-bold text-gray-900 dark:text-white">{{ __('AamarPay') }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('All major methods') }}</p>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- Manual Payment Section --}}
                <div class="space-y-2">
                    <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mt-4 px-1">{{ __('Manual Payment (Send Money)') }}</div>

                    <div class="grid grid-cols-3 gap-2">
                        {{-- bKash --}}
                        <label class="flex flex-col items-center gap-2 p-3 rounded-xl border-2 cursor-pointer transition-all"
                            :class="currentGateway === 'bkash' ? 'border-pink-600 bg-pink-50 dark:bg-pink-900/20' : 'border-gray-200 dark:border-gray-700 hover:border-pink-300'">
                            <input type="radio" x-model="currentGateway" value="bkash" class="radio radio-secondary radio-sm hidden">
                            <div class="w-10 h-10 rounded-lg bg-pink-600 flex items-center justify-center text-white font-bold text-xs">BK</div>
                            <div class="text-center">
                                <p class="text-sm font-bold text-gray-900 dark:text-white">{{ __('bKash') }}</p>
                            </div>
                        </label>

                        {{-- Nagad --}}
                        <label class="flex flex-col items-center gap-2 p-3 rounded-xl border-2 cursor-pointer transition-all"
                            :class="currentGateway === 'nagad' ? 'border-orange-600 bg-orange-50 dark:bg-orange-900/20' : 'border-gray-200 dark:border-gray-700 hover:border-orange-300'">
                            <input type="radio" x-model="currentGateway" value="nagad" class="radio radio-warning radio-sm hidden">
                            <div class="w-10 h-10 rounded-lg bg-orange-600 flex items-center justify-center text-white font-bold text-xs">NG</div>
                            <div class="text-center">
                                <p class="text-sm font-bold text-gray-900 dark:text-white">{{ __('Nagad') }}</p>
                            </div>
                        </label>

                        {{-- Rocket --}}
                        <label class="flex flex-col items-center gap-2 p-3 rounded-xl border-2 cursor-pointer transition-all"
                            :class="currentGateway === 'rocket' ? 'border-purple-600 bg-purple-50 dark:bg-purple-900/20' : 'border-gray-200 dark:border-gray-700 hover:border-purple-300'">
                            <input type="radio" x-model="currentGateway" value="rocket" class="radio radio-accent radio-sm hidden">
                            <div class="w-10 h-10 rounded-lg bg-purple-600 flex items-center justify-center text-white font-bold text-xs">RK</div>
                            <div class="text-center">
                                <p class="text-sm font-bold text-gray-900 dark:text-white">{{ __('Rocket') }}</p>
                            </div>
                        </label>
                    </div>

                    {{-- Transaction ID Input (shown for all manual methods) --}}
                    <div x-show="['bkash', 'nagad', 'rocket'].includes(currentGateway)" x-transition
                        class="p-4 rounded-xl border space-y-3"
                        :class="{
                            'bg-pink-50 dark:bg-pink-900/10 border-pink-100 dark:border-pink-800': currentGateway === 'bkash',
                            'bg-orange-50 dark:bg-orange-900/10 border-orange-100 dark:border-orange-800': currentGateway === 'nagad',
                            'bg-purple-50 dark:bg-purple-900/10 border-purple-100 dark:border-purple-800': currentGateway === 'rocket'
                        }">
                        <div class="text-sm space-y-1"
                            :class="{
                                'text-pink-700 dark:text-pink-300': currentGateway === 'bkash',
                                'text-orange-700 dark:text-orange-300': currentGateway === 'nagad',
                                'text-purple-700 dark:text-purple-300': currentGateway === 'rocket'
                            }">
                            <p><strong>1.</strong> <span x-text="currentGateway === 'bkash' ? 'Open bKash app' : currentGateway === 'nagad' ? 'Open Nagad app' : 'Open Rocket app'"></span> and select <span class="font-bold">Send Money</span></p>
                            <p><strong>2.</strong> Send <span class="font-bold">৳{{ number_format($amount ?: 0, 2) }}</span> to
                                <span class="font-mono font-bold bg-white dark:bg-gray-800 px-2 rounded border select-all"
                                    :class="{
                                        'border-pink-200': currentGateway === 'bkash',
                                        'border-orange-200': currentGateway === 'nagad',
                                        'border-purple-200': currentGateway === 'rocket'
                                    }"
                                    x-text="currentGateway === 'bkash' ? '{{ env('BKASH_ACCOUNT_NUMBER', '01700000000') }}' : currentGateway === 'nagad' ? '{{ env('NAGAD_ACCOUNT_NUMBER', '01700000000') }}' : '{{ env('ROCKET_ACCOUNT_NUMBER', '01700000000') }}'"></span>
                            </p>
                            <p><strong>3.</strong> Enter the Transaction ID below</p>
                        </div>
                        <x-input wire:model="transaction_id" :label="__('Transaction ID')" icon="o-hashtag" placeholder="TrxID" required />
                    </div>
                </div>
            </div>

        </div>

        <x-slot:actions>
            <x-button :label="__('Cancel')" @click="$wire.showDonateModal = false" />

            <x-button :label="__('Submit Donation')" icon="o-heart" wire:click="donate" class="btn-primary" spinner="donate" />
        </x-slot:actions>
    </x-modal>

</div>
