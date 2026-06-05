<?php

use Bernskiold\LaravelCurrencyConverter\Drivers\FixerDriver;
use Bernskiold\LaravelCurrencyConverter\Exceptions\CurrencyConversionException;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\Facades\Http;

function fixer(): FixerDriver
{
    return new FixerDriver(app(HttpFactory::class), [
        'base_url' => 'https://data.fixer.io',
        'access_key' => 'test-key',
    ]);
}

it('fetches the rate using the access key', function () {
    Http::fake([
        'data.fixer.io/*' => Http::response(['success' => true, 'rates' => ['SEK' => 11.4]]),
    ]);

    expect(fixer()->getRate('EUR', 'SEK'))->toBe(11.4);

    Http::assertSent(fn ($request) => str_contains($request->url(), 'access_key=test-key'));
});

it('throws when the api reports failure', function () {
    Http::fake([
        'data.fixer.io/*' => Http::response(['success' => false]),
    ]);

    fixer()->getRate('EUR', 'SEK');
})->throws(CurrencyConversionException::class);

it('throws when no access key is configured', function () {
    (new FixerDriver(app(HttpFactory::class), ['access_key' => null]))
        ->getRate('EUR', 'SEK');
})->throws(CurrencyConversionException::class);
