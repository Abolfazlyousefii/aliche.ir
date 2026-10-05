<?php

namespace Tests\Unit;

use App\Support\UnionIcon;
use PHPUnit\Framework\TestCase;

class UnionIconTest extends TestCase
{
    public function test_legacy_emoji_icons_map_to_supported_svg_names(): void
    {
        $this->assertSame('document', UnionIcon::resolve('📋'));
        $this->assertSame('rules', UnionIcon::resolve('⚖️'));
        $this->assertSame('prices', UnionIcon::resolve('💰'));
        $this->assertSame('education', UnionIcon::resolve('📚'));
        $this->assertSame('shield', UnionIcon::resolve('🛡️'));
    }

    public function test_unknown_icons_are_rejected_or_fall_back_safely(): void
    {
        $this->assertNull(UnionIcon::resolve('totally-unknown-icon'));
        $this->assertSame('link', UnionIcon::normalize('totally-unknown-icon'));
        $this->assertSame('commission', UnionIcon::normalize(null, 'commission'));
    }
}
