<?php

namespace Database\Seeders;

use App\Models\Contribution;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ContributionSeeder extends Seeder
{
    public function run(): void
    {
        $contributions = [
            [
                'title' => 'Winter Blanket Distribution 2024',
                'description' => 'Distributed regular blankets to 500 families in rural areas.',
                'amount' => 150000,
                'location' => 'Rangpur, Bangladesh',
                'status' => 'published',
            ],
            [
                'title' => 'Ramadan Food Ration Pack',
                'description' => 'Provided food packs containing rice, oil, lentils, and dates to 1000 families.',
                'amount' => 500000,
                'location' => 'Dhaka Slums',
                'status' => 'published',
            ],
            [
                'title' => 'Emergency Flood Relief',
                'description' => 'Immediate response to flash floods, providing cooked meals and clean water.',
                'amount' => 250000,
                'location' => 'Sylhet, Bangladesh',
                'status' => 'published',
            ],
            [
                'title' => 'Educational Scholarship Program',
                'description' => 'Annual scholarship distribution for meritorious students from low-income families.',
                'amount' => 100000,
                'location' => 'Chittagong',
                'status' => 'draft',
            ],
        ];

        foreach ($contributions as $data) {
            Contribution::updateOrCreate(
                ['title' => $data['title']],
                [
                    ...$data,
                    'slug' => Str::slug($data['title']),
                    'date' => now()->subDays(rand(1, 30)),
                ]
            );
        }
    }
}
