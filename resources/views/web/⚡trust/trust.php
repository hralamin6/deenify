<?php

use App\Models\Backup;
use App\Models\ManualPaymentProof;
use App\Models\PaymentAttempt;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Title('Trust & Safety')]
#[Layout('layouts.auth')]
class extends Component
{
    public array $stats = [];

    public array $gateways = [];

    public function mount(): void
    {
        $now = now();
        $periodStart = $now->copy()->subDays(30)->startOfDay();

        $paymentAttemptsTotal = PaymentAttempt::query()
            ->whereBetween('created_at', [$periodStart, $now])
            ->count();
        $paymentAttemptsSuccess = PaymentAttempt::query()
            ->whereBetween('created_at', [$periodStart, $now])
            ->where('status', 'success')
            ->count();
        $paymentSuccessRate = $paymentAttemptsTotal > 0
            ? round(($paymentAttemptsSuccess / $paymentAttemptsTotal) * 100, 1)
            : 0;

        $manualVerified = ManualPaymentProof::query()
            ->where('verification_status', 'verified')
            ->count();
        $manualRejected = ManualPaymentProof::query()
            ->where('verification_status', 'rejected')
            ->count();
        $manualPending = ManualPaymentProof::query()
            ->where('verification_status', 'pending')
            ->count();
        $manualTotalReviewed = $manualVerified + $manualRejected;
        $manualSuccessRate = $manualTotalReviewed > 0
            ? round(($manualVerified / $manualTotalReviewed) * 100, 1)
            : 0;

        $lastBackup = Backup::query()->completed()->latest('completed_at')->first();

        $notificationsTotal = DB::table('notifications')->count();
        $notificationsUnread = DB::table('notifications')->whereNull('read_at')->count();
        $notificationReadRate = $notificationsTotal > 0
            ? round((($notificationsTotal - $notificationsUnread) / $notificationsTotal) * 100, 1)
            : 0;

        $gateways = PaymentAttempt::query()
            ->select('gateway')
            ->distinct()
            ->pluck('gateway')
            ->map(fn ($gateway) => strtoupper($gateway))
            ->values()
            ->all();

        if (empty($gateways)) {
            $gateways = ['BKASH', 'NAGAD', 'ROCKET', 'AAMARPAY', 'SHURJOPAY'];
        }

        $this->gateways = $gateways;
        $this->stats = [
            'payment_success_rate' => $paymentSuccessRate,
            'payment_attempts_total' => $paymentAttemptsTotal,
            'manual_success_rate' => $manualSuccessRate,
            'manual_verified' => $manualVerified,
            'manual_rejected' => $manualRejected,
            'manual_pending' => $manualPending,
            'last_backup_at' => $lastBackup?->completed_at,
            'notifications_total' => $notificationsTotal,
            'notifications_unread' => $notificationsUnread,
            'notification_read_rate' => $notificationReadRate,
        ];
    }
};
