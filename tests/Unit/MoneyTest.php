<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Support\Money;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class MoneyTest extends TestCase
{
    #[Test]
    public function itFormatsPenceAsWholePoundsWithThousandSeparators(): void
    {
        $this->assertSame('£0', Money::format(0));
        $this->assertSame('£1', Money::format(199));
        $this->assertSame('£1,284', Money::format(128_400));
    }
}
