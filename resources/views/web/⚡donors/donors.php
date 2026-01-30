<?php

use App\Models\Donation;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Title('Donors')]
#[Layout('layouts.auth')]
class extends Component
{
    public array $stats = [];

    public $topDonors = [];

    public function mount(): void
    {
        $now = now();
        $monthStart = $now->copy()->startOfMonth();

        $donorKey = "COALESCE(donor_email, CONCAT('user:', user_id), CONCAT('name:', donor_name))";

        $totalDonors = DB::table('donations')
            ->where('status', 'paid')
            ->selectRaw("COUNT(DISTINCT {$donorKey}) as aggregate")
            ->value('aggregate');

        $firstPaidSubquery = DB::table('donations')
            ->selectRaw("{$donorKey} as donor_key, MIN(paid_at) as first_paid_at")
            ->where('status', 'paid')
            ->groupBy('donor_key');

        $newDonorsThisMonth = DB::query()
            ->fromSub($firstPaidSubquery, 'first_paid')
            ->whereBetween('first_paid_at', [$monthStart, $now])
            ->count();

        $avgDonation = Donation::query()
            ->where('status', 'paid')
            ->avg('amount');

        $repeatDonors = DB::table('donations')
            ->where('status', 'paid')
            ->selectRaw("{$donorKey} as donor_key, COUNT(*) as count")
            ->groupBy('donor_key')
            ->having('count', '>=', 2)
            ->count();
        $repeatRate = $totalDonors > 0
            ? round(($repeatDonors / $totalDonors) * 100, 1)
            : 0;

        $topDonors = DB::table('donations')
            ->where('status', 'paid')
            ->selectRaw("{$donorKey} as donor_key, MAX(user_id) as user_id, MAX(donor_name) as donor_name, MAX(donor_email) as donor_email, SUM(amount) as total_amount, COUNT(*) as donation_count")
            ->groupBy('donor_key')
            ->orderByDesc('total_amount')
            ->limit(10)
            ->get();

        $this->stats = [
            'currency' => setting('currency', 'BDT'),
            'total_donors' => $totalDonors,
            'new_donors_month' => $newDonorsThisMonth,
            'repeat_rate' => $repeatRate,
            'avg_donation' => $avgDonation,
        ];

        $this->topDonors = $topDonors->map(function ($row) {
            $name = $row->donor_name ?: 'Anonymous';
            $email = $row->donor_email;

            if ($email) {
                $parts = explode('@', $email);
                $local = $parts[0];
                $domain = $parts[1] ?? '';
                $maskedLocal = strlen($local) > 2
                    ? substr($local, 0, 2).str_repeat('*', max(1, strlen($local) - 2))
                    : $local.'*';
                $masked = $maskedLocal.($domain ? '@'.$domain : '');
                $display = $name.' ('.$masked.')';
            } else {
                $display = $name;
            }

            return [
                'label' => $display,
                'user_id' => $row->user_id,
                'total' => (float) $row->total_amount,
                'count' => (int) $row->donation_count,
            ];
        })->toArray();
    }
};
