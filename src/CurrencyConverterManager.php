<?php

namespace Bernskiold\LaravelCurrencyConverter;

use Bernskiold\LaravelCurrencyConverter\Contracts\ExchangeRateProvider;
use Bernskiold\LaravelCurrencyConverter\Drivers\ExchangeRateHostDriver;
use Bernskiold\LaravelCurrencyConverter\Drivers\FixedRateDriver;
use Bernskiold\LaravelCurrencyConverter\Drivers\FrankfurterDriver;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\Manager;

class CurrencyConverterManager extends Manager
{
    public function getDefaultDriver(): string
    {
        return (string) $this->config->get('currency-converter.default', 'frankfurter');
    }

    protected function createFrankfurterDriver(): ExchangeRateProvider
    {
        return new FrankfurterDriver(
            $this->container->make(HttpFactory::class),
            $this->driverConfig('frankfurter'),
        );
    }

    protected function createExchangeRateHostDriver(): ExchangeRateProvider
    {
        return new ExchangeRateHostDriver(
            $this->container->make(HttpFactory::class),
            $this->driverConfig('exchangerate_host'),
        );
    }

    protected function createFixedDriver(): ExchangeRateProvider
    {
        return new FixedRateDriver(
            $this->driverConfig('fixed')['rates'] ?? [],
        );
    }

    /**
     * @return array<string, mixed>
     */
    protected function driverConfig(string $driver): array
    {
        return $this->config->get("currency-converter.drivers.{$driver}", []);
    }
}
