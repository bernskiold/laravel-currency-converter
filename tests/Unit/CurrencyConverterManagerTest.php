<?php

use Bernskiold\LaravelCurrencyConverter\Contracts\ExchangeRateProvider;
use Bernskiold\LaravelCurrencyConverter\CurrencyConverterManager;
use Bernskiold\LaravelCurrencyConverter\Drivers\DatabaseDriver;
use Bernskiold\LaravelCurrencyConverter\Drivers\ExchangeRateApiDriver;
use Bernskiold\LaravelCurrencyConverter\Drivers\ExchangeRateHostDriver;
use Bernskiold\LaravelCurrencyConverter\Drivers\FixedRateDriver;
use Bernskiold\LaravelCurrencyConverter\Drivers\FixerDriver;
use Bernskiold\LaravelCurrencyConverter\Drivers\FrankfurterDriver;
use Bernskiold\LaravelCurrencyConverter\Drivers\OpenExchangeRatesDriver;

it('resolves every configured driver name to its provider', function (string $name, string $class) {
    $provider = app(CurrencyConverterManager::class)->driver($name);

    expect($provider)->toBeInstanceOf(ExchangeRateProvider::class)
        ->and($provider)->toBeInstanceOf($class);
})->with([
    'frankfurter' => ['frankfurter', FrankfurterDriver::class],
    'exchangerate_host' => ['exchangerate_host', ExchangeRateHostDriver::class],
    'exchangerate_api' => ['exchangerate_api', ExchangeRateApiDriver::class],
    'open_exchange_rates' => ['open_exchange_rates', OpenExchangeRatesDriver::class],
    'fixer' => ['fixer', FixerDriver::class],
    'database' => ['database', DatabaseDriver::class],
    'fixed' => ['fixed', FixedRateDriver::class],
]);

it('uses the configured default driver', function () {
    config()->set('currency-converter.default', 'fixed');

    expect(app(CurrencyConverterManager::class)->driver())->toBeInstanceOf(FixedRateDriver::class);
});
