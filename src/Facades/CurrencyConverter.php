<?php

namespace Bernskiold\LaravelCurrencyConverter\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static float convert(float $value, string $from, string $to, ?string $driver = null)
 * @method static float toBase(float $value, string $from, ?string $driver = null)
 * @method static float rate(string $from, string $to, ?string $driver = null)
 * @method static string baseCurrency()
 * @method static \Bernskiold\LaravelCurrencyConverter\Contracts\ExchangeRateProvider driver(?string $driver = null)
 * @method static \Bernskiold\LaravelCurrencyConverter\CurrencyConverter extend(string $driver, \Closure $callback)
 *
 * @see \Bernskiold\LaravelCurrencyConverter\CurrencyConverter
 */
class CurrencyConverter extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Bernskiold\LaravelCurrencyConverter\CurrencyConverter::class;
    }
}
