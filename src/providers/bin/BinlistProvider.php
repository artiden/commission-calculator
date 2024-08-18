<?php

namespace artiden\exchange\providers\bin;

use artiden\exchange\providers\bin\BinProviderInterface;
use artiden\exchange\providers\bin\exceptions\BinDataUnavailableException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\RequestOptions;
use Psr\Http\Client\ClientInterface;

class   BinlistProvider implements BinProviderInterface {
    /**
     * Array to store data after fetch. To avoid second fetching, if requested data available already
     *
     * @var array
     */
    private array $cachedData;

    /**
     * Class constructor
     *
     * @param ClientInterface $httpClient - Http client to perform a request
     * @param string$serviceUrl - Base service URL to fetch data from
     */
    public function __construct(
        protected ClientInterface $httpClient,
        protected string $serviceUrl
    ) {
        if (!filter_var($serviceUrl, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException('Base service URL should be a valid URL');
        }

        $this->cachedData = [];
    }

    /**
     * {@inheritDoc}
     */
    public function getBinData(string $bin): object {
        // Do we need to get data from a remote server?
        if (!array_key_exists($bin, $this->cachedData)) {
            $url = sprintf(
                '%s%s',
                $this->serviceUrl,
                $bin
            );
            $request = new Request(
                'GET',
                $url,
                $this->prepareHeaders()
            );

            $response = $this->httpClient->sendRequest($request);
            $statusCode = $response->getStatusCode();
            if ($statusCode !== 200) {
                throw new BinDataUnavailableException(sprintf(
                    'Unable to get bin data: Status code: %d, Message: %s',
                    $statusCode,
                    $response->getReasonPhrase()
                ));
            }

            try {
                $this->cachedData[$bin] = json_decode($response->getBody(), null, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException $exception) {
                throw new BinDataUnavailableException($exception->getMessage());
            }
        }

        return $this->cachedData[$bin];
    }

    /**
     * Here we can prepare required headers. For authorization, for example
     *
     * @return array
     */
    protected function prepareHeaders(): array {
        return [];
    }
}
