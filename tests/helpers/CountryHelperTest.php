<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use artiden\exchange\helpers\CountryHelper;

class CountryHelperTest extends TestCase {
    public function testIsEu(): void {
        $this->assertTrue(CountryHelper::isEu('nl'));

        $this->assertFalse(CountryHelper::isEu('gb'));
    }
}
