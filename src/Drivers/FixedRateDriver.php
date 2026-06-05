<?php

namespace Bernskiold\LaravelCurrencyConverter\Drivers;

use Bernskiold\LaravelCurrencyConverter\Contracts\ExchangeRateProvider;
use Bernskiold\LaravelCurrencyConverter\Exceptions\CurrencyConversionException;

class FixedRateDriver implements ExchangeRateProvider
{
    /**
     * @param  array<string, array<string, float|int>>  $rates
     */
    public function __construct(
        protected array $rates = [],
    ) {}

    public function getRate(string $from, string $to): float
    {
        $rate = $this->rates[$from][$to] ?? null;

        if (! is_numeric($rate)) {
            throw CurrencyConversionException::missingRate('fixed', $from, $to);
        }

        return (float) $rate;
    }
}
