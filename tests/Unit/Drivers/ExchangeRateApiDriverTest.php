<?php

use Bernskiold\LaravelCurrencyConverter\Drivers\ExchangeRateApiDriver;
use Bernskiold\LaravelCurrencyConverter\Exceptions\CurrencyConversionException;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\Facades\Http;

it('uses the authenticated v6 endpoint when an api key is present', function () {
    Http::fake([
        'v6.exchangerate-api.com/*' => Http::response(['result' => 'success', 'conversion_rate' => 10.5]),
    ]);

    $driver = new ExchangeRateApiDriver(app(HttpFactory::class), [
        'base_url' => 'https://v6.exchangerate-api.com',
        'api_key' => 'secret',
    ]);

    expect($driver->getRate('USD', 'SEK'))->toBe(10.5);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/v6/secret/pair/USD/SEK'));
});

it('falls back to the keyless open endpoint without an api key', function () {
    Http::fake([
        'open.er-api.com/*' => Http::response(['result' => 'success', 'rates' => ['SEK' => 9.9]]),
    ]);

    $driver = new ExchangeRateApiDriver(app(HttpFactory::class), [
        'open_base_url' => 'https://open.er-api.com',
        'api_key' => null,
    ]);

    expect($driver->getRate('USD', 'SEK'))->toBe(9.9);

    Http::assertSent(fn ($request) => str_contains($request->url(), 'open.er-api.com/v6/latest/USD'));
});

it('throws when the rate is missing', function () {
    Http::fake([
        'open.er-api.com/*' => Http::response(['result' => 'success', 'rates' => []]),
    ]);

    (new ExchangeRateApiDriver(app(HttpFactory::class), ['open_base_url' => 'https://open.er-api.com']))
        ->getRate('USD', 'SEK');
})->throws(CurrencyConversionException::class);
