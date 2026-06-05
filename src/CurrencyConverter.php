<?php

namespace Bernskiold\LaravelCurrencyConverter;

use Bernskiold\LaravelCurrencyConverter\Contracts\ExchangeRateProvider;
use Illuminate\Contracts\Cache\Factory as CacheFactory;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Contracts\Config\Repository as ConfigRepository;

class CurrencyConverter
{
    public function __construct(
        protected CurrencyConverterManager $manager,
        protected CacheFactory $cache,
        protected ConfigRepository $config,
    ) {}

    /**
     * Convert a value from one currency to another, rounded to the configured decimals.
     *
     * @throws Exceptions\CurrencyConversionException
     */
    public function convert(float $value, string $from, string $to, ?string $driver = null): float
    {
        $decimals = (int) $this->config->get('currency-converter.decimals', 2);

        return round($value * $this->rate($from, $to, $driver), $decimals);
    }

    /**
     * Convert a value into the application's configured base currency.
     *
     * @throws Exceptions\CurrencyConversionException
     */
    public function toBase(float $value, string $from, ?string $driver = null): float
    {
        return $this->convert($value, $from, $this->baseCurrency(), $driver);
    }

    /**
     * Convert a value from the application's configured base currency into another currency.
     *
     * @throws Exceptions\CurrencyConversionException
     */
    public function fromBase(float $value, string $to, ?string $driver = null): float
    {
        return $this->convert($value, $this->baseCurrency(), $to, $driver);
    }

    /**
     * Get the (cached) exchange rate between two currencies.
     *
     * @throws Exceptions\CurrencyConversionException
     */
    public function rate(string $from, string $to, ?string $driver = null): float
    {
        $from = strtoupper($from);
        $to = strtoupper($to);

        if ($from === $to) {
            return 1.0;
        }

        $driver ??= $this->manager->getDefaultDriver();

        $ttl = $this->config->get('currency-converter.cache.ttl');

        if (empty($ttl)) {
            return $this->fetchRate($driver, $from, $to);
        }

        return $this->cacheStore()->remember(
            $this->cacheKey($driver, $from, $to),
            (int) $ttl,
            fn (): float => $this->fetchRate($driver, $from, $to),
        );
    }

    public function baseCurrency(): string
    {
        return strtoupper((string) $this->config->get('currency-converter.base_currency', 'SEK'));
    }

    /**
     * Format an amount for display using the configured number formatting,
     * optionally suffixed with a currency code (e.g. "1,234.56 USD").
     */
    public function format(float $value, ?string $currency = null, ?int $decimals = null): string
    {
        $formatting = $this->config->get('currency-converter.formatting', []);

        $number = number_format(
            $value,
            $decimals ?? (int) ($formatting['decimals'] ?? 2),
            $formatting['decimal_separator'] ?? '.',
            $formatting['thousands_separator'] ?? ',',
        );

        return $currency ? "{$number} {$currency}" : $number;
    }

    /**
     * Get the underlying provider for the given (or default) driver.
     */
    public function driver(?string $driver = null): ExchangeRateProvider
    {
        return $this->manager->driver($driver);
    }

    /**
     * Register a custom driver resolver.
     */
    public function extend(string $driver, \Closure $callback): static
    {
        $this->manager->extend($driver, $callback);

        return $this;
    }

    protected function fetchRate(string $driver, string $from, string $to): float
    {
        return $this->manager->driver($driver)->getRate($from, $to);
    }

    protected function cacheStore(): CacheRepository
    {
        return $this->cache->store($this->config->get('currency-converter.cache.store'));
    }

    protected function cacheKey(string $driver, string $from, string $to): string
    {
        $prefix = (string) $this->config->get('currency-converter.cache.prefix', 'currency_converter');

        return "{$prefix}:{$driver}:{$from}:{$to}";
    }
}
