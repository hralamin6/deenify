<?php

namespace Database\Seeders;

use App\Models\Campaign;
use App\Models\Donation;
use App\Models\DonationReceipt;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\PaymentAttempt;
use App\Models\RecurringPlan;
use App\Models\User;
use Illuminate\Database\Seeder;

class DonationSystemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::where('email', 'admin@mail.com')->first() ?? User::first();
        if (!$admin) {
            $admin = User::create([
                'name' => 'Admin',
                'email' => 'admin@mail.com',
                'password' => bcrypt('000000'),
                'email_verified_at' => now(),
            ]);
        }

        $donor = User::where('email', 'user@mail.com')->first() ?? $admin;

        $categories = collect([
            ['name' => 'Food', 'description' => 'Food relief and kits'],
            ['name' => 'Transport', 'description' => 'Logistics and delivery'],
            ['name' => 'Medical', 'description' => 'Medical supplies'],
            ['name' => 'Education', 'description' => 'Books and tuition support'],
            ['name' => 'Shelter', 'description' => 'Temporary housing and repairs'],
        ])->map(function ($data) {
            return ExpenseCategory::firstOrCreate(['name' => $data['name']], $data);
        });

        $campaign = Campaign::firstOrCreate([
            'slug' => 'winter-relief',
        ], [
            'title' => 'Winter Relief',
            'description' => 'Support families with food and blankets.',
            'goal_amount' => 500000,
            'status' => 'active',
            'starts_at' => now()->subWeek(),
            'ends_at' => now()->addMonth(),
            'created_by' => $admin->id,
        ]);

        Expense::firstOrCreate([
            'campaign_id' => $campaign->id,
            'expense_category_id' => $categories->first()->id ?? null,
            'amount' => 120000,
        ], [
            'spent_at' => now()->subDays(3),
            'description' => 'Blankets and food packets',
            'created_by' => $admin->id,
        ]);

        $recurringPlan = RecurringPlan::firstOrCreate([
            'user_id' => $donor->id,
            'campaign_id' => $campaign->id,
            'interval' => 'monthly',
        ], [
            'amount' => 2000,
            'currency' => 'BDT',
            'day_of_month' => 5,
            'status' => 'active',
            'starts_at' => now()->startOfMonth(),
            'next_run_at' => now()->addMonth()->startOfMonth(),
        ]);

        $paidDonation = Donation::firstOrCreate([
            'campaign_id' => $campaign->id,
            'donor_email' => $donor->email,
            'status' => 'paid',
        ], [
            'user_id' => $donor->id,
            'recurring_plan_id' => $recurringPlan->id,
            'donor_name' => $donor->name,
            'amount' => 2000,
            'currency' => 'BDT',
            'paid_at' => now()->subDays(1),
        ]);

        $pendingDonation = Donation::firstOrCreate([
            'campaign_id' => $campaign->id,
            'donor_email' => 'guest@example.com',
            'status' => 'pending',
        ], [
            'donor_name' => 'Guest Donor',
            'amount' => 1000,
            'currency' => 'BDT',
        ]);

        PaymentAttempt::firstOrCreate([
            'donation_id' => $paidDonation->id,
            'gateway' => 'bkash',
            'status' => 'success',
        ], [
            'amount' => $paidDonation->amount,
            'currency' => $paidDonation->currency,
            'provider_reference' => 'BKASH-DEMO-001',
            'initiated_at' => now()->subDays(1)->subMinutes(2),
            'completed_at' => now()->subDays(1),
            'response_payload' => ['demo' => true],
        ]);

        PaymentAttempt::firstOrCreate([
            'donation_id' => $pendingDonation->id,
            'gateway' => 'sslcommerz',
            'status' => 'pending',
        ], [
            'amount' => $pendingDonation->amount,
            'currency' => $pendingDonation->currency,
            'initiated_at' => now()->subHours(2),
        ]);

        DonationReceipt::firstOrCreate([
            'donation_id' => $paidDonation->id,
        ], [
            'receipt_number' => 'DN-2026-0001',
            'issued_at' => now()->subDays(1),
            'meta' => ['source' => 'seed'],
        ]);

        $moreCampaigns = [
            [
                'slug' => 'food-support',
                'title' => 'Food Support',
                'description' => 'Provide daily meals for families in need.',
                'goal_amount' => 300000,
                'status' => 'active',
                'starts_at' => now()->subDays(10),
                'ends_at' => now()->addWeeks(3),
            ],
            [
                'slug' => 'flood-relief',
                'title' => 'Flood Relief',
                'description' => 'Emergency relief for flood-affected regions.',
                'goal_amount' => 800000,
                'status' => 'active',
                'starts_at' => now()->subDays(20),
                'ends_at' => now()->addMonth(),
            ],
            [
                'slug' => 'education-fund',
                'title' => 'Education Fund',
                'description' => 'Scholarships and school supplies for students.',
                'goal_amount' => 450000,
                'status' => 'draft',
                'starts_at' => now()->addDays(7),
                'ends_at' => now()->addMonths(3),
            ],
            [
                'slug' => 'health-camp',
                'title' => 'Health Camp',
                'description' => 'Free medical camp and medicine distribution.',
                'goal_amount' => 200000,
                'status' => 'closed',
                'starts_at' => now()->subMonths(2),
                'ends_at' => now()->subMonth(),
            ],
        ];

        foreach ($moreCampaigns as $data) {
            $campaignItem = Campaign::firstOrCreate(
                ['slug' => $data['slug']],
                $data + ['created_by' => $admin->id]
            );

            Expense::firstOrCreate([
                'campaign_id' => $campaignItem->id,
                'expense_category_id' => $categories->where('name', 'Food')->first()->id ?? null,
                'amount' => 45000,
            ], [
                'spent_at' => now()->subDays(5),
                'description' => 'Initial campaign expenses',
                'created_by' => $admin->id,
            ]);

            Expense::firstOrCreate([
                'campaign_id' => $campaignItem->id,
                'expense_category_id' => $categories->where('name', 'Transport')->first()->id ?? null,
                'amount' => 18000,
            ], [
                'spent_at' => now()->subDays(2),
                'description' => 'Distribution logistics',
                'created_by' => $admin->id,
            ]);

            $monthlyPlan = RecurringPlan::firstOrCreate([
                'user_id' => $donor->id,
                'campaign_id' => $campaignItem->id,
                'interval' => 'monthly',
            ], [
                'amount' => 1500,
                'currency' => 'BDT',
                'day_of_month' => 10,
                'status' => 'active',
                'starts_at' => now()->startOfMonth(),
                'next_run_at' => now()->addMonth()->startOfMonth(),
            ]);

            $weeklyPlan = RecurringPlan::firstOrCreate([
                'user_id' => $donor->id,
                'campaign_id' => $campaignItem->id,
                'interval' => 'weekly',
            ], [
                'amount' => 500,
                'currency' => 'BDT',
                'day_of_week' => 5,
                'status' => 'active',
                'starts_at' => now()->subWeek(),
                'next_run_at' => now()->addDays(7),
            ]);

            $paid = Donation::firstOrCreate([
                'campaign_id' => $campaignItem->id,
                'donor_email' => $donor->email,
                'status' => 'paid',
                'amount' => 1500,
            ], [
                'user_id' => $donor->id,
                'recurring_plan_id' => $monthlyPlan->id,
                'donor_name' => $donor->name,
                'currency' => 'BDT',
                'paid_at' => now()->subDays(2),
            ]);

            $failed = Donation::firstOrCreate([
                'campaign_id' => $campaignItem->id,
                'donor_email' => 'guest.fail@example.com',
                'status' => 'failed',
                'amount' => 800,
            ], [
                'donor_name' => 'Guest Failed',
                'currency' => 'BDT',
            ]);

            $cancelled = Donation::firstOrCreate([
                'campaign_id' => $campaignItem->id,
                'donor_email' => 'guest.cancel@example.com',
                'status' => 'cancelled',
                'amount' => 1200,
            ], [
                'donor_name' => 'Guest Cancelled',
                'currency' => 'BDT',
            ]);

            PaymentAttempt::firstOrCreate([
                'donation_id' => $paid->id,
                'gateway' => 'nagad',
                'status' => 'success',
            ], [
                'amount' => $paid->amount,
                'currency' => $paid->currency,
                'provider_reference' => 'NAGAD-DEMO-'.$campaignItem->id,
                'initiated_at' => now()->subDays(2)->subMinutes(3),
                'completed_at' => now()->subDays(2),
                'response_payload' => ['demo' => true],
            ]);

            PaymentAttempt::firstOrCreate([
                'donation_id' => $failed->id,
                'gateway' => 'sslcommerz',
                'status' => 'failed',
            ], [
                'amount' => $failed->amount,
                'currency' => $failed->currency,
                'initiated_at' => now()->subHours(6),
                'completed_at' => now()->subHours(5),
                'response_payload' => ['error' => 'DECLINED'],
            ]);

            PaymentAttempt::firstOrCreate([
                'donation_id' => $cancelled->id,
                'gateway' => 'bkash',
                'status' => 'cancelled',
            ], [
                'amount' => $cancelled->amount,
                'currency' => $cancelled->currency,
                'initiated_at' => now()->subHours(4),
                'completed_at' => now()->subHours(3),
                'response_payload' => ['error' => 'USER_CANCELLED'],
            ]);

            DonationReceipt::firstOrCreate([
                'donation_id' => $paid->id,
            ], [
                'receipt_number' => 'DN-2026-'.str_pad((string) $campaignItem->id, 4, '0', STR_PAD_LEFT),
                'issued_at' => now()->subDays(2),
                'meta' => ['source' => 'seed'],
            ]);

            Donation::firstOrCreate([
                'campaign_id' => $campaignItem->id,
                'donor_email' => 'weekly@example.com',
                'status' => 'pending',
                'amount' => 500,
            ], [
                'recurring_plan_id' => $weeklyPlan->id,
                'donor_name' => 'Weekly Donor',
                'currency' => 'BDT',
            ]);
        }
    }
}
