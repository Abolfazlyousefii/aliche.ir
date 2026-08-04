<?php

namespace Database\Seeders;

use App\Models\Price;
use Illuminate\Database\Seeder;

class PriceSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['قیمت هر گرم طلای ۱۸ عیار', 'gold', 28500000, 'ریال', 'مصوب اتحادیه'],
            ['سکه امامی', 'coin', 312000000, 'ریال', 'بازار آزاد'],
            ['نیم سکه', 'coin', 185000000, 'ریال', 'بازار آزاد'],
            ['ربع سکه', 'coin', 120000000, 'ریال', 'بازار آزاد'],
            ['نقره هر گرم', 'silver', 460000, 'ریال', 'بازار آزاد'],
            ['دلار', 'currency', 615000, 'ریال', 'بازار آزاد'],
            ['اجرت ساخت هر گرم مصنوعات طلا', 'service', 850000, 'ریال', 'مصوب اتحادیه'],
        ];

        foreach ($items as $index => [$title, $type, $amount, $unit, $source]) {
            Price::updateOrCreate(
                ['title' => $title],
                [
                    'type' => $type,
                    'amount' => $amount,
                    'unit' => $unit,
                    'source' => $source,
                    'published_at' => now()->subHours($index),
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ]
            );
        }
    }
}
