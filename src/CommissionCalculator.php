<?php

namespace artiden\exchange;

use artiden\exchange\helpers\CountryHelper;
use artiden\exchange\providers\bin\BinProviderInterface;
use artiden\exchange\providers\bin\exceptions\BinDataUnavailableException;
use artiden\exchange\providers\rate\exceptions\RatesUnavailableException;
use artiden\exchange\providers\rate\exceptions\UnsupportedCurrencyException;
use artiden\exchange\providers\rate\RateProviderInterface;
use artiden\exchange\providers\transactions\TransactionsProviderInterface;

class CommissionCalculator {
    /**
     * Class constructor
     *
     * @param TransactionsProviderInterface $transactionsProvider
     * @param BinProviderInterface $binProvider
     * @param RateProviderInterface $rateProvider
     */
    public function __construct(
        // We able to use that form from latest PHP versions... It means we shouldn't define those properties in the class it self
        protected TransactionsProviderInterface $transactionsProvider,
        protected BinProviderInterface $binProvider,
        protected RateProviderInterface $rateProvider
    ){}

    /**
     * Returns array of calculated commision values for each transaction.
     *
     * @return array
     */
    public function getCommissions(): array {
        $commissions = [];
        $errors = [];

        $transactions = $this->transactionsProvider->getTransactions();
        foreach ($transactions as $transaction) {
            // A caching have been done in each of those providers, so we able to ask data that way.
            try {
                $binData = $this->binProvider->getBinData($transaction->bin);
            } catch (BinDataUnavailableException $exception) {
                $errors[] = sprintf(
                    "Error processing transaction for BIN %s: %s",
                    $transaction->bin,
                    $exception->getMessage()
                );
                continue;
            }

            $isEuCountry = CountryHelper::isEu($binData->country->alpha2);

            if ($transaction->currency === 'EUR') {
                $amount = $transaction->amount;
            } else {
                try {
                    $rate = $this->rateProvider->getRate($transaction->currency);
                } catch (RatesUnavailableException | UnsupportedCurrencyException $exception) {
                    $errors[] = sprintf(
                        "Error processing transaction for BIN %s: %s",
                        $transaction->bin,
                        $exception->getMessage()
                    );
                    continue;
                }

                $amount = $transaction->amount / $rate;
            }

            $commissions[] = floatval(sprintf(
                '%.2f',
                $amount * ($isEuCountry ? 0.01 : 0.02)
            ));
        }

        // I'm not sure why, but in most cases I getting an error when tried to get BIN data from an URL. That's why I added this errors array.
        return [
            'commissions' => $commissions,
            'errors' => $errors,
        ];
    }
}
