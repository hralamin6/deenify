<?php

use App\Jobs\ScheduledBackupJob;
use App\Models\Donation;
use App\Models\RecurringPlan;
use App\Notifications\RecurringDonationPendingNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

// log a message every minute
//Artisan::command('log:message', function () {
//    Log::info('This is a log message from the log:message command.');
//})->purpose('Log a message every minute')->everyMinute();

// Scheduled backup commands
//Artisan::command('backup:schedule-daily', function () {
//    ScheduledBackupJob::dispatch('daily');
//    $this->info('Daily backup job dispatched successfully.');
//})->purpose('Run daily scheduled backup')->daily();
//
//Artisan::command('backup:schedule-weekly', function () {
//    ScheduledBackupJob::dispatch('weekly');
//    $this->info('Weekly backup job dispatched successfully.');
//})->purpose('Run weekly scheduled backup')->weekly();
//
//Artisan::command('backup:schedule-monthly', function () {
//    ScheduledBackupJob::dispatch('monthly');
//    $this->info('Monthly backup job dispatched successfully.');
//})->purpose('Run monthly scheduled backup')->monthly();
//
//// Cleanup command for old backups
//Artisan::command('backup:cleanup-old', function () {
//    $this->info('Cleaning up old backups...');
//
//    // Cleanup logic handled by the ScheduledBackupJob
//    // This command can also be run manually if needed
//    $job = new ScheduledBackupJob;
//    $reflection = new \ReflectionClass($job);
//    $method = $reflection->getMethod('cleanupOldBackups');
//    $method->setAccessible(true);
//    $method->invoke($job);
//
//    $this->info('Old backups cleanup completed.');
//})->purpose('Cleanup old backup files')->daily();

Artisan::command('recurring:process-pending', function () {
    $now = now();

    $getNextRunAt = function (RecurringPlan $plan, Carbon $from): Carbon {
        if ($plan->interval === 'weekly') {
            $targetDay = $plan->day_of_week ?? Carbon::SATURDAY;
            $next = $from->copy();

            if ($next->dayOfWeek === $targetDay) {
                return $next->addWeek();
            }

            return $next->next($targetDay);
        }

        $day = (int) ($plan->day_of_month ?? 1);
        $next = $from->copy()->addMonth();

        return $next->day(min($day, $next->daysInMonth));
    };

    $plans = RecurringPlan::query()
        ->with(['user', 'campaign'])
        ->where('status', 'active')
        ->whereNotNull('next_run_at')
        ->where('next_run_at', '<=', $now)
        ->get();

    foreach ($plans as $plan) {
        if (! $plan->user || ! $plan->campaign) {
            continue;
        }

        $hasPending = Donation::query()
            ->where('recurring_plan_id', $plan->id)
            ->where('status', 'pending')
            ->exists();

        if ($hasPending) {
            continue;
        }

        $donation = Donation::create([
            'campaign_id' => $plan->campaign_id,
            'user_id' => $plan->user_id,
            'recurring_plan_id' => $plan->id,
            'donor_name' => $plan->user->name,
            'donor_email' => $plan->user->email,
            'amount' => $plan->amount,
            'currency' => $plan->currency ?? 'BDT',
            'status' => 'pending',
        ]);

        $plan->update([
            'last_run_at' => $now,
            'next_run_at' => $getNextRunAt($plan, $plan->next_run_at ?? $now),
        ]);

        $plan->user->notify(new RecurringDonationPendingNotification($donation));
    }

    $pendingDonations = Donation::query()
        ->with(['campaign', 'user'])
        ->whereNotNull('recurring_plan_id')
        ->where('status', 'pending')
        ->whereDate('created_at', '<', $now->toDateString())
        ->get();

    foreach ($pendingDonations as $donation) {
        if (! $donation->user) {
            continue;
        }

        $donation->user->notify(new RecurringDonationPendingNotification($donation, true));
    }
})->purpose('Generate recurring pending donations and reminders')->daily();
