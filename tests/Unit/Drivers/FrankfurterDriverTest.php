<?php

use Bernskiold\LaravelCurrencyConverter\Drivers\FrankfurterDriver;
use Bernskiold\LaravelCurrencyConverter\Exceptions\CurrencyConversionException;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\Facades\Http;

function frankfurter(): FrankfurterDriver
{
    return new FrankfurterDriver(app(HttpFactory::class), ['base_url' => 'https://api.frankfurter.dev']);
}

it('fetches the rate for a currency pair', function () {
    Http::fake([
        'api.frankfurter.dev/*' => Http::response(['rates' => ['SEK' => 10.42]]),
    ]);

    expect(frankfurter()->getRate('USD', 'SEK'))->toBe(10.42);
});

it('throws when the request fails', function () {
    Http::fake([
        'api.frankfurter.dev/*' => Http::response(null, 500),
    ]);

    frankfurter()->getRate('USD', 'SEK');
})->throws(CurrencyConversionException::class);

it('throws when the rate is missing from the response', function () {
    Http::fake([
        'api.frankfurter.dev/*' => Http::response(['rates' => []]),
    ]);

    frankfurter()->getRate('USD', 'SEK');
})->throws(CurrencyConversionException::class);
