<?php

namespace artiden\exchange\providers\transactions;

use artiden\exchange\providers\transactions\exceptions\TransactionsUnavailableException;
use artiden\exchange\providers\transactions\TransactionsProviderInterface;

class TransactionsFileProvider implements TransactionsProviderInterface {
    /**
     * Class constructor
     *
     * @param string $fileName
     */
    public function __construct(
        // We able to use that form from latest PHP versions... It means we shouldn't define those properties in the class it self
        protected string $fileName
    ) {}

    /**
     * {@inheritDoc}
     */
    public function getTransactions(): array {
        $transactions = [];

        $file = @fopen($this->fileName, 'r');
        if ($file === false) {
            throw new TransactionsUnavailableException('Unable to open a file with transactions');
        }

        while (!feof($file)) {
            if (!$line = fgets($file)) {
                continue;
            }
            try {
                $transactions[] = json_decode($line, null, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException $exception){
                // If some transaction have invalid JSON, or other issue - we're going to skip it
            }
        }

        return $transactions;
    }
}
