<?php

namespace artiden\exchange\providers\bin;

use artiden\exchange\providers\bin\BinProviderInterface;
use artiden\exchange\providers\bin\exceptions\BinDataUnavailableException;

class BinlistProvider implements BinProviderInterface {
    /**
     * URL to fetch data from
     *
     * @var string
     */
    private string $serviceUrl;

    /**
     * Array to store data after fetch. To avoid second fetching, if requested data available already
     *
     * @var array
     */
    private array $cachedData;

    /**
     * Class constructor
     */
    public function __construct() {
        $this->cachedData = [];

        // In general, we could make this class more general and pass an URL.
        // But for this example, as for me, it be OK as is.
        $this->serviceUrl = 'https://lookup.binlist.net/';
    }

    /**
     * {@inheritDoc}
     */
    public function getBinData(string $bin): object {
        // Check if data already available - just return it
        if (array_key_exists($bin, $this->cachedData)) {
            return $this->cachedData[$bin];
        }

        $url = sprintf(
            '%s%s',
            $this->serviceUrl,
            $bin
        );

        $fileContent = @file_get_contents($url);
        if ($fileContent === false) {
            throw new BinDataUnavailableException('Unable to get bin data: URL content unavailable');
        }

        try {
            $this->cachedData[$bin] = json_decode($fileContent, null, 512, JSON_THROW_ON_ERROR);
            return $this->cachedData[$bin];
        } catch (\JsonException $exception) {
            throw new BinDataUnavailableException($exception->getMessage());
        }
    }
}
