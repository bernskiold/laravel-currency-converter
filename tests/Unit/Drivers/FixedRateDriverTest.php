<?php

use Bernskiold\LaravelCurrencyConverter\Drivers\FixedRateDriver;
use Bernskiold\LaravelCurrencyConverter\Exceptions\CurrencyConversionException;

it('returns a configured static rate', function () {
    $driver = new FixedRateDriver(['USD' => ['SEK' => 10.5]]);

    expect($driver->getRate('USD', 'SEK'))->toBe(10.5);
});

it('throws when no rate is configured for the pair', function () {
    $driver = new FixedRateDriver(['USD' => ['SEK' => 10.5]]);

    $driver->getRate('EUR', 'SEK');
})->throws(CurrencyConversionException::class);
