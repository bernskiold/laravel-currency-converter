<?php

namespace Bernskiold\LaravelCurrencyConverter;

use Bernskiold\LaravelCurrencyConverter\Contracts\ExchangeRateProvider;
use Bernskiold\LaravelCurrencyConverter\Drivers\DatabaseDriver;
use Bernskiold\LaravelCurrencyConverter\Drivers\ExchangeRateApiDriver;
use Bernskiold\LaravelCurrencyConverter\Drivers\ExchangeRateHostDriver;
use Bernskiold\LaravelCurrencyConverter\Drivers\FixedRateDriver;
use Bernskiold\LaravelCurrencyConverter\Drivers\FixerDriver;
use Bernskiold\LaravelCurrencyConverter\Drivers\FrankfurterDriver;
use Bernskiold\LaravelCurrencyConverter\Drivers\OpenExchangeRatesDriver;
use Illuminate\Database\ConnectionResolverInterface;
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

    protected function createExchangerateHostDriver(): ExchangeRateProvider
    {
        return new ExchangeRateHostDriver(
            $this->container->make(HttpFactory::class),
            $this->driverConfig('exchangerate_host'),
        );
    }

    protected function createExchangerateApiDriver(): ExchangeRateProvider
    {
        return new ExchangeRateApiDriver(
            $this->container->make(HttpFactory::class),
            $this->driverConfig('exchangerate_api'),
        );
    }

    protected function createOpenExchangeRatesDriver(): ExchangeRateProvider
    {
        return new OpenExchangeRatesDriver(
            $this->container->make(HttpFactory::class),
            $this->driverConfig('open_exchange_rates'),
        );
    }

    protected function createFixerDriver(): ExchangeRateProvider
    {
        return new FixerDriver(
            $this->container->make(HttpFactory::class),
            $this->driverConfig('fixer'),
        );
    }

    protected function createDatabaseDriver(): ExchangeRateProvider
    {
        return new DatabaseDriver(
            $this->container->make(ConnectionResolverInterface::class),
            $this->driverConfig('database'),
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
