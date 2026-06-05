<?php

use Bernskiold\LaravelCurrencyConverter\Drivers\OpenExchangeRatesDriver;
use Bernskiold\LaravelCurrencyConverter\Exceptions\CurrencyConversionException;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\Facades\Http;

it('fetches the rate using the app id', function () {
    Http::fake([
        'openexchangerates.org/*' => Http::response(['rates' => ['SEK' => 10.25]]),
    ]);

    $driver = new OpenExchangeRatesDriver(app(HttpFactory::class), [
        'base_url' => 'https://openexchangerates.org',
        'app_id' => 'test-app-id',
    ]);

    expect($driver->getRate('USD', 'SEK'))->toBe(10.25);

    Http::assertSent(fn ($request) => str_contains($request->url(), 'app_id=test-app-id'));
});

it('throws when no app id is configured', function () {
    (new OpenExchangeRatesDriver(app(HttpFactory::class), ['app_id' => null]))
        ->getRate('USD', 'SEK');
})->throws(CurrencyConversionException::class);
