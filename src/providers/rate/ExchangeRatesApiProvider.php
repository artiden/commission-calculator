<?php

namespace artiden\exchange\providers\rate;

use artiden\exchange\providers\rate\exceptions\RatesUnavailableException;
use artiden\exchange\providers\rate\exceptions\UnsupportedCurrencyException;
use artiden\exchange\providers\rate\RateProviderInterface;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Client\ClientInterface;

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
     * @param string $serviceUrl
     * @param string $accessKey
     * @param bool $secureProtocol
     */
    public function __construct(
        // We able to use that form from latest PHP versions... It means we shouldn't define those properties in the class it self
        protected ClientInterface $httpClient,
        protected string          $serviceUrl,
        protected string          $accessKey,
        protected bool $secureProtocol = false
    ) {
        $this->scheme = 'https';
        if (!$this->secureProtocol) {
            $this->scheme = 'http';
        }

        $this->cachedData = [];
    }

    /**
     * {@inheritDoc}
     */
    public function getRate(string $currency): float {
        $currency = mb_strtoupper($currency);

        // Should we get data from a remote server?
        if (!array_key_exists($currency, $this->cachedData)) {
            $url = $this->buildUrl();
            $request = new Request(
                'GET',
                $url,
                $this->prepareHeaders()
            );
            $response = $this->httpClient->sendRequest($request);
            $statusCode = $response->getStatusCode();
            if ($statusCode !== 200) {
                throw new RatesUnavailableException(sprintf(
                    'Unable to get rates. Status code: %d, Message: %s',
                    $statusCode,
                    $response->getReasonPhrase()
                ));
            }

            try {
                $rates = json_decode($response->getBody(), true, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException $exception) {
                throw new RatesUnavailableException($exception->getMessage());
            }

            if (!isset($rates['rates']) || !array_key_exists($currency, $rates['rates'])) {
                throw new UnsupportedCurrencyException(sprintf(
                    'Unsupported currency: %s',
                    $currency
                ));
            }

            $this->cachedData = $rates['rates'];
        }

        return floatval($this->cachedData[$currency]);
    }

    /**
     * Builds an URL to get currency rate
     *
     * @return string
     */
    protected function buildUrl(): string {
        return sprintf(
            '%s://%s/latest?access_key=%s',
            $this->scheme,
            $this->serviceUrl,
            $this->accessKey,
        );
    }

    /**
     * Prepare required headers. Authorization, for example
     *
     * @return array
     */
    protected function prepareHeaders(): array {
        return [];
    }
}