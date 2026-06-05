<?php

use Bernskiold\LaravelCurrencyConverter\Contracts\ExchangeRateProvider;
use Bernskiold\LaravelCurrencyConverter\Facades\CurrencyConverter;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Cache::flush();
    config()->set('currency-converter.default', 'frankfurter');
});

it('returns a rate of 1 for the same currency without calling a driver', function () {
    Http::fake();

    expect(CurrencyConverter::rate('SEK', 'SEK'))->toBe(1.0);

    Http::assertNothingSent();
});

it('converts using the fetched rate and preserves decimals', function () {
    Http::fake([
        'api.frankfurter.dev/*' => Http::response(['rates' => ['SEK' => 10.5]]),
    ]);

    expect(CurrencyConverter::convert(1234.56, 'USD', 'SEK'))->toBe(12962.88);
});

it('normalises currency casing', function () {
    Http::fake([
        'api.frankfurter.dev/*' => Http::response(['rates' => ['SEK' => 10.0]]),
    ]);

    expect(CurrencyConverter::rate('usd', 'sek'))->toBe(10.0);
});

it('caches the rate so the driver is only called once', function () {
    Http::fake([
        'api.frankfurter.dev/*' => Http::response(['rates' => ['SEK' => 10.0]]),
    ]);

    CurrencyConverter::rate('USD', 'SEK');
    CurrencyConverter::rate('USD', 'SEK');

    Http::assertSentCount(1);
});

it('does not cache when the ttl is disabled', function () {
    config()->set('currency-converter.cache.ttl', 0);

    Http::fake([
        'api.frankfurter.dev/*' => Http::response(['rates' => ['SEK' => 10.0]]),
    ]);

    CurrencyConverter::rate('USD', 'SEK');
    CurrencyConverter::rate('USD', 'SEK');

    Http::assertSentCount(2);
});

it('converts into the configured base currency', function () {
    config()->set('currency-converter.base_currency', 'SEK');

    Http::fake([
        'api.frankfurter.dev/*' => Http::response(['rates' => ['SEK' => 9.0]]),
    ]);

    expect(CurrencyConverter::toBase(100, 'USD'))->toBe(900.0);
});

it('can use a specific driver for a single call', function () {
    config()->set('currency-converter.drivers.fixed.rates', ['USD' => ['SEK' => 12.0]]);

    Http::fake();

    expect(CurrencyConverter::convert(100, 'USD', 'SEK', driver: 'fixed'))->toBe(1200.0);

    Http::assertNothingSent();
});

it('supports registering a custom driver', function () {
    CurrencyConverter::extend('static-double', fn () => new class implements ExchangeRateProvider
    {
        public function getRate(string $from, string $to): float
        {
            return 2.0;
        }
    });

    expect(CurrencyConverter::convert(50, 'USD', 'SEK', driver: 'static-double'))->toBe(100.0);
});
