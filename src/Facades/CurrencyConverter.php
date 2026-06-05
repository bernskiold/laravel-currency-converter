<?php

namespace Bernskiold\LaravelCurrencyConverter\Facades;

use Bernskiold\LaravelCurrencyConverter\CurrencyConverter as CurrencyConverterService;
use Bernskiold\LaravelCurrencyConverter\CurrencyConverterFake;
use Illuminate\Support\Facades\Facade;

/**
 * @method static float convert(float $value, string $from, string $to, ?string $driver = null)
 * @method static float toBase(float $value, string $from, ?string $driver = null)
 * @method static float fromBase(float $value, string $to, ?string $driver = null)
 * @method static float rate(string $from, string $to, ?string $driver = null)
 * @method static string baseCurrency()
 * @method static string format(float $value, ?string $currency = null, ?int $decimals = null)
 * @method static \Bernskiold\LaravelCurrencyConverter\Contracts\ExchangeRateProvider driver(?string $driver = null)
 * @method static \Bernskiold\LaravelCurrencyConverter\CurrencyConverter extend(string $driver, \Closure $callback)
 *
 * @see CurrencyConverterService
 */
class CurrencyConverter extends Facade
{
    /**
     * Replace the bound converter with a fake for testing.
     *
     * @param  array<string, array<string, float|int>>  $rates
     */
    public static function fake(array $rates = [], ?string $baseCurrency = null): CurrencyConverterFake
    {
        $baseCurrency ??= (string) config('currency-converter.base_currency', 'SEK');

        static::swap($fake = new CurrencyConverterFake($rates, $baseCurrency));

        return $fake;
    }

    protected static function getFacadeAccessor(): string
    {
        return CurrencyConverterService::class;
    }
}
