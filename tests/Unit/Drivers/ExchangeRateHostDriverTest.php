<?php

use Bernskiold\LaravelCurrencyConverter\Drivers\ExchangeRateHostDriver;
use Bernskiold\LaravelCurrencyConverter\Exceptions\CurrencyConversionException;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\Facades\Http;

it('fetches the rate using the access key', function () {
    Http::fake([
        'api.exchangerate.host/*' => Http::response(['result' => 11.1]),
    ]);

    $driver = new ExchangeRateHostDriver(app(HttpFactory::class), [
        'base_url' => 'https://api.exchangerate.host',
        'access_key' => 'test-key',
    ]);

    expect($driver->getRate('USD', 'SEK'))->toBe(11.1);

    Http::assertSent(fn ($request) => str_contains($request->url(), 'access_key=test-key'));
});

it('throws when no access key is configured', function () {
    $driver = new ExchangeRateHostDriver(app(HttpFactory::class), [
        'base_url' => 'https://api.exchangerate.host',
        'access_key' => null,
    ]);

    $driver->getRate('USD', 'SEK');
})->throws(CurrencyConversionException::class);
