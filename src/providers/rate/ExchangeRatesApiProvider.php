<?php

namespace artiden\exchange\providers\rate;

use artiden\exchange\providers\rate\exceptions\RatesUnavailableException;
use artiden\exchange\providers\rate\exceptions\UnsupportedCurrencyException;
use artiden\exchange\providers\rate\RateProviderInterface;

class ExchangeRatesApiProvider implements RateProviderInterface {
    /**
     * @var string
     */
    protected string $scheme;

    /**
     * Cachet data
     *
     * @var array
     */
    protected array $cachedData;

    /**
     * Class constructor
     *
     * @param string $apiUri
     * @param string $accessKey
     * @param bool $httpsSupported
     */
    public function __construct(
        // We able to use that form from latest PHP versions... It means we shouldn't define those properties in the class it self
        private string $apiUri,
        private string $accessKey,
        private bool $httpsSupported = false
    ) {
        $this->scheme = 'https';
        if (!$this->httpsSupported) {
            $this->scheme = 'http';
        }

        $this->cachedData = [];
    }

    /**
     * {@inheritDoc}
     */
    public function getRate(string $currency): float {
        $currency = mb_strtoupper($currency);

        // If rate already available - just return it.
        if (array_key_exists($currency, $this->cachedData)) {
            return $this->cachedData[$currency];
        }

        $ratesUrl = $this->buildUrl();
        if (!$ratesData = file_get_contents($ratesUrl)) {
            throw new RatesUnavailableException('Cannot get data from API');
        }

        try {
            $rates = json_decode($ratesData, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new RatesUnavailableException($exception->getMessage());
        }

        // If we have some errors in the API response, it means we can't return rates
        if (isset($rates['success']) && !$rates['success']) {
            throw new RatesUnavailableException($rates['error']['info']);
        }

        if (!isset($rates['rates']) || !array_key_exists($currency, $rates['rates'])) {
            throw new UnsupportedCurrencyException(sprintf(
                'Unsupported currency: %s',
                $currency
            ));
        }

        $this->cachedData[$currency] = floatval($rates['rates'][$currency]);
        return $this->cachedData[$currency];
    }

    /**
     * Building an URL to get currency rate
     *
     * @return string
     */
    private function buildUrl(): string {
        return sprintf(
            '%s://%s/latest?access_key=%s',
            $this->scheme,
            $this->apiUri,
            $this->accessKey,
        );
    }
}