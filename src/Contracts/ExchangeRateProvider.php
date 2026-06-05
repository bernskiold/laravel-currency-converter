<?php

namespace Bernskiold\LaravelCurrencyConverter\Contracts;

use Bernskiold\LaravelCurrencyConverter\Exceptions\CurrencyConversionException;

interface ExchangeRateProvider
{
    /**
     * Get the exchange rate to convert one unit of $from into $to.
     *
     * @throws CurrencyConversionException
     */
    public function getRate(string $from, string $to): float;
}
