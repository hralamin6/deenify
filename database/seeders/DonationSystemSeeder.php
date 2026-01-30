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
use Illuminate\Support\Facades\Hash;

class DonationSystemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create Users
        $admin = User::firstOrCreate(
            ['email' => 'admin@mail.com'],
            [
                'name' => 'অ্যাডমিন',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $users = collect([
            ['name' => 'আব্দুল্লাহ আল মাহমুদ', 'email' => 'abdullah@mail.com'],
            ['name' => 'ফাতেমা আক্তার', 'email' => 'fatema@mail.com'],
            ['name' => 'মুহাম্মদ ইব্রাহিম', 'email' => 'ibrahim@mail.com'],
            ['name' => 'আয়েশা সিদ্দিকা', 'email' => 'ayesha@mail.com'],
            ['name' => 'সাইফুল ইসলাম', 'email' => 'saiful@mail.com'],
            ['name' => 'জান্নাতুল ফেরদৌস', 'email' => 'jannatul@mail.com'],
            ['name' => 'ওমর ফারুক', 'email' => 'omar@mail.com'],
            ['name' => 'সুমাইয়া খাতুন', 'email' => 'sumaiya@mail.com'],
            ['name' => 'হাসান মাহমুদ', 'email' => 'hasan@mail.com'],
            ['name' => 'মারিয়া চৌধুরী', 'email' => 'maria@mail.com'],
        ])->map(function ($userData) {
            return User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );
        });
        $donors = $users->push($admin);

        // 2. Create Expense Categories in Bengali
        $categories = collect([
            ['name' => 'খাদ্য সামগ্রী', 'description' => 'দুস্থদের জন্য খাদ্য ও ত্রাণ বিতরণ'],
            ['name' => 'পরিবহন ও যাতায়াত', 'description' => 'ত্রাণ পৌঁছানো এবং লজিস্টিক খরচ'],
            ['name' => 'চিকিৎসা সেবা', 'description' => 'বিনামূল্যে ঔষধ ও স্বাস্থ্যসেবা প্রদান'],
            ['name' => 'শিক্ষা উপকরণ', 'description' => 'বই, খাতা এবং শিক্ষাবৃত্তি প্রদান'],
            ['name' => 'আশ্রয়ণ প্রকল্প', 'description' => 'গৃহহীনদের জন্য অস্থায়ী বাসস্থান নির্মাণ'],
            ['name' => 'বস্ত্র বিতরণ', 'description' => 'শ���তবস্ত্র এবং প্রয়োজনীয় পোশাক বিতরণ'],
            ['name' => 'বিশুদ্ধ পানি', 'description' => 'নলকূপ স্থাপন ও নিরাপদ পানির ব্যবস্থা'],
            ['name' => 'দাওয়াহ কার্যক্রম', 'description' => 'ইসলামিক বই প্রকাশনা ও প্রচার'],
            ['name' => 'ব্যবস্থাপনা', 'description' => 'প্রকল্প পরিচালনা ও প্রশাসনিক খরচ'],
            ['name' => 'জরুরী ত্রাণ', 'description' => 'প্রাকৃতিক দুর্যোগে فوری সহায়তা'],
        ])->map(function ($data) {
            return ExpenseCategory::firstOrCreate(['name' => $data['name']], $data);
        });

        // 3. Create Campaigns with detailed Bengali descriptions
        $campaignsData = [
            [
                'slug' => 'etim-shohayota',
                'title' => 'এতিম সহায়তা কর্মসূচি',
                'description' => "পবিত্র কুরআনে আল্লাহ তা'আলা বারবার এতিমদের প্রতি সদয় আচরণের নির্দেশ দিয়েছেন। এই ঐশী অনুপ্রেরণায় আমরা ১০০ জন এতিম শিশুর শিক্ষা, স্বাস্থ্য, এবং মৌলিক চাহিদা পূরণের দায়িত্ব নিয়েছি। আপনার অনুদান একজন এতিমের জীবনে আনতে পারে বিশাল পরিবর্তন।",
                'goal_amount' => 750000, 'status' => 'active',
            ],
            [
                'slug' => 'mosjid-nirman',
                'title' => 'মসজিদ নির্মাণ প্রকল্প',
                'description' => "রাসূলুল্লাহ (সা.) বলেছেন, 'যে ব্যক্তি আল্লাহর জন্য একটি মসজিদ নির্মাণ করবে, আল্লাহ তার জন্য জান্নাতে একটি ঘর নির্মাণ করবেন।' আমরা প্রত্যন্ত অঞ্চলে একটি নতুন মসজিদ নির্মাণের উদ্যোগ নিয়েছি, যা একটি ইসলামিক শিক্ষা ও সামাজিক কেন্দ্র হিসেবেও কাজ করবে।",
                'goal_amount' => 2500000, 'status' => 'active',
            ],
            [
                'slug' => 'bidhoba-shabolombikoron',
                'title' => 'বিধবা ও অসহায় নারীদের স্বাবলম্বীকরণ',
                'description' => 'আমরা অসহায় নারীদের সেলাই মেশিন প্রদান, হস্তশিল্পের প্রশিক্ষণ এবং ক্ষুদ্র ব্যবসা শুরু করার জন্য আর্থিক সহায়তা দিয়ে থাকি, যাতে তারা সম্মানের সাথে জীবিকা নির্বাহ করতে পারে।',
                'goal_amount' => 600000, 'status' => 'active',
            ],
            [
                'slug' => 'bishuddho-pani',
                'title' => 'বিশুদ্ধ পানি সরবরাহ প্রকল্প',
                'description' => "রাসূলুল্লাহ (সা.) বলেছেন, '��র্বোত্তম সাদকা হলো পানি পান করানো।' আমরা আর্সেনিকমুক্ত ও নিরাপদ পানির উৎস নিশ্চিত করার জন্য গভীর নলকূপ স্থাপনের প্রকল্প গ্রহণ করেছি।",
                'goal_amount' => 300000, 'status' => 'active',
            ],
            [
                'slug' => 'ramadan-iftar',
                'title' => 'রমজান মাসে ইফতার ও সাহরি বিতরণ',
                'description' => 'এই পবিত্র মাসে আমরা প্রতিদিন শত শত গরিব ও অসহায় রোজাদারের জন্য ইফতার এবং সাহরির আয়োজন করে থাকি। আপনার অনুদান তাদের মুখে হাসি ফোটাতে পারে।',
                'goal_amount' => 900000, 'status' => 'active',
            ],
            [
                'slug' => 'quran-shikkha',
                'title' => 'কুরআন শিক্ষা ও বিতরণ',
                'description' => " 'তোমা��ের মধ্যে সেই ব্যক্তিই সর্বোত্তম, যে নিজে কুরআন শিখে এবং অন্যকে তা শিক্ষা দেয়।' আমরা গরিব ও মেধাবী শিক্ষার্থীদের মধ্যে বিনামূল্যে কুরআন শরিফ বিতরণ এবং তাদের জন্য কুরআন শিক্ষার ব্যবস্থা করেছি।",
                'goal_amount' => 450000, 'status' => 'draft',
            ],
            [
                'slug' => 'shitbostro-bitoron',
                'title' => 'শীতবস্ত্র বিতরণ কর্মসূচি',
                'description' => 'প্রতি বছর শীতের তীব্রতায় আমাদের দেশের উত্তরাঞ্চলের দরিদ্র মানুষ অসহনীয় কষ্টে দিন কাটায়। আপনার একটি উষ্ণ অনুদান তাদের শীতের কষ্ট থেকে মুক্তি দিতে পারে।',
                'goal_amount' => 800000, 'status' => 'active',
            ],
            [
                'slug' => 'binamulle-shasthosheba',
                'title' => 'বিনামূল্যে স্বাস্থ্যসেবা ও মেডিকেল ক্যাম্প',
                'description' => 'আমরা বিভিন্ন সুবিধাবঞ্চিত এলাকায় বিনামূল্যে মেডিকেল ক্যাম্পের আয়োজন করি, যেখানে অভিজ্ঞ ডাক্তাররা গরিব রোগীদের স্বাস্থ্য পরীক্ষা করেন এবং বিনামূল্যে ঔষধ বিতরণ করা হয়।',
                'goal_amount' => 550000, 'status' => 'closed',
            ],
            [
                'slug' => 'dawah-prochar',
                'title' => 'দাওয়াহ ও ইসলামিক বই প্রকাশনা',
                'description' => "ইসলামের সঠিক জ্ঞান মানুষের কাছে পৌঁছে দেওয়ার লক্ষ্যে আমরা সহজ ভাষায় বই এবং প্রচারপত্র প্রকাশ ও বিতরণ করে থাকি। আপনার অনুদান হতে পারে কারো হি��ায়াতের কারণ।",
                'goal_amount' => 350000, 'status' => 'active',
            ],
            [
                'slug' => 'ringrosto-shahajjo',
                'title' => 'ঋণগ্রস্তদের সাহায্য প্রদান',
                'description' => 'আমরা যাচাই-বাছাই করে ঋণগ্রস্তদের ঋণ পরিশোধে সহায়তা করি, যাতে তারা একটি চিন্তামুক্ত ও স্বাভাবিক জীবনে ফিরে আসতে পারে। আপনার যাকাতের অর্থ এখানে ব্যয় করতে পারেন।',
                'goal_amount' => 1200000, 'status' => 'draft',
            ],
        ];

        $createdCampaigns = collect($campaignsData)->map(function ($data) use ($admin) {
            return Campaign::firstOrCreate(
                ['slug' => $data['slug']],
                [
                    'title' => $data['title'],
                    'description' => $data['description'],
                    'goal_amount' => $data['goal_amount'],
                    'status' => $data['status'],
                    'starts_at' => now()->subDays(rand(5, 20)),
                    'ends_at' => now()->addDays(rand(30, 90)),
                    'created_by' => $admin->id,
                ]
            );
        });

        // 4. Create 30 Expenses
        $createdCampaigns->each(function ($campaign) use ($categories, $admin) {
            for ($i = 0; $i < 3; $i++) {
                Expense::create([
                    'campaign_id' => $campaign->id,
                    'expense_category_id' => $categories->random()->id,
                    'amount' => rand(10000, 50000),
                    'spent_at' => now()->subDays(rand(1, 10)),
                    'description' => 'প্রকল্পের প্রাথমিক পর্যায়ের খরচ নং-'.($i + 1),
                    'created_by' => $admin->id,
                ]);
            }
        });

        // 5. Create Donations, Payment Attempts, and other related data
        $createdCampaigns->each(function ($campaign) use ($donors) {
            // Create a few donations for each campaign
            for ($i = 0; $i < 5; $i++) {
                $donor = $donors->random();
                $status = ['paid', 'pending', 'failed', 'cancelled'][rand(0, 3)];
                $amount = rand(500, 5000);

                $donation = Donation::create([
                    'campaign_id' => $campaign->id,
                    'user_id' => $donor->id,
                    'donor_name' => $donor->name,
                    'donor_email' => $donor->email,
                    'amount' => $amount,
                    'currency' => 'BDT',
                    'status' => $status,
                    'paid_at' => $status === 'paid' ? now()->subDays(rand(1, 5)) : null,
                ]);

                if ($status !== 'pending') {
                    $paymentAttempt = PaymentAttempt::create([
                        'donation_id' => $donation->id,
                        'gateway' => ['bkash', 'nagad', 'rocket', 'shurjopay', 'aamarpay'][rand(0, 3)],
                        'status' => $status === 'paid' ? 'success' : $status,
                        'amount' => $amount,
                        'currency' => 'BDT',
                        'initiated_at' => now()->subDays(rand(1, 5))->subMinutes(10),
                        'completed_at' => now()->subDays(rand(1, 5)),
                        'provider_reference' => strtoupper(str()->random(10)),
                        'response_payload' => ['status' => $status],
                    ]);
                }

                if ($status === 'paid') {
                    DonationReceipt::create([
                        'donation_id' => $donation->id,
                        'receipt_number' => 'DN-'.date('Y').'-'.str_pad($donation->id, 6, '0', STR_PAD_LEFT),
                        'issued_at' => now(),
                        'meta' => ['source' => 'seed'],
                    ]);
                }
            }
        });
    }
}
