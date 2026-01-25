<div>
    {{-- Navigation --}}
    {{-- Hero Section --}}
    <section class="relative overflow-hidden py-12 sm:py-20">
        <div class="absolute inset-0 -z-10">
            <div class="absolute top-0 right-0 h-96 w-96 rounded-full bg-purple-300/30 dark:bg-purple-600/20 blur-3xl"></div>
            <div class="absolute bottom-0 left-0 h-96 w-96 rounded-full bg-indigo-300/30 dark:bg-indigo-600/20 blur-3xl"></div>
        </div>

        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-8 lg:grid-cols-2 lg:gap-12">
                <div class="text-center lg:text-left">
                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-indigo-100 dark:bg-indigo-900/30 border border-indigo-200 dark:border-indigo-800">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-indigo-500"></span>
                        </span>
                        <span class="text-xs font-semibold text-indigo-700 dark:text-indigo-300 uppercase tracking-wider">{{ __('Donor-first platform') }}</span>
                    </div>
                    
                    <h1 class="mt-6 text-4xl sm:text-5xl lg:text-6xl font-bold leading-tight">
                        <span class="bg-gradient-to-r from-indigo-600 to-purple-600 dark:from-indigo-400 dark:to-purple-400 bg-clip-text text-transparent">
                            {{ __('Give with confidence.') }}
                        </span>
                        <br>
                        <span class="text-gray-900 dark:text-white">{{ __('Track every taka.') }}</span>
                    </h1>
                    
                    <p class="mt-6 text-lg text-gray-600 dark:text-gray-300 max-w-2xl mx-auto lg:mx-0">
                        {{ __('Donate to verified campaigns, see transparent progress, and receive instant receipts. Deenify powers secure giving with bKash, Nagad, and SSLCommerz.') }}
                    </p>
                    
                    <div class="mt-8 flex flex-wrap gap-4 justify-center lg:justify-start">
                        <a href="{{ route('web.campaigns') }}" class="btn bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white border-0 shadow-xl hover:shadow-2xl transform hover:scale-105 transition">
                            <x-icon name="o-heart" class="w-5 h-5" />
                            {{ __('Donate Now') }}
                        </a>
                        <a href="#features" class="btn btn-outline border-2 border-indigo-600 text-indigo-600 hover:bg-indigo-600 hover:text-white dark:border-indigo-400 dark:text-indigo-400">
                            {{ __('Explore Features') }}
                        </a>
                    </div>
                    
                    <div class="mt-8 flex flex-wrap gap-4 justify-center lg:justify-start text-sm">
                        <div class="flex items-center gap-2 px-3 py-1 rounded-full bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300">
                            <x-icon name="o-check-circle" class="w-4 h-4" />
                            {{ __('Secure payments') }}
                        </div>
                        <div class="flex items-center gap-2 px-3 py-1 rounded-full bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300">
                            <x-icon name="o-check-circle" class="w-4 h-4" />
                            {{ __('Guest donations') }}
                        </div>
                        <div class="flex items-center gap-2 px-3 py-1 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300">
                            <x-icon name="o-check-circle" class="w-4 h-4" />
                            {{ __('Live tracking') }}
                        </div>
                    </div>
                </div>

                {{-- Featured Campaign Card --}}
                <div class="relative">
                    <div class="absolute inset-0 bg-gradient-to-r from-indigo-600 to-purple-600 rounded-3xl blur-2xl opacity-20"></div>
                    <div class="relative backdrop-blur-sm bg-white/90 dark:bg-gray-800/90 rounded-3xl shadow-2xl border border-gray-200 dark:border-gray-700 p-6 sm:p-8">
                        @php
                            $featured = $featuredCampaign;
                            $featuredRaised = $featured ? (float) $featured->paid_donations_sum : 0;
                            $featuredExpense = $featured ? (float) $featured->expenses_sum : 0;
                            $featuredGoal = $featured ? (float) $featured->goal_amount : 0;
                            $featuredBalance = $featuredRaised - $featuredExpense;
                            $featuredProgress = $featuredGoal > 0 ? min(100, round(($featuredRaised / $featuredGoal) * 100)) : 0;
                        @endphp
                        
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold uppercase tracking-wider text-indigo-600 dark:text-indigo-400">{{ __('Featured Campaign') }}</span>
                            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300">
                                {{ $featured ? ucfirst($featured->status) : __('None') }}
                            </span>
                        </div>
                        
                        <h3 class="mt-4 text-2xl font-bold text-gray-900 dark:text-white">
                            {{ $featured?->title ?? __('No active campaign yet') }}
                        </h3>
                        
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-300 line-clamp-2">
                            {{ $featured?->description ?? __('Launch a campaign to start receiving donations.') }}
                        </p>

                        <div class="mt-6">
                            <div class="flex justify-between text-sm mb-2">
                                <span class="text-gray-600 dark:text-gray-400">{{ __('Raised') }}</span>
                                <span class="font-bold text-indigo-600 dark:text-indigo-400">৳ {{ number_format($featuredRaised, 0) }}</span>
                            </div>
                            <div class="h-3 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                                <div class="h-full rounded-full bg-gradient-to-r from-indigo-600 to-purple-600 transition-all duration-500" style="width: {{ $featuredProgress }}%"></div>
                            </div>
                            <div class="flex justify-between text-xs mt-2 text-gray-500 dark:text-gray-400">
                                <span>{{ __('Goal') }}: ৳ {{ number_format($featuredGoal, 0) }}</span>
                                <span class="font-semibold">{{ $featuredProgress }}%</span>
                            </div>
                        </div>

                        <div class="mt-6 grid grid-cols-3 gap-3">
                            <div class="text-center p-3 rounded-2xl bg-gradient-to-br from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 border border-indigo-100 dark:border-indigo-800">
                                <p class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">{{ number_format($featured?->paid_donations_count ?? 0) }}</p>
                                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">{{ __('Donations') }}</p>
                            </div>
                            <div class="text-center p-3 rounded-2xl bg-gradient-to-br from-red-50 to-orange-50 dark:from-red-900/20 dark:to-orange-900/20 border border-red-100 dark:border-red-800">
                                <p class="text-lg font-bold text-red-600 dark:text-red-400">৳{{ number_format($featuredExpense, 0) }}</p>
                                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">{{ __('Expense') }}</p>
                            </div>
                            <div class="text-center p-3 rounded-2xl bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 border border-green-100 dark:border-green-800">
                                <p class="text-lg font-bold text-green-600 dark:text-green-400">৳{{ number_format($featuredBalance, 0) }}</p>
                                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">{{ __('Balance') }}</p>
                            </div>
                        </div>

                        @if($featured)
                        <a href="{{ route('web.campaign', $featured->slug) }}" class="mt-6 btn w-full bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white border-0 shadow-lg">
                            {{ __('View Campaign') }}
                            <x-icon name="o-arrow-right" class="w-4 h-4" />
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Stats Section --}}
    <section class="py-16 bg-white dark:bg-gray-900" id="features">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl sm:text-4xl font-bold">
                    <span class="bg-gradient-to-r from-indigo-600 to-purple-600 dark:from-indigo-400 dark:to-purple-400 bg-clip-text text-transparent">
                        {{ __('Live Statistics') }}
                    </span>
                </h2>
                <p class="mt-4 text-gray-600 dark:text-gray-300">{{ __('Real-time insights into our donation platform') }}</p>
            </div>

            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div class="group relative overflow-hidden rounded-3xl bg-gradient-to-br from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 p-6 border border-indigo-100 dark:border-indigo-800 shadow-lg hover:shadow-2xl transition-all duration-300 hover:-translate-y-1">
                    <div class="absolute top-0 right-0 w-20 h-20 bg-indigo-200/30 dark:bg-indigo-600/20 rounded-full blur-2xl"></div>
                    <x-icon name="o-currency-bangladeshi" class="w-8 h-8 text-indigo-600 dark:text-indigo-400 mb-3" />
                    <p class="text-3xl font-bold text-indigo-600 dark:text-indigo-400">৳{{ number_format($stats['paid_total'] ?? 0, 0) }}</p>
                    <p class="mt-2 text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Total Raised') }}</p>
                </div>

                <div class="group relative overflow-hidden rounded-3xl bg-gradient-to-br from-red-50 to-orange-50 dark:from-red-900/20 dark:to-orange-900/20 p-6 border border-red-100 dark:border-red-800 shadow-lg hover:shadow-2xl transition-all duration-300 hover:-translate-y-1">
                    <div class="absolute top-0 right-0 w-20 h-20 bg-red-200/30 dark:bg-red-600/20 rounded-full blur-2xl"></div>
                    <x-icon name="o-arrow-trending-down" class="w-8 h-8 text-red-600 dark:text-red-400 mb-3" />
                    <p class="text-3xl font-bold text-red-600 dark:text-red-400">৳{{ number_format($stats['expense_total'] ?? 0, 0) }}</p>
                    <p class="mt-2 text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Total Spent') }}</p>
                </div>

                <div class="group relative overflow-hidden rounded-3xl bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 p-6 border border-green-100 dark:border-green-800 shadow-lg hover:shadow-2xl transition-all duration-300 hover:-translate-y-1">
                    <div class="absolute top-0 right-0 w-20 h-20 bg-green-200/30 dark:bg-green-600/20 rounded-full blur-2xl"></div>
                    <x-icon name="o-banknotes" class="w-8 h-8 text-green-600 dark:text-green-400 mb-3" />
                    <p class="text-3xl font-bold text-green-600 dark:text-green-400">৳{{ number_format($stats['net_balance'] ?? 0, 0) }}</p>
                    <p class="mt-2 text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Net Balance') }}</p>
                </div>

                <div class="group relative overflow-hidden rounded-3xl bg-gradient-to-br from-amber-50 to-yellow-50 dark:from-amber-900/20 dark:to-yellow-900/20 p-6 border border-amber-100 dark:border-amber-800 shadow-lg hover:shadow-2xl transition-all duration-300 hover:-translate-y-1">
                    <div class="absolute top-0 right-0 w-20 h-20 bg-amber-200/30 dark:bg-amber-600/20 rounded-full blur-2xl"></div>
                    <x-icon name="o-megaphone" class="w-8 h-8 text-amber-600 dark:text-amber-400 mb-3" />
                    <p class="text-3xl font-bold text-amber-600 dark:text-amber-400">{{ number_format($stats['active_campaigns'] ?? 0) }}</p>
                    <p class="mt-2 text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Active Campaigns') }}</p>
                </div>
            </div>

            <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-2xl bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm p-6 border border-gray-200 dark:border-gray-700 shadow hover:shadow-lg transition">
                    <div class="flex items-center gap-3">
                        <div class="p-2 rounded-lg bg-blue-100 dark:bg-blue-900/30">
                            <x-icon name="o-users" class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['paid_count'] ?? 0) }}</p>
                            <p class="text-xs text-gray-600 dark:text-gray-400">{{ __('Successful Donations') }}</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm p-6 border border-gray-200 dark:border-gray-700 shadow hover:shadow-lg transition">
                    <div class="flex items-center gap-3">
                        <div class="p-2 rounded-lg bg-orange-100 dark:bg-orange-900/30">
                            <x-icon name="o-clock" class="w-5 h-5 text-orange-600 dark:text-orange-400" />
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['pending_count'] ?? 0) }}</p>
                            <p class="text-xs text-gray-600 dark:text-gray-400">{{ __('Pending Payments') }}</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm p-6 border border-gray-200 dark:border-gray-700 shadow hover:shadow-lg transition">
                    <div class="flex items-center gap-3">
                        <div class="p-2 rounded-lg bg-purple-100 dark:bg-purple-900/30">
                            <x-icon name="o-arrow-path" class="w-5 h-5 text-purple-600 dark:text-purple-400" />
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['recurring_active'] ?? 0) }}</p>
                            <p class="text-xs text-gray-600 dark:text-gray-400">{{ __('Active Plans') }}</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm p-6 border border-gray-200 dark:border-gray-700 shadow hover:shadow-lg transition">
                    <div class="flex items-center gap-3">
                        <div class="p-2 rounded-lg bg-teal-100 dark:bg-teal-900/30">
                            <x-icon name="o-document-text" class="w-5 h-5 text-teal-600 dark:text-teal-400" />
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['receipts_count'] ?? 0) }}</p>
                            <p class="text-xs text-gray-600 dark:text-gray-400">{{ __('Generated Receipts') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Payment Methods & Recent Donors --}}
    <section class="py-16 bg-gradient-to-br from-gray-50 to-indigo-50/30 dark:from-gray-900 dark:to-indigo-950/30">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-8 lg:grid-cols-3">
                <div class="lg:col-span-2 rounded-3xl bg-white dark:bg-gray-800 p-6 sm:p-8 shadow-xl border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h3 class="text-2xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 dark:from-indigo-400 dark:to-purple-400 bg-clip-text text-transparent">{{ __('Gateway Performance') }}</h3>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Payment method distribution') }}</p>
                        </div>
                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300">{{ __('Paid only') }}</span>
                    </div>
                    @php
                        $splitTotal = collect($paymentSplit)->sum('total');
                        $gateways = ['bkash' => 'bKash', 'nagad' => 'Nagad', 'sslcommerz' => 'SSLCommerz'];
                        $colors = ['bkash' => 'pink', 'nagad' => 'orange', 'sslcommerz' => 'blue'];
                    @endphp
                    <div class="space-y-4">
                        @foreach($gateways as $key => $label)
                            @php
                                $value = $paymentSplit[$key]['total'] ?? 0;
                                $count = $paymentSplit[$key]['count'] ?? 0;
                                $percent = $splitTotal > 0 ? round(($value / $splitTotal) * 100) : 0;
                                $color = $colors[$key];
                            @endphp
                            <div class="rounded-2xl bg-gradient-to-r from-{{ $color }}-50 to-{{ $color }}-100/50 dark:from-{{ $color }}-900/20 dark:to-{{ $color }}-800/10 p-5 border border-{{ $color }}-200 dark:border-{{ $color }}-800">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="font-bold text-gray-900 dark:text-white">{{ $label }}</span>
                                    <span class="text-sm text-gray-600 dark:text-gray-400">৳ {{ number_format($value, 0) }} • <span class="font-semibold">{{ $count }}</span> {{ __('tx') }}</span>
                                </div>
                                <div class="h-2.5 w-full overflow-hidden rounded-full bg-white dark:bg-gray-700">
                                    <div class="h-full rounded-full bg-gradient-to-r from-{{ $color }}-500 to-{{ $color }}-600 transition-all duration-500" style="width: {{ $percent }}%"></div>
                                </div>
                                <p class="mt-2 text-xs font-semibold text-{{ $color }}-600 dark:text-{{ $color }}-400">{{ $percent }}% of total</p>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="rounded-3xl bg-white dark:bg-gray-800 p-6 shadow-xl border border-gray-200 dark:border-gray-700">
                    <h3 class="text-2xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 dark:from-indigo-400 dark:to-purple-400 bg-clip-text text-transparent">{{ __('Latest Supporters') }}</h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400 mb-6">{{ __('Recent donations') }}</p>
                    <div class="space-y-3">
                        @forelse($recentDonors as $donation)
                            <div class="flex items-center justify-between rounded-2xl bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 px-4 py-3 border border-indigo-100 dark:border-indigo-800 hover:shadow-md transition">
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
                            <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-8">{{ __('No donors yet.') }}</p>
                        @endforelse
                    </div>
                    <a href="{{ route('web.campaigns') }}" class="mt-6 btn w-full btn-outline border-2 border-indigo-600 text-indigo-600 hover:bg-indigo-600 hover:text-white dark:border-indigo-400 dark:text-indigo-400">{{ __('View All Campaigns') }}</a>
                </div>
            </div>
        </div>
    </section>

    {{-- Top Campaigns & How It Works --}}
    <section class="py-16 bg-white dark:bg-gray-900" id="reports">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-8 lg:grid-cols-2">
                <div class="rounded-3xl bg-gradient-to-br from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 p-6 sm:p-8 border border-indigo-100 dark:border-indigo-800 shadow-xl">
                    <h3 class="text-2xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 dark:from-indigo-400 dark:to-purple-400 bg-clip-text text-transparent">{{ __('Top Campaigns') }}</h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400 mb-6">{{ __('Highest impact right now') }}</p>
                    <div class="space-y-4">
                        @forelse($topCampaigns as $item)
                            @php
                                $raised = (float) ($item->paid_donations_sum ?? 0);
                                $goal = (float) ($item->goal_amount ?? 0);
                                $progress = $goal > 0 ? min(100, round(($raised / $goal) * 100)) : 0;
                            @endphp
                            <div class="rounded-2xl bg-white dark:bg-gray-800 p-5 border border-gray-200 dark:border-gray-700 hover:shadow-lg transition">
                                <div class="flex items-center justify-between mb-3">
                                    <div>
                                        <p class="font-bold text-gray-900 dark:text-white">{{ $item->title }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ number_format($item->paid_donations_count ?? 0) }} {{ __('donations') }}</p>
                                    </div>
                                    <span class="text-lg font-bold text-indigo-600 dark:text-indigo-400">৳{{ number_format($raised, 0) }}</span>
                                </div>
                                <div class="h-2 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                                    <div class="h-full rounded-full bg-gradient-to-r from-indigo-600 to-purple-600 transition-all duration-500" style="width: {{ $progress }}%"></div>
                                </div>
                                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">{{ $progress }}% of ৳{{ number_format($goal, 0) }} goal</p>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-8">{{ __('No campaigns yet.') }}</p>
                        @endforelse
                    </div>
                </div>

                <div class="rounded-3xl bg-white dark:bg-gray-800 p-6 sm:p-8 border border-gray-200 dark:border-gray-700 shadow-xl" id="workflow">
                    <h3 class="text-2xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 dark:from-indigo-400 dark:to-purple-400 bg-clip-text text-transparent">{{ __('How It Works') }}</h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400 mb-6">{{ __('Transparent donation workflow') }}</p>
                    <div class="space-y-6">
                        <div class="flex gap-4">
                            <div class="flex-shrink-0 w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-600 to-purple-600 flex items-center justify-center text-white font-bold text-lg shadow-lg">1</div>
                            <div>
                                <p class="font-bold text-gray-900 dark:text-white">{{ __('Create campaigns') }}</p>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Set targets, timelines, and payment methods in minutes.') }}</p>
                            </div>
                        </div>
                        <div class="flex gap-4">
                            <div class="flex-shrink-0 w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-600 to-purple-600 flex items-center justify-center text-white font-bold text-lg shadow-lg">2</div>
                            <div>
                                <p class="font-bold text-gray-900 dark:text-white">{{ __('Collect donations') }}</p>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Guests or logged-in donors pay and receive instant receipts.') }}</p>
                            </div>
                        </div>
                        <div class="flex gap-4">
                            <div class="flex-shrink-0 w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-600 to-purple-600 flex items-center justify-center text-white font-bold text-lg shadow-lg">3</div>
                            <div>
                                <p class="font-bold text-gray-900 dark:text-white">{{ __('Track expenses') }}</p>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Categorize spendings and see net balance in real time.') }}</p>
                            </div>
                        </div>
                        <div class="flex gap-4">
                            <div class="flex-shrink-0 w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-600 to-purple-600 flex items-center justify-center text-white font-bold text-lg shadow-lg">4</div>
                            <div>
                                <p class="font-bold text-gray-900 dark:text-white">{{ __('Report & export') }}</p>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Generate PDF/Excel reports for audits and transparency.') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Testimonials & Features --}}
    <section class="py-16 bg-gradient-to-br from-gray-50 to-purple-50/30 dark:from-gray-900 dark:to-purple-950/30">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-6 lg:grid-cols-3">
                <div class="rounded-3xl bg-white dark:bg-gray-800 p-6 sm:p-8 border border-gray-200 dark:border-gray-700 shadow-xl">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-600 to-purple-600 flex items-center justify-center mb-4">
                        <x-icon name="o-chat-bubble-left-right" class="w-6 h-6 text-white" />
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ __('Trusted by donors') }}</h3>
                    <p class="mt-3 text-gray-600 dark:text-gray-300 italic">"{{ __('Clear receipts and transparent tracking changed how we manage fundraising.') }}"</p>
                    <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">— {{ __('Relief Foundation') }}</p>
                </div>
                <div class="rounded-3xl bg-white dark:bg-gray-800 p-6 sm:p-8 border border-gray-200 dark:border-gray-700 shadow-xl">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-green-600 to-emerald-600 flex items-center justify-center mb-4">
                        <x-icon name="o-shield-check" class="w-6 h-6 text-white" />
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ __('Built for accountability') }}</h3>
                    <ul class="mt-4 space-y-2 text-sm text-gray-600 dark:text-gray-300">
                        <li class="flex items-center gap-2"><x-icon name="o-check-circle" class="w-4 h-4 text-green-600" />{{ __('Verified payment gateways') }}</li>
                        <li class="flex items-center gap-2"><x-icon name="o-check-circle" class="w-4 h-4 text-green-600" />{{ __('Receipt audit trail') }}</li>
                        <li class="flex items-center gap-2"><x-icon name="o-check-circle" class="w-4 h-4 text-green-600" />{{ __('Role-based access control') }}</li>
                        <li class="flex items-center gap-2"><x-icon name="o-check-circle" class="w-4 h-4 text-green-600" />{{ __('Exportable reports') }}</li>
                    </ul>
                </div>
                <div class="rounded-3xl bg-white dark:bg-gray-800 p-6 sm:p-8 border border-gray-200 dark:border-gray-700 shadow-xl">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-amber-600 to-orange-600 flex items-center justify-center mb-4">
                        <x-icon name="o-star" class="w-6 h-6 text-white" />
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ __('Reliability at scale') }}</h3>
                    <div class="mt-4 grid grid-cols-2 gap-3">
                        <span class="px-3 py-2 text-xs font-semibold text-center rounded-full bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">{{ __('PWA Ready') }}</span>
                        <span class="px-3 py-2 text-xs font-semibold text-center rounded-full bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 border border-green-200 dark:border-green-800">{{ __('Live Reports') }}</span>
                        <span class="px-3 py-2 text-xs font-semibold text-center rounded-full bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800">{{ __('Auto Receipts') }}</span>
                        <span class="px-3 py-2 text-xs font-semibold text-center rounded-full bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800">{{ __('Multi-Locale') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- CTA Section --}}
    <section class="py-16 bg-white dark:bg-gray-900">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-indigo-600 to-purple-600 p-8 sm:p-12 shadow-2xl">
                <div class="absolute inset-0 bg-grid-white/10"></div>
                <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full blur-3xl"></div>
                <div class="absolute bottom-0 left-0 w-64 h-64 bg-purple-400/20 rounded-full blur-3xl"></div>
                
                <div class="relative z-10 flex flex-col items-center justify-between gap-6 lg:flex-row">
                    <div class="text-center lg:text-left">
                        <h3 class="text-3xl sm:text-4xl font-bold text-white">{{ __('Ready to make a difference?') }}</h3>
                        <p class="mt-3 text-lg text-indigo-100">{{ __('Join donors building a transparent future.') }}</p>
                        <p class="mt-2 text-sm text-indigo-200">{{ __('Support campaigns or create a donor account to manage your giving history.') }}</p>
                    </div>
                    <div class="flex flex-wrap gap-3 justify-center lg:justify-end">
                        <a href="{{ route('web.campaigns') }}" class="btn bg-white text-indigo-600 hover:bg-indigo-50 border-0 shadow-xl">
                            <x-icon name="o-heart" class="w-5 h-5" />
                            {{ __('Donate Now') }}
                        </a>
                        <a href="{{ route('register') }}" class="btn btn-outline border-2 border-white text-white hover:bg-white hover:text-indigo-600">
                            {{ __('Create Account') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

  
</div>
