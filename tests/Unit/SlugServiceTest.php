<?php

namespace Tests\Unit;

use App\Services\SlugService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class SlugServiceTest extends TestCase
{
    #[DataProvider('slugCases')]
    public function test_it_normalizes_safe_persian_and_latin_slugs(string $input, string $expected): void
    {
        $this->assertSame($expected, (new SlugService())->make($input, ''));
    }

    public static function slugCases(): array
    {
        return [
            ['gold-market-news', 'gold-market-news'],
            ['Gold Market News', 'gold-market-news'],
            ['gold_market_news', 'gold-market-news'],
            ['خبر-مهم-گرگان', 'خبر-مهم-گرگان'],
            ['خبر مهم گرگان', 'خبر-مهم-گرگان'],
            ['news--test', 'news-test'],
            ['خبر مهم! گرگان', 'خبر-مهم-گرگان'],
        ];
    }
}
