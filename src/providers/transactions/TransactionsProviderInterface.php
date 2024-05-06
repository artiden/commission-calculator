<?php

namespace artiden\exchange\providers\transactions;

interface TransactionsProviderInterface {
    /**
     * Returns list of transactions
     *
     * @return array
     */
    public function getTransactions(): array;
}
