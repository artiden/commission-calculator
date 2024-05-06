<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use artiden\exchange\providers\bin\BinProviderInterface;
use artiden\exchange\providers\rate\RateProviderInterface;
use artiden\exchange\providers\transactions\TransactionsProviderInterface;
use artiden\exchange\CommissionCalculator;

class CommissionCalculatorTest extends TestCase {
    public function testGetCommissions(): void {
        $binProvider = $this->createStub(BinProviderInterface::class);
        $rateProvider = $this->createStub(RateProviderInterface::class);
        $transactionsProvider = $this->createStub(TransactionsProviderInterface::class);

        // Configuring stubs...
        $binProvider->method('getBinData')
            ->willReturn((object)[
                'country' => (object)[
                    'alpha2' => 'DK',
                ],
            ]);

        $rateProvider->method('getRate')
            ->willReturn(floatval(1));

        $transactionsProvider->method('getTransactions')
            ->willReturn([
                (object)[
                    'bin' => '45717360',
                    'amount' => 100.00,
                    'currency' => 'EUR',
                ],
            ]);

        // To be sure, we need configure more tests. But it's just for demonstrating purpose only

        // Testing calculator, using configured stubs.
        $calc = new CommissionCalculator($transactionsProvider, $binProvider, $rateProvider);
        $commissions = $calc->getCommissions();
        $this->assertCount(1, $commissions['commissions']);
        $this->assertEmpty($commissions['errors']);
        $this->assertEquals(1.00, $commissions['commissions'][0]);
    }
}
