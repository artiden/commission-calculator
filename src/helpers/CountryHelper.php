<?php

namespace artiden\exchange\helpers;

class CountryHelper {
    public static function isEu(string $countryCode): bool {
        $euCountryCodes = [
            'AT',
            'BE',
            'BG',
            'CY',
            'CZ',
            'DE',
            'DK',
            'EE',
            'ES',
            'FI',
            'FR',
            'GR',
            'HR',
            'HU',
            'IE',
            'IT',
            'LT',
            'LU',
            'LV',
            'MT',
            'NL',
            'PO',
            'PT',
            'RO',
            'SE',
            'SI',
            'SK',
        ];

        return in_array(mb_strtoupper($countryCode), $euCountryCodes);
    }
}
