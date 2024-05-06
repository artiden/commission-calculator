<?php
require 'vendor/autoload.php';

use Symfony\Component\Dotenv\Dotenv;
use artiden\exchange\CommissionCalculator;
use artiden\exchange\providers\bin\BinlistProvider;
use artiden\exchange\providers\transactions\TransactionsFileProvider;
use artiden\exchange\providers\rate\ExchangeRatesApiProvider;

$dotEnv = new Dotenv();
$dotEnv->loadEnv(__DIR__.'/.env.local');

$binProvider = new BinlistProvider();
$transactionsProvider = new TransactionsFileProvider($argv[1]);
$rateProvider = new ExchangeRatesApiProvider(
    $_ENV['EXCHANGE_RATES_API_URL'],
    $_ENV['EXCHANGE_API_KEY'],
    boolval($_ENV['EXCHANGE_SECURE'])
);

$commissionCalculator = new CommissionCalculator($transactionsProvider, $binProvider, $rateProvider);
try {
    $data = $commissionCalculator->getCommissions();
} catch (\Exception $exception) {
    echo 'Unable to get commissions. Due to: ' . $exception->getMessage();
    exit(1);
}

if (!empty($data['errors'])) {
    echo 'Some errors found:' . PHP_EOL;
    echo implode(PHP_EOL, $data['errors']);
}

echo PHP_EOL . 'Calculation result:' . PHP_EOL;
echo implode(PHP_EOL, $data['commissions']);