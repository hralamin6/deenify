<div>
    <x-header :title="__('Dashboard')" :subtitle="__('Fundraising & revenue overview')" separator />

    @php
        $stats = $this->stats;
        $formatBytes = function (?float $bytes): string {
            if (! $bytes || $bytes <= 0) {
                return '0 B';
            }

            $units = ['B', 'KB', 'MB', 'GB', 'TB'];
            $i = 0;
            while ($bytes >= 1024 && $i < count($units) - 1) {
                $bytes /= 1024;
                $i++;
            }

            return number_format($bytes, 2).' '.$units[$i];
        };
    @endphp

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4 mb-6">
        <x-stat
            :title="__('Total Donations')"
            :value="$stats['currency'].' '.number_format($stats['total_paid'], 2)"
            icon="o-banknotes"
            color="text-primary"
        />
        <x-stat
            :title="__('Donations Today')"
            :value="$stats['currency'].' '.number_format($stats['today_paid'], 2)"
            icon="o-sparkles"
            color="text-success"
        />
        <x-stat
            :title="__('This Week')"
            :value="$stats['currency'].' '.number_format($stats['week_paid'], 2)"
            icon="o-calendar"
            color="text-info"
        />
        <x-stat
            :title="__('This Month')"
            :value="$stats['currency'].' '.number_format($stats['month_paid'], 2)"
            icon="o-calendar-days"
            color="text-warning"
        />
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4 mb-6">
        <x-stat
            :title="__('Average Donation')"
            :value="$stats['currency'].' '.number_format($stats['avg_paid'] ?? 0, 2)"
            icon="o-scale"
            color="text-primary"
        />
        <x-stat
            :title="__('Unique Donors')"
            :value="number_format($stats['unique_donors'] ?? 0)"
            icon="o-user-group"
            color="text-success"
        />
        <x-stat
            :title="__('New Donors (Month)')"
            :value="number_format($stats['new_donors_month'] ?? 0)"
            icon="o-user-plus"
            color="text-info"
        />
        <x-stat
            :title="__('Recurring MRR Est.')"
            :value="$stats['currency'].' '.number_format($stats['recurring_mrr'] ?? 0, 2)"
            icon="o-arrow-path-rounded-square"
            color="text-warning"
        />
    </div>

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3 mb-6">
        <x-card>
            <div class="text-sm font-semibold mb-2">{{ __('Donation Growth') }}</div>
            <div class="grid grid-cols-1 gap-3">
                <div class="flex items-center justify-between">
                    <div class="text-xs text-base-content/60">{{ __('Last 7 days') }}</div>
                    <div class="text-sm font-semibold {{ $stats['growth_7'] !== null && $stats['growth_7'] >= 0 ? 'text-success' : 'text-error' }}">
                        {{ $stats['growth_7'] !== null ? $stats['growth_7'].'%' : '—' }}
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <div class="text-xs text-base-content/60">{{ __('Last 30 days') }}</div>
                    <div class="text-sm font-semibold {{ $stats['growth_30'] !== null && $stats['growth_30'] >= 0 ? 'text-success' : 'text-error' }}">
                        {{ $stats['growth_30'] !== null ? $stats['growth_30'].'%' : '—' }}
                    </div>
                </div>
            </div>
        </x-card>

        <x-card class="lg:col-span-2">
            <div class="text-sm font-semibold mb-2">{{ __('One-time vs Recurring') }}</div>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <div class="flex items-center justify-between text-xs text-base-content/60 mb-1">
                        <span>{{ __('One-time') }}</span>
                        <span>{{ $stats['one_time_pct'] }}%</span>
                    </div>
                    <progress max="100" value="{{ $stats['one_time_pct'] }}" class="progress progress-primary h-2 w-full"></progress>
                    <div class="mt-2 text-xs font-medium">
                        {{ $stats['currency'].' '.number_format($stats['one_time_paid'], 2) }}
                    </div>
                </div>
                <div>
                    <div class="flex items-center justify-between text-xs text-base-content/60 mb-1">
                        <span>{{ __('Recurring') }}</span>
                        <span>{{ $stats['recurring_pct'] }}%</span>
                    </div>
                    <progress max="100" value="{{ $stats['recurring_pct'] }}" class="progress progress-success h-2 w-full"></progress>
                    <div class="mt-2 text-xs font-medium">
                        {{ $stats['currency'].' '.number_format($stats['recurring_paid'], 2) }}
                    </div>
                </div>
            </div>
        </x-card>
    </div>

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        <x-card>
            <div class="text-sm font-semibold mb-3">{{ __('Top Campaigns by Raised') }}</div>
            <div class="space-y-3">
                @forelse($this->topCampaigns as $campaign)
                    @php
                        $raised = (float) ($campaign->paid_total ?? 0);
                        $goal = (float) ($campaign->goal_amount ?? 0);
                        $percent = $goal > 0 ? min(100, ($raised / $goal) * 100) : 0;
                    @endphp
                    <div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-medium text-base-content">{{ $campaign->title }}</span>
                            <span class="text-base-content/60">{{ $stats['currency'].' '.number_format($raised, 2) }}</span>
                        </div>
                        <progress max="100" value="{{ $percent }}" class="progress progress-primary h-2 w-full mt-2"></progress>
                    </div>
                @empty
                    <div class="text-xs text-base-content/60">{{ __('No campaigns yet.') }}</div>
                @endforelse
            </div>
        </x-card>

        <x-card>
            <div class="text-sm font-semibold mb-3">{{ __('Fundraising Goal Progress') }}</div>
            <div class="max-h-72 overflow-y-auto pr-1 space-y-3">
                @forelse($this->campaignGoals as $campaign)
                    @php
                        $raised = (float) ($campaign->paid_total ?? 0);
                        $goal = (float) ($campaign->goal_amount ?? 0);
                        $percent = $goal > 0 ? min(100, ($raised / $goal) * 100) : 0;
                    @endphp
                    <div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-medium text-base-content">{{ $campaign->title }}</span>
                            <span class="text-base-content/60">
                                {{ $stats['currency'].' '.number_format($raised, 2) }} / {{ $stats['currency'].' '.number_format($goal, 2) }}
                            </span>
                        </div>
                        <progress max="100" value="{{ $percent }}" class="progress progress-success h-2 w-full mt-2"></progress>
                    </div>
                @empty
                    <div class="text-xs text-base-content/60">{{ __('No campaigns yet.') }}</div>
                @endforelse
            </div>
        </x-card>
    </div>

    <x-card class="mt-6">
        <div class="flex items-center justify-between gap-3 mb-4">
            <div>
                <div class="text-sm font-semibold">{{ __('Campaign Performance') }}</div>
                <div class="text-xs text-base-content/60">{{ __('Health and momentum across campaigns') }}</div>
            </div>
        </div>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-stat
                :title="__('Active Campaigns')"
                :value="number_format($stats['campaign_active'])"
                icon="o-bolt"
                color="text-success"
            />
            <x-stat
                :title="__('Draft Campaigns')"
                :value="number_format($stats['campaign_draft'])"
                icon="o-document"
                color="text-warning"
            />
            <x-stat
                :title="__('Closed Campaigns')"
                :value="number_format($stats['campaign_closed'])"
                icon="o-lock-closed"
                color="text-error"
            />
            <x-stat
                :title="__('Ending Soon (7d)')"
                :value="number_format($stats['campaigns_ending_soon'])"
                icon="o-clock"
                color="text-info"
            />
            <x-stat
                :title="__('Launched This Month')"
                :value="number_format($stats['campaigns_launched_month'])"
                icon="o-rocket-launch"
                color="text-primary"
            />
            <x-stat
                :title="__('Avg Donation / Campaign')"
                :value="$stats['currency'].' '.number_format($stats['avg_donation_per_campaign'] ?? 0, 2)"
                icon="o-scale"
                color="text-secondary"
            />
            <x-stat
                :title="__('Campaigns w/ No Donations')"
                :value="number_format($stats['campaigns_no_donations'])"
                icon="o-exclamation-triangle"
                color="text-warning"
            />
            <x-card class="flex items-center justify-between">
                <div>
                    <div class="text-xs text-base-content/60">{{ __('Conversion') }}</div>
                    <div class="text-sm font-semibold">{{ __('N/A') }}</div>
                </div>
                <x-icon name="o-eye" class="w-6 h-6 text-base-content/40" />
            </x-card>
        </div>
    </x-card>

    <x-card class="mt-6">
        @php
            $avgPaymentSeconds = (int) round($stats['payment_avg_seconds'] ?? 0);
            $avgPaymentLabel = $avgPaymentSeconds > 0
                ? ($avgPaymentSeconds >= 60
                    ? number_format($avgPaymentSeconds / 60, 1).' '.__('min')
                    : $avgPaymentSeconds.' '.__('sec'))
                : '—';
        @endphp
        <div class="flex items-center justify-between gap-3 mb-4">
            <div>
                <div class="text-sm font-semibold">{{ __('Payments & Risk') }}</div>
                <div class="text-xs text-base-content/60">{{ __('Payment health, verification, and gateway mix') }}</div>
            </div>
        </div>
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
            <x-card>
                <div class="text-sm font-semibold mb-2">{{ __('Gateway Mix') }}</div>
                <div class="space-y-2 text-xs">
                    @forelse($stats['payment_gateway_counts'] as $gateway => $count)
                        <div class="flex items-center justify-between">
                            <span class="badge badge-ghost badge-sm uppercase">{{ $gateway }}</span>
                            <span class="font-medium">{{ number_format($count) }}</span>
                        </div>
                    @empty
                        <div class="text-xs text-base-content/60">{{ __('No payment attempts yet.') }}</div>
                    @endforelse
                </div>
            </x-card>

            <x-card>
                <div class="text-sm font-semibold mb-2">{{ __('Success & Failures') }}</div>
                <div class="space-y-3">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-base-content/60">{{ __('Success rate') }}</span>
                        <span class="font-semibold">{{ $stats['payment_success_rate'] }}%</span>
                    </div>
                    <progress max="100" value="{{ $stats['payment_success_rate'] }}" class="progress progress-success h-2 w-full"></progress>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-base-content/60">{{ __('Failed + Cancelled (7d)') }}</span>
                        <span class="font-semibold">{{ number_format($stats['payment_failed_cancelled_7']) }}</span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-base-content/60">{{ __('Trend vs prev 7d') }}</span>
                        <span class="font-semibold {{ $stats['payment_failed_cancelled_growth_7'] !== null && $stats['payment_failed_cancelled_growth_7'] <= 0 ? 'text-success' : 'text-error' }}">
                            {{ $stats['payment_failed_cancelled_growth_7'] !== null ? $stats['payment_failed_cancelled_growth_7'].'%' : '—' }}
                        </span>
                    </div>
                </div>
            </x-card>

            <x-card>
                <div class="text-sm font-semibold mb-2">{{ __('Manual Verification') }}</div>
                <div class="grid grid-cols-1 gap-3 text-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-base-content/60">{{ __('Pending') }}</span>
                        <span class="font-semibold">{{ number_format($stats['manual_proofs_pending']) }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-base-content/60">{{ __('Verified') }}</span>
                        <span class="font-semibold">{{ number_format($stats['manual_proofs_verified']) }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-base-content/60">{{ __('Rejected') }}</span>
                        <span class="font-semibold">{{ number_format($stats['manual_proofs_rejected']) }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-base-content/60">{{ __('Avg completion') }}</span>
                        <span class="font-semibold">{{ $avgPaymentLabel }}</span>
                    </div>
                </div>
            </x-card>
        </div>
    </x-card>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2 mt-6">
        <x-card class="relative overflow-hidden">
            <div class="absolute -right-10 -top-10 h-24 w-24 rounded-full bg-success/10 blur-2xl"></div>
            <div class="flex items-center justify-between gap-3 mb-4">
                <div>
                    <div class="text-sm font-semibold">{{ __('Receipts & Compliance') }}</div>
                    <div class="text-xs text-base-content/60">{{ __('Receipt issuance and coverage') }}</div>
                </div>
                <span class="badge badge-success badge-outline badge-sm">{{ __('Receipts') }}</span>
            </div>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <x-stat
                    :title="__('Issued')"
                    :value="number_format($stats['receipts_issued'])"
                    icon="o-document-check"
                    color="text-success"
                />
                <x-stat
                    :title="__('Issued This Month')"
                    :value="number_format($stats['receipts_issued_month'])"
                    icon="o-calendar-days"
                    color="text-info"
                />
                <x-stat
                    :title="__('Missing Receipts')"
                    :value="number_format($stats['receipts_missing'])"
                    icon="o-exclamation-circle"
                    color="text-warning"
                />
            </div>
        </x-card>

        <x-card class="relative overflow-hidden">
            <div class="absolute -left-10 -bottom-10 h-24 w-24 rounded-full bg-primary/10 blur-2xl"></div>
            <div class="flex items-center justify-between gap-3 mb-4">
                <div>
                    <div class="text-sm font-semibold">{{ __('Recurring Plans') }}</div>
                    <div class="text-xs text-base-content/60">{{ __('Status, schedules, and churn') }}</div>
                </div>
                <span class="badge badge-primary badge-outline badge-sm">{{ __('Recurring') }}</span>
            </div>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <x-stat
                    :title="__('Active')"
                    :value="number_format($stats['recurring_active'])"
                    icon="o-bolt"
                    color="text-success"
                />
                <x-stat
                    :title="__('Paused')"
                    :value="number_format($stats['recurring_paused'])"
                    icon="o-pause"
                    color="text-warning"
                />
                <x-stat
                    :title="__('Cancelled')"
                    :value="number_format($stats['recurring_cancelled'])"
                    icon="o-x-circle"
                    color="text-error"
                />
            </div>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 mt-4">
                <x-stat
                    :title="__('Upcoming (7d)')"
                    :value="number_format($stats['recurring_upcoming'])"
                    icon="o-clock"
                    color="text-info"
                />
                <x-stat
                    :title="__('Overdue')"
                    :value="number_format($stats['recurring_overdue'])"
                    icon="o-exclamation-triangle"
                    color="text-warning"
                />
                <x-card class="flex items-center justify-between">
                    <div>
                        <div class="text-xs text-base-content/60">{{ __('Churn (30d)') }}</div>
                        <div class="text-sm font-semibold">{{ $stats['recurring_churn_rate'] }}%</div>
                        <div class="text-xs text-base-content/50">{{ __(':count cancelled', ['count' => number_format($stats['recurring_churn_count'])]) }}</div>
                    </div>
                    <x-icon name="o-arrow-trending-down" class="w-6 h-6 text-base-content/40" />
                </x-card>
            </div>
        </x-card>
    </div>

    <x-card class="relative overflow-hidden mt-6">
        <div class="absolute -left-10 -top-10 h-28 w-28 rounded-full bg-amber-200/30 blur-2xl"></div>
        <div class="absolute -right-10 -bottom-10 h-28 w-28 rounded-full bg-rose-200/30 blur-2xl"></div>
        <div class="flex items-center justify-between gap-3 mb-4">
            <div>
                <div class="text-sm font-semibold">{{ __('Expenses') }}</div>
                <div class="text-xs text-base-content/60">{{ __('Spend tracking, burn rate, and allocations') }}</div>
            </div>
            <span class="badge badge-warning badge-outline badge-sm">{{ __('Spend') }}</span>
        </div>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <x-stat
                    :title="__('Total Expenses')"
                    :value="$stats['currency'].' '.number_format($stats['expenses_total'], 2)"
                    icon="o-banknotes"
                    color="text-warning"
                />
                <x-stat
                    :title="__('This Week')"
                    :value="$stats['currency'].' '.number_format($stats['expenses_week'], 2)"
                    icon="o-calendar"
                    color="text-info"
                />
                <x-stat
                    :title="__('This Month')"
                    :value="$stats['currency'].' '.number_format($stats['expenses_month'], 2)"
                    icon="o-calendar-days"
                    color="text-error"
                />
                <x-stat
                    :title="__('Average Expense')"
                    :value="$stats['currency'].' '.number_format($stats['expenses_avg'] ?? 0, 2)"
                    icon="o-scale"
                    color="text-secondary"
                />
            </div>

            <x-card>
                @php
                    $burnRate = $stats['expenses_burn_rate'];
                    $burnRateValue = $burnRate !== null ? min(100, max(0, $burnRate)) : 0;
                @endphp
                <div class="text-sm font-semibold mb-2">{{ __('Burn Rate (Month)') }}</div>
                <div class="flex items-center justify-between text-xs text-base-content/60 mb-2">
                    <span>{{ __('Expenses vs donations') }}</span>
                    <span class="font-semibold text-base-content">
                        {{ $burnRate !== null ? $burnRate.'%' : '—' }}
                    </span>
                </div>
                <progress max="100" value="{{ $burnRateValue }}" class="progress progress-warning h-2 w-full"></progress>
                <div class="mt-3 flex items-center justify-between text-xs">
                    <span class="text-base-content/60">{{ __('Uncategorized') }}</span>
                    <span class="font-semibold">{{ number_format($stats['expenses_uncategorized']) }}</span>
                </div>
            </x-card>

            <x-card>
                <div class="text-sm font-semibold mb-2">{{ __('Top Spending Campaigns') }}</div>
                <div class="space-y-3">
                    @forelse($this->topExpenseCampaigns as $campaign)
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-medium text-base-content">{{ $campaign->title }}</span>
                            <span class="text-base-content/60">
                                {{ $stats['currency'].' '.number_format((float) ($campaign->expenses_sum_amount ?? 0), 2) }}
                            </span>
                        </div>
                    @empty
                        <div class="text-xs text-base-content/60">{{ __('No expenses yet.') }}</div>
                    @endforelse
                </div>
            </x-card>
        </div>

        <div class="mt-5">
            <x-card>
                <div class="text-sm font-semibold mb-3">{{ __('Expenses by Category') }}</div>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    @forelse($this->expenseByCategory as $row)
                        <div class="flex items-center justify-between rounded-lg border border-base-200 px-3 py-2 text-xs">
                            <span class="font-medium text-base-content">{{ $row->name }}</span>
                            <span class="text-base-content/60">
                                {{ $stats['currency'].' '.number_format((float) $row->total, 2) }}
                            </span>
                        </div>
                    @empty
                        <div class="text-xs text-base-content/60">{{ __('No categorized expenses yet.') }}</div>
                    @endforelse
                </div>
            </x-card>
        </div>
    </x-card>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2 mt-6">
        <x-card class="relative overflow-hidden">
            <div class="absolute -right-12 -top-12 h-28 w-28 rounded-full bg-indigo-200/30 blur-2xl"></div>
            <div class="flex items-center justify-between gap-3 mb-4">
                <div>
                    <div class="text-sm font-semibold">{{ __('Content & Pages') }}</div>
                    <div class="text-xs text-base-content/60">{{ __('Publishing status and SEO health') }}</div>
                </div>
                <span class="badge badge-info badge-outline badge-sm">{{ __('Content') }}</span>
            </div>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <x-stat
                    :title="__('Published')"
                    :value="number_format($stats['pages_published'])"
                    icon="o-check-circle"
                    color="text-success"
                />
                <x-stat
                    :title="__('Drafts')"
                    :value="number_format($stats['pages_draft'])"
                    icon="o-document"
                    color="text-warning"
                />
                <x-stat
                    :title="__('Scheduled')"
                    :value="number_format($stats['pages_scheduled'])"
                    icon="o-clock"
                    color="text-info"
                />
                <x-stat
                    :title="__('Missing SEO')"
                    :value="number_format($stats['pages_missing_seo'])"
                    icon="o-exclamation-triangle"
                    color="text-error"
                />
            </div>
            <div class="mt-5">
                <div class="text-sm font-semibold mb-2">{{ __('Recently Updated') }}</div>
                <div class="space-y-2 text-xs">
                    @forelse($this->recentPages as $page)
                        <div class="flex items-center justify-between">
                            <span class="font-medium text-base-content">{{ $page->title }}</span>
                            <span class="badge badge-ghost badge-xs">{{ $page->status }}</span>
                        </div>
                    @empty
                        <div class="text-xs text-base-content/60">{{ __('No pages yet.') }}</div>
                    @endforelse
                </div>
            </div>
        </x-card>

        <x-card class="relative overflow-hidden">
            <div class="absolute -left-12 -bottom-12 h-28 w-28 rounded-full bg-emerald-200/30 blur-2xl"></div>
            <div class="flex items-center justify-between gap-3 mb-4">
                <div>
                    <div class="text-sm font-semibold">{{ __('Messaging & Support') }}</div>
                    <div class="text-xs text-base-content/60">{{ __('Conversations, unread messages, engagement') }}</div>
                </div>
                <span class="badge badge-success badge-outline badge-sm">{{ __('Support') }}</span>
            </div>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <x-stat
                    :title="__('Conversations')"
                    :value="number_format($stats['conversations_total'])"
                    icon="o-chat-bubble-left-right"
                    color="text-primary"
                />
                <x-stat
                    :title="__('Messages')"
                    :value="number_format($stats['messages_total'])"
                    icon="o-chat-bubble-left"
                    color="text-info"
                />
                <x-stat
                    :title="__('Unread Messages')"
                    :value="number_format($stats['messages_unread'])"
                    icon="o-envelope"
                    color="text-warning"
                />
                <x-stat
                    :title="__('Avg Msg/Conv')"
                    :value="number_format($stats['messages_avg_per_conversation'], 1)"
                    icon="o-chart-bar"
                    color="text-secondary"
                />
                <x-stat
                    :title="__('Reactions')"
                    :value="number_format($stats['reactions_total'])"
                    icon="o-heart"
                    color="text-error"
                />
            </div>
            <div class="mt-5">
                <div class="text-sm font-semibold mb-2">{{ __('Most Active Conversations') }}</div>
                <div class="space-y-2 text-xs">
                    @forelse($this->activeConversations as $conversation)
                        <div class="flex items-center justify-between">
                            <span class="font-medium text-base-content">{{ __('Conversation #') }}{{ $conversation->id }}</span>
                            <span class="text-base-content/60">{{ optional($conversation->last_message_at)->diffForHumans() }}</span>
                        </div>
                    @empty
                        <div class="text-xs text-base-content/60">{{ __('No recent conversations.') }}</div>
                    @endforelse
                </div>
            </div>
        </x-card>
    </div>

    <x-card class="relative overflow-hidden mt-6">
        <div class="absolute -right-12 -top-12 h-28 w-28 rounded-full bg-violet-200/30 blur-2xl"></div>
        <div class="flex items-center justify-between gap-3 mb-4">
            <div>
                <div class="text-sm font-semibold">{{ __('AI Chat') }}</div>
                <div class="text-xs text-base-content/60">{{ __('Usage, activity, and provider mix') }}</div>
            </div>
            <span class="badge badge-secondary badge-outline badge-sm">{{ __('AI') }}</span>
        </div>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-stat
                :title="__('Conversations')"
                :value="number_format($stats['ai_conversations_total'])"
                icon="o-cpu-chip"
                color="text-secondary"
            />
            <x-stat
                :title="__('Active (7d)')"
                :value="number_format($stats['ai_conversations_active_7d'])"
                icon="o-bolt"
                color="text-info"
            />
            <x-stat
                :title="__('Tokens Total')"
                :value="number_format($stats['ai_tokens_total'])"
                icon="o-chart-bar"
                color="text-primary"
            />
            <x-stat
                :title="__('Tokens 7d')"
                :value="number_format($stats['ai_tokens_7d'])"
                icon="o-calendar"
                color="text-warning"
            />
        </div>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 mt-5">
            <x-card class="flex items-center justify-between">
                <div>
                    <div class="text-xs text-base-content/60">{{ __('Top Provider') }}</div>
                    <div class="text-sm font-semibold">{{ $stats['ai_top_provider'] ?? '—' }}</div>
                </div>
                <x-icon name="o-sparkles" class="w-6 h-6 text-base-content/40" />
            </x-card>
            <x-card class="flex items-center justify-between">
                <div>
                    <div class="text-xs text-base-content/60">{{ __('Top Model') }}</div>
                    <div class="text-sm font-semibold">{{ $stats['ai_top_model'] ?? '—' }}</div>
                </div>
                <x-icon name="o-beaker" class="w-6 h-6 text-base-content/40" />
            </x-card>
        </div>
    </x-card>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2 mt-6">
        <x-card class="relative overflow-hidden">
            <div class="absolute -left-12 -top-12 h-28 w-28 rounded-full bg-emerald-200/30 blur-2xl"></div>
            <div class="flex items-center justify-between gap-3 mb-4">
                <div>
                    <div class="text-sm font-semibold">{{ __('Net Impact') }}</div>
                    <div class="text-xs text-base-content/60">{{ __('Balance and contribution impact') }}</div>
                </div>
                <span class="badge badge-success badge-outline badge-sm">{{ __('Impact') }}</span>
            </div>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <x-stat
                    :title="__('Net Balance')"
                    :value="$stats['currency'].' '.number_format($stats['net_balance'], 2)"
                    icon="o-chart-pie"
                    color="{{ $stats['net_balance'] >= 0 ? 'text-success' : 'text-error' }}"
                />
                <x-stat
                    :title="__('Contributions Total')"
                    :value="$stats['currency'].' '.number_format($stats['contributions_total'], 2)"
                    icon="o-hand-raised"
                    color="text-primary"
                />
            </div>
            <div class="mt-5">
                <div class="text-sm font-semibold mb-2">{{ __('Net Balance by Campaign') }}</div>
                <div class="max-h-72 overflow-y-auto pr-1 space-y-3 text-xs">
                    @forelse($this->netBalanceByCampaign as $campaign)
                        @php
                            $paid = (float) ($campaign->paid_total ?? 0);
                            $spent = (float) ($campaign->expenses_sum_amount ?? 0);
                            $net = $paid - $spent;
                        @endphp
                        <div class="flex items-center justify-between">
                            <span class="font-medium text-base-content">{{ $campaign->title }}</span>
                            <span class="font-semibold {{ $net >= 0 ? 'text-success' : 'text-error' }}">
                                {{ $stats['currency'].' '.number_format($net, 2) }}
                            </span>
                        </div>
                    @empty
                        <div class="text-xs text-base-content/60">{{ __('No campaigns yet.') }}</div>
                    @endforelse
                </div>
            </div>
        </x-card>

        <x-card class="relative overflow-hidden">
            <div class="absolute -right-12 -bottom-12 h-28 w-28 rounded-full bg-sky-200/30 blur-2xl"></div>
            <div class="flex items-center justify-between gap-3 mb-4">
                <div>
                    <div class="text-sm font-semibold">{{ __('Users & Community') }}</div>
                    <div class="text-xs text-base-content/60">{{ __('Growth, activity, and profile health') }}</div>
                </div>
                <span class="badge badge-info badge-outline badge-sm">{{ __('Community') }}</span>
            </div>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <x-stat
                    :title="__('Total Users')"
                    :value="number_format($stats['users_total'])"
                    icon="o-users"
                    color="text-primary"
                />
                <x-stat
                    :title="__('New Users (7d)')"
                    :value="number_format($stats['users_new_week'])"
                    icon="o-user-plus"
                    color="text-success"
                />
                <x-stat
                    :title="__('Active Users (7d)')"
                    :value="number_format($stats['users_active_7d'])"
                    icon="o-bolt"
                    color="text-warning"
                />
            </div>
            <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2">
                <x-card>
                    <div class="text-xs text-base-content/60 mb-2">{{ __('Email Verified Rate') }}</div>
                    <div class="flex items-center justify-between text-sm font-semibold mb-2">
                        <span>{{ $stats['email_verified_rate'] }}%</span>
                        <span class="text-xs text-base-content/50">{{ __('of users') }}</span>
                    </div>
                    <progress max="100" value="{{ $stats['email_verified_rate'] }}" class="progress progress-success h-2 w-full"></progress>
                </x-card>
                <x-card>
                    <div class="text-xs text-base-content/60 mb-2">{{ __('Profile Completion Rate') }}</div>
                    <div class="flex items-center justify-between text-sm font-semibold mb-2">
                        <span>{{ $stats['profile_completion_rate'] }}%</span>
                        <span class="text-xs text-base-content/50">{{ __('with details') }}</span>
                    </div>
                    <progress max="100" value="{{ $stats['profile_completion_rate'] }}" class="progress progress-info h-2 w-full"></progress>
                </x-card>
            </div>
        </x-card>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2 mt-6">
        <x-card class="relative overflow-hidden">
            <div class="absolute -right-12 -top-12 h-28 w-28 rounded-full bg-blue-200/30 blur-2xl"></div>
            <div class="flex items-center justify-between gap-3 mb-4">
                <div>
                    <div class="text-sm font-semibold">{{ __('Notifications') }}</div>
                    <div class="text-xs text-base-content/60">{{ __('Delivery coverage and unread backlog') }}</div>
                </div>
                <span class="badge badge-info badge-outline badge-sm">{{ __('Comms') }}</span>
            </div>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <x-stat
                    :title="__('Sent')"
                    :value="number_format($stats['notifications_total'])"
                    icon="o-bell"
                    color="text-primary"
                />
                <x-stat
                    :title="__('Unread')"
                    :value="number_format($stats['notifications_unread'])"
                    icon="o-envelope"
                    color="text-warning"
                />
                <x-card>
                    <div class="text-xs text-base-content/60 mb-2">{{ __('Preference Coverage') }}</div>
                    <div class="flex items-center justify-between text-sm font-semibold mb-2">
                        <span>{{ $stats['notification_pref_coverage'] }}%</span>
                        <span class="text-xs text-base-content/50">{{ __('of users') }}</span>
                    </div>
                    <progress max="100" value="{{ $stats['notification_pref_coverage'] }}" class="progress progress-info h-2 w-full"></progress>
                </x-card>
                <x-card class="space-y-2">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-base-content/60">{{ __('Push Subscriptions') }}</span>
                        <span class="font-semibold">{{ number_format($stats['push_subscriptions']) }}</span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-base-content/60">{{ __('Guest Subscriptions') }}</span>
                        <span class="font-semibold">{{ number_format($stats['guest_subscriptions']) }}</span>
                    </div>
                </x-card>
            </div>
        </x-card>

        <x-card class="relative overflow-hidden">
            <div class="absolute -left-12 -bottom-12 h-28 w-28 rounded-full bg-amber-200/30 blur-2xl"></div>
            <div class="flex items-center justify-between gap-3 mb-4">
                <div>
                    <div class="text-sm font-semibold">{{ __('Operations & Safety') }}</div>
                    <div class="text-xs text-base-content/60">{{ __('Backup health and storage trend') }}</div>
                </div>
                <span class="badge badge-warning badge-outline badge-sm">{{ __('Ops') }}</span>
            </div>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <x-card>
                    <div class="text-xs text-base-content/60 mb-2">{{ __('Backup Success Rate') }}</div>
                    <div class="flex items-center justify-between text-sm font-semibold mb-2">
                        <span>{{ $stats['backup_success_rate'] }}%</span>
                        <span class="text-xs text-base-content/50">{{ __('overall') }}</span>
                    </div>
                    <progress max="100" value="{{ $stats['backup_success_rate'] }}" class="progress progress-success h-2 w-full"></progress>
                </x-card>
                <x-card>
                    <div class="text-xs text-base-content/60 mb-2">{{ __('Last Successful Backup') }}</div>
                    <div class="text-sm font-semibold">
                        {{ $stats['backup_last_success_at'] ? \Carbon\Carbon::parse($stats['backup_last_success_at'])->diffForHumans() : '—' }}
                    </div>
                    <div class="text-xs text-base-content/50 mt-2">
                        {{ __('Failed backups: :count', ['count' => number_format($stats['backup_failed_count'])]) }}
                    </div>
                </x-card>
                <x-card class="sm:col-span-2">
                    <div class="flex items-center justify-between text-xs text-base-content/60 mb-2">
                        <span>{{ __('Backup Storage Total') }}</span>
                        <span class="font-semibold text-base-content">{{ $formatBytes($stats['backup_storage_total']) }}</span>
                    </div>
                    <div class="grid grid-cols-3 gap-2">
                        @forelse($this->backupSizeTrend as $backup)
                            <div class="rounded-lg border border-base-200 px-2 py-2 text-[11px] text-base-content/70">
                                <div class="font-semibold text-base-content">
                                    {{ $formatBytes($backup->file_size) }}
                                </div>
                                <div>{{ optional($backup->completed_at)->format('M d') }}</div>
                            </div>
                        @empty
                            <div class="text-xs text-base-content/60">{{ __('No backup history yet.') }}</div>
                        @endforelse
                    </div>
                </x-card>
            </div>
            <div class="mt-4">
                <div class="text-sm font-semibold mb-2">{{ __('Failed Backup Alerts') }}</div>
                <div class="space-y-2 text-xs">
                    @forelse($this->failedBackups as $backup)
                        <div class="flex items-center justify-between gap-2">
                            <span class="font-medium text-base-content">{{ __('Backup #') }}{{ $backup->id }}</span>
                            <span class="text-base-content/60 truncate max-w-[12rem]">{{ $backup->error_message ?: __('Unknown error') }}</span>
                        </div>
                    @empty
                        <div class="text-xs text-base-content/60">{{ __('No failed backups.') }}</div>
                    @endforelse
                </div>
            </div>
        </x-card>
    </div>

    <x-card class="relative overflow-hidden mt-6">
        <div class="absolute -right-12 -top-12 h-28 w-28 rounded-full bg-slate-200/30 blur-2xl"></div>
        <div class="flex items-center justify-between gap-3 mb-4">
            <div>
                <div class="text-sm font-semibold">{{ __('Media & Assets') }}</div>
                <div class="text-xs text-base-content/60">{{ __('Storage usage and recent uploads') }}</div>
            </div>
            <span class="badge badge-ghost badge-sm">{{ __('Assets') }}</span>
        </div>
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <x-stat
                    :title="__('Total Media Items')"
                    :value="number_format($stats['media_total_items'])"
                    icon="o-photo"
                    color="text-primary"
                />
                <x-stat
                    :title="__('Storage Used')"
                    :value="$formatBytes($stats['media_total_size'])"
                    icon="o-circle-stack"
                    color="text-info"
                />
            </div>
            <x-card>
                <div class="text-sm font-semibold mb-2">{{ __('Largest Collections') }}</div>
                <div class="space-y-2 text-xs">
                    @forelse($this->mediaCollectionsTop as $collection)
                        <div class="flex items-center justify-between">
                            <span class="badge badge-ghost badge-xs">{{ $collection->collection_name }}</span>
                            <span class="text-base-content/60">
                                {{ $formatBytes($collection->total_size) }}
                            </span>
                        </div>
                    @empty
                        <div class="text-xs text-base-content/60">{{ __('No media yet.') }}</div>
                    @endforelse
                </div>
            </x-card>
            <x-card>
                <div class="text-sm font-semibold mb-2">{{ __('Recent Uploads') }}</div>
                <div class="space-y-2 text-xs">
                    @forelse($this->recentMedia as $media)
                        <div class="flex items-center justify-between">
                            <span class="font-medium text-base-content">{{ $media->name }}</span>
                            <span class="text-base-content/60">{{ $formatBytes($media->size) }}</span>
                        </div>
                    @empty
                        <div class="text-xs text-base-content/60">{{ __('No uploads yet.') }}</div>
                    @endforelse
                </div>
            </x-card>
        </div>
    </x-card>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2 mt-6">
        <x-card class="relative overflow-hidden">
            <div class="absolute -right-12 -top-12 h-28 w-28 rounded-full bg-rose-200/30 blur-2xl"></div>
            <div class="flex items-center justify-between gap-3 mb-4">
                <div>
                    <div class="text-sm font-semibold">{{ __('Activity & Audit') }}</div>
                    <div class="text-xs text-base-content/60">{{ __('Recent admin actions and trends') }}</div>
                </div>
                <span class="badge badge-error badge-outline badge-sm">{{ __('Audit') }}</span>
            </div>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <x-stat
                    :title="__('Total Activities')"
                    :value="number_format($stats['activity_total'])"
                    icon="o-document-text"
                    color="text-primary"
                />
                <x-card>
                    <div class="text-xs text-base-content/60 mb-2">{{ __('Activity Spike (7d)') }}</div>
                    <div class="flex items-center justify-between text-sm font-semibold">
                        <span class="{{ $stats['activity_growth_7'] !== null && $stats['activity_growth_7'] >= 0 ? 'text-success' : 'text-error' }}">
                            {{ $stats['activity_growth_7'] !== null ? $stats['activity_growth_7'].'%' : '—' }}
                        </span>
                        <span class="text-xs text-base-content/50">{{ __('vs prev 7d') }}</span>
                    </div>
                </x-card>
            </div>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 mt-4">
                <x-card>
                    <div class="text-sm font-semibold mb-2">{{ __('Top Events') }}</div>
                    <div class="space-y-2 text-xs">
                        @forelse($this->activityByEvent as $event)
                            <div class="flex items-center justify-between">
                                <span class="badge badge-ghost badge-xs">{{ $event->event }}</span>
                                <span class="font-medium">{{ number_format($event->total) }}</span>
                            </div>
                        @empty
                            <div class="text-xs text-base-content/60">{{ __('No events yet.') }}</div>
                        @endforelse
                    </div>
                </x-card>
                <x-card>
                    <div class="text-sm font-semibold mb-2">{{ __('Top Log Types') }}</div>
                    <div class="space-y-2 text-xs">
                        @forelse($this->activityByLog as $log)
                            <div class="flex items-center justify-between">
                                <span class="badge badge-ghost badge-xs">{{ $log->log_name }}</span>
                                <span class="font-medium">{{ number_format($log->total) }}</span>
                            </div>
                        @empty
                            <div class="text-xs text-base-content/60">{{ __('No logs yet.') }}</div>
                        @endforelse
                    </div>
                </x-card>
            </div>
            <div class="mt-4">
                <div class="text-sm font-semibold mb-2">{{ __('Recent Activity') }}</div>
                <div class="space-y-2 text-xs">
                    @forelse($this->recentActivities as $activity)
                        <div class="flex items-center justify-between gap-2">
                            <span class="font-medium text-base-content truncate">{{ $activity->description }}</span>
                            <span class="text-base-content/60">{{ optional($activity->created_at)->diffForHumans() }}</span>
                        </div>
                    @empty
                        <div class="text-xs text-base-content/60">{{ __('No activity yet.') }}</div>
                    @endforelse
                </div>
            </div>
        </x-card>

        <x-card class="relative overflow-hidden">
            <div class="absolute -left-12 -bottom-12 h-28 w-28 rounded-full bg-teal-200/30 blur-2xl"></div>
            <div class="flex items-center justify-between gap-3 mb-4">
                <div>
                    <div class="text-sm font-semibold">{{ __('Geography') }}</div>
                    <div class="text-xs text-base-content/60">{{ __('User distribution and regional performance') }}</div>
                </div>
                <span class="badge badge-success badge-outline badge-sm">{{ __('Region') }}</span>
            </div>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <x-card>
                    <div class="text-xs text-base-content/60 mb-2">{{ __('Users with Region Data') }}</div>
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <div class="flex items-center justify-between">
                            <span>{{ __('Division') }}</span>
                            <span class="font-semibold">{{ number_format($stats['users_by_division_count']) }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span>{{ __('District') }}</span>
                            <span class="font-semibold">{{ number_format($stats['users_by_district_count']) }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span>{{ __('Upazila') }}</span>
                            <span class="font-semibold">{{ number_format($stats['users_by_upazila_count']) }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span>{{ __('Union') }}</span>
                            <span class="font-semibold">{{ number_format($stats['users_by_union_count']) }}</span>
                        </div>
                    </div>
                </x-card>
                <x-card>
                    <div class="text-sm font-semibold mb-2">{{ __('Top Divisions (Users)') }}</div>
                    <div class="space-y-2 text-xs">
                        @forelse($this->usersByDivision as $row)
                            <div class="flex items-center justify-between">
                                <span class="font-medium text-base-content">{{ $row->name }}</span>
                                <span class="text-base-content/60">{{ number_format($row->total) }}</span>
                            </div>
                        @empty
                            <div class="text-xs text-base-content/60">{{ __('No data yet.') }}</div>
                        @endforelse
                    </div>
                </x-card>
            </div>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 mt-4">
                <x-card>
                    <div class="text-sm font-semibold mb-2">{{ __('Top Districts (Users)') }}</div>
                    <div class="space-y-2 text-xs">
                        @forelse($this->usersByDistrict as $row)
                            <div class="flex items-center justify-between">
                                <span class="font-medium text-base-content">{{ $row->name }}</span>
                                <span class="text-base-content/60">{{ number_format($row->total) }}</span>
                            </div>
                        @empty
                            <div class="text-xs text-base-content/60">{{ __('No data yet.') }}</div>
                        @endforelse
                    </div>
                </x-card>
                <x-card>
                    <div class="text-sm font-semibold mb-2">{{ __('Top Upazilas (Users)') }}</div>
                    <div class="space-y-2 text-xs">
                        @forelse($this->usersByUpazila as $row)
                            <div class="flex items-center justify-between">
                                <span class="font-medium text-base-content">{{ $row->name }}</span>
                                <span class="text-base-content/60">{{ number_format($row->total) }}</span>
                            </div>
                        @empty
                            <div class="text-xs text-base-content/60">{{ __('No data yet.') }}</div>
                        @endforelse
                    </div>
                </x-card>
            </div>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 mt-4">
                <x-card>
                    <div class="text-sm font-semibold mb-2">{{ __('Top Unions (Users)') }}</div>
                    <div class="space-y-2 text-xs">
                        @forelse($this->usersByUnion as $row)
                            <div class="flex items-center justify-between">
                                <span class="font-medium text-base-content">{{ $row->name }}</span>
                                <span class="text-base-content/60">{{ number_format($row->total) }}</span>
                            </div>
                        @empty
                            <div class="text-xs text-base-content/60">{{ __('No data yet.') }}</div>
                        @endforelse
                    </div>
                </x-card>
                <x-card>
                    <div class="text-sm font-semibold mb-2">{{ __('Donations by Division') }}</div>
                    <div class="space-y-2 text-xs">
                        @forelse($this->donationsByDivision as $row)
                            <div class="flex items-center justify-between">
                                <span class="font-medium text-base-content">{{ $row->name }}</span>
                                <span class="text-base-content/60">{{ $stats['currency'].' '.number_format((float) $row->total, 2) }}</span>
                            </div>
                        @empty
                            <div class="text-xs text-base-content/60">{{ __('No regional donations yet.') }}</div>
                        @endforelse
                    </div>
                </x-card>
            </div>
            <div class="mt-4">
                <x-card>
                    <div class="text-sm font-semibold mb-2">{{ __('Campaign Performance by Division') }}</div>
                    <div class="space-y-2 text-xs">
                        @forelse($this->campaignNetByDivision as $row)
                            <div class="flex items-center justify-between">
                                <span class="font-medium text-base-content">{{ $row->name }}</span>
                                <span class="text-base-content/60">{{ $stats['currency'].' '.number_format((float) $row->total, 2) }}</span>
                            </div>
                        @empty
                            <div class="text-xs text-base-content/60">{{ __('No regional performance yet.') }}</div>
                        @endforelse
                    </div>
                </x-card>
            </div>
        </x-card>
    </div>
</div>
