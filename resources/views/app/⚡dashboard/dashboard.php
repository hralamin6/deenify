<?php

use App\Models\Activity;
use App\Models\AiConversation;
use App\Models\AiMessage;
use App\Models\Backup;
use App\Models\Campaign;
use App\Models\Contribution;
use App\Models\Conversation;
use App\Models\Donation;
use App\Models\DonationReceipt;
use App\Models\Expense;
use App\Models\GuestSubscription;
use App\Models\ManualPaymentProof;
use App\Models\Message;
use App\Models\MessageReaction;
use App\Models\NotificationPreference;
use App\Models\Page;
use App\Models\PaymentAttempt;
use App\Models\RecurringPlan;
use App\Models\User;
use App\Models\UserDetail;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Dashboard')] #[Layout('layouts.app')] class extends Component
{
    public function mount(): void
    {
        $this->authorize('dashboard.view');
    }

    #[Computed]
    public function stats(): array
    {
        $now = now();
        $paidBaseQuery = Donation::query()->where('status', 'paid');

        $todayStart = $now->copy()->startOfDay();
        $todayEnd = $now->copy()->endOfDay();
        $weekStart = $now->copy()->startOfWeek();
        $monthStart = $now->copy()->startOfMonth();
        $monthEnd = $now->copy()->endOfMonth();

        $totalPaid = (clone $paidBaseQuery)->sum('amount');
        $todayPaid = (clone $paidBaseQuery)->whereBetween('paid_at', [$todayStart, $todayEnd])->sum('amount');
        $weekPaid = (clone $paidBaseQuery)->whereBetween('paid_at', [$weekStart, $todayEnd])->sum('amount');
        $monthPaid = (clone $paidBaseQuery)->whereBetween('paid_at', [$monthStart, $todayEnd])->sum('amount');
        $avgPaid = (clone $paidBaseQuery)->avg('amount');

        $donorKey = "COALESCE(donor_email, CONCAT('user:', user_id), CONCAT('name:', donor_name))";
        $uniqueDonors = DB::table('donations')
            ->where('status', 'paid')
            ->selectRaw("COUNT(DISTINCT {$donorKey}) as aggregate")
            ->value('aggregate');

        $firstPaidSubquery = DB::table('donations')
            ->selectRaw("{$donorKey} as donor_key, MIN(paid_at) as first_paid_at")
            ->where('status', 'paid')
            ->groupBy('donor_key');

        $newDonorsThisMonth = DB::query()
            ->fromSub($firstPaidSubquery, 'first_paid')
            ->whereBetween('first_paid_at', [$monthStart, $monthEnd])
            ->count();

        $recurringMrr = RecurringPlan::query()
            ->where('status', 'active')
            ->selectRaw("SUM(CASE WHEN `interval` = 'weekly' THEN amount * 4.33 ELSE amount END) as aggregate")
            ->value('aggregate');

        $oneTimePaid = (clone $paidBaseQuery)->whereNull('recurring_plan_id')->sum('amount');
        $recurringPaid = (clone $paidBaseQuery)->whereNotNull('recurring_plan_id')->sum('amount');
        $totalSplit = $oneTimePaid + $recurringPaid;
        $oneTimePct = $totalSplit > 0 ? round(($oneTimePaid / $totalSplit) * 100, 1) : 0;
        $recurringPct = $totalSplit > 0 ? round(($recurringPaid / $totalSplit) * 100, 1) : 0;

        $current7Start = $now->copy()->subDays(6)->startOfDay();
        $previous7Start = $now->copy()->subDays(13)->startOfDay();
        $previous7End = $now->copy()->subDays(7)->endOfDay();
        $current7 = (clone $paidBaseQuery)->whereBetween('paid_at', [$current7Start, $todayEnd])->sum('amount');
        $previous7 = (clone $paidBaseQuery)->whereBetween('paid_at', [$previous7Start, $previous7End])->sum('amount');
        $growth7 = $this->calculateGrowth($current7, $previous7);

        $current30Start = $now->copy()->subDays(29)->startOfDay();
        $previous30Start = $now->copy()->subDays(59)->startOfDay();
        $previous30End = $now->copy()->subDays(30)->endOfDay();
        $current30 = (clone $paidBaseQuery)->whereBetween('paid_at', [$current30Start, $todayEnd])->sum('amount');
        $previous30 = (clone $paidBaseQuery)->whereBetween('paid_at', [$previous30Start, $previous30End])->sum('amount');
        $growth30 = $this->calculateGrowth($current30, $previous30);

        $expenseDateColumn = DB::raw('COALESCE(spent_at, created_at)');
        $expensesTotal = Expense::query()->sum('amount');
        $expensesWeek = Expense::query()
            ->whereBetween($expenseDateColumn, [$weekStart, $todayEnd])
            ->sum('amount');
        $expensesMonth = Expense::query()
            ->whereBetween($expenseDateColumn, [$monthStart, $todayEnd])
            ->sum('amount');
        $expensesAvg = Expense::query()->avg('amount');
        $burnRateMonth = $monthPaid > 0 ? round(($expensesMonth / $monthPaid) * 100, 1) : null;
        $uncategorizedExpenses = Expense::query()->whereNull('expense_category_id')->count();

        $campaignCounts = Campaign::query()
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $endingSoonCount = Campaign::query()
            ->whereNotNull('ends_at')
            ->whereBetween('ends_at', [$now, $now->copy()->addDays(7)])
            ->count();

        $launchedThisMonth = Campaign::query()
            ->whereNotNull('starts_at')
            ->whereBetween('starts_at', [$monthStart, $monthEnd])
            ->count();

        $avgDonationPerCampaign = Campaign::query()
            ->leftJoin('donations', function ($join) {
                $join->on('campaigns.id', '=', 'donations.campaign_id')
                    ->where('donations.status', 'paid');
            })
            ->selectRaw('AVG(donations.amount) as average')
            ->value('average');

        $campaignsWithNoDonations = Campaign::query()
            ->leftJoin('donations', function ($join) {
                $join->on('campaigns.id', '=', 'donations.campaign_id')
                    ->where('donations.status', 'paid');
            })
            ->whereNull('donations.id')
            ->distinct('campaigns.id')
            ->count('campaigns.id');

        $paymentGatewayCounts = PaymentAttempt::query()
            ->select('gateway', DB::raw('count(*) as total'))
            ->groupBy('gateway')
            ->pluck('total', 'gateway');

        $paymentAttemptsTotal = PaymentAttempt::query()->count();
        $paymentAttemptsSuccess = PaymentAttempt::query()->where('status', 'success')->count();
        $paymentSuccessRate = $paymentAttemptsTotal > 0
            ? round(($paymentAttemptsSuccess / $paymentAttemptsTotal) * 100, 1)
            : 0;

        $pendingManualProofs = ManualPaymentProof::query()
            ->where('verification_status', 'pending')
            ->count();
        $verifiedManualProofs = ManualPaymentProof::query()
            ->where('verification_status', 'verified')
            ->count();
        $rejectedManualProofs = ManualPaymentProof::query()
            ->where('verification_status', 'rejected')
            ->count();

        $failedCancelledCurrent7 = PaymentAttempt::query()
            ->whereIn('status', ['failed', 'cancelled'])
            ->whereBetween('created_at', [$current7Start, $todayEnd])
            ->count();
        $failedCancelledPrevious7 = PaymentAttempt::query()
            ->whereIn('status', ['failed', 'cancelled'])
            ->whereBetween('created_at', [$previous7Start, $previous7End])
            ->count();
        $failedCancelledGrowth = $this->calculateGrowth($failedCancelledCurrent7, $failedCancelledPrevious7);

        $avgPaymentSeconds = PaymentAttempt::query()
            ->whereNotNull('initiated_at')
            ->whereNotNull('completed_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, initiated_at, completed_at)) as average')
            ->value('average');

        $receiptsIssued = DonationReceipt::query()->count();
        $receiptsIssuedThisMonth = DonationReceipt::query()
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->count();
        $missingReceipts = Donation::query()
            ->where('status', 'paid')
            ->whereDoesntHave('receipt')
            ->count();

        $recurringCounts = RecurringPlan::query()
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $recurringUpcoming = RecurringPlan::query()
            ->where('status', 'active')
            ->whereNotNull('next_run_at')
            ->whereBetween('next_run_at', [$now, $now->copy()->addDays(7)])
            ->count();

        $recurringOverdue = RecurringPlan::query()
            ->where('status', 'active')
            ->whereNotNull('next_run_at')
            ->where('next_run_at', '<', $now)
            ->count();

        $recurringCancelled30 = RecurringPlan::query()
            ->where('status', 'cancelled')
            ->whereBetween('updated_at', [$now->copy()->subDays(30)->startOfDay(), $todayEnd])
            ->count();
        $recurringTotal = RecurringPlan::query()->count();
        $recurringChurnRate = $recurringTotal > 0
            ? round(($recurringCancelled30 / $recurringTotal) * 100, 1)
            : 0;

        $pageCounts = Page::query()
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');
        $pagesScheduled = Page::query()
            ->whereNotNull('published_at')
            ->where('published_at', '>', $now)
            ->count();
        $pagesMissingSeo = Page::query()
            ->where(function ($query) {
                $query->whereNull('meta_title')
                    ->orWhereNull('meta_description');
            })
            ->count();

        $conversationTotal = Conversation::query()->count();
        $messageTotal = Message::query()->count();
        $unreadMessages = Message::query()->whereNull('read_at')->count();
        $avgMessagesPerConversation = $conversationTotal > 0
            ? round($messageTotal / $conversationTotal, 1)
            : 0;
        $reactionCount = MessageReaction::query()->count();

        $aiConversations = AiConversation::query()->count();
        $aiActiveConversations = AiConversation::query()
            ->whereNotNull('last_message_at')
            ->whereBetween('last_message_at', [$now->copy()->subDays(7)->startOfDay(), $todayEnd])
            ->count();
        $aiTokensTotal = AiMessage::query()->sum('tokens');
        $aiTokens7d = AiMessage::query()
            ->whereBetween('created_at', [$current7Start, $todayEnd])
            ->sum('tokens');
        $aiTopProvider = AiConversation::query()
            ->select('ai_provider', DB::raw('count(*) as total'))
            ->groupBy('ai_provider')
            ->orderByDesc('total')
            ->value('ai_provider');
        $aiTopModel = AiConversation::query()
            ->select('model', DB::raw('count(*) as total'))
            ->whereNotNull('model')
            ->groupBy('model')
            ->orderByDesc('total')
            ->value('model');

        $netBalance = $totalPaid - $expensesTotal;
        $contributionsTotal = Contribution::query()->sum('amount');

        $usersTotal = User::query()->count();
        $usersNewWeek = User::query()
            ->whereBetween('created_at', [$weekStart, $todayEnd])
            ->count();
        $activeUsers = DB::table('sessions')
            ->whereNotNull('user_id')
            ->where('last_activity', '>=', $now->copy()->subDays(7)->timestamp)
            ->distinct('user_id')
            ->count('user_id');
        $emailVerifiedCount = User::query()->whereNotNull('email_verified_at')->count();
        $emailVerifiedRate = $usersTotal > 0
            ? round(($emailVerifiedCount / $usersTotal) * 100, 1)
            : 0;
        $profileCompletedCount = UserDetail::query()->distinct('user_id')->count('user_id');
        $profileCompletionRate = $usersTotal > 0
            ? round(($profileCompletedCount / $usersTotal) * 100, 1)
            : 0;

        $notificationsTotal = DB::table('notifications')->count();
        $notificationsUnread = DB::table('notifications')->whereNull('read_at')->count();
        $notificationPrefUsers = NotificationPreference::query()
            ->distinct('user_id')
            ->count('user_id');
        $notificationPrefCoverage = $usersTotal > 0
            ? round(($notificationPrefUsers / $usersTotal) * 100, 1)
            : 0;

        $pushTable = config('webpush.table_name', 'push_subscriptions');
        $pushConnection = config('webpush.database_connection');
        $pushSubscriptions = DB::connection($pushConnection)->table($pushTable)->count();
        $guestSubscriptions = GuestSubscription::query()->count();

        $backupTotal = Backup::query()->count();
        $backupCompleted = Backup::query()->completed()->count();
        $backupSuccessRate = $backupTotal > 0
            ? round(($backupCompleted / $backupTotal) * 100, 1)
            : 0;
        $lastSuccessfulBackup = Backup::query()->completed()->latest('completed_at')->value('completed_at');
        $backupFailedCount = Backup::query()->failed()->count();
        $backupStorageTotal = Backup::query()->completed()->sum('file_size');

        $mediaTotalItems = DB::table('media')->count();
        $mediaTotalSize = DB::table('media')->sum('size');

        $activityTotal = Activity::query()->count();
        $activityCurrent7 = Activity::query()
            ->whereBetween('created_at', [$current7Start, $todayEnd])
            ->count();
        $activityPrevious7 = Activity::query()
            ->whereBetween('created_at', [$previous7Start, $previous7End])
            ->count();
        $activityGrowth7 = $this->calculateGrowth($activityCurrent7, $activityPrevious7);

        $usersByDivision = UserDetail::query()->whereNotNull('division_id')->distinct('user_id')->count('user_id');
        $usersByDistrict = UserDetail::query()->whereNotNull('district_id')->distinct('user_id')->count('user_id');
        $usersByUpazila = UserDetail::query()->whereNotNull('upazila_id')->distinct('user_id')->count('user_id');
        $usersByUnion = UserDetail::query()->whereNotNull('union_id')->distinct('user_id')->count('user_id');

        return [
            'currency' => setting('currency', 'BDT'),
            'total_paid' => $totalPaid,
            'today_paid' => $todayPaid,
            'week_paid' => $weekPaid,
            'month_paid' => $monthPaid,
            'avg_paid' => $avgPaid,
            'unique_donors' => $uniqueDonors,
            'new_donors_month' => $newDonorsThisMonth,
            'recurring_mrr' => $recurringMrr,
            'one_time_paid' => $oneTimePaid,
            'recurring_paid' => $recurringPaid,
            'one_time_pct' => $oneTimePct,
            'recurring_pct' => $recurringPct,
            'growth_7' => $growth7,
            'growth_30' => $growth30,
            'campaign_active' => (int) ($campaignCounts['active'] ?? 0),
            'campaign_draft' => (int) ($campaignCounts['draft'] ?? 0),
            'campaign_closed' => (int) ($campaignCounts['closed'] ?? 0),
            'campaigns_ending_soon' => $endingSoonCount,
            'campaigns_launched_month' => $launchedThisMonth,
            'avg_donation_per_campaign' => $avgDonationPerCampaign,
            'campaigns_no_donations' => $campaignsWithNoDonations,
            'payment_gateway_counts' => $paymentGatewayCounts,
            'payment_attempts_total' => $paymentAttemptsTotal,
            'payment_attempts_success' => $paymentAttemptsSuccess,
            'payment_success_rate' => $paymentSuccessRate,
            'payment_failed_cancelled_7' => $failedCancelledCurrent7,
            'payment_failed_cancelled_growth_7' => $failedCancelledGrowth,
            'payment_avg_seconds' => $avgPaymentSeconds,
            'manual_proofs_pending' => $pendingManualProofs,
            'manual_proofs_verified' => $verifiedManualProofs,
            'manual_proofs_rejected' => $rejectedManualProofs,
            'receipts_issued' => $receiptsIssued,
            'receipts_issued_month' => $receiptsIssuedThisMonth,
            'receipts_missing' => $missingReceipts,
            'recurring_active' => (int) ($recurringCounts['active'] ?? 0),
            'recurring_paused' => (int) ($recurringCounts['paused'] ?? 0),
            'recurring_cancelled' => (int) ($recurringCounts['cancelled'] ?? 0),
            'recurring_upcoming' => $recurringUpcoming,
            'recurring_overdue' => $recurringOverdue,
            'recurring_churn_rate' => $recurringChurnRate,
            'recurring_churn_count' => $recurringCancelled30,
            'expenses_total' => $expensesTotal,
            'expenses_week' => $expensesWeek,
            'expenses_month' => $expensesMonth,
            'expenses_avg' => $expensesAvg,
            'expenses_burn_rate' => $burnRateMonth,
            'expenses_uncategorized' => $uncategorizedExpenses,
            'pages_published' => (int) ($pageCounts['published'] ?? 0),
            'pages_draft' => (int) ($pageCounts['draft'] ?? 0),
            'pages_scheduled' => $pagesScheduled,
            'pages_missing_seo' => $pagesMissingSeo,
            'conversations_total' => $conversationTotal,
            'messages_total' => $messageTotal,
            'messages_unread' => $unreadMessages,
            'messages_avg_per_conversation' => $avgMessagesPerConversation,
            'reactions_total' => $reactionCount,
            'ai_conversations_total' => $aiConversations,
            'ai_conversations_active_7d' => $aiActiveConversations,
            'ai_tokens_total' => $aiTokensTotal,
            'ai_tokens_7d' => $aiTokens7d,
            'ai_top_provider' => $aiTopProvider,
            'ai_top_model' => $aiTopModel,
            'net_balance' => $netBalance,
            'contributions_total' => $contributionsTotal,
            'users_total' => $usersTotal,
            'users_new_week' => $usersNewWeek,
            'users_active_7d' => $activeUsers,
            'email_verified_rate' => $emailVerifiedRate,
            'profile_completion_rate' => $profileCompletionRate,
            'notifications_total' => $notificationsTotal,
            'notifications_unread' => $notificationsUnread,
            'notification_pref_coverage' => $notificationPrefCoverage,
            'push_subscriptions' => $pushSubscriptions,
            'guest_subscriptions' => $guestSubscriptions,
            'backup_success_rate' => $backupSuccessRate,
            'backup_last_success_at' => $lastSuccessfulBackup,
            'backup_failed_count' => $backupFailedCount,
            'backup_storage_total' => $backupStorageTotal,
            'media_total_items' => $mediaTotalItems,
            'media_total_size' => $mediaTotalSize,
            'activity_total' => $activityTotal,
            'activity_growth_7' => $activityGrowth7,
            'users_by_division_count' => $usersByDivision,
            'users_by_district_count' => $usersByDistrict,
            'users_by_upazila_count' => $usersByUpazila,
            'users_by_union_count' => $usersByUnion,
        ];
    }

    #[Computed]
    public function topCampaigns()
    {
        return Campaign::query()
            ->withSum(['donations as paid_total' => fn ($q) => $q->where('status', 'paid')], 'amount')
            ->orderByDesc('paid_total')
            ->take(5)
            ->get(['id', 'title', 'goal_amount']);
    }

    #[Computed]
    public function campaignGoals()
    {
        return Campaign::query()
            ->withSum(['donations as paid_total' => fn ($q) => $q->where('status', 'paid')], 'amount')
            ->orderBy('title')
            ->get(['id', 'title', 'goal_amount']);
    }

    #[Computed]
    public function expenseByCategory()
    {
        return Expense::query()
            ->join('expense_categories', 'expense_categories.id', '=', 'expenses.expense_category_id')
            ->select('expense_categories.name', DB::raw('SUM(expenses.amount) as total'))
            ->groupBy('expense_categories.name')
            ->orderByDesc('total')
            ->get();
    }

    #[Computed]
    public function topExpenseCampaigns()
    {
        return Campaign::query()
            ->withSum('expenses', 'amount')
            ->orderByDesc('expenses_sum_amount')
            ->take(5)
            ->get(['id', 'title']);
    }

    #[Computed]
    public function recentPages()
    {
        return Page::query()
            ->orderByDesc('updated_at')
            ->take(5)
            ->get(['id', 'title', 'status', 'updated_at']);
    }

    #[Computed]
    public function activeConversations()
    {
        return Conversation::query()
            ->orderByDesc('last_message_at')
            ->whereNotNull('last_message_at')
            ->take(5)
            ->get(['id', 'last_message_at']);
    }

    #[Computed]
    public function netBalanceByCampaign()
    {
        return Campaign::query()
            ->withSum(['donations as paid_total' => fn ($q) => $q->where('status', 'paid')], 'amount')
            ->withSum('expenses', 'amount')
            ->orderBy('title')
            ->get(['id', 'title']);
    }

    #[Computed]
    public function backupSizeTrend()
    {
        return Backup::query()
            ->completed()
            ->orderByDesc('completed_at')
            ->take(6)
            ->get(['id', 'file_size', 'completed_at']);
    }

    #[Computed]
    public function failedBackups()
    {
        return Backup::query()
            ->failed()
            ->orderByDesc('created_at')
            ->take(3)
            ->get(['id', 'error_message', 'created_at']);
    }

    #[Computed]
    public function mediaCollectionsTop()
    {
        return DB::table('media')
            ->select('collection_name', DB::raw('COUNT(*) as items'), DB::raw('SUM(size) as total_size'))
            ->groupBy('collection_name')
            ->orderByDesc('total_size')
            ->take(5)
            ->get();
    }

    #[Computed]
    public function recentMedia()
    {
        return DB::table('media')
            ->orderByDesc('created_at')
            ->take(5)
            ->get(['id', 'name', 'collection_name', 'size', 'created_at']);
    }

    #[Computed]
    public function recentActivities()
    {
        return Activity::query()
            ->orderByDesc('created_at')
            ->take(8)
            ->get(['id', 'description', 'event', 'log_name', 'created_at']);
    }

    #[Computed]
    public function activityByEvent()
    {
        return Activity::query()
            ->select('event', DB::raw('count(*) as total'))
            ->whereNotNull('event')
            ->groupBy('event')
            ->orderByDesc('total')
            ->take(5)
            ->get();
    }

    #[Computed]
    public function activityByLog()
    {
        return Activity::query()
            ->select('log_name', DB::raw('count(*) as total'))
            ->whereNotNull('log_name')
            ->groupBy('log_name')
            ->orderByDesc('total')
            ->take(5)
            ->get();
    }

    #[Computed]
    public function usersByDivision()
    {
        return UserDetail::query()
            ->join('divisions', 'divisions.id', '=', 'user_details.division_id')
            ->select('divisions.name', DB::raw('count(distinct user_details.user_id) as total'))
            ->groupBy('divisions.name')
            ->orderByDesc('total')
            ->take(6)
            ->get();
    }

    #[Computed]
    public function usersByDistrict()
    {
        return UserDetail::query()
            ->join('districts', 'districts.id', '=', 'user_details.district_id')
            ->select('districts.name', DB::raw('count(distinct user_details.user_id) as total'))
            ->groupBy('districts.name')
            ->orderByDesc('total')
            ->take(6)
            ->get();
    }

    #[Computed]
    public function usersByUpazila()
    {
        return UserDetail::query()
            ->join('upazilas', 'upazilas.id', '=', 'user_details.upazila_id')
            ->select('upazilas.name', DB::raw('count(distinct user_details.user_id) as total'))
            ->groupBy('upazilas.name')
            ->orderByDesc('total')
            ->take(6)
            ->get();
    }

    #[Computed]
    public function usersByUnion()
    {
        return UserDetail::query()
            ->join('unions', 'unions.id', '=', 'user_details.union_id')
            ->select('unions.name', DB::raw('count(distinct user_details.user_id) as total'))
            ->groupBy('unions.name')
            ->orderByDesc('total')
            ->take(6)
            ->get();
    }

    #[Computed]
    public function donationsByDivision()
    {
        return Donation::query()
            ->where('donations.status', 'paid')
            ->join('user_details', 'user_details.user_id', '=', 'donations.user_id')
            ->join('divisions', 'divisions.id', '=', 'user_details.division_id')
            ->select('divisions.name', DB::raw('SUM(donations.amount) as total'))
            ->groupBy('divisions.name')
            ->orderByDesc('total')
            ->take(6)
            ->get();
    }

    #[Computed]
    public function campaignNetByDivision()
    {
        return Donation::query()
            ->where('donations.status', 'paid')
            ->join('user_details', 'user_details.user_id', '=', 'donations.user_id')
            ->join('divisions', 'divisions.id', '=', 'user_details.division_id')
            ->select('divisions.name', DB::raw('SUM(donations.amount) as total'))
            ->groupBy('divisions.name')
            ->orderByDesc('total')
            ->take(6)
            ->get();
    }

    protected function calculateGrowth(float $current, float $previous): ?float
    {
        if ($previous <= 0) {
            return null;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }
};
