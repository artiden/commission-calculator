<?php

namespace artiden\exchange\providers\rate;

interface RateProviderInterface {
    /**
     * Returns rate for given currency
     *
     * @param string $currency
     *
     * @return float
     */
    public function getRate(string $currency): float;
}
