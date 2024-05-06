<?php
namespace artiden\exchange\providers\bin;

use artiden\exchange\providers\bin\exceptions\BinDataUnavailableException;

interface BinProviderInterface {
    /**
     * Returns data for given card leading digits
     *
     * @param string $bin
     *
     * @return object
     *
     * @throws BinDataUnavailableException
     */
    public function getBinData(string $bin): object;
}
